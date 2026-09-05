<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminDashboardController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function index()
    {
        $stats = $this->adminService->getGlobalStats();
        return view('admin.dashboard.index', compact('stats'));
    }

    public function tenants(Request $request)
    {
        $query = Tenant::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'All Statuses') {
            $status = strtolower($request->status) === 'active only' ? 'active' : strtolower($request->status);
            $query->where('status', $status);
        }

        $tenants = $query->withCount(['conversations', 'apiKeys'])->latest()->paginate(15);
        return view('admin.dashboard.tenants', compact('tenants'));
    }

    public function tenantCreate()
    {
        return view('admin.dashboard.tenant-create');
    }

    public function tenantStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants,email',
            'password' => 'required|min:8',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = 'active';
        $validated['widget_key'] = Str::random(32);
        
        $this->adminService->createTenant($validated);

        return redirect()->route('admin.tenants')->with('success', 'Tenant created.');
    }

    public function tenantShow($id)
    {
        $tenant = Tenant::with(['apiKeys', 'webhooks', 'whatsappInstances'])
            ->withCount(['conversations', 'knowledgeSources'])
            ->findOrFail($id);
        
        return view('admin.dashboard.tenant-show', compact('tenant'));
    }

    public function tenantUpdateStatus($id, Request $request)
    {
        $request->validate(['status' => 'required|in:active,suspended']);
        $this->adminService->updateTenantStatus($id, $request->status);
        
        return back()->with('success', 'Tenant status updated.');
    }

    public function apiControl()
    {
        return view('admin.dashboard.api-control');
    }

    public function whatsappHealth()
    {
        $instances = \App\Models\WhatsappInstance::with('tenant')->latest()->paginate(20);
        
        $stats = [
            'total' => \App\Models\WhatsappInstance::count(),
            'connected' => \App\Models\WhatsappInstance::where('status', 'connected')->count(),
            'errors' => \App\Models\WhatsappInstance::where('status', 'disconnected')->orWhere('status', 'failed')->count(),
        ];

        return view('admin.dashboard.whatsapp', compact('instances', 'stats'));
    }

    public function whatsappRestartInstance($id)
    {
        $instance = \App\Models\WhatsappInstance::findOrFail($id);
        
        try {
            // Logic to contact gateway and restart
            app(\App\Services\WhatsAppService::class)->startInstance($instance->instance_name);
            $instance->update(['status' => 'initializing']);
            
            $this->adminService->logAction('whatsapp_instance_restarted', "Admin restarted WhatsApp instance: {$instance->instance_name}", $instance->tenant_id, 'WhatsappInstance', $instance->id);
            
            return back()->with('success', "Instance '{$instance->instance_name}' restart command sent.");
        } catch (\Exception $e) {
            return back()->with('error', "Failed to restart instance: " . $e->getMessage());
        }
    }

    public function logs()
    {
        $logs = \App\Models\AdminActivityLog::with('adminUser')->latest()->paginate(50);
        return view('admin.dashboard.logs', compact('logs'));
    }

    public function settings()
    {
        return view('admin.dashboard.settings');
    }
}

