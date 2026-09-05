<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class MetricsService
{
    public static function log(int $tenantId, string $channel, string $type, float $latencyMs, ?int $tokens = null, array $metadata = [])
    {
        DB::table('rag_metrics')->insert([
            'tenant_id'        => $tenantId,
            'channel'          => $channel,
            'type'             => $type,
            'latency_ms'       => $latencyMs,
            'estimated_tokens' => $tokens,
            'metadata'         => json_encode($metadata),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }
}
