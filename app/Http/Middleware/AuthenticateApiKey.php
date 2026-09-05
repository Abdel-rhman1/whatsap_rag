<?php

namespace App\Http\Middleware;

use App\Models\TenantApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key') 
            ?? $request->header('Authorization')
            ?? $request->bearerToken();

        if (!$apiKey) {
            return response()->json([
                'error' => 'API key required',
                'message' => 'Please provide an API key via X-API-Key header'
            ], 401);
        }

        // Remove 'Bearer ' prefix if present
        $apiKey = str_replace('Bearer ', '', $apiKey);

        // Find and validate API key
        $tenantApiKey = TenantApiKey::where('key', $apiKey)
            ->where('status', 'active')
            ->with('tenant')
            ->first();

        if (!$tenantApiKey) {
            return response()->json([
                'error' => 'Invalid API key',
                'message' => 'The provided API key is invalid or has been revoked'
            ], 401);
        }

        // Check if key is expired
        if (!$tenantApiKey->isActive()) {
            return response()->json([
                'error' => 'API key expired',
                'message' => 'The provided API key has expired'
            ], 401);
        }

        // Check if tenant is active
        if (!$tenantApiKey->tenant->isActive()) {
            return response()->json([
                'error' => 'Account suspended',
                'message' => 'Your account has been suspended. Please contact support.'
            ], 403);
        }

        // Mark key as used (async to avoid blocking)
        dispatch(function () use ($tenantApiKey) {
            $tenantApiKey->markAsUsed();
        })->afterResponse();

        // Attach tenant to request
        $request->merge(['tenant_id' => $tenantApiKey->tenant_id]);
        $request->attributes->set('tenant', $tenantApiKey->tenant);
        $request->attributes->set('api_key', $tenantApiKey);

        return $next($request);
    }
}
