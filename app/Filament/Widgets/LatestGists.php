<?php

namespace App\Filament\Widgets;

use App\Models\Gist;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestGists extends BaseWidget
{
    protected static ?string $heading = '最新 Gist';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Gist::query()->with(['user'])->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('标题')
                    ->searchable()
                    ->limit(50)
                    ->url(fn (Gist $record): string => route('gists.show', $record))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('作者')
                    ->sortable(),
                Tables\Columns\TextColumn::make('language')
                    ->label('语言')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\IconColumn::make('is_public')
                    ->label('公开')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash'),
                Tables\Columns\TextColumn::make('views_count')
                    ->label('浏览')
                    ->numeric()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('likes_count')
                    ->label('点赞')
                    ->numeric()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('m-d H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('查看')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Gist $record): string => route('gists.show', $record))
                    ->openUrlInNewTab(),
            ])
            ->paginated(false);
    }
}
