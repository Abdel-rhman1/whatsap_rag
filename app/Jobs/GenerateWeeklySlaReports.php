<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\SlaReportingService;
use App\Services\TenantContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateWeeklySlaReports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(SlaReportingService $reportingService): void
    {
        $startDate = now()->startOfWeek()->subWeek();
        $endDate   = now()->endOfWeek()->subWeek();

        Tenant::where('status', 'active')->chunk(50, function ($tenants) use ($reportingService, $startDate, $endDate) {
            foreach ($tenants as $tenant) {
                TenantContext::set(tenantId: $tenant->id);
                $reportingService->generateWeeklyReport($tenant, $startDate, $endDate);
            }
        });
    }
}
