<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToTenant;

class WidgetSetting extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'widget_settings';

    protected $fillable = [
        'tenant_id',
        'theme',
        'primary_color',
        'bot_name',
        'bubble_title',
        'logo_path',
        'greeting_message',
        'placeholder_text',
        'position',
        'suggested_questions',
        'is_enabled',
        'sound_enabled',
        'allowed_domains',
    ];

    protected $casts = [
        'suggested_questions' => 'array',
        'allowed_domains'     => 'array',
        'is_enabled'          => 'boolean',
        'sound_enabled'       => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get or create settings with sensible defaults.
     */
    public static function forTenant(Tenant|int $tenant): self
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        return static::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'theme'               => 'dark',
                'primary_color'       => '#6366f1',
                'bot_name'            => 'AI Support',
                'bubble_title'        => 'Chat with us',
                'greeting_message'    => 'Hello! 👋 How can I help you today?',
                'placeholder_text'    => 'Type a message...',
                'position'            => 'bottom-right',
                'suggested_questions' => [
                    'What services do you offer?',
                    'How can I get started?',
                    'Speak to a human agent'
                ],
                'is_enabled'          => true,
                'sound_enabled'       => true,
                'allowed_domains'     => ['*'],
            ]
        );
    }
}
