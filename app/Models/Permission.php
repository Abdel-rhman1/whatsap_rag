<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'module',
        'description',
        'risk_level',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeByRisk($query, string $risk)
    {
        return $query->where('risk_level', $risk);
    }
}
