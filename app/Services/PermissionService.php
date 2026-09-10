<?php

namespace App\Services;

use App\Models\User;
use App\Models\AdminUser;
use App\Models\Tenant;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Schema;

class PermissionService
{
    protected $rolePermissions = [
        'super_admin' => ['*'],
        'tenant_admin' => ['*'],
        'agent' => [
            'conversations.view', 'conversations.reply', 'conversations.escalate',
            'knowledge.view', 'whatsapp.view', 'sla.view'
        ],
        'analyst' => [
            'users.view', 'conversations.view', 'conversations.export',
            'sla.view', 'sla.export', 'campaigns.view'
        ],
        'billing' => [
            'billing.view', 'billing.manage', 'settings.view'
        ],
    ];

    /**
     * Check if a user has permission, optionally enforcing tenant ownership on target resources.
     */
    public function hasPermission($user, string $permission, mixed $targetResource = null): bool
    {
        if ($user instanceof AdminUser) {
            if ($user->role === 'super_admin') return true;
            $permissions = $this->rolePermissions[$user->role] ?? [];
            if (in_array('*', $permissions)) return true;
            return in_array($permission, $permissions);
        }

        if ($user instanceof Tenant) {
            if (!$user->isActive()) return false;
            $role = 'tenant_admin';
            $userTenantId = $user->id;
            $tenant = $user;
        } else {
            if (!$user || !$user->is_active) return false;
            $role = $user->role;
            $userTenantId = $user->tenant_id;
            $tenant = $user->tenant;
        }

        // Strict tenant-aware boundary check: Target resource must belong to user's tenant
        if ($targetResource !== null && isset($targetResource->tenant_id)) {
            if ((int) $userTenantId !== (int) $targetResource->tenant_id) {
                return false;
            }
        }

        // Tenant owner has all tenant permissions
        if ($role === 'tenant_admin') {
            return true;
        }

        // 1. Try resolving via Database Role & Permissions
        try {
            if (Schema::hasTable('roles') && Schema::hasTable('permissions')) {
                $dbRole = Role::forTenant($userTenantId)
                    ->where('slug', $role)
                    ->with('permissions')
                    ->first();

                if ($dbRole) {
                    return $dbRole->permissions->contains('name', $permission);
                }
            }
        } catch (\Throwable $e) {
            // Fall back to array below if tables do not exist or during initial migrations
        }

        // 2. Fallback to hardcoded array
        $permissions = $this->rolePermissions[$role] ?? [];

        if (in_array('*', $permissions)) return true;

        return in_array($permission, $permissions);
    }

    public function getRolePermissions(string $role, ?int $tenantId = null): array
    {
        try {
            if (Schema::hasTable('roles')) {
                $dbRole = Role::forTenant($tenantId)
                    ->where('slug', $role)
                    ->with('permissions')
                    ->first();

                if ($dbRole) {
                    return $dbRole->permissions->pluck('name')->toArray();
                }
            }
        } catch (\Throwable $e) {}

        return $this->rolePermissions[$role] ?? [];
    }
}
