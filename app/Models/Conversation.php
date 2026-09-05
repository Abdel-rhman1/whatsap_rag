<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\BelongsToTenant;

class Conversation extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'customer_id',
        'whatsapp_account_id',
        'external_id',
        'platform',
        'device_id',
        'session_id',
        'phone_number',
        'contact_name',
        'language',
        'escalated_to_human',
        'last_message_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'escalated_to_human' => 'boolean',
        'last_message_at' => 'datetime',
    ];

    public function whatsappAccount(): BelongsTo
    {
        return $this->belongsTo(WhatsAppAccount::class, 'whatsapp_account_id');
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function session()
    {
        return $this->belongsTo(WhatsappSession::class);
    }

    public function humanRequests()
    {
        return $this->hasMany(HumanRequest::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
