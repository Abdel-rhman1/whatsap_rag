<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantUserController extends Controller
{
    public function index()
    {
        $tenantId = $this->getTenantId();
        $tenant = auth('tenant')->user();
        if ($tenant instanceof \App\Models\User) {
            $tenant = $tenant->tenant;
        }
        $roles = \App\Models\Role::forTenant($tenantId)->get();
        $users = User::where('tenant_id', $tenantId)->latest()->get();
        return view('dashboard.users.index', compact('users', 'tenant', 'roles'));
    }

    public function store(Request $request)
    {
        $tenantId = $this->getTenantId();
        $tenant = auth('tenant')->user();
        if ($tenant instanceof \App\Models\User) {
            $tenant = $tenant->tenant;
        }

        $validRoles = \App\Models\Role::forTenant($tenantId)->pluck('slug')->toArray();
        if (empty($validRoles)) {
            $validRoles = ['tenant_admin', 'agent', 'analyst', 'billing'];
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:' . implode(',', $validRoles),
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make(Str::random(12)),
            'tenant_id' => $tenantId,
            'role' => $request->role,
            'is_active' => true,
        ]);

        \App\Models\ActivityLog::log(
            action: 'invite_user',
            entityType: 'User',
            entityId: $user->id,
            metadata: ['email' => $user->email, 'role' => $user->role]
        );

        // In a real app, send invitation email here
        return back()->with('success', 'User invited successfully.');
    }

    public function toggleStatus(User $user)
    {
        $this->authorizeTenant($user);

        // Check if user is trying to deactivate themselves (if logged in as sub-user)
        $currentUser = auth('tenant')->user();
        if ($currentUser instanceof \App\Models\User && $user->id === $currentUser->id) {
            return back()->with('error', 'You cannot deactivate yourself.');
        }

        $user->update(['is_active' => !$user->is_active]);

        \App\Models\ActivityLog::log(
            action: 'toggle_user_status',
            entityType: 'User',
            entityId: $user->id,
            metadata: ['new_status' => $user->is_active ? 'active' : 'inactive']
        );

        return back()->with('success', 'User status updated.');
    }

    public function updateRole(Request $request, User $user)
    {
        $this->authorizeTenant($user);
        $tenantId = $this->getTenantId();

        $validRoles = \App\Models\Role::forTenant($tenantId)->pluck('slug')->toArray();
        if (empty($validRoles)) {
            $validRoles = ['tenant_admin', 'agent', 'analyst', 'billing'];
        }
        
        $request->validate(['role' => 'required|in:' . implode(',', $validRoles)]);

        // Check if user is trying to change their own role
        $currentUser = auth('tenant')->user();
        if ($currentUser instanceof \App\Models\User && $user->id === $currentUser->id && $request->role !== $user->role) {
            return back()->with('error', 'You cannot change your own role.');
        }

        $user->update(['role' => $request->role]);

        \App\Models\ActivityLog::log(
            action: 'update_user_role',
            entityType: 'User',
            entityId: $user->id,
            metadata: ['new_role' => $user->role]
        );

        return back()->with('success', 'User role updated.');
    }

    protected function getTenantId()
    {
        $user = auth('tenant')->user();
        return $user instanceof \App\Models\Tenant ? $user->id : $user->tenant_id;
    }

    protected function authorizeTenant(User $user)
    {
        if ($user->tenant_id !== $this->getTenantId()) {
            abort(403);
        }
    }
}
