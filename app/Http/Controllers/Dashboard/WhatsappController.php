<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\WhatsappInstance;
use App\Models\WhatsappDefaultTemplate;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsappController extends Controller
{
    public function index()
    {
        $instances = auth('tenant')->user()->whatsappInstances;
        $templates = auth('tenant')->user()->whatsappDefaultTemplates;
        return view('dashboard.whatsapp', compact('instances', 'templates'));
    }

    public function storeInstance(Request $request)
    {
        $request->validate(['instance_name' => 'required|string|unique:whatsapp_instances,instance_name']);
        
        $instance = auth('tenant')->user()->whatsappInstances()->create([
            'instance_name' => $request->instance_name,
            'status' => 'initializing'
        ]);

        app(WhatsAppService::class)->startInstance($instance->instance_name);

        return back()->with('success', 'Instance created and initializing.');
    }

    public function toggleInstance(WhatsappInstance $instance)
    {
        // If enabling, check limits
        if (!$instance->is_active) {
            $limit = auth('tenant')->user()->plan->limits['max_whatsapp_sessions'] ?? 1;
            
            if ($limit !== -1) {
                // Count active/busy instances
                $currentActive = auth('tenant')->user()->whatsappInstances()
                    ->whereIn('status', ['active', 'connected', 'connecting', 'initializing', 'QR_READY', 'LINKING', 'AUTHENTICATING'])
                    ->where('is_active', true)
                    ->count();

                if ($currentActive >= $limit) {
                    return back()->with('error', "Cannot activate. Your plan allows only {$limit} WhatsApp session(s).");
                }
            }
        }

        $instance->update(['is_active' => !$instance->is_active]);
        return back()->with('success', 'Instance status updated.');
    }

    public function storeTemplate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:greeting,offline,fallback,handoff',
            'message' => 'required|string'
        ]);

        auth('tenant')->user()->whatsappDefaultTemplates()->updateOrCreate(
            ['type' => $request->type],
            ['message' => $request->message]
        );

        return back()->with('success', 'Template updated.');
    }

    public function getQr(WhatsappInstance $instance)
    {
        // Ensure user owns instance
        if ($instance->tenant_id !== auth('tenant')->id()) {
            abort(403);
        }

        $gatewayUrl = env('WHATSAPP_GATEWAY_URL', 'http://localhost:3000');

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get("{$gatewayUrl}/qr/{$instance->instance_name}");

            // 200 + text "CONNECTED"
            if ($response->status() === 200 && $response->body() === 'CONNECTED') {
                return response()->json(['status' => 'CONNECTED']);
            }

            // 200 + image/png = QR is ready
            if ($response->status() === 200) {
                return response($response->body(), 200)
                    ->header('Content-Type', 'image/png')
                    ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
            }

            // 202 = still loading / initializing
            if ($response->status() === 202) {
                return response()->json(['status' => 'LOADING'], 202);
            }

            return response()->json(['error' => 'QR not ready'], 404);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gateway connection failed: " . $e->getMessage(), ['tenantId' => auth('tenant')->id()]);
            return response()->json(['error' => 'Gateway offline. Start the WhatsApp gateway first.'], 503);
        }
    }

    public function getStatus(WhatsappInstance $instance)
    {
        if ($instance->tenant_id !== auth('tenant')->id()) {
            abort(403);
        }

        $gatewayUrl = env('WHATSAPP_GATEWAY_URL', 'http://localhost:3000');

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get("{$gatewayUrl}/status/{$instance->instance_name}");
            $data = $response->json();

            // Auto-update phone number and status in DB when connected
            if (isset($data['status']) && $data['status'] === 'CONNECTED') {
                $updates = ['status' => 'connected'];
                if (!empty($data['phone']) && $instance->phone_number !== $data['phone']) {
                    $updates['phone_number'] = $data['phone'];
                }
                $instance->update($updates);
            }

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['status' => 'GATEWAY_OFFLINE', 'hasQr' => false]);
        }
    }

    public function deleteInstance(WhatsappInstance $instance)
    {
        if ($instance->tenant_id !== auth('tenant')->id()) {
            abort(403);
        }

        $gatewayUrl = env('WHATSAPP_GATEWAY_URL', 'http://localhost:3000');

        // Tell gateway to logout and delete session
        try {
            \Illuminate\Support\Facades\Http::timeout(10)->delete("{$gatewayUrl}/session/{$instance->instance_name}");
        } catch (\Exception $e) {
            // Gateway might be offline, still delete from DB
        }

        $instance->delete();

        return back()->with('success', "Instance '{$instance->instance_name}' deleted.");
    }
}
