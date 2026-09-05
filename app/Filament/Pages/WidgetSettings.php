<?php

namespace App\Filament\Pages;

use App\Models\WidgetSetting;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class WidgetSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Settings';
    protected static string $view = 'filament.pages.widget-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = WidgetSetting::firstOrCreate([
            'tenant_id' => Filament()->getTenant()->id
        ]);

        $this->form->fill($settings->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Appearance')
                    ->schema([
                        ColorPicker::make('primary_color')
                            ->required(),
                        Select::make('theme')
                            ->options([
                                'light' => 'Light',
                                'dark' => 'Dark',
                            ])
                            ->required(),
                    ])->columns(2),
                
                Section::make('Content')
                    ->schema([
                        TextInput::make('greeting_message')
                            ->maxLength(255),
                    ]),

                Section::make('Security')
                    ->schema([
                        TagsInput::make('allowed_domains')
                            ->placeholder('Add domains...')
                            ->suggestions([
                                'localhost',
                                'example.com',
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            
            $settings = WidgetSetting::updateOrCreate(
                ['tenant_id' => Filament()->getTenant()->id],
                $data
            );

            Notification::make()
                ->success()
                ->title(__('filament-panels::resources/pages/edit-record.notifications.saved.title'))
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error saving settings')
                ->body($e->getMessage())
                ->send();
        }
    }
    
    public function getWidgetSnippetProperty(): string
    {
        $tenant = Filament()->getTenant();
        $url = url('/widget.js');
        return "<script src=\"{$url}\" data-key=\"{$tenant->widget_key}\"></script>";
    }
}
