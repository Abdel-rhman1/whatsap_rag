<?php

namespace App\Http\Controllers;

use App\Services\TenantService;
use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantDashboardController extends Controller
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function index()
    {
        $tenant = Auth::guard('tenant')->user();
        $stats = $this->tenantService->getDashboardStats($tenant);
        $analytics = $this->tenantService->getUsageAnalytics($tenant);
        
        $setupChecklist = [
            'knowledge_uploaded' => $tenant->knowledgeSources()->count() > 0,
            'api_key_generated' => $tenant->apiKeys()->count() > 0,
            'whatsapp_connected' => $tenant->whatsappInstances()->where('status', 'connected')->count() > 0,
        ];

        return view('dashboard.index', compact('tenant', 'stats', 'analytics', 'setupChecklist'));
    }

    public function apiKeys()
    {
        $tenant = Auth::guard('tenant')->user();
        $apiKeys = $tenant->apiKeys()->latest()->get();
        return view('dashboard.api-keys', compact('apiKeys'));
    }

    public function generateApiKey(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $tenant = Auth::guard('tenant')->user();
        
        $this->tenantService->generateApiKey($tenant, $request->name);
        
        return back()->with('success', 'API Key generated successfully.');
    }

    public function revokeApiKey($id)
    {
        $tenant = Auth::guard('tenant')->user();
        $this->tenantService->revokeApiKey($id, $tenant);
        
        return back()->with('success', 'API Key revoked.');
    }

    public function webhooks()
    {
        $tenant = Auth::guard('tenant')->user();
        $webhooks = $tenant->webhooks()->latest()->get();
        return view('dashboard.webhooks', compact('webhooks'));
    }

    public function storeWebhook(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'events' => 'required|array',
        ]);

        $tenant = Auth::guard('tenant')->user();
        $this->tenantService->storeWebhook($tenant, $request->all());

        return back()->with('success', 'Webhook added successfully.');
    }

    public function destroyWebhook(Webhook $webhook)
    {
        if ($webhook->tenant_id !== Auth::guard('tenant')->id()) {
            abort(403);
        }

        $webhook->delete();
        return back()->with('success', 'Webhook deleted.');
    }

    public function logs()
    {
        $tenant = Auth::guard('tenant')->user();
        $logs = $this->tenantService->getLogs($tenant);
        return view('dashboard.logs', compact('logs'));
    }

    public function exportLogs()
    {
        $tenant = Auth::guard('tenant')->user();
        $logs = $tenant->messages()->latest()->get();
        
        $filename = "logs-" . now()->format('Y-m-d') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['ID', 'Role', 'Content', 'Source', 'Platform', 'Created At'];

        $callback = function() use($logs, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->role,
                    $log->content,
                    $log->source,
                    $log->conversation->platform ?? 'n/a',
                    $log->created_at
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

