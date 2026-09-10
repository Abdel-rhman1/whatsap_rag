<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

use App\Traits\BelongsToTenant;

class User extends Authenticatable implements HasTenants
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'role',
        'is_active',
        'last_login_at',
        'phone_number',
        'avatar_url',
        'preferred_language',
        'timezone',
        'notification_settings',
        'password_changed_at',
    ];

    public function getTenants(Panel $panel): Collection
    {
        return collect([$this->tenant])->filter();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return (int) $this->tenant_id === (int) $tenant->id;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function roleModel(): ?Role
    {
        return Role::forTenant($this->tenant_id)->where('slug', $this->role)->first();
    }

    public function hasPermission(string $permission): bool
    {
        return app(\App\Services\PermissionService::class)->hasPermission($this, $permission);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'notification_settings' => 'array',
        ];
    }
}
