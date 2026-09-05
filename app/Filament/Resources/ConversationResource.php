<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConversationResource\Pages;
use App\Filament\Resources\ConversationResource\RelationManagers;
use App\Models\Conversation;
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
use Filament\Support\Enums\Alignment;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Communications';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Conversation Status')
                    ->schema([
                        Forms\Components\TextInput::make('phone_number')->disabled(),
                        Forms\Components\TextInput::make('contact_name')->disabled(),
                        Forms\Components\Toggle::make('escalated_to_human')->label('Escalated to Human')->disabled(),
                        Forms\Components\TextInput::make('language')->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Chat History')
                    ->schema([
                        Forms\Components\ViewField::make('messages')
                            ->view('filament.components.conversation-log')
                            ->columnSpanFull()
                            ->formatStateUsing(fn ($record) => $record->messages()->oldest()->get()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_name')
                    ->label('Contact')
                    ->searchable(),
                Tables\Columns\TextColumn::make('language')
                    ->formatStateUsing(fn ($state) => strtoupper($state))
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('last_message_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('escalated_to_human')
                    ->label('Escalated')
                    ->boolean(),
            ])
            ->defaultSort('last_message_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('escalated_to_human'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('send_reply')
                    ->label('Reply')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('message')
                            ->required()
                            ->placeholder('Type your WhatsApp reply here...'),
                    ])
                    ->action(function (Conversation $record, array $data, WhatsAppService $ws): void {
                        try {
                            $instanceId = $record->session?->instance_id ?? 'k'; // Fallback
                            $ws->sendMessage($instanceId, $record->external_id, $data['message']);
                            
                            Message::create([
                                'conversation_id' => $record->id,
                                'role' => 'assistant',
                                'source' => 'human',
                                'content' => $data['message'],
                            ]);

                            $record->update([
                                'last_message_at' => now(),
                                'escalated_to_human' => false, // Resolve it
                            ]);

                            // Also resolve any open human requests
                            $record->humanRequests()->where('status', 'pending')->update(['status' => 'resolved', 'resolved_at' => now()]);

                            Notification::make()->title('Message sent!')->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()->title('Send failed')->body($e->getMessage())->danger()->send();
                        }
                    }),
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
            'index' => Pages\ListConversations::route('/'),
            'create' => Pages\CreateConversation::route('/create'),
            'view' => Pages\ViewConversation::route('/{record}'),
            'edit' => Pages\EditConversation::route('/{record}/edit'),
        ];
    }
}
