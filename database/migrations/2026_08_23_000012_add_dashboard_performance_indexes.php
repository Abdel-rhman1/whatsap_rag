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
        if (Schema::hasTable('conversations') && !Schema::hasColumn('conversations', 'idx_tenant_escalated')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->index(['tenant_id', 'escalated_to_human']);
            });
        }

        if (Schema::hasTable('messages') && !Schema::hasColumn('messages', 'idx_tenant_created')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->index(['tenant_id', 'created_at']);
            });
        }

        if (Schema::hasTable('customers') && !Schema::hasColumn('customers', 'idx_tenant_created')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->index(['tenant_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropIndex(['tenant_id', 'created_at']);
            });
        }

        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropIndex(['tenant_id', 'created_at']);
            });
        }

        if (Schema::hasTable('conversations')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->dropIndex(['tenant_id', 'escalated_to_human']);
            });
        }
    }
};
