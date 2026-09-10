<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $tenantId = $this->getTenantId();

        $systemRoles = Role::system()
            ->with(['permissions'])
            ->withCount(['users' => function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            }])
            ->get();

        $customRoles = Role::where('tenant_id', $tenantId)
            ->with(['permissions'])
            ->withCount('users')
            ->latest()
            ->get();

        $totalPermissions = Permission::count();
        $totalMembers = User::where('tenant_id', $tenantId)->count();

        return view('dashboard.roles.index', compact(
            'systemRoles',
            'customRoles',
            'totalPermissions',
            'totalMembers'
        ));
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy('module');
        return view('dashboard.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $tenantId = $this->getTenantId();

        $request->merge([
            'slug' => Str::slug($request->slug ?: $request->name, '_'),
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:60|regex:/^[a-z0-9_]+$/|unique:roles,slug,NULL,id,tenant_id,' . $tenantId,
            'description' => 'nullable|string|max:1000',
            'color' => 'required|string|max:30',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // Guard: Don't allow colliding with system role slugs
        if (Role::system()->where('slug', $request->slug)->exists()) {
            return back()->withInput()->with('error', 'The role identifier "' . $request->slug . '" is reserved by a system role.');
        }

        $role = Role::create([
            'tenant_id' => $tenantId,
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'color' => $request->color,
            'is_system' => false,
        ]);

        $permissionIds = Permission::whereIn('name', $request->permissions)->pluck('id')->toArray();
        $role->permissions()->sync($permissionIds);

        ActivityLog::log(
            action: 'create_role',
            entityType: 'Role',
            entityId: $role->id,
            metadata: ['name' => $role->name, 'slug' => $role->slug, 'permissions_count' => count($permissionIds)]
        );

        return redirect()->route('roles.index')->with('success', 'Custom role created successfully.');
    }

    public function edit(Role $role)
    {
        $tenantId = $this->getTenantId();

        if ($role->is_system) {
            return redirect()->route('roles.index')->with('error', 'System built-in roles cannot be modified directly. You can create a custom role with tailored permissions.');
        }

        if ((int) $role->tenant_id !== (int) $tenantId) {
            abort(403);
        }

        $role->load('permissions');
        $permissions = Permission::all()->groupBy('module');
        $assignedPermissionNames = $role->permissions->pluck('name')->toArray();

        return view('dashboard.roles.edit', compact('role', 'permissions', 'assignedPermissionNames'));
    }

    public function update(Request $request, Role $role)
    {
        $tenantId = $this->getTenantId();

        if ($role->is_system || (int) $role->tenant_id !== (int) $tenantId) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'required|string|max:30',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color,
        ]);

        $permissionIds = Permission::whereIn('name', $request->permissions)->pluck('id')->toArray();
        $role->permissions()->sync($permissionIds);

        ActivityLog::log(
            action: 'update_role',
            entityType: 'Role',
            entityId: $role->id,
            metadata: ['name' => $role->name, 'slug' => $role->slug, 'permissions_count' => count($permissionIds)]
        );

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        $tenantId = $this->getTenantId();

        if ($role->is_system || (int) $role->tenant_id !== (int) $tenantId) {
            abort(403);
        }

        $assignedUsersCount = User::where('tenant_id', $tenantId)->where('role', $role->slug)->count();
        if ($assignedUsersCount > 0) {
            return back()->with('error', "Cannot delete role: {$assignedUsersCount} team member(s) are currently assigned to it. Reassign them first.");
        }

        $roleName = $role->name;
        $role->permissions()->detach();
        $role->delete();

        ActivityLog::log(
            action: 'delete_role',
            entityType: 'Role',
            entityId: $role->id,
            metadata: ['name' => $roleName]
        );

        return redirect()->route('roles.index')->with('success', "Role '{$roleName}' deleted successfully.");
    }

    protected function getTenantId(): int
    {
        $user = auth('tenant')->user();
        return $user instanceof \App\Models\Tenant ? $user->id : $user->tenant_id;
    }
}
