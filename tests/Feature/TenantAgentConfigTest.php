<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\TenantAgentConfig;
use App\Models\AgentExecutionLog;
use App\Services\Agent\AiAgentEngine;
use App\Services\Agent\AgentToolRegistry;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantAgentConfigTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = Tenant::create([
            'name' => 'Company Alpha',
            'email' => 'alpha@company.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $this->tenantB = Tenant::create([
            'name' => 'Company Beta',
            'email' => 'beta@company.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
    }

    public function test_tenants_have_isolated_agent_configurations()
    {
        TenantContext::set($this->tenantA);
        $configA = TenantAgentConfig::create([
            'tenant_id'     => $this->tenantA->id,
            'agent_name'    => 'Alpha Customer Bot',
            'provider'      => 'groq',
            'model'         => 'llama-3.3-70b-versatile',
            'system_prompt' => 'You are Alpha Bot. Always say Welcome to Alpha.',
            'tone'          => 'friendly',
            'enabled_tools' => ['knowledge_search', 'order_creation'],
        ]);

        TenantContext::set($this->tenantB);
        $configB = TenantAgentConfig::create([
            'tenant_id'     => $this->tenantB->id,
            'agent_name'    => 'Beta Support Bot',
            'provider'      => 'openai',
            'model'         => 'gpt-4o',
            'system_prompt' => 'You are Beta Bot. Be extremely formal.',
            'tone'          => 'formal',
            'enabled_tools' => ['human_handoff'],
        ]);

        $this->assertNotEquals($configA->system_prompt, $configB->system_prompt);
        $this->assertNotEquals($configA->model, $configB->model);
        $this->assertNotEquals($configA->enabled_tools, $configB->enabled_tools);
    }

    public function test_agent_tool_registry_filters_tools_per_tenant_config()
    {
        TenantContext::set($this->tenantA);
        $configA = TenantAgentConfig::create([
            'tenant_id'     => $this->tenantA->id,
            'enabled_tools' => ['knowledge_search'],
        ]);

        $registry = app(AgentToolRegistry::class);
        $enabledTools = $registry->getEnabledToolsForConfig($configA);

        $this->assertArrayHasKey('knowledge_search', $enabledTools);
        $this->assertArrayNotHasKey('order_creation', $enabledTools);
        $this->assertArrayNotHasKey('human_handoff', $enabledTools);
    }

    public function test_ai_agent_engine_logs_execution_and_tokens()
    {
        TenantContext::set($this->tenantA);
        TenantAgentConfig::create([
            'tenant_id'     => $this->tenantA->id,
            'provider'      => 'groq',
            'model'         => 'llama-3.3-70b-versatile',
            'enabled_tools' => ['knowledge_search'],
        ]);

        $engine = app(AiAgentEngine::class);

        $response = $engine->run('Hello AI', $this->tenantA->id, [
            'external_id' => '+1234567890',
            'platform'    => 'whatsapp',
        ]);

        $this->assertNotNull($response['reply']);

        // Verify execution log stored with correct tenant context
        $log = AgentExecutionLog::withoutGlobalScopes()->where('tenant_id', $this->tenantA->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals('groq', $log->provider);
        $this->assertEquals($this->tenantA->id, $log->tenant_id);
    }
}
