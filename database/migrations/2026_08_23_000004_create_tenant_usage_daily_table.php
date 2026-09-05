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
        Schema::create('tenant_usage_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->unsignedInteger('messages_count')->default(0);
            $table->unsignedInteger('ai_tokens_used')->default(0);
            $table->unsignedInteger('whatsapp_messages_sent')->default(0);
            $table->unsignedInteger('whatsapp_messages_received')->default(0);
            $table->unsignedInteger('knowledge_queries')->default(0);
            $table->unsignedInteger('api_requests')->default(0);
            $table->unsignedBigInteger('storage_bytes')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'date']);
            $table->index('tenant_id');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_usage_daily');
    }
};
