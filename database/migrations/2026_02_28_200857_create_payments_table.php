<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            
            // Transaction Details
            $table->string('transaction_id')->unique(); // Internal tracking ID
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EGP'); // EGP, USD, EUR
            
            // Gateway Details
            $table->string('gateway')->default('paymob');
            $table->string('gateway_transaction_id')->nullable(); // Order ID from gateway
            
            $table->string('status')->default('pending'); // pending, paid, failed, refunded
            $table->string('type')->default('subscription_renewal'); // initial_checkout, renewal, add_on
            
            $table->text('failure_reason')->nullable();
            
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
