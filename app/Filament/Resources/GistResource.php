<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GistResource\Pages;
use App\Filament\Resources\GistResource\RelationManagers;
use App\Models\Gist;
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GistResource extends Resource
{
    protected static ?string $model = Gist::class;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.gist.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.gist.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.gist.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation_groups.content');
    }

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('filament.sections.basic_info'))
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label(__('filament.fields.gist.user'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->label(__('filament.fields.gist.title'))
                            ->required()
                            ->maxLength(255)
                            ->validationMessages([
                                'required' => __('filament.validation.required', ['attribute' => __('filament.fields.gist.title')]),
                                'max' => __('filament.validation.max', ['attribute' => __('filament.fields.gist.title'), 'max' => 255]),
                            ]),
                        Forms\Components\Textarea::make('description')
                            ->label(__('filament.fields.gist.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('language')
                            ->label(__('filament.fields.gist.language'))
                            ->required()
                            ->maxLength(50)
                            ->validationMessages([
                                'required' => __('filament.validation.required', ['attribute' => __('filament.fields.gist.language')]),
                                'max' => __('filament.validation.max', ['attribute' => __('filament.fields.gist.language'), 'max' => 50]),
                            ]),
                        Forms\Components\TextInput::make('filename')
                            ->label(__('filament.fields.gist.filename'))
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make(__('filament.sections.code_content'))
                    ->schema([
                        Forms\Components\Textarea::make('content')
                            ->label(__('filament.fields.gist.content'))
                            ->required()
                            ->rows(15)
                            ->columnSpanFull()
                            ->validationMessages([
                                'required' => __('filament.validation.required', ['attribute' => __('filament.fields.gist.content')]),
                            ]),
                    ]),

                Forms\Components\Section::make(__('filament.sections.settings'))
                    ->schema([
                        Forms\Components\Toggle::make('is_public')
                            ->label(__('filament.fields.gist.is_public'))
                            ->default(true),
                        Forms\Components\Toggle::make('is_synced')
                            ->label('已同步到 GitHub')
                            ->default(false),
                        Forms\Components\TextInput::make('github_gist_id')
                            ->label(__('filament.fields.gist.github_gist_id'))
                            ->maxLength(255),
                    ])->columns(3),

                Forms\Components\Section::make('统计信息')
                    ->schema([
                        Forms\Components\TextInput::make('views_count')
                            ->label('浏览次数')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                        Forms\Components\TextInput::make('likes_count')
                            ->label('点赞数')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                        Forms\Components\TextInput::make('comments_count')
                            ->label('评论数')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                        Forms\Components\TextInput::make('favorites_count')
                            ->label('收藏数')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                    ])->columns(4),

                Forms\Components\Section::make('GitHub 信息')
                    ->schema([
                        Forms\Components\DateTimePicker::make('github_created_at')
                            ->label('GitHub 创建时间'),
                        Forms\Components\DateTimePicker::make('github_updated_at')
                            ->label('GitHub 更新时间'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('标题')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('作者')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('language')
                    ->label('语言')
                    ->searchable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\IconColumn::make('is_public')
                    ->label('公开')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash'),
                Tables\Columns\IconColumn::make('is_synced')
                    ->label('已同步')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('views_count')
                    ->label('浏览')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('likes_count')
                    ->label('点赞')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('comments_count')
                    ->label('评论')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('language')
                    ->label('编程语言')
                    ->options(function () {
                        return Gist::distinct('language')
                            ->whereNotNull('language')
                            ->pluck('language', 'language')
                            ->toArray();
                    }),
                TernaryFilter::make('is_public')
                    ->label('是否公开')
                    ->boolean()
                    ->trueLabel('公开')
                    ->falseLabel('私有')
                    ->native(false),
                TernaryFilter::make('is_synced')
                    ->label('是否同步')
                    ->boolean()
                    ->trueLabel('已同步')
                    ->falseLabel('未同步')
                    ->native(false),
                SelectFilter::make('user')
                    ->label('作者')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListGists::route('/'),
            'create' => Pages\CreateGist::route('/create'),
            'edit' => Pages\EditGist::route('/{record}/edit'),
        ];
    }
}
