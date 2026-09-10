<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPermissionSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'allow_member_invites',
        'default_role',
        'require_2fa_for_admins',
        'allow_conversation_export',
        'mask_customer_pii',
        'session_timeout_minutes',
        'enabled_modules',
    ];

    protected $casts = [
        'allow_member_invites' => 'boolean',
        'require_2fa_for_admins' => 'boolean',
        'allow_conversation_export' => 'boolean',
        'mask_customer_pii' => 'boolean',
        'session_timeout_minutes' => 'integer',
        'enabled_modules' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isModuleEnabled(string $module): bool
    {
        if (empty($this->enabled_modules)) {
            return true; // All modules enabled by default if empty
        }

        return in_array($module, $this->enabled_modules, true);
    }
}
