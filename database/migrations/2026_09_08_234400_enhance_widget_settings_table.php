<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('widget_settings', 'bot_name')) {
                $table->string('bot_name')->default('AI Assistant')->after('primary_color');
            }
            if (!Schema::hasColumn('widget_settings', 'bubble_title')) {
                $table->string('bubble_title')->default('Chat with us')->after('bot_name');
            }
            if (!Schema::hasColumn('widget_settings', 'placeholder_text')) {
                $table->string('placeholder_text')->default('Type a message...')->after('greeting_message');
            }
            if (!Schema::hasColumn('widget_settings', 'position')) {
                $table->string('position')->default('bottom-right')->after('placeholder_text');
            }
            if (!Schema::hasColumn('widget_settings', 'suggested_questions')) {
                $table->json('suggested_questions')->nullable()->after('position');
            }
            if (!Schema::hasColumn('widget_settings', 'is_enabled')) {
                $table->boolean('is_enabled')->default(true)->after('suggested_questions');
            }
            if (!Schema::hasColumn('widget_settings', 'sound_enabled')) {
                $table->boolean('sound_enabled')->default(true)->after('is_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $columns = [
                'bot_name',
                'bubble_title',
                'placeholder_text',
                'position',
                'suggested_questions',
                'is_enabled',
                'sound_enabled'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('widget_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
