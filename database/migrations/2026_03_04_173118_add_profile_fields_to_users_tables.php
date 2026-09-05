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
        $tables = ['users', 'tenants', 'admin_users'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'phone_number')) {
                    $table->string('phone_number')->nullable();
                }
                if (!Schema::hasColumn($table->getTable(), 'avatar_url')) {
                    $table->string('avatar_url')->nullable();
                }
                if (!Schema::hasColumn($table->getTable(), 'preferred_language')) {
                    $table->string('preferred_language')->default('en');
                }
                if (!Schema::hasColumn($table->getTable(), 'timezone')) {
                    $table->string('timezone')->default('UTC');
                }
                if (!Schema::hasColumn($table->getTable(), 'notification_settings')) {
                    $table->json('notification_settings')->nullable();
                }
                if (!Schema::hasColumn($table->getTable(), 'password_changed_at')) {
                    $table->timestamp('password_changed_at')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['users', 'tenants', 'admin_users'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn([
                    'phone_number',
                    'avatar_url',
                    'preferred_language',
                    'timezone',
                    'notification_settings',
                    'password_changed_at'
                ]);
            });
        }
    }
};
