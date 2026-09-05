<?php

namespace App\Services;

use App\Models\User;
use App\Models\AdminUser;
use App\Models\Tenant;

class PermissionService
{
    protected $rolePermissions = [
        'super_admin' => ['*'],
        'tenant_admin' => [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'conversations.view', 'conversations.reply',
            'billing.view', 'billing.manage',
            'knowledge.manage', 'campaigns.manage',
            'settings.manage', 'sla.view', 'sla.export',
            'webhooks.manage', 'api_keys.manage'
        ],
        'agent' => [
            'conversations.view', 'conversations.reply',
            'knowledge.view'
        ],
        'analyst' => [
            'users.view',
            'conversations.view',
            'sla.view',
            'reports.view'
        ],
        'billing' => [
            'billing.view', 'billing.manage'
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
        } else {
            if (!$user || !$user->is_active) return false;
            $role = $user->role;
            $userTenantId = $user->tenant_id;
        }

        // Strict tenant-aware boundary check: Target resource must belong to user's tenant
        if ($targetResource !== null && isset($targetResource->tenant_id)) {
            if ((int) $userTenantId !== (int) $targetResource->tenant_id) {
                return false;
            }
        }

        $permissions = $this->rolePermissions[$role] ?? [];

        if (in_array('*', $permissions)) return true;

        return in_array($permission, $permissions);
    }

    public function getRolePermissions(string $role): array
    {
        return $this->rolePermissions[$role] ?? [];
    }
}
