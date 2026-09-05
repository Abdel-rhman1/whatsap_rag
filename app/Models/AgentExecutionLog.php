<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToTenant;

class AgentExecutionLog extends Model
{
    use BelongsToTenant;

    protected $table = 'agent_execution_logs';

    protected $fillable = [
        'tenant_id',
        'conversation_id',
        'message_id',
        'agent_config_id',
        'provider',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'latency_ms',
        'tools_executed',
        'status',
        'error_message',
    ];

    protected $casts = [
        'prompt_tokens'     => 'integer',
        'completion_tokens' => 'integer',
        'total_tokens'      => 'integer',
        'latency_ms'        => 'integer',
        'tools_executed'    => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function config(): BelongsTo
    {
        return $this->belongsTo(TenantAgentConfig::class, 'agent_config_id');
    }
}
