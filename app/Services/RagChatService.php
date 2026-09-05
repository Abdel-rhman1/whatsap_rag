<?php

namespace App\Services;

use App\Services\Agent\AiAgentEngine;
use App\Models\Tenant;
use App\Models\Conversation;
use Exception;
use Illuminate\Support\Facades\Log;

class RagChatService
{
    public function __construct(
        protected AiAgentEngine $agentEngine
    ) {}

    public function answer(string $query, int $tenantId, string $platform = 'widget', ?string $externalId = null, ?string $instanceId = null, array $userMetadata = []): array
    {
        $maxLen = config('rag.guardrails.max_message_length', 500);
        if (strlen($query) > $maxLen) {
            $query = substr($query, 0, $maxLen);
        }

        $externalId = $externalId ?? 'session_' . (session()->getId() ?: 'api');
        TenantContext::set(tenantId: $tenantId);

        $conversation = Conversation::withoutGlobalScopes()->firstOrCreate([
            'tenant_id'   => $tenantId,
            'external_id' => $externalId,
            'platform'    => $platform,
        ]);

        $context = array_merge($userMetadata, [
            'external_id'     => $externalId,
            'instance_id'     => $instanceId,
            'platform'        => $platform,
            'conversation_id' => $conversation->id,
        ]);

        // Execute tenant-scoped AI Agent Engine
        $result = $this->agentEngine->run($query, $tenantId, $context);

        // Fetch recent conversation history for response JSON
        $log = $conversation->messages()->latest()->take(5)->get()->reverse()->map(function($m) {
            return [
                'role'      => $m->role === 'user' ? 'user' : 'ai',
                'message'   => $m->content,
                'timestamp' => $m->created_at->toIso8601String()
            ];
        })->toArray();

        return [
            'reply'            => $result['reply'],
            'answer'           => $result['answer'],
            'language'         => $result['language'],
            'source'           => $result['source'],
            'is_noisy'         => false,
            'summary'          => null,
            'human_request_id' => null,
            'conversation_log' => $log,
            'citations'        => [],
            'confidence'       => $result['confidence'],
            'tools_executed'   => $result['tools_executed'],
            'latency_ms'       => $result['latency_ms'],
        ];
    }
}
