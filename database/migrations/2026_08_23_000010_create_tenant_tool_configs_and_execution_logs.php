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
        if (!Schema::hasTable('tenant_tool_configs')) {
            Schema::create('tenant_tool_configs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('tool_name');
                $table->boolean('is_enabled')->default(true);
                $table->json('settings')->nullable();
                $table->text('credentials')->nullable(); // Encrypted credentials
                $table->timestamps();

                $table->unique(['tenant_id', 'tool_name']);
            });
        }

        if (!Schema::hasTable('tool_execution_logs')) {
            Schema::create('tool_execution_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('conversation_id')->nullable()->constrained('conversations')->onDelete('set null');
                $table->string('tool_name');
                $table->json('arguments')->nullable();
                $table->json('response_payload')->nullable();
                $table->string('status')->default('success'); // success, failed, unauthorized
                $table->integer('execution_time_ms')->default(0);
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'tool_name']);
                $table->index(['tenant_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tool_execution_logs');
        Schema::dropIfExists('tenant_tool_configs');
    }
};
