<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWhatsAppHMAC
{
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-WHATSAPP-SIGNATURE');
        $secret = config('rag.whatsapp.secret');

        if (!$signature || !$secret) {
            // If secret is not set, we might use the old simple secret for compatibility
            $simpleHeader = $request->header('X-WHATSAPP-SECRET');
            if ($simpleHeader === env('WHATSAPP_SECRET')) {
                return $next($request);
            }
            return response()->json(['error' => 'Unauthorized signature'], 401);
        }

        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json(['error' => 'Invalid HMAC signature'], 401);
        }

        return $next($request);
    }
}
