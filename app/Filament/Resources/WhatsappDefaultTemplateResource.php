<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsappDefaultTemplateResource\Pages;
use App\Models\WhatsappDefaultTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WhatsappDefaultTemplateResource extends Resource
{
    protected static ?string $model = WhatsappDefaultTemplate::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';
    protected static ?string $navigationGroup = 'WhatsApp Management';
    protected static ?string $label = 'Auto-Response Templates';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->options([
                        'greeting' => 'Greeting (First Message)',
                        'fallback' => 'Low Confidence Fallback',
                        'offline' => 'System Error / Busy',
                        'handoff' => 'Human Handoff Request'
                    ])
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('message')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('message')
                    ->limit(50),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWhatsappDefaultTemplates::route('/'),
            'create' => Pages\CreateWhatsappDefaultTemplate::route('/create'),
            'edit' => Pages\EditWhatsappDefaultTemplate::route('/{record}/edit'),
        ];
    }
}
