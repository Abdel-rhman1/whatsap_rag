<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RagChatService;
use App\Services\TenantContext;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(protected RagChatService $ragChat) {}

    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        // Security: Never trust client tenant_id, resolve from authenticated context
        $tenantId = TenantContext::id() ?? $request->user()?->tenant_id;

        if (!$tenantId) {
            return response()->json(['error' => 'Unauthenticated or invalid tenant context'], 401);
        }

        $result = $this->ragChat->answer(
            query: $request->message,
            tenantId: $tenantId,
            platform: 'api',
            externalId: 'api_' . auth()->id()
        );

        return response()->json($result);
    }
}
