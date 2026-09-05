<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCampaignMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $backoff = [10, 30, 60];

    public function __construct(
        protected int $campaignId,
        protected int $recipientId
    ) {}

    public function handle(WhatsAppService $whatsapp): void
    {
        $campaign = Campaign::withoutGlobalScopes()->find($this->campaignId);
        if (!$campaign || $campaign->status !== 'running') {
            return;
        }

        \App\Services\TenantContext::set(tenantId: $campaign->tenant_id);

        $recipient = CampaignRecipient::find($this->recipientId);
        if (!$recipient || $recipient->status !== 'pending') {
            return;
        }

        try {
            // Personalize message
            $message = $recipient->custom_message ?: $campaign->message_template;
            $message = str_replace('{{name}}', $recipient->name ?? 'User', $message);
            $message = str_replace('{{phone}}', $recipient->phone, $message);

            // Send via WhatsApp
            $whatsapp->sendMessage(
                $campaign->whatsappInstance->instance_name,
                $recipient->phone,
                $message
            );

            // Create Message Record for Conversation persistence
            $conversation = \App\Models\Conversation::withoutGlobalScopes()->firstOrCreate([
                'tenant_id' => $campaign->tenant_id,
                'external_id' => $recipient->phone,
                'platform' => 'whatsapp',
                'device_id' => $campaign->whatsappInstance->device_id, // assuming instance has device relation or similar
            ]);

            $msgRecord = \App\Models\Message::create([
                'tenant_id' => $campaign->tenant_id,
                'conversation_id' => $conversation->id,
                'role' => 'assistant', // outgoing from system
                'source' => 'campaign', 
                'content' => $message,
                'metadata' => ['campaign_id' => $campaign->id],
            ]);

            // Track Outbound Delivery Latency
            $startTime = $campaign->scheduled_at ?: $campaign->created_at;
            $latencyMs = now()->diffInMilliseconds($startTime);

            // Update recipient status
            $recipient->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // Update campaign counts
            $campaign->increment('sent_count');

        } catch (\Exception $e) {
            Log::warning("Campaign Message Attempt Failed", [
                'campaign_id' => $this->campaignId,
                'recipient_id' => $this->recipientId,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage()
            ]);

            // If we have more tries, throw the exception to trigger the queue retry/backoff
            if ($this->attempts() < $this->tries) {
                throw $e;
            }

            // If we've exhausted all tries, the 'failed' method below will be called
            // Or we handle it here if not using the 'failed' method
            $this->markAsFailed($recipient, $campaign, $e->getMessage());
        }

        // Check if campaign is finished
        $remaining = $campaign->recipients()->where('status', 'pending')->count();
        if ($remaining === 0) {
            $campaign->update(['status' => 'completed']);
        }
    }

    /**
     * Handle final failure of the job.
     */
    public function failed(\Throwable $exception): void
    {
        $campaign = Campaign::find($this->campaignId);
        $recipient = CampaignRecipient::find($this->recipientId);
        
        if ($recipient && $campaign) {
            $this->markAsFailed($recipient, $campaign, $exception->getMessage());
        }
    }

    protected function markAsFailed($recipient, $campaign, $errorMessage): void
    {
        if ($recipient->status !== 'failed') {
            $recipient->update([
                'status' => 'failed',
                'error_reason' => $errorMessage,
            ]);

            $campaign->increment('failed_count');
        }
    }
}
