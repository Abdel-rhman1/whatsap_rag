<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $column) {
            $column->id();
            $column->string('name');
            $column->string('slug')->unique();
            $column->text('description')->nullable();
            $column->decimal('monthly_price', 10, 2)->default(0);
            $column->decimal('yearly_price', 10, 2)->default(0);
            $column->json('limits')->nullable()->comment('API limits, WhatsApp sessions, etc.');
            $column->boolean('is_active')->default(true);
            $column->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
