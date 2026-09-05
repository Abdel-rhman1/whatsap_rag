<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhooks', function (Blueprint $column) {
            $column->id();
            $column->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $column->string('url');
            $column->string('secret')->nullable();
            $column->json('events')->nullable();
            $column->string('status')->default('active')->comment('active, inactive');
            $column->timestamp('last_triggered_at')->nullable();
            $column->integer('last_response_code')->nullable();
            $column->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};
