<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_conversations', function (Blueprint $table) {
            $table->id();
            // The tenant who opened the conversation
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('subject')->nullable();
            // Cached for list view performance
            $table->text('last_message')->nullable();
            $table->timestamp('last_message_at')->nullable();
            // Count of unread messages for the admin side
            $table->unsignedInteger('unread_admin')->default(0);
            // Count of unread messages for the tenant side
            $table->unsignedInteger('unread_tenant')->default(0);
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();

            $table->index(['tenant_id', 'last_message_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_conversations');
    }
};
