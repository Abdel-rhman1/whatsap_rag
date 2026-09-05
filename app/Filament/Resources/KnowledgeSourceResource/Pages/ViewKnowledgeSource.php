<?php

namespace App\Filament\Resources\KnowledgeSourceResource\Pages;

use App\Filament\Resources\KnowledgeSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewKnowledgeSource extends ViewRecord
{
    protected static string $resource = KnowledgeSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
