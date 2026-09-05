<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\WhatsAppAccount;
use App\Models\Conversation;
use App\Services\WhatsAppProviderFactory;
use App\Services\WhatsApp\BaileysGatewayProvider;
use App\Services\WhatsApp\MetaCloudApiProvider;
use App\Services\WhatsApp\TwilioWhatsAppProvider;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenantWhatsAppTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = Tenant::create([
            'name' => 'Tenant A',
            'email' => 'a@tenant.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $this->tenantB = Tenant::create([
            'name' => 'Tenant B',
            'email' => 'b@tenant.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
    }

    public function test_tenant_can_have_multiple_whatsapp_accounts_with_different_providers()
    {
        TenantContext::set($this->tenantA);

        $acc1 = WhatsAppAccount::create([
            'tenant_id'          => $this->tenantA->id,
            'name'               => 'Sales Line Baileys',
            'provider'           => 'baileys',
            'account_identifier' => 'baileys_inst_1',
            'phone_number'       => '+111111111',
            'credentials'        => ['gateway_url' => 'http://localhost:3000'],
        ]);

        $acc2 = WhatsAppAccount::create([
            'tenant_id'          => $this->tenantA->id,
            'name'               => 'Support Meta Cloud',
            'provider'           => 'meta',
            'account_identifier' => 'meta_phone_id_999',
            'phone_number'       => '+222222222',
            'credentials'        => ['access_token' => 'EAAG123456SECRET'],
        ]);

        $this->assertEquals(2, $this->tenantA->whatsappAccounts()->count());

        $provider1 = WhatsAppProviderFactory::make($acc1);
        $provider2 = WhatsAppProviderFactory::make($acc2);

        $this->assertInstanceOf(BaileysGatewayProvider::class, $provider1);
        $this->assertInstanceOf(MetaCloudApiProvider::class, $provider2);
    }

    public function test_whatsapp_credentials_are_encrypted_and_hidden_from_json()
    {
        TenantContext::set($this->tenantA);

        $acc = WhatsAppAccount::create([
            'tenant_id'          => $this->tenantA->id,
            'name'               => 'Secure Account',
            'provider'           => 'meta',
            'account_identifier' => 'meta_id_777',
            'credentials'        => ['access_token' => 'super_secret_token_abc'],
        ]);

        // Verify credentials attribute is encrypted in raw database array
        $raw = \DB::table('whatsapp_accounts')->where('id', $acc->id)->first();
        $this->assertStringNotContainsString('super_secret_token_abc', $raw->credentials);

        // Verify hidden from array/json serialization
        $json = $acc->toArray();
        $this->assertArrayNotHasKey('credentials', $json);

        // Verify getter decrypts properly
        $this->assertEquals('super_secret_token_abc', $acc->getCredential('access_token'));
    }

    public function test_incoming_webhook_resolves_account_and_associates_conversation()
    {
        TenantContext::set($this->tenantA);

        $acc = WhatsAppAccount::create([
            'tenant_id'          => $this->tenantA->id,
            'name'               => 'Main Account',
            'provider'           => 'baileys',
            'account_identifier' => 'tenant_a_instance',
            'phone_number'       => '+1000000000',
        ]);

        $response = $this->postJson('/api/v1/whatsapp/webhook', [
            'instance_id' => 'tenant_a_instance',
            'from'        => '+9876543210',
            'body'        => 'Hello AI',
            'pushName'    => 'Customer John',
        ]);

        $response->assertStatus(200);

        // Verify conversation was created and linked to account
        $conv = Conversation::withoutGlobalScopes()->where('external_id', '+9876543210')->first();
        $this->assertNotNull($conv);
        $this->assertEquals($this->tenantA->id, $conv->tenant_id);
        $this->assertEquals($acc->id, $conv->whatsapp_account_id);
    }
}
