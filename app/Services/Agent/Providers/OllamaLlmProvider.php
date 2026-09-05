<?php

namespace App\Services\Agent\Providers;

use App\Contracts\LlmProviderInterface;
use App\Models\TenantAgentConfig;
use Illuminate\Support\Facades\Http;
use Exception;

class OllamaLlmProvider implements LlmProviderInterface
{
    public function chat(array $messages, TenantAgentConfig $config): array
    {
        $baseUrl = config('rag.ollama.url', 'http://localhost:11434');
        $model   = $config->model ?: config('rag.ollama.model', 'llama3');

        $response = Http::timeout(120)->post("{$baseUrl}/api/chat", [
            'model'    => $model,
            'messages' => $messages,
            'format'   => 'json',
            'stream'   => false,
            'options'  => [
                'temperature' => $config->temperature ?? 0.3,
            ]
        ]);

        if ($response->failed()) {
            throw new Exception("Ollama LLM Provider Error: " . $response->body());
        }

        $data = $response->json();

        return [
            'content'           => $data['message']['content'] ?? '',
            'prompt_tokens'     => $data['prompt_eval_count'] ?? 0,
            'completion_tokens' => $data['eval_count'] ?? 0,
            'total_tokens'      => ($data['prompt_eval_count'] ?? 0) + ($data['eval_count'] ?? 0),
        ];
    }
}
