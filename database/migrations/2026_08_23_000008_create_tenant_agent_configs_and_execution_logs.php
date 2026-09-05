<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('tenant_agent_configs')) {
            Schema::create('tenant_agent_configs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('agent_name')->default('Enterprise AI Agent');
                $table->string('provider')->default('groq'); // groq, openai, ollama, anthropic
                $table->string('model')->default('llama-3.3-70b-versatile');
                $table->text('system_prompt')->nullable();
                $table->float('temperature')->default(0.3);
                $table->integer('max_tokens')->default(1024);
                $table->string('language')->default('auto'); // ar, en, auto
                $table->string('tone')->default('professional'); // professional, friendly, formal, casual
                $table->json('business_rules')->nullable();
                $table->float('similarity_threshold')->default(0.35);
                $table->boolean('context_only_mode')->default(false);
                $table->json('enabled_tools')->nullable(); // ["knowledge_search", "order_creation", "human_handoff", "crm_lookup"]
                $table->json('escalation_rules')->nullable();
                $table->json('working_hours')->nullable();
                $table->string('human_handoff_behavior')->default('escalate_immediately');
                $table->json('conversation_limits')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['tenant_id', 'is_active']);
            });
        }

        if (!Schema::hasTable('agent_execution_logs')) {
            Schema::create('agent_execution_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('conversation_id')->nullable()->constrained('conversations')->onDelete('set null');
                $table->foreignId('message_id')->nullable()->constrained('messages')->onDelete('set null');
                $table->foreignId('agent_config_id')->nullable()->constrained('tenant_agent_configs')->onDelete('set null');
                $table->string('provider');
                $table->string('model');
                $table->integer('prompt_tokens')->default(0);
                $table->integer('completion_tokens')->default(0);
                $table->integer('total_tokens')->default(0);
                $table->integer('latency_ms')->default(0);
                $table->json('tools_executed')->nullable();
                $table->string('status')->default('success'); // success, failed, escalated
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_execution_logs');
        Schema::dropIfExists('tenant_agent_configs');
    }
};
