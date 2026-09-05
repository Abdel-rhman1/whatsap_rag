<?php

namespace App\Contracts;

use App\Models\TenantAgentConfig;

interface LlmProviderInterface
{
    /**
     * Send chat completion request to the provider model.
     *
     * @param array $messages Array of messages [['role' => 'system'|'user'|'assistant', 'content' => '...']]
     * @param TenantAgentConfig $config Tenant-specific agent configuration
     * @return array Standardized response ['content' => string, 'prompt_tokens' => int, 'completion_tokens' => int]
     */
    public function chat(array $messages, TenantAgentConfig $config): array;
}
