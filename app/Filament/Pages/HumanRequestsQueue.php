<?php

namespace App\Filament\Pages;

use App\Models\HumanRequest;
use Filament\Pages\Page;

class HumanRequestsQueue extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Human Requests';
    protected static ?string $title = 'Human Escalation Queue';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.human-requests-queue';

    public ?int $selectedRequestId = null;
    public string $filterStatus = 'all';

    public function getRequestsProperty()
    {
        $query = HumanRequest::with('conversation')
            ->latest();

        if ($this->filterStatus === 'pending') {
            $query->where('status', 'pending');
        } elseif ($this->filterStatus === 'resolved') {
            $query->where('status', 'resolved');
        }

        return $query->limit(50)->get();
    }

    public function getSelectedRequestProperty()
    {
        if (!$this->selectedRequestId) return null;
        return HumanRequest::with(['conversation.messages' => fn($q) => $q->oldest()])->find($this->selectedRequestId);
    }

    public function selectRequest(int $id): void
    {
        $this->selectedRequestId = $id;
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
