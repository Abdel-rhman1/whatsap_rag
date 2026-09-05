<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            
            // Gateway tracking
            $table->string('gateway_name')->default('paymob'); // paymob, stripe, fawry
            $table->string('gateway_subscription_id')->nullable();
            
            // Statuses: active, past_due, canceled, trialing
            $table->string('status')->default('trialing');
            
            // Billing cycle
            $table->enum('billing_period', ['monthly', 'yearly'])->default('monthly');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            
            $table->timestamps();
            
            // One active subscription per tenant
            $table->unique(['tenant_id', 'status'], 'tenant_active_subscription_unique')
                  ->where('status', 'active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
