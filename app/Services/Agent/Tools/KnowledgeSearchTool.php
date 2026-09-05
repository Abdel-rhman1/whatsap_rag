<?php

namespace App\Services\Agent\Tools;

use App\Contracts\AgentToolInterface;
use App\Services\QdrantService;
use App\Services\EmbeddingService;
use App\Models\TenantKnowledgeConfig;
use App\Models\Tenant;

class KnowledgeSearchTool implements AgentToolInterface
{
    public function __construct(
        protected QdrantService $qdrant,
        protected EmbeddingService $embedding
    ) {}

    public function name(): string
    {
        return 'knowledge_search';
    }

    public function description(): string
    {
        return 'Searches tenant-isolated knowledge bases for relevant vector documents and facts.';
    }

    public function validate(array $params): bool
    {
        return !empty($params['query']);
    }

    public function execute(array $params, int $tenantId, array $context = []): array
    {
        $query = $params['query'] ?? '';
        if (empty($query)) {
            return ['hits' => [], 'context' => 'No query provided'];
        }

        $tenant  = Tenant::findOrFail($tenantId);
        $kConfig = TenantKnowledgeConfig::firstOrCreate(['tenant_id' => $tenantId]);

        $queryVector      = $this->embedding->embed($query);
        $collectionName   = $kConfig->vector_namespace ?? $tenant->qdrant_collection;
        $knowledgeBaseIds = $params['knowledge_base_ids'] ?? [];

        // Vector search strictly scoped to tenantId and knowledgeBaseIds at vector store level
        $hits = $this->qdrant->search(
            tenantId:         $tenantId,
            queryEmbedding:   $queryVector,
            topK:             config('rag.search.top_k', 5),
            collectionName:   $collectionName,
            knowledgeBaseIds: $knowledgeBaseIds
        );

        $threshold = $params['similarity_threshold'] ?? $kConfig->similarity_threshold ?? 0.35;
        $filteredHits = array_values(collect($hits)->filter(fn($hit) => $hit['score'] >= $threshold)->toArray());

        $formattedContext = "";
        foreach ($filteredHits as $hit) {
            $source = $hit['metadata']['source_name'] ?? 'Unknown';
            $formattedContext .= "[Source: {$source}] Content: {$hit['content']}\n\n";
        }

        return [
            'hits'         => $filteredHits,
            'context_text' => $formattedContext,
            'hits_count'   => count($filteredHits),
            'max_score'    => collect($filteredHits)->max('score') ?? 0.0,
        ];
    }
}
