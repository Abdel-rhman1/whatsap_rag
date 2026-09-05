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
        if (!Schema::hasTable('whatsapp_accounts')) {
            Schema::create('whatsapp_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('name')->default('Main WhatsApp Account');
                $table->string('provider')->default('baileys'); // baileys, meta, twilio
                $table->string('account_identifier')->unique(); // instance_name or phone_number_id
                $table->string('phone_number')->nullable();
                $table->string('status')->default('disconnected');
                $table->boolean('is_active')->default(true);
                $table->text('qr_code')->nullable();
                $table->text('credentials')->nullable(); // Encrypted credentials payload
                $table->string('webhook_verify_token')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'provider']);
            });
        }

        if (Schema::hasTable('conversations') && !Schema::hasColumn('conversations', 'whatsapp_account_id')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->foreignId('whatsapp_account_id')->nullable()->after('tenant_id')->constrained('whatsapp_accounts')->onDelete('set null');
                $table->index(['tenant_id', 'whatsapp_account_id']);
            });
        }

        if (Schema::hasTable('campaigns') && !Schema::hasColumn('campaigns', 'whatsapp_account_id')) {
            Schema::table('campaigns', function (Blueprint $table) {
                $table->foreignId('whatsapp_account_id')->nullable()->after('whatsapp_instance_id')->constrained('whatsapp_accounts')->onDelete('set null');
            });
        }

        // Backfill existing whatsapp_instances into whatsapp_accounts if table exists
        if (Schema::hasTable('whatsapp_instances')) {
            $instances = DB::table('whatsapp_instances')->get();
            foreach ($instances as $inst) {
                $existing = DB::table('whatsapp_accounts')->where('account_identifier', $inst->instance_name)->first();
                if (!$existing) {
                    DB::table('whatsapp_accounts')->insert([
                        'tenant_id'          => $inst->tenant_id,
                        'name'               => 'WhatsApp Instance (' . $inst->instance_name . ')',
                        'provider'           => 'baileys',
                        'account_identifier' => $inst->instance_name,
                        'phone_number'       => $inst->phone_number ?? null,
                        'status'             => $inst->status ?? 'disconnected',
                        'is_active'          => $inst->is_active ?? true,
                        'qr_code'            => $inst->qr_code ?? null,
                        'created_at'         => $inst->created_at ?? now(),
                        'updated_at'         => $inst->updated_at ?? now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('campaigns') && Schema::hasColumn('campaigns', 'whatsapp_account_id')) {
            Schema::table('campaigns', function (Blueprint $table) {
                $table->dropForeign(['whatsapp_account_id']);
                $table->dropColumn('whatsapp_account_id');
            });
        }

        if (Schema::hasTable('conversations') && Schema::hasColumn('conversations', 'whatsapp_account_id')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->dropForeign(['whatsapp_account_id']);
                $table->dropColumn('whatsapp_account_id');
            });
        }

        Schema::dropIfExists('whatsapp_accounts');
    }
};
