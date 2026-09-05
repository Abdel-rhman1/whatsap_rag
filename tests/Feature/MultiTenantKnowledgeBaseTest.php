<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\KnowledgeBase;
use App\Models\KnowledgeSource;
use App\Models\KnowledgeChunk;
use App\Services\QdrantService;
use App\Services\Agent\Tools\KnowledgeSearchTool;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenantKnowledgeBaseTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = Tenant::create([
            'name' => 'Company Alpha',
            'email' => 'alpha@kb.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $this->tenantB = Tenant::create([
            'name' => 'Company Beta',
            'email' => 'beta@kb.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
    }

    public function test_tenants_have_isolated_knowledge_bases_and_documents()
    {
        TenantContext::set($this->tenantA);

        $kbA = KnowledgeBase::create([
            'tenant_id'   => $this->tenantA->id,
            'name'        => 'Alpha Confidential KB',
            'description' => 'Alpha Internal Files',
        ]);

        $sourceA = KnowledgeSource::create([
            'tenant_id'         => $this->tenantA->id,
            'knowledge_base_id' => $kbA->id,
            'name'              => 'alpha_secret.pdf',
            'type'              => 'pdf',
            'status'            => 'indexed',
        ]);

        $chunkA = KnowledgeChunk::create([
            'tenant_id'           => $this->tenantA->id,
            'knowledge_base_id'   => $kbA->id,
            'knowledge_source_id' => $sourceA->id,
            'content'             => 'Alpha Secret Code is ALPHA-999',
            'chunk_index'         => 0,
        ]);

        TenantContext::set($this->tenantB);

        $kbB = KnowledgeBase::create([
            'tenant_id'   => $this->tenantB->id,
            'name'        => 'Beta Confidential KB',
            'description' => 'Beta Internal Files',
        ]);

        $sourceB = KnowledgeSource::create([
            'tenant_id'         => $this->tenantB->id,
            'knowledge_base_id' => $kbB->id,
            'name'              => 'beta_secret.pdf',
            'type'              => 'pdf',
            'status'            => 'indexed',
        ]);

        $chunkB = KnowledgeChunk::create([
            'tenant_id'           => $this->tenantB->id,
            'knowledge_base_id'   => $kbB->id,
            'knowledge_source_id' => $sourceB->id,
            'content'             => 'Beta Secret Code is BETA-777',
            'chunk_index'         => 0,
        ]);

        // Test SQL Scoping
        TenantContext::set($this->tenantA);
        $kbsForA = KnowledgeBase::all();
        $chunksForA = KnowledgeChunk::all();

        $this->assertTrue($kbsForA->contains('id', $kbA->id));
        $this->assertFalse($kbsForA->contains('id', $kbB->id));

        $this->assertTrue($chunksForA->contains('id', $chunkA->id));
        $this->assertFalse($chunksForA->contains('id', $chunkB->id));
    }

    public function test_vector_search_strictly_enforces_tenant_and_knowledge_base_filtering()
    {
        TenantContext::set($this->tenantA);

        $kbA = KnowledgeBase::create([
            'tenant_id' => $this->tenantA->id,
            'name'      => 'Alpha Knowledge',
        ]);

        $searchTool = app(KnowledgeSearchTool::class);

        $result = $searchTool->execute([
            'query'              => 'Alpha Secret Code',
            'knowledge_base_ids' => [$kbA->id],
        ], $this->tenantA->id);

        $this->assertIsArray($result['hits']);

        // Verify no Tenant B data is returned
        foreach ($result['hits'] as $hit) {
            $this->assertEquals($this->tenantA->id, $hit['metadata']['tenant_id']);
        }
    }
}
