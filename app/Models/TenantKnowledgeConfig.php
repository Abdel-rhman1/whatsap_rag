<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToTenant;

class TenantKnowledgeConfig extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'vector_namespace',
        'similarity_threshold',
        'allowed_file_types',
        'context_only_mode',
        'hallucination_prevention',
    ];

    protected $casts = [
        'allowed_file_types'       => 'array',
        'context_only_mode'        => 'boolean',
        'hallucination_prevention' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
