<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('action')->comment('create_tenant, revoke_api_key, etc');
            $table->string('entity_type')->nullable()->comment('Tenant, ApiKey, etc');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('metadata')->nullable()->comment('Additional context');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['admin_user_id', 'created_at']);
            $table->index(['tenant_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
    }
};
