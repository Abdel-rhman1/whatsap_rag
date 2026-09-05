<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'widget_key',
        'qdrant_collection',
        'status',
        'last_login_at',
        'phone_number',
        'avatar_url',
        'preferred_language',
        'timezone',
        'notification_settings',
        'password_changed_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant) {
            if (empty($tenant->widget_key)) {
                $tenant->widget_key = \Illuminate\Support\Str::random(32);
            }
            if (empty($tenant->qdrant_collection)) {
                $tenant->qdrant_collection = 'tenant_' . \Illuminate\Support\Str::random(16);
            }
        });
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'password_changed_at' => 'datetime',
        'notification_settings' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function knowledgeSources(): HasMany
    {
        return $this->hasMany(KnowledgeSource::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function whatsappAccounts(): HasMany
    {
        return $this->hasMany(WhatsAppAccount::class);
    }

    public function whatsappInstances(): HasMany
    {
        return $this->hasMany(WhatsappInstance::class);
    }

    public function whatsappDefaultTemplates(): HasMany
    {
        return $this->hasMany(WhatsappDefaultTemplate::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(TenantApiKey::class);
    }

    public function humanRequests(): HasMany
    {
        return $this->hasMany(HumanRequest::class, 'tenant_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function knowledgeConfig(): HasOne
    {
        return $this->hasOne(TenantKnowledgeConfig::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function suspend(): void
    {
        $this->update(['status' => 'suspended']);
    }

    public function activate(): void
    {
        $this->update(['status' => 'active']);
    }

    public function hasRole(string $role): bool
    {
        return $role === 'tenant_admin'; // The main tenant account is always admin
    }

    public function hasPermission(string $permission): bool
    {
        return app(\App\Services\PermissionService::class)->hasPermission($this, $permission);
    }
}

