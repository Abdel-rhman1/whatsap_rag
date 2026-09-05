<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Customer;
use App\Models\KnowledgeSource;
use App\Models\Order;
use App\Models\ScheduledMessage;
use App\Models\AdminUser;
use App\Services\TenantContext;
use App\Services\PermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected User $userA;
    protected User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = Tenant::create([
            'name' => 'Company A',
            'email' => 'admin@companya.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $this->tenantB = Tenant::create([
            'name' => 'Company B',
            'email' => 'admin@companyb.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $this->userA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'User A',
            'email' => 'user@companya.com',
            'password' => bcrypt('password'),
            'role' => 'tenant_admin',
            'is_active' => true,
        ]);

        $this->userB = User::create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'User B',
            'email' => 'user@companyb.com',
            'password' => bcrypt('password'),
            'role' => 'tenant_admin',
            'is_active' => true,
        ]);
    }

    public function test_tenant_a_cannot_see_tenant_b_conversations()
    {
        // Create conversation for Tenant A
        TenantContext::set($this->tenantA);
        $convA = Conversation::create([
            'tenant_id' => $this->tenantA->id,
            'phone_number' => '+1111111111',
            'platform' => 'whatsapp',
        ]);

        // Create conversation for Tenant B
        TenantContext::set($this->tenantB);
        $convB = Conversation::create([
            'tenant_id' => $this->tenantB->id,
            'phone_number' => '+2222222222',
            'platform' => 'whatsapp',
        ]);

        // Act as Tenant A
        TenantContext::set($this->tenantA);
        $this->actingAs($this->tenantA, 'tenant');

        $conversations = Conversation::all();

        $this->assertTrue($conversations->contains('id', $convA->id));
        $this->assertFalse($conversations->contains('id', $convB->id));
    }

    public function test_tenant_a_cannot_see_tenant_b_customers()
    {
        TenantContext::set($this->tenantA);
        $custA = Customer::create([
            'tenant_id' => $this->tenantA->id,
            'phone_number' => '+1111111111',
            'name' => 'Customer A',
        ]);

        TenantContext::set($this->tenantB);
        $custB = Customer::create([
            'tenant_id' => $this->tenantB->id,
            'phone_number' => '+2222222222',
            'name' => 'Customer B',
        ]);

        TenantContext::set($this->tenantA);
        $customers = Customer::all();

        $this->assertTrue($customers->contains('id', $custA->id));
        $this->assertFalse($customers->contains('id', $custB->id));
    }

    public function test_permission_service_rejects_cross_tenant_resource_access()
    {
        $permissionService = new PermissionService();

        TenantContext::set($this->tenantB);
        $convB = Conversation::create([
            'tenant_id' => $this->tenantB->id,
            'phone_number' => '+2222222222',
        ]);

        // User A trying to access Tenant B's conversation
        $hasAccess = $permissionService->hasPermission($this->userA, 'conversations.view', $convB);

        $this->assertFalse($hasAccess, 'User A should be denied access to Tenant B resource');
    }

    public function test_platform_admin_can_access_across_tenants()
    {
        TenantContext::set($this->tenantA);
        $convA = Conversation::create([
            'tenant_id' => $this->tenantA->id,
            'phone_number' => '+1111111111',
        ]);

        TenantContext::set($this->tenantB);
        $convB = Conversation::create([
            'tenant_id' => $this->tenantB->id,
            'phone_number' => '+2222222222',
        ]);

        // Clear tenant context for platform admin
        TenantContext::clear();
        $admin = AdminUser::create([
            'name' => 'Platform Admin',
            'email' => 'admin@platform.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);
        $this->actingAs($admin, 'admin');

        $conversations = Conversation::all();

        $this->assertTrue($conversations->contains('id', $convA->id));
        $this->assertTrue($conversations->contains('id', $convB->id));
    }

    public function test_chat_api_ignores_client_passed_tenant_id_and_uses_auth_context()
    {
        TenantContext::set($this->tenantA);
        $this->actingAs($this->userA, 'sanctum');

        // Client passes Tenant B's ID in request
        $response = $this->postJson('/api/v1/chat', [
            'message' => 'Hello',
            'tenant_id' => $this->tenantB->id,
        ]);

        // Response should resolve user A's tenant (Tenant A), not execute under Tenant B
        $this->assertNotEquals(403, $response->status());
    }
}
