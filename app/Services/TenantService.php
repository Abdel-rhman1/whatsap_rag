<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\AgentExecutionLog;
use App\Models\KnowledgeChunk;
use App\Models\ToolExecutionLog;
use App\Models\WhatsAppAccount;
use App\Models\TenantApiKey;
use App\Models\Webhook;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TenantService
{
    /**
     * Get tenant-scoped dashboard metrics using optimized database queries.
     */
    public function getDashboardStats(Tenant $tenant): array
    {
        TenantContext::set($tenant);

        $totalConversations = Conversation::where('tenant_id', $tenant->id)->count();
        $humanEscalations   = Conversation::where('tenant_id', $tenant->id)->where('escalated_to_human', true)->count();
        
        $aiResolutionRate = $totalConversations > 0
            ? round((($totalConversations - $humanEscalations) / $totalConversations) * 100, 1)
            : 100.0;

        $totalMessages  = Message::where('tenant_id', $tenant->id)->count();

        $aiUsage = AgentExecutionLog::where('tenant_id', $tenant->id)->where('status', 'success')->count();
        $kbUsage = KnowledgeChunk::where('tenant_id', $tenant->id)->count();

        $toolExecutions = ToolExecutionLog::where('tenant_id', $tenant->id)->count();

        $whatsappAccounts   = WhatsAppAccount::where('tenant_id', $tenant->id)->count();
        $activeWhatsapp     = WhatsAppAccount::where('tenant_id', $tenant->id)->where('status', 'connected')->count();

        $teamMembers = User::where('tenant_id', $tenant->id)->count();

        $usageToday = Message::where('tenant_id', $tenant->id)
            ->whereDate('created_at', today())
            ->count();

        $usageMonth = Message::where('tenant_id', $tenant->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $apiKeysActive = $tenant->apiKeys()->where('status', 'active')->count();
        $pendingRequests = $tenant->humanRequests()->where('status', 'pending')->count();

        return [
            'conversations'        => $totalConversations,
            'ai_resolution_rate'   => $aiResolutionRate,
            'human_escalations'    => $humanEscalations,
            'messages'             => $totalMessages,
            'ai_usage'             => $aiUsage,
            'knowledge_base_usage' => $kbUsage,
            'tool_executions'      => $toolExecutions,
            'whatsapp_accounts'    => $whatsappAccounts,
            'team_members'         => $teamMembers,

            // Legacy Dashboard Keys
            'usage_today'          => $usageToday,
            'usage_month'          => $usageMonth,
            'active_whatsapp'      => $activeWhatsapp,
            'api_keys_active'      => $apiKeysActive,
            'pending_requests'     => $pendingRequests,
        ];
    }

    public function getUsageAnalytics(Tenant $tenant, int $days = 7)
    {
        return Message::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->get();
    }

    public function generateApiKey(Tenant $tenant, string $name)
    {
        $key = TenantApiKey::generateKey();
        
        return $tenant->apiKeys()->create([
            'name'       => $name,
            'key'        => $key,
            'key_prefix' => substr($key, 0, 7),
            'status'     => 'active',
        ]);
    }

    public function revokeApiKey(int $id, Tenant $tenant)
    {
        return $tenant->apiKeys()->where('id', $id)->update(['status' => 'revoked']);
    }

    public function storeWebhook(Tenant $tenant, array $data)
    {
        return $tenant->webhooks()->create([
            'url'    => $data['url'],
            'events' => $data['events'] ?? ['message.received', 'message.sent'],
            'status' => 'active',
            'secret' => Str::random(32),
        ]);
    }

    public function getLogs(Tenant $tenant, int $limit = 50)
    {
        return Message::where('tenant_id', $tenant->id)
            ->with('conversation')
            ->latest()
            ->paginate($limit);
    }
}
