<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KnowledgeSourceResource\Pages;
use App\Models\KnowledgeSource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Jobs\ExtractTextJob;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class KnowledgeSourceResource extends Resource
{
    protected static ?string $model = KnowledgeSource::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Knowledge Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Upload Document')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('path')
                            ->label('File')
                            ->required()
                            ->disk('public')
                            ->directory('tenants/sources')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'text/plain',
                                'text/markdown',
                                'text/html'
                            ])
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('name', $state->getClientOriginalName());
                                }
                            })
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pdf' => 'danger',
                        'docx' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'indexed' => 'success',
                        'failed' => 'danger',
                        'pending' => 'gray',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('chunks_count')
                    ->label('Chunks')
                    ->counts('chunks'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'pdf' => 'PDF',
                        'docx' => 'Word',
                        'txt' => 'Text',
                        'md' => 'Markdown',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'indexed' => 'Indexed',
                        'pending' => 'Pending',
                        'failed' => 'Failed',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('reindex')
                    ->label('Reindex')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (KnowledgeSource $record) {
                        $record->update(['status' => 'pending']);
                        ExtractTextJob::dispatch($record);
                        Notification::make()
                            ->title('Reindexing started')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->after(function (KnowledgeSource $record) {
                        // Cleanup Qdrant
                        app(\App\Services\QdrantService::class)->deleteBySource($record->tenant_id, $record->id);
                        if ($record->path) {
                            Storage::disk('public')->delete($record->path);
                        }
                    }),
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
            'index' => Pages\ListKnowledgeSources::route('/'),
            'create' => Pages\CreateKnowledgeSource::route('/create'),
            'view' => Pages\ViewKnowledgeSource::route('/{record}'),
            'edit' => Pages\EditKnowledgeSource::route('/{record}/edit'),
        ];
    }
}
