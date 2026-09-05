<?php

namespace App\Services\Agent\Providers;

use App\Contracts\LlmProviderInterface;
use App\Models\TenantAgentConfig;
use Illuminate\Support\Facades\Http;
use Exception;

class GroqLlmProvider implements LlmProviderInterface
{
    protected string $baseUrl = 'https://api.groq.com/openai/v1';

    public function chat(array $messages, TenantAgentConfig $config): array
    {
        $apiKey = config('rag.groq.api_key');
        if (empty($apiKey)) {
            throw new Exception("Groq API Key is not configured in environment.");
        }

        $model = $config->model ?: config('rag.groq.model', 'llama-3.3-70b-versatile');

        $response = Http::withToken($apiKey)
            ->timeout(60)
            ->post($this->baseUrl . '/chat/completions', [
                'model'           => $model,
                'messages'        => $messages,
                'temperature'     => $config->temperature ?? 0.3,
                'max_tokens'      => $config->max_tokens ?? 1024,
                'response_format' => ['type' => 'json_object']
            ]);

        if ($response->failed()) {
            throw new Exception("Groq LLM Provider Error: " . $response->body());
        }

        $data = $response->json();

        return [
            'content'           => $data['choices'][0]['message']['content'] ?? '',
            'prompt_tokens'     => $data['usage']['prompt_tokens'] ?? 0,
            'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
            'total_tokens'      => $data['usage']['total_tokens'] ?? 0,
        ];
    }
}
