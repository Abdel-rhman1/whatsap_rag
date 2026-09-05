<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToTenant;

class TenantToolConfig extends Model
{
    use BelongsToTenant;

    protected $table = 'tenant_tool_configs';

    protected $fillable = [
        'tenant_id',
        'tool_name',
        'is_enabled',
        'settings',
        'credentials',
    ];

    protected $casts = [
        'is_enabled'  => 'boolean',
        'settings'    => 'array',
        'credentials' => 'encrypted:array',
    ];

    protected $hidden = [
        'credentials',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getCredential(string $key, mixed $default = null): mixed
    {
        $creds = $this->credentials ?? [];
        return $creds[$key] ?? $default;
    }
}
