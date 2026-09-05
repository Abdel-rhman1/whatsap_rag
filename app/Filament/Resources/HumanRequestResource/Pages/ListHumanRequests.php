<?php

namespace App\Filament\Resources\HumanRequestResource\Pages;

use App\Filament\Resources\HumanRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHumanRequests extends ListRecords
{
    protected static string $resource = HumanRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getPollingInterval(): ?string
    {
        return '3s';
    }
}
