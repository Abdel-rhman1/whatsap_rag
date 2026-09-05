<?php

namespace App\Services\Agent;

use App\Contracts\AgentToolInterface;
use App\Models\TenantAgentConfig;
use App\Services\Agent\Tools\KnowledgeSearchTool;
use App\Services\Agent\Tools\HumanHandoffTool;
use Illuminate\Contracts\Container\Container;

class AgentToolRegistry
{
    protected array $tools = [];

    public function __construct(Container $container)
    {
        // Register RAG and Human Handoff tools into registry
        $this->register($container->make(KnowledgeSearchTool::class));
        $this->register($container->make(HumanHandoffTool::class));
    }

    public function register(AgentToolInterface $tool): void
    {
        $this->tools[$tool->name()] = $tool;
    }

    /**
     * Get only the tools enabled for the given tenant agent configuration.
     *
     * @return AgentToolInterface[]
     */
    public function getEnabledToolsForConfig(TenantAgentConfig $config): array
    {
        $enabledNames = $config->getEffectiveTools();
        $activeTools = [];

        foreach ($enabledNames as $name) {
            if (isset($this->tools[$name])) {
                $activeTools[$name] = $this->tools[$name];
            }
        }

        return $activeTools;
    }
}
