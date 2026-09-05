<?php

namespace App\Jobs;

use App\Models\KnowledgeSource;
use App\Services\QdrantService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Exception;

class UpsertToQdrantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $backoff = [10, 60, 300, 600, 1200];

    public function __construct(public KnowledgeSource $source, public array $points) {}

    public function handle(QdrantService $qdrant)
    {
        \App\Services\TenantContext::set(tenantId: $this->source->tenant_id);
        $this->source->update(['status' => 'indexing']);

        try {
            $tenant = $this->source->tenant ?? $this->source->load('tenant')->tenant;
            $collectionName = $tenant->qdrant_collection;

            $qdrant->ensureCollection($collectionName);
            $qdrant->upsert($tenant->id, $this->points, $collectionName);

            $this->source->update(['status' => 'indexed']);
        } catch (Exception $e) {
            if ($this->attempts() >= $this->tries) {
                $this->source->update([
                    'status' => 'failed', 
                    'metadata' => array_merge($this->source->metadata ?? [], ['error' => $e->getMessage()])
                ]);
            }
            throw $e;
        }
    }
}
