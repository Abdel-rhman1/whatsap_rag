<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('month_year'); // e.g., "Week 09, 2026"
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->json('stats'); // Cache of stats for the report
            $table->string('pdf_path')->nullable();
            $table->boolean('is_notified')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_reports');
    }
};
