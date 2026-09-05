<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToTenant;

class KnowledgeChunk extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'knowledge_base_id',
        'knowledge_source_id',
        'content',
        'chunk_index',
        'vector_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function knowledgeBase(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBase::class, 'knowledge_base_id');
    }

    public function knowledgeSource(): BelongsTo
    {
        return $this->belongsTo(KnowledgeSource::class);
    }
}
