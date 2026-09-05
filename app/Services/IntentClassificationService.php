<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class IntentClassificationService
{
    public function __construct(
        protected GroqService $llm
    ) {}

    /**
     * Classify user message intent
     */
    public function classify(string $query, array $history = []): array
    {
        $systemPrompt = "You are an AI assistant specialized in classifying user intents for a WhatsApp RAG system.
        
        Classify the user message into ONE of these intents:
        1. GREETING: 'Hi', 'Hello', 'السلام عليكم', 'Good morning'
        2. THANKING: 'Thanks', 'Thank you', 'مشكور'
        3. FAREWELL: 'Bye', 'See you', 'مع السلامة'
        4. SMALL_TALK: 'How are you?', 'What's up?', 'How's your day?'
        5. GENERAL_KNOWLEDGE: 'What is AI?', 'Explain WhatsApp', 'How to fly?' (General questions not related to the private knowledge base)
        6. DOMAIN_QUESTION: Questions specifically about the business, APIs, pricing, technical documentation, or anything that would require searching a private knowledge base.
        7. ORDER_CREATION: When the user wants to book an appointment, make an order, request a service (e.g. 'عايز احجز كشف', 'I want to order', 'book a consultation').
        8. UNCLEAR: If the message is too short, ambiguous, or nonsensical that you cannot determine what the user wants.

        OUTPUT FORMAT (STRICT JSON):
        {
          \"intent\": \"GREETING | THANKING | FAREWELL | SMALL_TALK | GENERAL_KNOWLEDGE | DOMAIN_QUESTION | ORDER_CREATION | UNCLEAR\",
          \"confidence\": float (0-1),
          \"language\": \"ar | en\",
          \"reason\": \"brief explanation\"
        }";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        // Add context from last 2 messages for better classification (e.g. if user says "Thanks" after an answer)
        foreach (array_slice($history, -2) as $msg) {
            $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }

        $messages[] = ['role' => 'user', 'content' => $query];

        try {
            $response = $this->llm->chat($messages);
            $content = $response['message']['content'] ?? "";
            
            Log::info("Intent Classification RAW: " . $content);

            $data = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Fallback parsing if LLM failed to return clean JSON
                preg_match('/\{.*\}/s', $content, $matches);
                if (!empty($matches)) {
                    $data = json_decode($matches[0], true);
                }
            }

            return $data ?? [
                'intent' => 'DOMAIN_QUESTION', // Safe default
                'confidence' => 0.5,
                'language' => 'ar',
                'reason' => 'parsing failed'
            ];

        } catch (\Exception $e) {
            Log::error("Intent Classification Error: " . $e->getMessage());
            return [
                'intent' => 'DOMAIN_QUESTION',
                'confidence' => 0.5,
                'language' => 'ar',
                'reason' => 'exception occurred'
            ];
        }
    }
}
