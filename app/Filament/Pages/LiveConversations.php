<?php

namespace App\Filament\Pages;

use App\Models\Conversation;
use App\Models\Message;
use Filament\Pages\Page;

class LiveConversations extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Live Conversations';
    protected static ?string $title = 'Live Conversations';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.live-conversations';

    public ?int $selectedConversationId = null;
    public string $searchQuery = '';
    public string $filterStatus = 'all';

    protected $queryString = ['selectedConversationId'];

    public function getConversationsProperty()
    {
        $query = Conversation::withCount('messages')
            ->with(['messages' => fn($q) => $q->latest()->limit(1)])
            ->latest('last_message_at');

        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('phone_number', 'like', "%{$this->searchQuery}%")
                  ->orWhere('contact_name', 'like', "%{$this->searchQuery}%")
                  ->orWhere('external_id', 'like', "%{$this->searchQuery}%");
            });
        }

        if ($this->filterStatus === 'ai') {
            $query->where('escalated_to_human', false);
        } elseif ($this->filterStatus === 'escalated') {
            $query->where('escalated_to_human', true);
        } elseif ($this->filterStatus === 'pending') {
            $query->whereHas('humanRequests', fn($q) => $q->where('status', 'pending'));
        }

        return $query->limit(50)->get();
    }

    public function getSelectedConversationProperty()
    {
        if (!$this->selectedConversationId) return null;
        return Conversation::with(['messages' => fn($q) => $q->oldest()])->find($this->selectedConversationId);
    }

    public function selectConversation(int $id): void
    {
        $this->selectedConversationId = $id;
    }

    public function updatedSearchQuery(): void
    {
        // Livewire auto-updates
    }

    public function setFilter(string $status): void
    {
        $this->filterStatus = $status;
    }

    public function getPollingInterval(): ?string
    {
        return '5s';
    }
}
