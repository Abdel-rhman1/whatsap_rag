<?php

namespace App\Filament\Resources\HumanRequestResource\Pages;

use App\Filament\Resources\HumanRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHumanRequest extends EditRecord
{
    protected static string $resource = HumanRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
