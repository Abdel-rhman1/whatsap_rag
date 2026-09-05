<?php

namespace App\Filament\Resources\HumanRequestResource\Pages;

use App\Filament\Resources\HumanRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHumanRequest extends ViewRecord
{
    protected static string $resource = HumanRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function getPollingInterval(): ?string
    {
        return '3s';
    }
}
