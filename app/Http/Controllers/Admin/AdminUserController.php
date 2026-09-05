<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Tenant;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('tenant');

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        $users = $query->latest()->paginate(20);
        $tenants = Tenant::all();

        return view('admin.users.index', compact('users', 'tenants'));
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        
        // Log action
        \App\Models\AdminActivityLog::log(
            action: 'toggle_user_status',
            tenantId: $user->tenant_id,
            entityType: 'User',
            entityId: $user->id,
            metadata: ['new_status' => $user->is_active ? 'active' : 'inactive']
        );

        return back()->with('success', 'User status updated successfully.');
    }

    public function destroy(User $user)
    {
        $tenantId = $user->tenant_id;
        $userId = $user->id;
        
        $user->delete();

        \App\Models\AdminActivityLog::log(
            action: 'delete_user',
            tenantId: $tenantId,
            entityType: 'User',
            entityId: $userId
        );

        return back()->with('success', 'User deleted successfully.');
    }

    public function logs(Request $request)
    {
        $logs = \App\Models\ActivityLog::with(['user', 'tenant'])->latest()->paginate(50);
        return view('admin.users.logs', compact('logs'));
    }
}
