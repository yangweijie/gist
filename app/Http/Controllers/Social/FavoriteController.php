<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Gist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * 切换收藏状态
     */
    public function toggle(Request $request, Gist $gist)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => '请先登录'
            ], 401);
        }

        $userId = Auth::id();
        
        // 防刷机制：检查最近的收藏频率
        $recentFavorites = Favorite::where('user_id', $userId)
            ->where('created_at', '>', now()->subMinutes(1))
            ->count();

        if ($recentFavorites >= 20) {
            return response()->json([
                'success' => false,
                'message' => '操作过于频繁，请稍后再试'
            ], 429);
        }

        $isFavorited = Favorite::toggle($userId, $gist->id);
        $favoriteCount = Favorite::getFavoriteCount($gist->id);

        // 更新 Gist 的收藏数缓存
        $gist->update(['favorites_count' => $favoriteCount]);

        return response()->json([
            'success' => true,
            'is_favorited' => $isFavorited,
            'favorite_count' => $favoriteCount,
            'message' => $isFavorited ? '收藏成功' : '取消收藏'
        ]);
    }

    /**
     * 获取收藏状态
     */
    public function status(Gist $gist)
    {
        $isFavorited = Auth::check() ? Favorite::isFavorited(Auth::id(), $gist->id) : false;
        $favoriteCount = Favorite::getFavoriteCount($gist->id);

        return response()->json([
            'is_favorited' => $isFavorited,
            'favorite_count' => $favoriteCount
        ]);
    }

    /**
     * 获取用户的收藏列表
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $query = Favorite::where('user_id', Auth::id())
            ->with(['gist.user', 'gist.tags'])
            ->orderBy('created_at', 'desc');

        // 搜索功能
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('gist', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // 标签筛选
        if ($request->filled('tag')) {
            $query->whereHas('gist.tags', function ($q) use ($request) {
                $q->where('slug', $request->tag);
            });
        }

        // 语言筛选
        if ($request->filled('language')) {
            $query->whereHas('gist', function ($q) use ($request) {
                $q->where('language', $request->language);
            });
        }

        $favorites = $query->paginate(12)->appends($request->query());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'favorites' => $favorites
            ]);
        }

        return view('favorites.index', compact('favorites'));
    }

    /**
     * 批量获取多个 Gist 的收藏状态
     */
    public function batchStatus(Request $request)
    {
        $request->validate([
            'gist_ids' => 'required|array',
            'gist_ids.*' => 'integer|exists:gists,id'
        ]);

        $gistIds = $request->gist_ids;
        $userId = Auth::id();

        $favorites = [];
        $counts = [];

        if ($userId) {
            $userFavorites = Favorite::where('user_id', $userId)
                ->whereIn('gist_id', $gistIds)
                ->pluck('gist_id')
                ->toArray();
            
            foreach ($gistIds as $gistId) {
                $favorites[$gistId] = in_array($gistId, $userFavorites);
            }
        } else {
            foreach ($gistIds as $gistId) {
                $favorites[$gistId] = false;
            }
        }

        // 获取收藏数
        $favoriteCounts = Favorite::whereIn('gist_id', $gistIds)
            ->groupBy('gist_id')
            ->selectRaw('gist_id, count(*) as count')
            ->pluck('count', 'gist_id')
            ->toArray();

        foreach ($gistIds as $gistId) {
            $counts[$gistId] = $favoriteCounts[$gistId] ?? 0;
        }

        return response()->json([
            'success' => true,
            'favorites' => $favorites,
            'counts' => $counts
        ]);
    }

    /**
     * 批量删除收藏
     */
    public function batchDestroy(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => '请先登录'
            ], 401);
        }

        $request->validate([
            'favorite_ids' => 'required|array',
            'favorite_ids.*' => 'integer'
        ]);

        $userId = Auth::id();
        $favoriteIds = $request->favorite_ids;

        // 只能删除自己的收藏
        $deleted = Favorite::where('user_id', $userId)
            ->whereIn('id', $favoriteIds)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "成功删除 {$deleted} 个收藏",
            'deleted_count' => $deleted
        ]);
    }

    /**
     * 获取收藏统计
     */
    public function stats()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => '请先登录'
            ], 401);
        }

        $userId = Auth::id();
        
        $totalFavorites = Favorite::where('user_id', $userId)->count();
        $recentFavorites = Favorite::where('user_id', $userId)
            ->where('created_at', '>', now()->subDays(7))
            ->count();

        // 按语言统计
        $languageStats = Favorite::where('user_id', $userId)
            ->join('gists', 'favorites.gist_id', '=', 'gists.id')
            ->groupBy('gists.language')
            ->selectRaw('gists.language, count(*) as count')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'stats' => [
                'total_favorites' => $totalFavorites,
                'recent_favorites' => $recentFavorites,
                'language_stats' => $languageStats
            ]
        ]);
    }
}
