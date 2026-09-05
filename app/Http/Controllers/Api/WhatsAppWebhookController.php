<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\WhatsAppService;
use App\Services\WhatsAppProviderFactory;
use App\Services\TenantContext;
use App\Jobs\ProcessWhatsAppMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsapp
    ) {}

    /**
     * Handle generic multi-provider incoming webhook.
     *
     * Returns 200 immediately and dispatches heavy processing
     * (RAG, LLM, response delivery) to a background job so the
     * gateway never times out.
     */
    public function handle(Request $request, ?string $provider = 'baileys')
    {
        try {
            // Determine provider driver
            $driver = $request->route('provider') ?? $provider;
            $providerImpl = WhatsAppProviderFactory::makeByDriver($driver);

            // Parse webhook into unified payload DTO
            $webhookPayload = $providerImpl->handleWebhook($request);

            if (!$webhookPayload) {
                // Meta verification challenge
                if ($request->isMethod('GET') && $request->has('hub_challenge')) {
                    return response($request->input('hub_challenge'), 200);
                }
                return response()->json(['error' => 'Invalid or empty webhook payload'], 400);
            }

            $accountIdentifier = $webhookPayload->accountIdentifier;
            $from = $webhookPayload->from;

            // Resolve WhatsApp Account and Tenant
            $account = $this->whatsapp->resolveAccount($accountIdentifier);
            if (!$account || !$account->is_active) {
                Log::warning("WhatsApp Webhook: Account disabled or unknown [{$accountIdentifier}] from [{$from}].");
                return response()->json(['error' => 'Account unknown or disabled'], 403);
            }

            $tenantId = $account->tenant_id;

            // Build serialisable payload for the background job
            $payloadData = array_merge($webhookPayload->toArray(), [
                'whatsapp_account_id' => $account->id,
                'instance_id'         => $accountIdentifier,
                'provider'            => $account->provider,
            ]);

            // Dispatch to background queue → return 200 instantly
            ProcessWhatsAppMessage::dispatch($payloadData, $tenantId);

            return response()->json(['status' => 'queued'], 200);

        } catch (Exception $e) {
            Log::error("WhatsApp Webhook Error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
