<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('tenant')->user();
        
        if ($user && (!$user->last_login_at || $user->last_login_at->diffInMinutes() >= 5)) {
            $user->update(['last_login_at' => now()]);
        }

        return $next($request);
    }
}
