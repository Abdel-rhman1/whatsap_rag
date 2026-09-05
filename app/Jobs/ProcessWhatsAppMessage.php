<?php

namespace App\Jobs;

use App\Services\WhatsAppProcessingService;
use App\Services\TenantContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [5, 15, 30];

    public function __construct(
        protected array $payload,
        protected int $tenantId
    ) {}

    public function handle(WhatsAppProcessingService $service): void
    {
        TenantContext::set(tenantId: $this->tenantId);
        $service->processPayload($this->payload, $this->tenantId);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessWhatsAppMessage job failed", [
            'tenant_id' => $this->tenantId,
            'from'      => $this->payload['from'] ?? 'unknown',
            'error'     => $exception->getMessage(),
        ]);
    }
}
