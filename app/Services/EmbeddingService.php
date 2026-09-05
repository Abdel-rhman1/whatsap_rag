<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class EmbeddingService
{
    protected string $url;
    protected string $model;

    public function __construct()
    {
        $this->url = config('rag.ollama.url', 'http://ollama:11434');
        $this->model = config('rag.ollama.embedding_model', 'nomic-embed-text');
    }

    public function embed(string $text): array
    {
        $maxRetries = 3;
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(30)->post($this->url . '/api/embeddings', [
                    'model'  => $this->model,
                    'prompt' => $text,
                ]);

                if ($response->failed()) {
                    throw new Exception("Ollama embedding failed (attempt {$attempt}): " . $response->body());
                }

                $embedding = $response->json('embedding');

                if (empty($embedding)) {
                    throw new Exception("Ollama returned empty embedding vector.");
                }

                return $embedding;

            } catch (Exception $e) {
                $lastException = $e;
                if ($attempt < $maxRetries) {
                    sleep(1); // Brief pause before retry
                }
            }
        }

        throw $lastException;
    }
}
