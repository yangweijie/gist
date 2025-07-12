<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function getNavigationLabel(): string
    {
        return __('filament.pages.settings.navigation_label');
    }

    public function getTitle(): string
    {
        return __('filament.pages.settings.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation_groups.system');
    }

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'site_name' => config('app.name', 'Gist Manager'),
            'site_description' => 'GitHub Gist 管理系统',
            'items_per_page' => 12,
            'enable_registration' => true,
            'enable_comments' => true,
            'auto_approve_comments' => false,
            'github_sync_enabled' => true,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('filament.sections.website_settings'))
                    ->schema([
                        Forms\Components\TextInput::make('site_name')
                            ->label(__('filament.fields.settings.site_name'))
                            ->required(),
                        Forms\Components\Textarea::make('site_description')
                            ->label(__('filament.fields.settings.site_description'))
                            ->rows(3),
                        Forms\Components\TextInput::make('items_per_page')
                            ->label(__('filament.fields.settings.items_per_page'))
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(100),
                    ]),

                Forms\Components\Section::make(__('filament.sections.feature_settings'))
                    ->schema([
                        Forms\Components\Toggle::make('enable_registration')
                            ->label(__('filament.fields.settings.enable_registration')),
                        Forms\Components\Toggle::make('enable_comments')
                            ->label(__('filament.fields.settings.enable_comments')),
                        Forms\Components\Toggle::make('auto_approve_comments')
                            ->label(__('filament.fields.settings.auto_approve_comments')),
                        Forms\Components\Toggle::make('github_sync_enabled')
                            ->label(__('filament.fields.settings.github_sync_enabled')),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament.actions.save'))
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // 这里可以保存设置到数据库或配置文件
        // 暂时只显示成功消息

        Notification::make()
            ->title(__('filament.messages.saved'))
            ->success()
            ->send();
    }
}
