<?php

namespace App\Services\Agent;

use App\Models\TenantAgentConfig;
use App\Models\AgentExecutionLog;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\TenantContext;
use App\Services\IntentClassificationService;
use App\Services\MetricsService;
class AiAgentEngine
{
    public function __construct(
        protected AgentToolRegistry $toolRegistry,
        protected IntentClassificationService $intentClassifier
    ) {}

    /**
     * Run the tenant-scoped AI Agent engine for a query.
     */
    public function run(string $query, int $tenantId, array $context = []): array
    {
        $startTime = microtime(true);

        // Guarantee active tenant context
        TenantContext::set(tenantId: $tenantId);

        // 1. Load or resolve Tenant Agent Configuration
        $config = TenantAgentConfig::withoutGlobalScopes()
            ->firstOrCreate([
                'tenant_id' => $tenantId,
            ], [
                'agent_name'          => 'Default AI Agent',
                'provider'            => config('rag.llm.provider', 'groq'),
                'model'               => config('rag.groq.model', 'llama-3.3-70b-versatile'),
                'temperature'         => 0.3,
                'max_tokens'          => 1024,
                'language'            => 'auto',
                'tone'                => 'professional',
                'context_only_mode'   => false,
                'enabled_tools'       => ['knowledge_search', 'order_creation', 'human_handoff', 'crm_lookup'],
            ]);

        // 2. Load Tenant-Enabled Tools
        $enabledTools = $this->toolRegistry->getEnabledToolsForConfig($config);
        $executedTools = [];

        // 3. Resolve Conversation
        $conversationId = $context['conversation_id'] ?? null;
        $conversation = null;
        $history = [];

        if ($conversationId) {
            $conversation = Conversation::withoutGlobalScopes()->find($conversationId);
        } elseif (!empty($context['external_id'])) {
            $conversation = Conversation::withoutGlobalScopes()->firstOrCreate([
                'tenant_id'   => $tenantId,
                'external_id' => $context['external_id'],
                'platform'    => $context['platform'] ?? 'whatsapp',
            ]);
        }

        if ($conversation) {
            $history = $conversation->messages()->latest()->take(10)->get()->reverse()->toArray();
        }

        try {
            // 4. Intent Classification
            $classification = $this->intentClassifier->classify($query, $history);
            $intent = $classification['intent'] ?? 'DOMAIN_QUESTION';
            $detectedLang = $classification['language'] ?? ($config->language !== 'auto' ? $config->language : 'ar');

            $contextText = "";
            $hits = [];

            // 5. Execute Knowledge Search Tool if enabled & intent is domain question
            if (isset($enabledTools['knowledge_search']) && in_array($intent, ['DOMAIN_QUESTION', 'PRODUCT_QUERY', 'COMPANY_QUERY'])) {
                $toolResult = $enabledTools['knowledge_search']->execute([
                    'query'                => $query,
                    'similarity_threshold' => $config->similarity_threshold,
                ], $tenantId, $context);

                $hits = $toolResult['hits'] ?? [];
                $contextText = $toolResult['context_text'] ?? "";
                $executedTools[] = 'knowledge_search';
            }

            // 6. Build Dynamic System Prompt with Tenant Rules & Persona
            $messages = $this->buildMessages($query, $contextText, $history, $config, $intent, $detectedLang);

            // 7. Resolve LLM Provider & Call Model
            $llmProvider = LlmProviderFactory::make($config);
            $llmResult = $llmProvider->chat($messages, $config);

            $rawContent = $llmResult['content'] ?? '';
            $parsedResponse = $this->parseJson($rawContent);

            $finalAnswer = $parsedResponse['reply'] ?? $parsedResponse['answer'] ?? $rawContent;
            $source      = $parsedResponse['source'] ?? strtolower($intent);
            $confidence  = $this->calculateConfidence($hits);

            // Intents that don't require RAG context — never escalate on low confidence
            $nonRagIntents = ['GREETING', 'FAREWELL', 'THANKS', 'SMALL_TALK', 'ORDER_CREATION'];

            // 8. Execute Tool Side-Effects (Order Creation / Human Handoff)
            if ($intent === 'ORDER_CREATION' && isset($enabledTools['order_creation']) && !empty($parsedResponse['order_data'])) {
                $orderResult = $enabledTools['order_creation']->execute([
                    'order_data' => $parsedResponse['order_data']
                ], $tenantId, $context);
                $executedTools[] = 'order_creation';
            }

            // Only escalate to human when:
            // 1. User explicitly asked for a human AND it's not a simple conversational intent
            // 2. A domain question had low RAG confidence
            $isConversational = in_array($intent, $nonRagIntents);
            $shouldEscalate = !$isConversational
                && ($source === 'human' || $confidence === 'low');

            $holdResponse = false;

            if ($shouldEscalate && isset($enabledTools['human_handoff'])) {
                $handoffResult = $enabledTools['human_handoff']->execute([
                    'reason'   => $confidence === 'low' ? 'Low Confidence RAG Score' : 'User Requested Human',
                    'language' => $detectedLang,
                ], $tenantId, array_merge($context, [
                    'conversation_id' => $conversation?->id,
                    'last_message'    => $query,
                ]));
                $executedTools[] = 'human_handoff';

                // Hold the response — don't auto-reply to the user.
                // The human agent will see the alert and reply manually from the dashboard.
                $holdResponse = true;
            }

            $latencyMs = (int)((microtime(true) - $startTime) * 1000);

            // 9. Log Execution to AgentExecutionLog
            $executionLog = AgentExecutionLog::create([
                'tenant_id'         => $tenantId,
                'conversation_id'   => $conversation?->id,
                'agent_config_id'   => $config->id,
                'provider'          => $config->provider,
                'model'             => $config->model,
                'prompt_tokens'     => $llmResult['prompt_tokens'] ?? 0,
                'completion_tokens' => $llmResult['completion_tokens'] ?? 0,
                'total_tokens'      => $llmResult['total_tokens'] ?? 0,
                'latency_ms'        => $latencyMs,
                'tools_executed'    => array_unique($executedTools),
                'status'            => $holdResponse ? 'escalated' : 'success',
            ]);

            // 10. Persist user message always; persist AI response only if not held
            if ($conversation) {
                Message::create([
                    'tenant_id'       => $tenantId,
                    'conversation_id' => $conversation->id,
                    'role'            => 'user',
                    'content'         => $query,
                ]);

                if (!$holdResponse) {
                    Message::create([
                        'tenant_id'       => $tenantId,
                        'conversation_id' => $conversation->id,
                        'role'            => 'assistant',
                        'source'          => $source,
                        'content'         => $finalAnswer,
                        'metadata'        => [
                            'confidence' => $confidence,
                            'tools'      => array_unique($executedTools),
                            'log_id'     => $executionLog->id,
                        ]
                    ]);
                }

                $conversation->update(['last_message_at' => now(), 'language' => $detectedLang]);
            }

            return [
                'reply'          => $finalAnswer,
                'answer'         => $finalAnswer,
                'source'         => $source,
                'language'       => $detectedLang,
                'confidence'     => $confidence,
                'hold_response'  => $holdResponse,
                'tools_executed' => array_unique($executedTools),
                'latency_ms'     => $latencyMs,
                'log_id'         => $executionLog->id,
            ];

        } catch (Exception $e) {
            $latencyMs = (int)((microtime(true) - $startTime) * 1000);
            Log::error("AiAgentEngine Exception", ['tenant_id' => $tenantId, 'error' => $e->getMessage()]);

            AgentExecutionLog::create([
                'tenant_id'       => $tenantId,
                'conversation_id' => $conversation?->id,
                'agent_config_id' => $config->id,
                'provider'        => $config->provider,
                'model'           => $config->model,
                'latency_ms'      => $latencyMs,
                'tools_executed'  => array_unique($executedTools),
                'status'          => 'failed',
                'error_message'   => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function buildMessages(string $query, string $contextText, array $history, TenantAgentConfig $config, string $intent, string $lang): array
    {
        $messages = [];

        $toneMap = [
            'professional' => 'Maintain a crisp, professional, and courteous business demeanor.',
            'friendly'     => 'Be warm, encouraging, empathetic, and approachable.',
            'formal'       => 'Use formal honorifics and clear structured explanations.',
            'casual'       => 'Be brief, conversational, and direct.',
        ];

        $toneInstruction = $toneMap[$config->tone] ?? $toneMap['professional'];

        $businessRulesText = "";
        if (!empty($config->business_rules)) {
            $businessRulesText = "\nTENANT BUSINESS RULES:\n- " . implode("\n- ", (array)$config->business_rules) . "\n";
        }

        $customSystemPrompt = $config->system_prompt ?? "You are an enterprise AI Assistant specialized in answering inquiries.";

        $contextInstruction = $config->context_only_mode
            ? "CRITICAL: Zero-hallucination mode is enabled. Strictly reply using provided context only. If absent, request human escalation."
            : "Use the knowledge context primarily, supplementing with general knowledge when appropriate.";

        $systemPrompt = "{$customSystemPrompt}

TONE GUIDELINE: {$toneInstruction}
{$contextInstruction}
{$businessRulesText}
OUTPUT FORMAT (STRICT JSON):
{
  \"reply\": \"Your clear response text\",
  \"language\": \"{$lang}\",
  \"source\": \"RAG | human | order | greeting\",
  \"order_data\": null
}";

        $messages[] = ['role' => 'system', 'content' => $systemPrompt];

        foreach ($history as $msg) {
            $role = is_array($msg) ? ($msg['role'] ?? 'user') : $msg->role;
            $content = is_array($msg) ? ($msg['content'] ?? '') : $msg->content;
            $messages[] = ['role' => $role, 'content' => $content];
        }

        $userPrompt = !empty($contextText)
            ? "KNOWLEDGE CONTEXT:\n---\n{$contextText}\n---\n\nUSER INQUIRY:\n{$query}"
            : "USER INQUIRY:\n{$query}";

        $messages[] = ['role' => 'user', 'content' => $userPrompt];

        return $messages;
    }

    protected function parseJson(string $content): array
    {
        $data = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            return $data;
        }

        preg_match('/\{.*\}/s', $content, $matches);
        if (!empty($matches)) {
            $data = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return $data;
            }
        }

        return ['reply' => $content];
    }

    protected function calculateConfidence(array $hits): string
    {
        if (empty($hits)) return 'low';
        $maxScore = collect($hits)->max('score');
        if ($maxScore >= 0.65) return 'high';
        if ($maxScore >= 0.35) return 'medium';
        return 'low';
    }
}
