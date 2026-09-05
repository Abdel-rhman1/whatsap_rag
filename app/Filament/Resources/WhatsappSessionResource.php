<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsappSessionResource\Pages;
use App\Filament\Resources\WhatsappSessionResource\RelationManagers;
use App\Models\WhatsappSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WhatsappSessionResource extends Resource
{
    protected static ?string $model = WhatsappSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-signal';
    protected static ?string $navigationGroup = 'Infrastructure';
    protected static bool $isScopedToTenant = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('device_id')
                    ->relationship('device', 'name')
                    ->required(),
                Forms\Components\TextInput::make('instance_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone_number')
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->options([
                        'connected' => 'Connected',
                        'disconnected' => 'Disconnected',
                        'connecting' => 'Connecting',
                    ])
                    ->default('disconnected'),
                Forms\Components\DateTimePicker::make('connected_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('device.name')->sortable(),
                Tables\Columns\TextColumn::make('instance_id')->searchable(),
                Tables\Columns\TextColumn::make('phone_number')->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'connected' => 'success',
                        'disconnected' => 'danger',
                        'connecting' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('connected_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['connected' => 'Connected', 'disconnected' => 'Disconnected']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWhatsappSessions::route('/'),
            'create' => Pages\CreateWhatsappSession::route('/create'),
            'edit' => Pages\EditWhatsappSession::route('/{record}/edit'),
        ];
    }
}
