<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TagResource\Pages;
use App\Filament\Resources\TagResource\RelationManagers;
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.tag.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.tag.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.tag.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation_groups.content');
    }

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('filament.fields.tag.name'))
                    ->required()
                    ->validationMessages([
                        'required' => __('filament.validation.required', ['attribute' => __('filament.fields.tag.name')]),
                    ]),
                Forms\Components\TextInput::make('slug')
                    ->label(__('filament.fields.tag.slug'))
                    ->required()
                    ->validationMessages([
                        'required' => __('filament.validation.required', ['attribute' => __('filament.fields.tag.slug')]),
                    ]),
                Forms\Components\Textarea::make('description')
                    ->label(__('filament.fields.tag.description'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('color')
                    ->label(__('filament.fields.tag.color'))
                    ->required()
                    ->validationMessages([
                        'required' => __('filament.validation.required', ['attribute' => __('filament.fields.tag.color')]),
                    ]),
                Forms\Components\TextInput::make('usage_count')
                    ->label(__('filament.fields.tag.usage_count'))
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->validationMessages([
                        'required' => __('filament.validation.required', ['attribute' => __('filament.fields.tag.usage_count')]),
                        'numeric' => __('filament.validation.numeric', ['attribute' => __('filament.fields.tag.usage_count')]),
                    ]),
                Forms\Components\Toggle::make('is_featured')
                    ->label(__('filament.fields.tag.is_featured'))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('color')
                    ->searchable(),
                Tables\Columns\TextColumn::make('usage_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
        ];
    }
}
