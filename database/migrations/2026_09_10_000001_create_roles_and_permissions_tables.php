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
        // 1. Permissions table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g. conversations.view
            $table->string('label'); // e.g. View Conversations
            $table->string('module'); // e.g. conversations, whatsapp, knowledge, users, sla, billing, settings, api
            $table->text('description')->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high', 'danger'])->default('low');
            $table->timestamps();
        });

        // 2. Roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('name'); // e.g. Agent, Support Specialist
            $table->string('slug'); // e.g. agent, support_specialist
            $table->text('description')->nullable();
            $table->string('color')->default('#6366f1');
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });

        // 3. Role Permissions pivot
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        // 4. Tenant Permission Settings
        Schema::create('tenant_permission_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained('tenants')->cascadeOnDelete();
            $table->boolean('allow_member_invites')->default(true);
            $table->string('default_role')->default('agent');
            $table->boolean('require_2fa_for_admins')->default(false);
            $table->boolean('allow_conversation_export')->default(false);
            $table->boolean('mask_customer_pii')->default(false);
            $table->integer('session_timeout_minutes')->default(120);
            $table->json('enabled_modules')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_permission_settings');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};
