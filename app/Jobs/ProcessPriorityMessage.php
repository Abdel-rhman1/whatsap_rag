<?php

namespace App\Jobs;

use App\Models\PriorityMessageQueue;
use App\Services\WhatsAppProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPriorityMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected PriorityMessageQueue $queueEntry) {}

    public function handle(WhatsAppProcessingService $service)
    {
        \App\Services\TenantContext::set(tenantId: $this->queueEntry->tenant_id);
        $service->process($this->queueEntry);
    }
}
