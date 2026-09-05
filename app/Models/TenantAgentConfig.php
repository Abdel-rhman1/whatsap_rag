<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;

class TenantAgentConfig extends Model
{
    use BelongsToTenant;

    protected $table = 'tenant_agent_configs';

    protected $fillable = [
        'tenant_id',
        'agent_name',
        'provider',
        'model',
        'system_prompt',
        'temperature',
        'max_tokens',
        'language',
        'tone',
        'business_rules',
        'similarity_threshold',
        'context_only_mode',
        'enabled_tools',
        'escalation_rules',
        'working_hours',
        'human_handoff_behavior',
        'conversation_limits',
        'is_active',
    ];

    protected $casts = [
        'temperature'           => 'float',
        'max_tokens'            => 'integer',
        'similarity_threshold'  => 'float',
        'context_only_mode'     => 'boolean',
        'is_active'             => 'boolean',
        'business_rules'        => 'array',
        'enabled_tools'         => 'array',
        'escalation_rules'      => 'array',
        'working_hours'         => 'array',
        'conversation_limits'   => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function executionLogs(): HasMany
    {
        return $this->hasMany(AgentExecutionLog::class, 'agent_config_id');
    }

    /**
     * Get default tools if none configured.
     */
    public function getEffectiveTools(): array
    {
        return $this->enabled_tools ?? ['knowledge_search', 'order_creation', 'human_handoff', 'crm_lookup'];
    }
}
