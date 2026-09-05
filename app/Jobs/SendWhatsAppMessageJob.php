<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use App\Services\TenantContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 60;

    public function __construct(
        public string $instanceId,
        public string $to,
        public string $message,
        public ?int $tenantId = null
    ) {}

    public function handle(WhatsAppService $whatsAppService)
    {
        if ($this->tenantId) {
            TenantContext::set(tenantId: $this->tenantId);
        }

        $whatsAppService->sendMessage($this->instanceId, $this->to, $this->message);
    }
}
