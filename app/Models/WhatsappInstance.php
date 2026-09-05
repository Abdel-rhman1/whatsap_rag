<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\BelongsToTenant;

class WhatsappInstance extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'instance_name',
        'qr_code',
        'status',
        'is_active',
        'phone_number',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
