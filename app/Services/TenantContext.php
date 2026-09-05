<?php

namespace App\Services;

use App\Models\Tenant;

class TenantContext
{
    protected static ?int $overrideTenantId = null;
    protected static ?Tenant $overrideTenant = null;

    /**
     * Set explicit tenant context (useful for background jobs, CLI commands, or admin impersonation).
     */
    public static function set(?Tenant $tenant = null, ?int $tenantId = null): void
    {
        if ($tenant) {
            static::$overrideTenant = $tenant;
            static::$overrideTenantId = $tenant->id;
        } else {
            static::$overrideTenant = null;
            static::$overrideTenantId = $tenantId;
        }
    }

    /**
     * Clear explicit tenant context override.
     */
    public static function clear(): void
    {
        static::$overrideTenant = null;
        static::$overrideTenantId = null;
    }

    /**
     * Get the active tenant ID from override, authentication guards, request attributes, or Sanctum.
     */
    public static function id(): ?int
    {
        if (static::$overrideTenantId !== null) {
            return static::$overrideTenantId;
        }

        if (auth('tenant')->check()) {
            return (int) auth('tenant')->id();
        }

        if (auth('sanctum')->check()) {
            return (int) auth('sanctum')->user()->tenant_id;
        }

        if (auth('web')->check()) {
            return (int) auth('web')->user()->tenant_id;
        }

        if (request()?->attributes?->has('tenant_id')) {
            return (int) request()->attributes->get('tenant_id');
        }

        return null;
    }

    /**
     * Get the active tenant model instance.
     */
    public static function get(): ?Tenant
    {
        if (static::$overrideTenant !== null) {
            return static::$overrideTenant;
        }

        $id = static::id();
        if (!$id) {
            return null;
        }

        if (auth('tenant')->check()) {
            return auth('tenant')->user();
        }

        return Tenant::find($id);
    }

    /**
     * Check if currently running under Platform Admin guard without an impersonated tenant context.
     */
    public static function isPlatformAdmin(): bool
    {
        return auth('admin')->check();
    }
}
