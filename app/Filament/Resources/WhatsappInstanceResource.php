<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsappInstanceResource\Pages;
use App\Models\WhatsappInstance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Services\WhatsAppService;
use Filament\Notifications\Notification;

class WhatsappInstanceResource extends Resource
{
    protected static ?string $model = WhatsappInstance::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'WhatsApp Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('instance_name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->label('RAG Enabled')
                    ->default(true),
                Forms\Components\TextInput::make('phone_number')
                    ->tel()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('instance_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'connected' => 'success',
                        'disconnected' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\Action::make('start')
                    ->label('Initialize')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->action(function (WhatsappInstance $record) {
                        $success = app(WhatsAppService::class)->startInstance($record->instance_name);
                        if ($success) {
                            Notification::make()->title('Instance initialization started')->success()->send();
                        } else {
                            Notification::make()->title('Failed to connect to gateway')->danger()->send();
                        }
                    }),
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
            'index' => Pages\ListWhatsappInstances::route('/'),
            'create' => Pages\CreateWhatsappInstance::route('/create'),
            'edit' => Pages\EditWhatsappInstance::route('/{record}/edit'),
        ];
    }
}
