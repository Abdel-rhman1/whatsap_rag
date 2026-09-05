<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class GroqService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://api.groq.com/openai/v1';

    public function __construct()
    {
        $this->apiKey = config('rag.groq.api_key');
        $this->model = config('rag.groq.model', 'llama-3.3-70b-versatile');
    }

    public function chat(array $messages, bool $stream = false): array
    {
        if (empty($this->apiKey)) {
            throw new Exception("Groq API Key is not configured.");
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post($this->baseUrl . '/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
                'stream' => $stream,
                'response_format' => ['type' => 'json_object']
            ]);

        if ($response->failed()) {
            throw new Exception("Groq Chat API failed: " . $response->body());
        }

        $result = $response->json();
        
        // Map Groq/OpenAI response format to match what RagChatService expects (which was based on Ollama)
        // RagChatService uses $response['message']['content']
        return [
            'message' => [
                'content' => $result['choices'][0]['message']['content'] ?? ''
            ]
        ];
    }
    public function summarize(string $text): string
    {
        if (empty($this->apiKey)) {
            throw new Exception("Groq API Key is not configured.");
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post($this->baseUrl . '/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'Summarize the following text concisely. Capture the key points and main intent. Keep the summary under 500 characters.'],
                    ['role' => 'user', 'content' => $text]
                ],
                'stream' => false
            ]);

        if ($response->failed()) {
            return substr($text, 0, 500) . "..."; // Fallback to truncation
        }

        $result = $response->json();
        return $result['choices'][0]['message']['content'] ?? "";
    }
}
