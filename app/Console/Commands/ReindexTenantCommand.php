<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\KnowledgeSource;
use App\Jobs\ExtractTextJob;
use Illuminate\Console\Command;

class ReindexTenantCommand extends Command
{
    protected $signature = 'rag:reindex {tenant_id?}';
    protected $description = 'Wipe and re-extract knowledge for a tenant (or all)';

    public function handle()
    {
        $tenantId = $this->argument('tenant_id');

        $query = KnowledgeSource::query();
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $sources = $query->get();

        if ($sources->isEmpty()) {
            $this->info("No sources found to reindex.");
            return;
        }

        if (!$this->confirm("Found " . $sources->count() . " sources. Do you want to restart the full extraction/indexing pipeline?")) {
            return;
        }

        foreach ($sources as $source) {
            $this->info("Re-dispatching: {$source->name} (Tenant: {$source->tenant_id})");
            // Status stays as is or reset to pending
            $source->update(['status' => 'pending']);
            ExtractTextJob::dispatch($source);
        }

        $this->info("All jobs dispatched to queue.");
    }
}
