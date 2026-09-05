<?php

namespace App\Services\Agent\Tools;

use App\Contracts\AgentToolInterface;
use App\Models\HumanRequest;
use App\Models\Conversation;

class HumanHandoffTool implements AgentToolInterface
{
    public function name(): string
    {
        return 'human_handoff';
    }

    public function description(): string
    {
        return 'Escalates the conversation to a human support agent and logs a HumanRequest ticket.';
    }

    public function validate(array $params): bool
    {
        return true;
    }

    public function execute(array $params, int $tenantId, array $context = []): array
    {
        $conversationId = $context['conversation_id'] ?? null;
        $reason         = $params['reason'] ?? 'Escalated by AI Agent';
        $language       = $params['language'] ?? 'ar';

        if ($conversationId) {
            Conversation::withoutGlobalScopes()->where('id', $conversationId)->update([
                'escalated_to_human' => true,
                'last_message_at'    => now(),
            ]);

            $humanRequest = HumanRequest::create([
                'conversation_id' => $conversationId,
                'reason'          => $reason,
                'instance_id'     => $context['instance_id'] ?? 'unknown',
                'external_id'     => $context['external_id'] ?? 'unknown',
                'name'            => $context['contact_name'] ?? 'User',
                'language'        => $language,
                'status'          => 'pending',
                'last_message'    => $context['last_message'] ?? 'Help requested',
            ]);

            $ticketId = 'HR-' . date('Ymd') . '-' . str_pad($humanRequest->id, 3, '0', STR_PAD_LEFT);

            return [
                'status'           => 'escalated',
                'human_request_id' => $ticketId,
                'message'          => 'Conversation escalated to human agent.',
            ];
        }

        return ['status' => 'failed', 'reason' => 'Conversation context missing'];
    }
}
