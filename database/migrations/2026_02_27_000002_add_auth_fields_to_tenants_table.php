<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('email')->unique()->nullable()->after('name');
            $table->string('password')->nullable()->after('email');
            $table->enum('status', ['active', 'suspended', 'pending'])->default('active')->after('password');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->rememberToken()->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['email', 'password', 'status', 'last_login_at', 'remember_token']);
        });
    }
};
