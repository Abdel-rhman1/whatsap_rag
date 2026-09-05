<?php

namespace App\Filament\Widgets;

use App\Models\Tenant;
use App\Models\SlaMetric;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class SlaLeaderboard extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Tenant::query()
                    ->with(['plan', 'slaConfig'])
                    ->withCount([
                        'slaMetrics as total_metrics' => fn($q) => $q->where('received_at', '>=', now()->subDays(7)),
                        'slaMetrics as violations' => fn($q) => $q->where('received_at', '>=', now()->subDays(7))
                            ->where(fn($sq) => $sq->where('is_ai_violation', true)->orWhere('is_human_violation', true))
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tenant')
                    ->searchable(),
                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Tier')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'enterprise' => 'success',
                        'pro' => 'info',
                        default => 'gray'
                    }),
                Tables\Columns\TextColumn::make('total_metrics')
                    ->label('Volume (7d)')
                    ->numeric(),
                Tables\Columns\TextColumn::make('compliance')
                    ->label('SLA Health')
                    ->state(function ($record) {
                        if ($record->total_metrics === 0) return '100%';
                        $rate = (($record->total_metrics - $record->violations) / $record->total_metrics) * 100;
                        return round($rate, 1) . '%';
                    })
                    ->badge()
                    ->color(fn($state) => (float)$state < 85 ? 'danger' : ((float)$state < 95 ? 'warning' : 'success')),
                Tables\Columns\TextColumn::make('avg_latency')
                    ->label('Avg Speed')
                    ->state(fn($record) => round($record->slaMetrics()->where('received_at', '>=', now()->subDays(7))->avg('ai_latency_ms') ?? 0) . 'ms')
                    ->fontFamily('mono'),
                Tables\Columns\IconColumn::make('slaConfig.is_enabled')
                    ->label('Active')
                    ->boolean(),
            ])
            ->defaultSort('violations', 'desc');
    }
}
