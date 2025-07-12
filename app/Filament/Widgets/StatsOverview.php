<?php

namespace App\Filament\Widgets;

use App\Models\Gist;
use App\Models\User;
use App\Models\Comment;
use App\Models\Tag;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // 使用缓存来优化性能，缓存 5 分钟
        $stats = Cache::remember('dashboard_stats', 300, function () {
            return [
                'total_gists' => Gist::count(),
                'public_gists' => Gist::where('is_public', true)->count(),
                'total_users' => User::count(),
                'approved_comments' => Comment::where('is_approved', true)->count(),
                'total_tags' => Tag::count(),
                'today_gists' => Gist::whereDate('created_at', today())->count(),
            ];
        });

        return [
            Stat::make('总 Gist 数', $stats['total_gists'])
                ->description('所有代码片段')
                ->descriptionIcon('heroicon-m-code-bracket')
                ->color('success'),

            Stat::make('公开 Gist', $stats['public_gists'])
                ->description('公开的代码片段')
                ->descriptionIcon('heroicon-m-eye')
                ->color('primary'),

            Stat::make('用户总数', $stats['total_users'])
                ->description('注册用户')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),

            Stat::make('评论总数', $stats['approved_comments'])
                ->description('已审核评论')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('info'),

            Stat::make('标签总数', $stats['total_tags'])
                ->description('所有标签')
                ->descriptionIcon('heroicon-m-tag')
                ->color('gray'),

            Stat::make('今日新增', $stats['today_gists'])
                ->description('今天创建的 Gist')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('success'),
        ];
    }
}
