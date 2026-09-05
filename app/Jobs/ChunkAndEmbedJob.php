<?php

namespace App\Jobs;

use App\Models\KnowledgeSource;
use App\Models\KnowledgeChunk;
use App\Services\Chunker;
use App\Services\EmbeddingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Exception;

class ChunkAndEmbedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 600];

    public function __construct(public KnowledgeSource $source) {}

    public function handle(Chunker $chunker, EmbeddingService $embeddingService)
    {
        \App\Services\TenantContext::set(tenantId: $this->source->tenant_id);
        $this->source->update(['status' => 'chunking']);

        try {
            $chunks = $chunker->chunk($this->source->content);
            $maxChunks = config('rag.guardrails.max_chunks_per_source', 1000);
            
            if (count($chunks) > $maxChunks) {
                $chunks = array_slice($chunks, 0, $maxChunks);
            }

            $points = [];

            foreach ($chunks as $index => $content) {
                $vector = $embeddingService->embed($content);
                $vectorId = (string) Str::uuid();

                $chunk = KnowledgeChunk::create([
                    'tenant_id'           => $this->source->tenant_id,
                    'knowledge_base_id'   => $this->source->knowledge_base_id,
                    'knowledge_source_id' => $this->source->id,
                    'content'             => $content,
                    'vector_id'           => $vectorId,
                    'metadata'            => [
                        'index'             => $index,
                        'char_count'        => strlen($content),
                        'knowledge_base_id' => $this->source->knowledge_base_id,
                    ]
                ]);

                $points[] = [
                    'id'      => $vectorId,
                    'vector'  => $vector,
                    'payload' => [
                        'tenant_id'         => (int) $this->source->tenant_id,
                        'knowledge_base_id' => (int) $this->source->knowledge_base_id,
                        'source_id'         => (int) $this->source->id,
                        'source_name'       => $this->source->name,
                        'chunk_id'          => (int) $chunk->id,
                        'content'           => $content,
                    ]
                ];
            }

            UpsertToQdrantJob::dispatch($this->source, $points);
            
            $this->source->update(['status' => 'embedding_queued']);

        } catch (Exception $e) {
            $this->source->update([
                'status'   => 'failed', 
                'metadata' => array_merge($this->source->metadata ?? [], ['error' => $e->getMessage()])
            ]);
            throw $e;
        }
    }
}
