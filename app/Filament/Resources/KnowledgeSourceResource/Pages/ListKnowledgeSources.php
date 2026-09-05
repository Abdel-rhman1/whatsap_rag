<?php

namespace App\Filament\Resources\KnowledgeSourceResource\Pages;

use App\Filament\Resources\KnowledgeSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKnowledgeSources extends ListRecords
{
    protected static string $resource = KnowledgeSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
