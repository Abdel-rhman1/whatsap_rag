<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\BelongsToTenant;

class WhatsappDefaultTemplate extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'type',
        'message',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
