<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\HumanRequest;
use App\Models\Message;
use App\Services\WhatsAppService;
use App\Services\TenantContext;

class DashboardController extends Controller
{
    public function index()
    {
        $tenant = auth('tenant')->user();
        $tenantId = $tenant->id;

        // Message stats for dashboard
        $stats = [
            'usage_today' => Message::where('tenant_id', $tenantId)->whereDate('created_at', now())->count(),
            'usage_month' => Message::where('tenant_id', $tenantId)->whereMonth('created_at', now()->month)->count(),
            'active_whatsapp' => $tenant->whatsappInstances()->where('status', 'connected')->count(),
            'pending_requests' => $tenant->humanRequests()->where('status', 'pending')->count(),
            'api_keys_active' => $tenant->apiKeys()->count(),
        ];

        // Analytics chart data (last 7 days)
        $analytics = Message::where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Setup checklist
        $setupChecklist = [
            'knowledge_uploaded' => $tenant->knowledgeSources()->count() > 0,
            'api_key_generated' => $tenant->apiKeys()->count() > 0,
            'whatsapp_connected' => $tenant->whatsappInstances()->where('status', 'connected')->count() > 0,
        ];

        return view('dashboard.index', compact('stats', 'analytics', 'setupChecklist', 'tenant'));
    }

    public function conversations(Request $request)
    {
        $conversations = Conversation::with(['messages' => function($query) {
            $query->latest()->limit(50);
        }])
        ->where('tenant_id', auth('tenant')->id())
        ->get()
        ->sortByDesc(function($conv) {
            $score = 0;
            if ($conv->escalated_to_human) {
                $score += 1000;
            }
            $score += $conv->last_message_at ? $conv->last_message_at->timestamp / 1000000 : 0;
            return $score;
        })
        ->values()
        ->map(function($conv) {
            return [
                'id' => $conv->id,
                'contact_name' => $conv->contact_name,
                'phone_number' => $conv->phone_number,
                'language' => $conv->language,
                'platform' => $conv->platform,
                'session_id' => $conv->session_id,
                'escalated_to_human' => $conv->escalated_to_human,
                'last_message_at' => $conv->last_message_at,
                'created_at' => $conv->created_at,
                'last_message' => $conv->messages->first()?->content,
                'source' => $conv->messages->where('role', 'assistant')->first()?->source ?? 'ai',
                'messages' => $conv->messages->map(function($msg) {
                    return [
                        'id' => $msg->id,
                        'role' => $msg->role,
                        'source' => $msg->source,
                        'content' => $msg->content,
                        'created_at' => $msg->created_at,
                        'metadata' => $msg->metadata,
                    ];
                }),
            ];
        });

        return response()->json($conversations);
    }

    public function humanRequests(Request $request)
    {
        $requests = HumanRequest::with('conversation.messages')
            ->whereHas('conversation', function($query) {
                $query->where('tenant_id', auth('tenant')->id());
            })
            ->latest()
            ->get()
            ->map(function($req) {
                return [
                    'id' => $req->id,
                    'name' => $req->name,
                    'language' => $req->language,
                    'reason' => $req->reason,
                    'status' => $req->status,
                    'last_message' => $req->last_message,
                    'created_at' => $req->created_at,
                    'resolved_at' => $req->resolved_at,
                    'conversation' => [
                        'id' => $req->conversation->id,
                        'contact_name' => $req->conversation->contact_name,
                        'phone_number' => $req->conversation->phone_number,
                        'language' => $req->conversation->language,
                        'session_id' => $req->conversation->session_id,
                        'platform' => $req->conversation->platform,
                        'escalated_to_human' => $req->conversation->escalated_to_human,
                        'last_message_at' => $req->conversation->last_message_at,
                        'created_at' => $req->conversation->created_at,
                        'messages' => $req->conversation->messages->map(function($msg) {
                            return [
                                'id' => $msg->id,
                                'role' => $msg->role,
                                'source' => $msg->source,
                                'content' => $msg->content,
                                'created_at' => $msg->created_at,
                                'metadata' => $msg->metadata,
                            ];
                        }),
                    ],
                ];
            });

        return response()->json($requests);
    }

    public function analytics(Request $request)
    {
        $tenantId = auth('tenant')->id();
        
        $totalConversations = Conversation::where('tenant_id', $tenantId)->count();
        
        $aiHandled = Conversation::where('tenant_id', $tenantId)
            ->where('escalated_to_human', false)
            ->whereHas('messages', function($query) {
                $query->where('source', 'ai');
            })
            ->count();
        
        $ragAnswered = Conversation::where('tenant_id', $tenantId)
            ->whereHas('messages', function($query) {
                $query->where('source', 'rag');
            })
            ->count();
        
        $humanRequired = Conversation::where('tenant_id', $tenantId)
            ->where('escalated_to_human', true)
            ->count();
        
        $byLanguage = Conversation::where('tenant_id', $tenantId)
            ->selectRaw('language, COUNT(*) as count')
            ->groupBy('language')
            ->pluck('count', 'language')
            ->toArray();
        
        $analytics = [
            'total_conversations' => $totalConversations,
            'ai_handled' => $aiHandled,
            'rag_answered' => $ragAnswered,
            'human_required' => $humanRequired,
            'ai_percentage' => $totalConversations > 0 ? round(($aiHandled / $totalConversations) * 100, 1) : 0,
            'rag_percentage' => $totalConversations > 0 ? round(($ragAnswered / $totalConversations) * 100, 1) : 0,
            'human_percentage' => $totalConversations > 0 ? round(($humanRequired / $totalConversations) * 100, 1) : 0,
            'by_language' => $byLanguage,
        ];
        
        return response()->json($analytics);
    }

