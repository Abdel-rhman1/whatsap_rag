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
        if (!Schema::hasTable('tenant_integrations')) {
            Schema::create('tenant_integrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('category'); // crm, calendar, messaging, payment, custom
                $table->string('provider'); // hubspot, salesforce, google_calendar, custom_rest, paymob, stripe
                $table->string('name')->default('Default Integration');
                $table->boolean('is_enabled')->default(true);
                $table->string('status')->default('active'); // active, error, disconnected, pending
                $table->json('configuration')->nullable();
                $table->text('credentials')->nullable(); // Encrypted array
                $table->string('webhook_url')->nullable();
                $table->text('webhook_secret')->nullable(); // Encrypted secret
                $table->timestamp('last_synced_at')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'category', 'provider']);
                $table->index(['tenant_id', 'category', 'is_enabled']);
            });
        }

        if (!Schema::hasTable('integration_logs')) {
            Schema::create('integration_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('integration_id')->constrained('tenant_integrations')->onDelete('cascade');
                $table->string('action');
                $table->string('status')->default('success'); // success, failed
                $table->json('request_payload')->nullable();
                $table->json('response_payload')->nullable();
                $table->integer('execution_time_ms')->default(0);
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'integration_id']);
                $table->index(['tenant_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_logs');
        Schema::dropIfExists('tenant_integrations');
    }
};
