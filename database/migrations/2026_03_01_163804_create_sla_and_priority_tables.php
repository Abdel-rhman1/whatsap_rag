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
        Schema::create('sla_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->onDelete('cascade');
            $table->string('tier')->default('basic'); // basic, business, enterprise
            $table->integer('max_ai_response_time_ms')->default(10000); // 10s default
            $table->integer('max_human_response_time_ms')->default(3600000); // 1hr default
            $table->integer('priority_level')->default(1); // 1–5
            $table->integer('human_handoff_timeout_mins')->default(15);
            $table->timestamps();
        });

        Schema::create('sla_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('message_id')->nullable();
            $table->string('platform')->default('whatsapp');
            $table->timestamp('received_at');
            $table->timestamp('first_ai_response_at')->nullable();
            $table->timestamp('human_response_at')->nullable();
            $table->integer('ai_latency_ms')->nullable();
            $table->boolean('is_ai_violation')->default(false);
            $table->boolean('is_human_violation')->default(false);
            $table->timestamps();
        });

        Schema::create('priority_message_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('external_id');
            $table->string('platform')->default('whatsapp');
            $table->json('payload');
            $table->integer('priority_score')->default(0);
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->integer('attempts')->default(0);
            $table->timestamp('queued_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('priority_message_queue');
        Schema::dropIfExists('sla_metrics');
        Schema::dropIfExists('sla_configs');
    }
};
