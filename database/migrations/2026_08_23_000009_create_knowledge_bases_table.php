<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('knowledge_bases')) {
            Schema::create('knowledge_bases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('name')->default('Default Knowledge Base');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('vector_collection')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'is_active']);
            });
        }

        if (Schema::hasTable('knowledge_sources') && !Schema::hasColumn('knowledge_sources', 'knowledge_base_id')) {
            Schema::table('knowledge_sources', function (Blueprint $table) {
                $table->foreignId('knowledge_base_id')->nullable()->after('tenant_id')->constrained('knowledge_bases')->onDelete('cascade');
                $table->index(['tenant_id', 'knowledge_base_id']);
            });
        }

        if (Schema::hasTable('knowledge_chunks') && !Schema::hasColumn('knowledge_chunks', 'knowledge_base_id')) {
            Schema::table('knowledge_chunks', function (Blueprint $table) {
                $table->foreignId('knowledge_base_id')->nullable()->after('knowledge_source_id')->constrained('knowledge_bases')->onDelete('cascade');
                $table->index(['tenant_id', 'knowledge_base_id']);
            });
        }

        // Backfill default KnowledgeBase per tenant
        if (Schema::hasTable('tenants')) {
            $tenants = DB::table('tenants')->get();
            foreach ($tenants as $tenant) {
                $kbId = DB::table('knowledge_bases')->insertGetId([
                    'tenant_id'   => $tenant->id,
                    'name'        => 'General Knowledge Base',
                    'description' => 'Default system knowledge base',
                    'is_active'   => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                DB::table('knowledge_sources')
                    ->where('tenant_id', $tenant->id)
                    ->whereNull('knowledge_base_id')
                    ->update(['knowledge_base_id' => $kbId]);

                DB::table('knowledge_chunks')
                    ->where('tenant_id', $tenant->id)
                    ->whereNull('knowledge_base_id')
                    ->update(['knowledge_base_id' => $kbId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('knowledge_chunks') && Schema::hasColumn('knowledge_chunks', 'knowledge_base_id')) {
            Schema::table('knowledge_chunks', function (Blueprint $table) {
                $table->dropForeign(['knowledge_base_id']);
                $table->dropColumn('knowledge_base_id');
            });
        }

        if (Schema::hasTable('knowledge_sources') && Schema::hasColumn('knowledge_sources', 'knowledge_base_id')) {
            Schema::table('knowledge_sources', function (Blueprint $table) {
                $table->dropForeign(['knowledge_base_id']);
                $table->dropColumn('knowledge_base_id');
            });
        }

        Schema::dropIfExists('knowledge_bases');
    }
};
