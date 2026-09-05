<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateTenant
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('tenant')->check()) {
            return redirect()->route('tenant.login');
        }

        $tenant = Auth::guard('tenant')->user();

        if (!$tenant->isActive()) {
            Auth::guard('tenant')->logout();
            return redirect()->route('tenant.login')
                ->with('error', 'Your account has been suspended. Please contact support.');
        }

        return $next($request);
    }
}
