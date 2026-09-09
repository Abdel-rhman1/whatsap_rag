<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantApiKey;
use App\Models\WidgetSetting;
use App\Services\RagChatService;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WidgetApiController extends Controller
{
    public function __construct(
        protected RagChatService $ragChatService
    ) {}

    /**
     * Resolve the tenant from API Key or Widget Key.
     */
    protected function resolveTenant(Request $request): ?Tenant
    {
        $key = $request->header('X-API-Key')
            ?? $request->header('X-Widget-Key')
            ?? $request->bearerToken()
            ?? $request->input('api_key')
            ?? $request->input('key');

        if (!$key) {
            return null;
        }

        // Clean bearer token prefix
        $key = str_replace('Bearer ', '', $key);

        // 1. Check TenantApiKey table (sk_...)
        $apiKeyRecord = TenantApiKey::where('key', $key)
            ->where('status', 'active')
            ->first();

        if ($apiKeyRecord && $apiKeyRecord->isActive()) {
            // Update last used timestamp
            $apiKeyRecord->update(['last_used_at' => now()]);
            return $apiKeyRecord->tenant;
        }

        // 2. Check direct tenant widget_key
        $tenant = Tenant::where('widget_key', $key)->first();
        if ($tenant && $tenant->isActive()) {
            return $tenant;
        }

        return null;
    }

    /**
     * Get Widget configuration for client-side rendering.
     */
    public function config(Request $request)
    {
        $tenant = $this->resolveTenant($request);

        if (!$tenant || !$tenant->isActive()) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'Invalid or missing API key.'
            ], 401)->header('Access-Control-Allow-Origin', '*');
        }

        $settings = WidgetSetting::forTenant($tenant);

        return response()->json([
            'tenant_id'           => $tenant->id,
            'tenant_name'         => $tenant->name,
            'bot_name'            => $settings->bot_name ?? 'AI Assistant',
            'bubble_title'        => $settings->bubble_title ?? 'Chat with us',
            'theme'               => $settings->theme ?? 'dark',
            'primary_color'       => $settings->primary_color ?? '#6366f1',
            'greeting_message'    => $settings->greeting_message ?? 'Hello! How can I help you today?',
            'placeholder_text'    => $settings->placeholder_text ?? 'Type a message...',
            'position'            => $settings->position ?? 'bottom-right',
            'suggested_questions' => $settings->suggested_questions ?? [],
            'is_enabled'          => (bool)$settings->is_enabled,
            'sound_enabled'       => (bool)$settings->sound_enabled,
            'logo_url'            => $settings->logo_path ? asset($settings->logo_path) : null,
        ])->header('Access-Control-Allow-Origin', '*')
          ->header('Access-Control-Allow-Headers', 'Content-Type, X-API-Key, Authorization');
    }

    /**
     * Process message from widget and return AI response.
     */
    public function chat(Request $request)
    {
        $tenant = $this->resolveTenant($request);

        if (!$tenant || !$tenant->isActive()) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'Invalid or missing API key.'
            ], 401)->header('Access-Control-Allow-Origin', '*');
        }

        $settings = WidgetSetting::forTenant($tenant);
        if (!$settings->is_enabled) {
            return response()->json([
                'error'   => 'Widget Disabled',
                'message' => 'The chat widget is currently disabled by the owner.'
            ], 403)->header('Access-Control-Allow-Origin', '*');
        }

        $request->validate([
            'message'    => 'required|string|max:1000',
            'session_id' => 'nullable|string|max:120',
        ]);

        $message = trim($request->input('message'));
        $sessionId = $request->input('session_id') ?: ('widget_' . md5($request->ip() . '_' . now()->toDateString()));

        try {
            TenantContext::set($tenant);

            $result = $this->ragChatService->answer(
                query: $message,
                tenantId: $tenant->id,
                platform: 'widget',
                externalId: $sessionId,
                instanceId: 'widget',
                userMetadata: ['source' => 'widget', 'ip' => $request->ip()]
            );

            return response()->json([
                'success'    => true,
                'reply'      => $result['reply'] ?? $result['answer'] ?? 'Thank you for your message.',
                'source'     => $result['source'] ?? 'ai',
                'session_id' => $sessionId,
            ])->header('Access-Control-Allow-Origin', '*')
              ->header('Access-Control-Allow-Headers', 'Content-Type, X-API-Key, Authorization');

        } catch (\Exception $e) {
            Log::error("Widget chat processing error: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error'   => 'Processing error',
                'reply'   => 'Sorry, I am having trouble answering right now. Please try again later.',
            ], 500)->header('Access-Control-Allow-Origin', '*')
                   ->header('Access-Control-Allow-Headers', 'Content-Type, X-API-Key, Authorization');
        }
    }
}
