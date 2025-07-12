<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentResource\Pages;
use App\Filament\Resources\CommentResource\RelationManagers;
use App\Models\Comment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.comment.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.comment.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.comment.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation_groups.content');
    }

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label(__('filament.fields.comment.user'))
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\Select::make('gist_id')
                    ->label(__('filament.fields.comment.gist'))
                    ->relationship('gist', 'title')
                    ->required(),
                Forms\Components\Select::make('parent_id')
                    ->label(__('filament.fields.comment.parent'))
                    ->relationship('parent', 'id'),
                Forms\Components\Textarea::make('content')
                    ->label(__('filament.fields.comment.content'))
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_approved')
                    ->label(__('filament.fields.comment.is_approved'))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gist.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('parent.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_approved')
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
            'index' => Pages\ListComments::route('/'),
            'create' => Pages\CreateComment::route('/create'),
            'edit' => Pages\EditComment::route('/{record}/edit'),
        ];
    }
}
