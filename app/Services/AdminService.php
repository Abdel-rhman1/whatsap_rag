<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\WhatsAppAccount;
use App\Models\AgentExecutionLog;
use App\Models\ToolExecutionLog;
use App\Models\AdminActivityLog;
use Illuminate\Support\Facades\DB;

class AdminService
{
    /**
     * Get platform-wide admin metrics using aggregate database queries.
     */
    public function getGlobalStats(): array
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('status', 'active')->count();

        $subscriptionStatus = Tenant::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalConversations = Conversation::withoutGlobalScopes()->count();

        $aiSuccessfulLogs = AgentExecutionLog::withoutGlobalScopes()->where('status', 'success')->count();
        $aiFailedLogs     = AgentExecutionLog::withoutGlobalScopes()->where('status', 'failed')->count();

        $totalTokens = (int) AgentExecutionLog::withoutGlobalScopes()->sum('total_tokens');

        $totalWhatsappAccounts  = WhatsAppAccount::withoutGlobalScopes()->count();
        $activeWhatsappAccounts = WhatsAppAccount::withoutGlobalScopes()->where('status', 'connected')->count();

        $totalErrors = $aiFailedLogs + ToolExecutionLog::withoutGlobalScopes()->where('status', 'failed')->count();

        $systemHealthScore = $totalWhatsappAccounts > 0
            ? round(($activeWhatsappAccounts / $totalWhatsappAccounts) * 100)
            : 100;

        return [
            'total_tenants'        => $totalTenants,
            'active_tenants'       => $activeTenants,
            'subscription_status'  => $subscriptionStatus,
            'total_conversations'  => $totalConversations,
            'ai_usage'             => $aiSuccessfulLogs,
            'token_usage'          => $totalTokens,
            'whatsapp_usage'       => $totalWhatsappAccounts,
            'active_whatsapp'      => $activeWhatsappAccounts,
            'errors'               => $totalErrors,
            'system_health'        => $systemHealthScore,
            'top_tenants'          => Tenant::withCount('conversations')->orderBy('conversations_count', 'desc')->take(5)->get(),
        ];
    }

    public function createTenant(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            $tenant = Tenant::create($data);
            $this->logAction('tenant_created', "Created tenant: {$tenant->name}");
            return $tenant;
        });
    }

    public function updateTenantStatus(int $id, string $status): Tenant
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update(['status' => $status]);
        $this->logAction('tenant_status_updated', "Updated tenant {$tenant->name} status to {$status}");
        return $tenant;
    }

    public function logAction(string $action, string $description, ?int $tenantId = null, ?string $entityType = null, ?int $entityId = null): void
    {
        AdminActivityLog::log(
            action: $action,
            tenantId: $tenantId,
            entityType: $entityType,
            entityId: $entityId,
            metadata: ['description' => $description]
        );
    }
}

