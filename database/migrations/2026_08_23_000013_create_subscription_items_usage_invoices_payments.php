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
        if (!Schema::hasTable('subscription_items')) {
            Schema::create('subscription_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade');
                $table->string('feature_key');
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 10, 2)->default(0.00);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('usage_records')) {
            Schema::create('usage_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->onDelete('set null');
                $table->string('metric'); // conversations, ai_tokens, storage_mb, whatsapp_accounts, users
                $table->bigInteger('quantity')->default(1);
                $table->timestamp('recorded_at');
                $table->timestamps();

                $table->index(['tenant_id', 'metric', 'recorded_at']);
            });
        }

        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->onDelete('set null');
                $table->string('invoice_number')->unique();
                $table->decimal('amount_due', 10, 2);
                $table->decimal('amount_paid', 10, 2)->default(0.00);
                $table->string('currency', 3)->default('USD');
                $table->string('status')->default('open'); // draft, open, paid, void, uncollectible
                $table->timestamp('due_date')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
            });
        }

        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
                $table->string('gateway')->default('paymob'); // paymob, stripe, fawry
                $table->string('transaction_id')->nullable();
                $table->decimal('amount', 10, 2);
                $table->string('currency', 3)->default('USD');
                $table->string('status')->default('succeeded'); // succeeded, failed, pending
                $table->json('gateway_response')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('usage_records');
        Schema::dropIfExists('subscription_items');
    }
};
