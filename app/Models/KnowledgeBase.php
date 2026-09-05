<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;

class KnowledgeBase extends Model
{
    use BelongsToTenant;

    protected $table = 'knowledge_bases';

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'is_active',
        'vector_collection',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata'  => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function sources(): HasMany
    {
        return $this->hasMany(KnowledgeSource::class, 'knowledge_base_id');
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(KnowledgeChunk::class, 'knowledge_base_id');
    }
}
