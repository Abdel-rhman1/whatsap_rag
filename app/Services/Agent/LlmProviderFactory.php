<?php

namespace App\Services\Agent;

use App\Contracts\LlmProviderInterface;
use App\Models\TenantAgentConfig;
use App\Services\Agent\Providers\GroqLlmProvider;
use App\Services\Agent\Providers\OpenAiLlmProvider;
use App\Services\Agent\Providers\OllamaLlmProvider;

class LlmProviderFactory
{
    public static function make(TenantAgentConfig $config): LlmProviderInterface
    {
        return match (strtolower($config->provider)) {
            'openai'  => new OpenAiLlmProvider(),
            'ollama'  => new OllamaLlmProvider(),
            'groq'    => new GroqLlmProvider(),
            default   => new GroqLlmProvider(),
        };
    }
}
