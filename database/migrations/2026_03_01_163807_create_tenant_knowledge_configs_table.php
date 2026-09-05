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
        Schema::create('tenant_knowledge_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->onDelete('cascade');
            $table->string('vector_namespace')->nullable();
            $table->float('similarity_threshold')->default(0.5);
            $table->json('allowed_file_types')->nullable(); // ['pdf', 'docx', 'audio', 'image']
            $table->boolean('context_only_mode')->default(false);
            $table->boolean('hallucination_prevention')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_knowledge_configs');
    }
};
