<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $column) {
            $column->foreignId('plan_id')->nullable()->constrained('plans')->onDelete('set null');
            $column->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $column) {
            $column->dropForeign(['plan_id']);
            $column->dropColumn(['plan_id', 'deleted_at']);
        });
    }
};
