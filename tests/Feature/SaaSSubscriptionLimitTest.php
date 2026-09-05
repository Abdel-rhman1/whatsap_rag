<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\WhatsAppAccount;
use App\Models\Conversation;
use App\Services\TenantLimitService;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaaSSubscriptionLimitTest extends TestCase
{
    use RefreshDatabase;

    protected Plan $freePlan;
    protected Plan $proPlan;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->freePlan = Plan::create([
            'name' => 'Free Plan',
            'slug' => 'free',
            'monthly_price' => 0.00,
            'yearly_price' => 0.00,
            'limits' => [
                'max_whatsapp_accounts'     => 1,
                'max_users'                 => 2,
                'max_knowledge_bases'       => 1,
                'max_monthly_conversations' => 5,
                'max_ai_tokens'             => 1000,
                'allowed_integrations'      => ['hubspot'],
            ],
            'is_active' => true,
        ]);

        $this->proPlan = Plan::create([
            'name' => 'Pro Plan',
            'slug' => 'pro',
            'monthly_price' => 99.00,
            'yearly_price' => 990.00,
            'limits' => [
                'max_whatsapp_accounts'     => 10,
                'max_users'                 => 20,
                'max_knowledge_bases'       => 10,
                'max_monthly_conversations' => 10000,
                'max_ai_tokens'             => 500000,
                'allowed_integrations'      => ['*'],
            ],
            'is_active' => true,
        ]);

        $this->tenant = Tenant::create([
            'name' => 'Subscription Test Tenant',
            'email' => 'sub@tenant.com',
            'password' => bcrypt('password'),
            'status' => 'active',
            'plan_id' => $this->freePlan->id,
        ]);
    }

    public function test_tenant_limit_service_answers_resource_and_feature_questions()
    {
        TenantContext::set($this->tenant);
        $service = app(TenantLimitService::class);

        // 1. WhatsApp Account Limit Test
        $this->assertTrue($service->canCreateWhatsAppAccount($this->tenant));

        WhatsAppAccount::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Account 1',
            'phone_number' => '201000000001',
            'provider' => 'baileys',
            'status' => 'connected',
        ]);

        $this->assertFalse($service->canCreateWhatsAppAccount($this->tenant));

        // 2. Feature Availability Test
        $this->assertTrue($service->hasFeature($this->tenant, 'hubspot'));
        $this->assertFalse($service->hasFeature($this->tenant, 'salesforce'));

        // 3. Monthly Conversation Allowance Test
        $this->assertFalse($service->hasExceededConversations($this->tenant));

        for ($i = 0; $i < 5; $i++) {
            Conversation::create([
                'tenant_id' => $this->tenant->id,
                'external_id' => "20100000000{$i}",
                'channel' => 'whatsapp',
                'status' => 'active',
            ]);
        }

        $this->assertTrue($service->hasExceededConversations($this->tenant));
    }

    public function test_upgrading_plan_instantly_expands_tenant_limits()
    {
        TenantContext::set($this->tenant);
        $service = app(TenantLimitService::class);

        WhatsAppAccount::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Account 1',
            'phone_number' => '201000000001',
            'provider' => 'baileys',
            'status' => 'connected',
        ]);

        $this->assertFalse($service->canCreateWhatsAppAccount($this->tenant));

        // Upgrade Plan
        $this->tenant->update(['plan_id' => $this->proPlan->id]);
        $this->tenant->refresh();

        $this->assertTrue($service->canCreateWhatsAppAccount($this->tenant));
        $this->assertTrue($service->hasFeature($this->tenant, 'salesforce'));
    }
}
