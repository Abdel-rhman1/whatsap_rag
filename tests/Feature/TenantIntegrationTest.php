<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\TenantIntegration;
use App\Models\IntegrationLog;
use App\Services\Integrations\IntegrationManager;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = Tenant::create([
            'name' => 'Company A (HubSpot)',
            'email' => 'a@hubspot.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $this->tenantB = Tenant::create([
            'name' => 'Company B (Salesforce)',
            'email' => 'b@salesforce.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
    }

    public function test_tenant_crm_integration_credentials_are_encrypted_and_hidden()
    {
        TenantContext::set($this->tenantA);

        $integration = TenantIntegration::create([
            'tenant_id'   => $this->tenantA->id,
            'category'    => 'crm',
            'provider'    => 'hubspot',
            'name'        => 'HubSpot Production CRM',
            'is_enabled'  => true,
            'status'      => 'active',
            'credentials' => ['access_token' => 'pat-na1-secret-token-12345'],
        ]);

        // Encrypted in database
        $raw = \DB::table('tenant_integrations')->where('id', $integration->id)->first();
        $this->assertStringNotContainsString('pat-na1-secret-token-12345', $raw->credentials);

        // Hidden from JSON/Array
        $array = $integration->toArray();
        $this->assertArrayNotHasKey('credentials', $array);

        // Decrypted server-side
        $this->assertEquals('pat-na1-secret-token-12345', $integration->getCredential('access_token'));
    }

    public function test_integration_manager_resolves_tenant_specific_adapters()
    {
        TenantContext::set($this->tenantA);
        TenantIntegration::create([
            'tenant_id'   => $this->tenantA->id,
            'category'    => 'crm',
            'provider'    => 'hubspot',
            'is_enabled'  => true,
            'status'      => 'active',
            'credentials' => ['access_token' => 'token_a'],
        ]);

        TenantContext::set($this->tenantB);
        TenantIntegration::create([
            'tenant_id'     => $this->tenantB->id,
            'category'      => 'crm',
            'provider'      => 'salesforce',
            'is_enabled'    => true,
            'status'        => 'active',
            'configuration' => ['instance_url' => 'https://na1.salesforce.com'],
            'credentials'   => ['access_token' => 'token_b'],
        ]);

        $manager = app(IntegrationManager::class);

        // Resolve Tenant A CRM integration
        $intA = $manager->getActiveIntegration($this->tenantA->id, 'crm');
        $this->assertEquals('hubspot', $intA->provider);

        // Resolve Tenant B CRM integration
        $intB = $manager->getActiveIntegration($this->tenantB->id, 'crm');
        $this->assertEquals('salesforce', $intB->provider);
    }
}
