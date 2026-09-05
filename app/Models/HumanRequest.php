<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;

class HumanRequest extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'conversation_id',
        'reason',
        'instance_id',
        'external_id',
        'name',
        'language',
        'status',
        'resolved_by',
        'last_message',
        'user_message',
        'resolved_at',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
