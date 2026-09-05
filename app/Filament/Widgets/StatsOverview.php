<?php

namespace App\Filament\Widgets;

use App\Models\Conversation;
use App\Models\HumanRequest;
use App\Models\ScheduledMessage;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '5s';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Conversations', Conversation::count()),
            Stat::make('Active Today', Conversation::where('last_message_at', '>=', now()->startOfDay())->count()),
            Stat::make('Escalated to Human', Conversation::where('escalated_to_human', true)->count())
                ->color('danger')
                ->description('Conversations needing attention'),
            Stat::make('Scheduled Messages', ScheduledMessage::where('status', 'pending')->count())
                ->description('Messages waiting to be sent'),
        ];
    }
}
