<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToTenant;

class ToolExecutionLog extends Model
{
    use BelongsToTenant;

    protected $table = 'tool_execution_logs';

    protected $fillable = [
        'tenant_id',
        'conversation_id',
        'tool_name',
        'arguments',
        'response_payload',
        'status',
        'execution_time_ms',
        'error_message',
    ];

    protected $casts = [
        'arguments'         => 'array',
        'response_payload'  => 'array',
        'execution_time_ms' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
