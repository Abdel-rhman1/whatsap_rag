<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduledMessageResource\Pages;
use App\Filament\Resources\ScheduledMessageResource\RelationManagers;
use App\Models\ScheduledMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScheduledMessageResource extends Resource
{
    protected static ?string $model = ScheduledMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Communications';
    protected static bool $isScopedToTenant = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('phone_number')
                    ->required()
                    ->tel()
                    ->placeholder('201101234567'),
                Forms\Components\Textarea::make('message')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('send_at')
                    ->label('Schedule for')
                    ->required()
                    ->native(false),
                Forms\Components\Select::make('recurrence')
                    ->options([
                        'none' => 'None',
                        'daily' => 'Daily',
                        'weekly' => 'Weekly',
                    ])
                    ->default('none'),
                Forms\Components\Select::make('language')
                    ->options(['ar' => 'Arabic', 'en' => 'English'])
                    ->default('ar'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('phone_number')->searchable(),
                Tables\Columns\TextColumn::make('send_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('recurrence')->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'sent' => 'success',
                        'failed' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('retries')->label('Retries'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'sent' => 'Sent', 'failed' => 'Failed']),
            ])
            ->actions([
                Tables\Actions\Action::make('resend')
                    ->label('Resend')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->action(fn ($record) => $record->update(['status' => 'pending', 'send_at' => now()]))
                    ->visible(fn ($record) => $record->status !== 'pending'),
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
            'index' => Pages\ListScheduledMessages::route('/'),
            'create' => Pages\CreateScheduledMessage::route('/create'),
            'edit' => Pages\EditScheduledMessage::route('/{record}/edit'),
        ];
    }
}
