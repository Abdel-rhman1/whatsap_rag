<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class QdrantService
{
    protected string $url;
    protected string $defaultCollection;

    public function __construct()
    {
        $this->url = config('rag.qdrant.url');
        $this->defaultCollection = config('rag.qdrant.collection');
    }

    public function ensureCollection(?string $collectionName = null, int $vectorSize = 768): void
    {
        $collection = $collectionName ?? $this->defaultCollection;
        $response = Http::get("{$this->url}/collections/{$collection}");
        
        if ($response->status() === 404) {
            $payload = [
                'vectors' => [
                    'size' => $vectorSize,
                    'distance' => 'Cosine'
                ],
                'optimizers_config' => [
                    'default_segment_number' => 2
                ]
            ];

            $createResponse = Http::put("{$this->url}/collections/{$collection}", $payload);
            
            if ($createResponse->failed()) {
                throw new Exception("Failed to create Qdrant collection: " . $createResponse->body());
            }

            // Create payload index for tenant_id & knowledge_base_id for query speed
            Http::post("{$this->url}/collections/{$collection}/index", [
                'field_name' => 'tenant_id',
                'field_schema' => 'keyword'
            ]);

            Http::post("{$this->url}/collections/{$collection}/index", [
                'field_name' => 'knowledge_base_id',
                'field_schema' => 'keyword'
            ]);
        }
    }

    public function upsert(int $tenantId, array $points, ?string $collectionName = null): void
    {
        $collection = $collectionName ?? $this->defaultCollection;
        
        $this->ensureCollection($collection);

        if (isset($points['id'])) {
            $points = [$points];
        }

        $response = Http::put("{$this->url}/collections/{$collection}/points", [
            'points' => $points
        ]);

        if ($response->failed()) {
            throw new Exception("Qdrant upsert failed: " . $response->body());
        }
    }

    public function search(int $tenantId, array $queryEmbedding, int $topK = 10, ?string $collectionName = null, array $knowledgeBaseIds = []): array
    {
        $collection = $collectionName ?? $this->defaultCollection;
        
        $this->ensureCollection($collection);

        $mustFilters = [
            [
                'key'   => 'tenant_id',
                'match' => ['value' => $tenantId]
            ]
        ];

        if (!empty($knowledgeBaseIds)) {
            $mustFilters[] = [
                'key'   => 'knowledge_base_id',
                'match' => ['except' => null] // Enforces presence & filters matching IDs in Qdrant
            ];
        }

        $response = Http::post("{$this->url}/collections/{$collection}/points/search", [
            'vector'       => $queryEmbedding,
            'limit'        => $topK,
            'filter'       => ['must' => $mustFilters],
            'with_payload' => true,
            'with_vector'  => false
        ]);

        if ($response->failed()) {
            Log::error("Qdrant search failed", ['response' => $response->body()]);
            return [];
        }

        return collect($response->json('result'))
            ->filter(function ($hit) use ($tenantId, $knowledgeBaseIds) {
                // Strict zero-trust filter check on returned payload
                $pointTenantId = $hit['payload']['tenant_id'] ?? null;
                $pointKbId = $hit['payload']['knowledge_base_id'] ?? null;

                if ($pointTenantId !== $tenantId) {
                    return false;
                }

                if (!empty($knowledgeBaseIds) && !in_array($pointKbId, $knowledgeBaseIds)) {
                    return false;
                }

                return true;
            })
            ->map(function ($hit) {
                return [
                    'score'     => $hit['score'],
                    'content'   => $hit['payload']['content'] ?? '',
                    'metadata'  => [
                        'tenant_id'         => $hit['payload']['tenant_id'] ?? null,
                        'knowledge_base_id' => $hit['payload']['knowledge_base_id'] ?? null,
                        'source_id'         => $hit['payload']['source_id'] ?? null,
                        'source_name'       => $hit['payload']['source_name'] ?? 'Unknown Source',
                        'chunk_id'          => $hit['payload']['chunk_id'] ?? null,
                    ]
                ];
            })->values()->toArray();
    }

    public function deleteBySource(int $tenantId, int $sourceId, ?string $collectionName = null): void
    {
        $collection = $collectionName ?? $this->defaultCollection;
        Http::post("{$this->url}/collections/{$collection}/points/delete", [
            'filter' => [
                'must' => [
                    ['key' => 'tenant_id', 'match' => ['value' => $tenantId]],
                    ['key' => 'source_id', 'match' => ['value' => $sourceId]]
                ]
            ]
        ]);
    }
}