    public function conversationsView()
    {
        return view('dashboard.conversations');
    }

    public function reply(Request $request, Conversation $conversation)
    {
        // Security check
        if ($conversation->tenant_id !== auth('tenant')->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        try {
            // Find an active WhatsApp instance for this tenant
            $instance = auth('tenant')->user()->whatsappInstances()
                ->where('status', 'connected')
                ->where('is_active', true)
                ->first();

            if (!$instance) {
                return response()->json(['error' => 'No active WhatsApp instance connected.'], 422);
            }

            // Send via WhatsApp Service
            $ws = app(\App\Services\WhatsAppService::class);
            $ws->sendMessage($instance->instance_name, $conversation->external_id, $request->message);

            // Store message in DB
            $message = Message::create([
                'tenant_id' => $conversation->tenant_id,
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'source' => 'human',
                'content' => $request->message,
            ]);

            // Update conversation state
            $conversation->update([
                'last_message_at' => now(),
                'escalated_to_human' => false,
            ]);

            // Resolve any related human requests
            $conversation->humanRequests()->where('status', 'pending')->update([
                'status' => 'resolved',
                'resolved_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'role' => $message->role,
                    'source' => $message->source,
                    'content' => $message->content,
                    'created_at' => $message->created_at->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function knowledgeConfigView()
    {
        $tenantId = auth('tenant')->id();
        $config = \App\Models\TenantKnowledgeConfig::firstOrCreate(['tenant_id' => $tenantId]);
        return view('dashboard.knowledge-config', compact('config'));
    }

    public function updateKnowledgeConfig(Request $request)
    {
        $tenantId = auth('tenant')->id();
        $config = \App\Models\TenantKnowledgeConfig::firstOrCreate(['tenant_id' => $tenantId]);

        $request->validate([
            'similarity_threshold' => 'required|numeric|min:0|max:1',
            'allowed_file_types' => 'nullable|array',
            'context_only_mode' => 'boolean',
            'hallucination_prevention' => 'boolean',
        ]);

        $config->update($request->all());

        return back()->with('success', 'Knowledge configuration updated successfully.');
    }

    /**
     * Polling endpoint for real-time human escalation alerts.
     * Returns pending human requests created after a given timestamp.
     */
    public function pendingAlerts(Request $request)
    {
        $tenantId = auth('tenant')->id();
        $since = $request->query('since');

        $query = HumanRequest::where('status', 'pending')
            ->whereHas('conversation', fn ($q) => $q->where('tenant_id', $tenantId));

        if ($since) {
            $query->where('created_at', '>', $since);
        }

        $pending = $query->latest()->get()->map(fn ($r) => [
            'id'           => $r->id,
            'name'         => $r->name,
            'reason'       => $r->reason,
            'language'     => $r->language,
            'last_message' => $r->last_message,
            'created_at'   => $r->created_at->toIso8601String(),
        ]);

        return response()->json([
            'count'    => $pending->count(),
            'requests' => $pending,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * Ignore a human escalation alert and record who ignored it in the database.
     */
    public function ignoreAlert(Request $request, int $id)
    {
        $user = auth('tenant')->user();
        $tenantId = $user->id;

        $humanRequest = HumanRequest::where('tenant_id', $tenantId)->findOrFail($id);

        $humanRequest->update([
            'status'      => 'ignored',
            'resolved_by' => $user->email ?? $user->name ?? ('User #' . $user->id),
            'resolved_at' => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Alert ignored successfully.',
            'by'      => $humanRequest->resolved_by,
        ]);
    }

    /**
     * Confirm alert and send fallback human handoff message to user on WhatsApp.
     */
    public function confirmAlert(Request $request, int $id, WhatsAppService $whatsapp)
    {
        $user = auth('tenant')->user();
        $tenantId = $user->id;

        $humanRequest = HumanRequest::where('tenant_id', $tenantId)->findOrFail($id);

        TenantContext::set(tenantId: $tenantId);

        $fallbackMessage = ($humanRequest->language === 'ar' || empty($humanRequest->language))
            ? "سؤالك محتاج تدخل بشري 👩‍💼، هنوصل لحضرتك قريبًا."
            : "Your inquiry requires human support 👩‍💼, an agent will assist you shortly.";

        // Send via WhatsApp if conversation & instance exist
        $conversation = $humanRequest->conversation;
        if ($conversation) {
            $account = $whatsapp->resolveAccount($humanRequest->instance_id ?? $conversation->external_id);
            if ($account) {
                try {
                    $whatsapp->sendMessage($account, $humanRequest->external_id, $fallbackMessage);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Failed sending confirmed handoff message to WhatsApp: " . $e->getMessage());
                }
            }

            // Record message in conversation history
            Message::create([
                'tenant_id'       => $tenantId,
                'conversation_id' => $conversation->id,
                'role'            => 'assistant',
                'source'          => 'human_fallback_confirmed',
                'content'         => $fallbackMessage,
                'metadata'        => [
                    'confirmed_by' => $user->email ?? $user->name ?? ('User #' . $user->id),
                    'request_id'   => $humanRequest->id,
                ]
            ]);

            $conversation->update([
                'escalated_to_human' => true,
                'last_message_at'    => now(),
            ]);
        }

        $humanRequest->update([
            'status'      => 'confirmed',
            'resolved_by' => $user->email ?? $user->name ?? ('User #' . $user->id),
            'resolved_at' => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Escalation message confirmed and sent to WhatsApp.',
            'by'      => $humanRequest->resolved_by,
        ]);
    }
}

