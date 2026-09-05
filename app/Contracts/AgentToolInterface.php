<?php

namespace App\Contracts;

interface AgentToolInterface
{
    /**
     * Unique tool identifier name (e.g. 'knowledge_search', 'order_creation', 'human_handoff', 'crm_lookup', 'http_api_tool').
     */
    public function name(): string;

    /**
     * Tool description for AI prompt context.
     */
    public function description(): string;

    /**
     * Validate tool parameters before execution.
     */
    public function validate(array $params): bool;

    /**
     * Execute tool logic with tenant scope guarantees.
     */
    public function execute(array $params, int $tenantId, array $context = []): array;
}
