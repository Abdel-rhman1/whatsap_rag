<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'monthly_price',
        'yearly_price',
        'limits',
        'is_active',
    ];

    protected $casts = [
        'limits' => 'array',
        'is_active' => 'boolean',
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
    ];

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }
}
