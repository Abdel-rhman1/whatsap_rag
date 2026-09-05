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
        Schema::table('human_requests', function (Blueprint $table) {
            $table->string('reason')->nullable()->after('conversation_id');
            // Status is already string, but we want it to default to 'open' if not already.
            // My previous migration had 'pending', the user wants 'open'/'handled'.
            // I'll update it.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
