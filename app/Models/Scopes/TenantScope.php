<?php

namespace App\Models\Scopes;

use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Platform Admins viewing global admin pages operate cross-tenant, unless impersonating a specific tenant
        if (TenantContext::isPlatformAdmin() && TenantContext::id() === null) {
            return;
        }

        $tenantId = TenantContext::id();

        if ($tenantId !== null) {
            $builder->where($model->getTable() . '.tenant_id', $tenantId);
        }
    }
}
