<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HumanRequestResource\Pages;
use App\Models\HumanRequest;
use App\Models\Message;
use App\Services\WhatsAppService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class HumanRequestResource extends Resource
{
    protected static ?string $model = HumanRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Communications';
    protected static bool $isScopedToTenant = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Request Details')
                    ->schema([
                        Forms\Components\TextInput::make('instance_id')->disabled(),
                        Forms\Components\TextInput::make('external_id')->label('Phone/ID')->disabled(),
                        Forms\Components\TextInput::make('name')->disabled(),
                        Forms\Components\TextInput::make('status')->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Conversation History')
                    ->schema([
                        Forms\Components\ViewField::make('conversation_history')
                            ->view('filament.components.conversation-log')
                            ->columnSpanFull()
                            ->formatStateUsing(fn ($record) => $record->conversation?->messages()?->oldest()?->get() ?? collect()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Escalated At')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('instance_id')->searchable(),
                Tables\Columns\TextColumn::make('external_id')->label('User')->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'resolved' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('language')
                    ->formatStateUsing(fn ($state) => strtoupper($state))
                    ->badge(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'resolved' => 'Resolved',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('reply')
                    ->label('Reply')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('message')
                            ->label('Your Response')
                            ->required()
                            ->placeholder('Type your message to the user...'),
                    ])
                    ->action(function (HumanRequest $record, array $data, WhatsAppService $ws): void {
                        try {
                            // Send message via WhatsApp
                            $ws->sendMessage($record->instance_id, $record->external_id, $data['message']);

                            // Store message in database
                            Message::create([
                                'conversation_id' => $record->conversation_id,
                                'role' => 'assistant',
                                'content' => $data['message'],
                                'metadata' => ['source' => 'human']
                            ]);

                            // Update request status
                            $record->update([
                                'status' => 'resolved',
                                'resolved_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Reply sent successfully')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Failed to send reply')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn ($record) => $record->status === 'pending'),
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
            'index' => Pages\ListHumanRequests::route('/'),
            'create' => Pages\CreateHumanRequest::route('/create'),
            'view' => Pages\ViewHumanRequest::route('/{record}'),
            'edit' => Pages\EditHumanRequest::route('/{record}/edit'),
        ];
    }
}
