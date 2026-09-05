<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Customer;
use App\Models\User;
use App\Services\AdminService;
use App\Services\TenantService;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaasDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = Tenant::create([
            'name' => 'Metrics Alpha',
            'email' => 'alpha@metrics.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $this->tenantB = Tenant::create([
            'name' => 'Metrics Beta',
            'email' => 'beta@metrics.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
    }

    public function test_platform_admin_global_dashboard_metrics()
    {
        $adminService = app(AdminService::class);
        $stats = $adminService->getGlobalStats();

        $this->assertArrayHasKey('total_tenants', $stats);
        $this->assertArrayHasKey('active_tenants', $stats);
        $this->assertArrayHasKey('subscription_status', $stats);
        $this->assertArrayHasKey('total_conversations', $stats);
        $this->assertArrayHasKey('ai_usage', $stats);
        $this->assertArrayHasKey('token_usage', $stats);
        $this->assertArrayHasKey('whatsapp_usage', $stats);
        $this->assertArrayHasKey('revenue', $stats);
        $this->assertArrayHasKey('errors', $stats);
        $this->assertArrayHasKey('system_health', $stats);

        $this->assertGreaterThanOrEqual(2, $stats['total_tenants']);
    }

    public function test_tenant_admin_metrics_are_strictly_tenant_scoped()
    {
        // Seed Tenant A data
        TenantContext::set($this->tenantA);

        $convA = Conversation::create([
            'tenant_id' => $this->tenantA->id,
            'external_id' => '201000000001',
            'channel' => 'whatsapp',
            'status' => 'active',
            'escalated_to_human' => false,
        ]);

        Message::create([
            'tenant_id' => $this->tenantA->id,
            'conversation_id' => $convA->id,
            'role' => 'user',
            'content' => 'Hello Alpha',
        ]);

        Customer::create([
            'tenant_id' => $this->tenantA->id,
            'phone_number' => '201000000001',
            'name' => 'Customer A',
        ]);

        // Seed Tenant B data
        TenantContext::set($this->tenantB);

        $convB1 = Conversation::create([
            'tenant_id' => $this->tenantB->id,
            'external_id' => '201000000002',
            'channel' => 'whatsapp',
            'status' => 'active',
            'escalated_to_human' => true,
        ]);

        $convB2 = Conversation::create([
            'tenant_id' => $this->tenantB->id,
            'external_id' => '201000000003',
            'channel' => 'whatsapp',
            'status' => 'active',
            'escalated_to_human' => false,
        ]);

        $tenantService = app(TenantService::class);

        // Assert Tenant A Stats
        $statsA = $tenantService->getDashboardStats($this->tenantA);
        $this->assertEquals(1, $statsA['conversations']);
        $this->assertEquals(1, $statsA['messages']);
        $this->assertEquals(1, $statsA['customers']);
        $this->assertEquals(0, $statsA['human_escalations']);
        $this->assertEquals(100.0, $statsA['ai_resolution_rate']);

        // Assert Tenant B Stats
        $statsB = $tenantService->getDashboardStats($this->tenantB);
        $this->assertEquals(2, $statsB['conversations']);
        $this->assertEquals(1, $statsB['human_escalations']);
        $this->assertEquals(50.0, $statsB['ai_resolution_rate']);
    }
}
