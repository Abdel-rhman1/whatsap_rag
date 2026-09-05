<?php

namespace App\Filament\Pages;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\HumanRequest;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class AnalyticsOverview extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Analytics';
    protected static ?string $title = 'Analytics Overview';
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.analytics-overview';

    public function getStatsProperty(): array
    {
        $totalConversations = Conversation::count();
        $totalMessages = Message::count();
        $aiMessages = Message::where('role', 'assistant')->where(function($q) {
            $q->where('source', 'rag')->orWhereNull('source');
        })->count();
        $humanMessages = Message::where('source', 'human')->count();
        $escalated = HumanRequest::count();
        $pendingEscalations = HumanRequest::where('status', 'pending')->count();
        $resolvedEscalations = HumanRequest::where('status', 'resolved')->count();

        $languages = Conversation::select('language', DB::raw('count(*) as total'))
            ->groupBy('language')
            ->pluck('total', 'language')
            ->toArray();

        $todayMessages = Message::whereDate('created_at', today())->count();

        return [
            'totalConversations' => $totalConversations,
            'totalMessages' => $totalMessages,
            'aiMessages' => $aiMessages,
            'humanMessages' => $humanMessages,
            'escalated' => $escalated,
            'pendingEscalations' => $pendingEscalations,
            'resolvedEscalations' => $resolvedEscalations,
            'languages' => $languages,
            'todayMessages' => $todayMessages,
            'aiPercent' => $totalMessages > 0 ? round(($aiMessages / $totalMessages) * 100) : 0,
            'humanPercent' => $totalMessages > 0 ? round(($humanMessages / $totalMessages) * 100) : 0,
        ];
    }

    public function getPollingInterval(): ?string
    {
        return '30s';
    }
}
