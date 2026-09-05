<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;
use Illuminate\Support\Str;

class WhatsAppAccount extends Model
{
    use BelongsToTenant;

    protected $table = 'whatsapp_accounts';

    protected $fillable = [
        'tenant_id',
        'name',
        'provider',
        'account_identifier',
        'phone_number',
        'status',
        'is_active',
        'qr_code',
        'credentials',
        'webhook_verify_token',
        'metadata',
    ];

    protected $hidden = [
        'credentials',
        'webhook_verify_token',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'whatsapp_account_id');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'whatsapp_account_id');
    }

    /**
     * Get a specific decrypted configuration property safely.
     */
    public function getCredential(string $key, mixed $default = null): mixed
    {
        return $this->credentials[$key] ?? $default;
    }

    /**
     * Set a credential property safely.
     */
    public function setCredential(string $key, mixed $value): void
    {
        $credentials = $this->credentials ?? [];
        $credentials[$key] = $value;
        $this->credentials = $credentials;
    }
}
