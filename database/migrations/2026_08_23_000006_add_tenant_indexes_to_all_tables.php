<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add multi-tenant composite indexes across tenant-scoped tables.
     */
    public function up(): void
    {
        $indexConfigs = [
            'users' => ['tenant_id', 'is_active'],
            'conversations' => ['tenant_id', 'last_message_at'],
            'messages' => ['tenant_id', 'created_at'],
            'knowledge_sources' => ['tenant_id', 'status'],
            'whatsapp_instances' => ['tenant_id', 'status'],
            'campaigns' => ['tenant_id', 'status'],
            'orders' => ['tenant_id', 'status'],
            'priority_message_queue' => ['tenant_id', 'status'],
            'sla_metrics' => ['tenant_id', 'campaign_id'],
        ];

        foreach ($indexConfigs as $tableName => $columns) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($columns, $tableName) {
                    $indexName = "idx_{$tableName}_" . implode('_', $columns);
                    $table->index($columns, $indexName);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexConfigs = [
            'users' => ['tenant_id', 'is_active'],
            'conversations' => ['tenant_id', 'last_message_at'],
            'messages' => ['tenant_id', 'created_at'],
            'knowledge_sources' => ['tenant_id', 'status'],
            'whatsapp_instances' => ['tenant_id', 'status'],
            'campaigns' => ['tenant_id', 'status'],
            'orders' => ['tenant_id', 'status'],
            'priority_message_queue' => ['tenant_id', 'status'],
            'sla_metrics' => ['tenant_id', 'campaign_id'],
        ];

        foreach ($indexConfigs as $tableName => $columns) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($columns, $tableName) {
                    $indexName = "idx_{$tableName}_" . implode('_', $columns);
                    $table->dropIndex($indexName);
                });
            }
        }
    }
};
