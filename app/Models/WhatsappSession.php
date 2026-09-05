<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class WhatsappSession extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'device_id', 'instance_id', 'phone_number', 'status', 'connected_at'];
    
    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
