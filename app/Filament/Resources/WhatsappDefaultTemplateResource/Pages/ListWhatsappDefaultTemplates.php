<?php

namespace App\Filament\Resources\WhatsappDefaultTemplateResource\Pages;

use App\Filament\Resources\WhatsappDefaultTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWhatsappDefaultTemplates extends ListRecords
{
    protected static string $resource = WhatsappDefaultTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
