<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('gateway'); // paymob, stripe, fawry
            $table->string('event_type'); // e.g. transaction.processed
            $table->json('payload');
            $table->string('status')->default('unprocessed'); // success, failed, skipped
            $table->text('error_message')->nullable();
            
            $table->timestamps();
            
            // Helpful for debugging later
            $table->index(['gateway', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
