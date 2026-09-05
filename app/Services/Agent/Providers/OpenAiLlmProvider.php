<?php

namespace App\Services\Agent\Providers;

use App\Contracts\LlmProviderInterface;
use App\Models\TenantAgentConfig;
use Illuminate\Support\Facades\Http;
use Exception;

class OpenAiLlmProvider implements LlmProviderInterface
{
    protected string $baseUrl = 'https://api.openai.com/v1';

    public function chat(array $messages, TenantAgentConfig $config): array
    {
        $apiKey = config('rag.openai.api_key', env('OPENAI_API_KEY'));
        if (empty($apiKey)) {
            throw new Exception("OpenAI API Key is missing.");
        }

        $model = $config->model ?: 'gpt-4o-mini';

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
            throw new Exception("OpenAI LLM Provider Error: " . $response->body());
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
