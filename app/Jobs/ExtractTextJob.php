<?php

namespace App\Jobs;

use App\Models\KnowledgeSource;
use App\Services\TextExtractor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ExtractTextJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public KnowledgeSource $source) {}

    public function handle(TextExtractor $extractor)
    {
        \App\Services\TenantContext::set(tenantId: $this->source->tenant_id);
        $this->source->update(['status' => 'extracting']);

        try {
            $path = Storage::path($this->source->path);
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            
            $text = $extractor->extract($path, $extension);
            
            $this->source->update([
                'content' => $text,
                'status' => 'extracted'
            ]);

            ChunkAndEmbedJob::dispatch($this->source);
        } catch (\Exception $e) {
            $this->source->update(['status' => 'failed', 'metadata' => array_merge($this->source->metadata ?? [], ['error' => $e->getMessage()])]);
        }
    }
}
