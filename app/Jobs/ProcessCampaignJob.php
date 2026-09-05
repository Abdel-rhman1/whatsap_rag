<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Services\TenantContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $campaignId) {}

    public function handle(): void
    {
        $campaign = Campaign::withoutGlobalScopes()->find($this->campaignId);
        if (!$campaign || $campaign->status !== 'running') {
            return;
        }

        TenantContext::set(tenantId: $campaign->tenant_id);

        $recipients = $campaign->recipients()
            ->where('status', 'pending')
            ->limit(50)
            ->get();

        if ($recipients->isEmpty()) {
            $inFlight = $campaign->recipients()->whereIn('status', ['pending'])->count();
            if ($inFlight === 0) {
                $campaign->update(['status' => 'completed']);
            }
            return;
        }

        foreach ($recipients as $index => $recipient) {
            $delay = ($index + 1) * 5;

            SendCampaignMessageJob::dispatch($campaign->id, $recipient->id)
                ->delay(now()->addSeconds($delay));
        }

        $batchDelay = count($recipients) * 5 + 2;
        ProcessCampaignJob::dispatch($campaign->id)
            ->delay(now()->addSeconds($batchDelay));
    }
}
