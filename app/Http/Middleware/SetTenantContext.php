<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TenantContext;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Clear any previous execution tenant context
        TenantContext::clear();

        // If authenticated via tenant guard
        if (auth('tenant')->check()) {
            TenantContext::set(auth('tenant')->user());
        }
        // If authenticated via user/web guard or sanctum
        elseif (auth('web')->check() && auth('web')->user()?->tenant_id) {
            TenantContext::set(tenantId: auth('web')->user()->tenant_id);
        }
        elseif (auth('sanctum')->check() && auth('sanctum')->user()?->tenant_id) {
            TenantContext::set(tenantId: auth('sanctum')->user()->tenant_id);
        }
        // If request attribute was set by API key auth or widget key auth
        elseif ($request->attributes->has('tenant_id')) {
            TenantContext::set(tenantId: (int) $request->attributes->get('tenant_id'));
        }

        return $next($request);
    }
}
