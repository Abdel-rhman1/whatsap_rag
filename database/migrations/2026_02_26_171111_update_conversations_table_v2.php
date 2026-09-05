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
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('device_id')->nullable()->after('id')->constrained()->onDelete('set null');
            $table->foreignId('session_id')->nullable()->after('device_id')->constrained('whatsapp_sessions')->onDelete('set null');
            $table->string('phone_number')->after('session_id')->nullable();
            $table->string('contact_name')->after('phone_number')->nullable();
            $table->string('language')->after('contact_name')->default('unknown');
            $table->boolean('escalated_to_human')->default(false)->after('language');
            $table->timestamp('last_message_at')->nullable()->after('escalated_to_human');
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
