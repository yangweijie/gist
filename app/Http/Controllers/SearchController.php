<?php

namespace App\Http\Controllers;

use App\Models\Gist;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * 显示搜索页面
     */
    public function index(Request $request)
    {
        $query = $request->get('q', '');
        $results = collect();
        $totalResults = 0;
        $searchTime = 0;

        if (!empty($query)) {
            $startTime = microtime(true);
            $results = $this->performSearch($request);
            $totalResults = $results->total();
            $searchTime = round((microtime(true) - $startTime) * 1000, 2);

            // 记录搜索历史
            $this->recordSearchHistory($query);
        }

        return view('search.index', compact(
            'query',
            'results',
            'totalResults',
            'searchTime'
        ));
    }

    /**
     * 高级搜索页面
     */
    public function advanced()
    {
        $languages = Gist::select('language')
            ->distinct()
            ->whereNotNull('language')
            ->orderBy('language')
            ->pluck('language');

        $tags = Tag::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('search.advanced', compact('languages', 'tags', 'users'));
    }

    /**
     * 执行搜索
     */
    private function performSearch(Request $request)
    {
        $query = Gist::with(['user', 'tags'])
            ->where('is_public', true);

        // 如果用户已登录，也包含其私有 Gist
        if (Auth::check()) {
            $query->orWhere('user_id', Auth::id());
        }

        // 全文搜索
        if ($request->filled('q')) {
            $searchTerm = $request->get('q');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('content', 'like', "%{$searchTerm}%")
                  ->orWhere('filename', 'like', "%{$searchTerm}%")
                  ->orWhereHas('tags', function ($tagQuery) use ($searchTerm) {
                      $tagQuery->where('name', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                      $userQuery->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // 语言筛选
        if ($request->filled('language')) {
            $query->where('language', $request->get('language'));
        }

        // 标签筛选
        if ($request->filled('tags')) {
            $tags = is_array($request->get('tags'))
                ? $request->get('tags')
                : [$request->get('tags')];
            $query->whereHas('tags', function ($tagQuery) use ($tags) {
                $tagQuery->whereIn('name', $tags);
            });
        }

        // 用户筛选
        if ($request->filled('user')) {
            $query->where('user_id', $request->get('user'));
        }

        // 日期范围筛选
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        // 排序
        $sortBy = $request->get('sort', 'relevance');
        switch ($sortBy) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'most_liked':
                $query->orderBy('likes_count', 'desc');
                break;
            case 'most_viewed':
                $query->orderBy('views_count', 'desc');
                break;
            default: // relevance
                if ($request->filled('q')) {
                    // 简单的相关性排序：标题匹配优先
                    $searchTerm = $request->get('q');
                    $query->orderByRaw("
                        CASE
                            WHEN title LIKE '%{$searchTerm}%' THEN 1
                            WHEN description LIKE '%{$searchTerm}%' THEN 2
                            ELSE 3
                        END, created_at DESC
                    ");
                } else {
                    $query->orderBy('created_at', 'desc');
                }
                break;
        }

        return $query->paginate(12)->appends($request->query());
    }

    /**
     * 搜索建议 API
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        // 缓存搜索建议
        $cacheKey = 'search_suggestions_' . md5($query);
        $suggestions = Cache::remember($cacheKey, 300, function () use ($query) {
            // Gist 标题建议
            $gistSuggestions = Gist::where('is_public', true)
                ->where('title', 'like', "%{$query}%")
                ->limit(5)
                ->pluck('title')
                ->map(function ($title) {
                    return [
                        'type' => 'gist',
                        'text' => $title,
                        'icon' => 'code-bracket'
                    ];
                });

            // 标签建议
            $tagSuggestions = Tag::where('name', 'like', "%{$query}%")
                ->limit(5)
                ->pluck('name')
                ->map(function ($name) {
                    return [
                        'type' => 'tag',
                        'text' => $name,
                        'icon' => 'tag'
                    ];
                });

            // 语言建议
            $languageSuggestions = Gist::select('language')
                ->distinct()
                ->where('language', 'like', "%{$query}%")
                ->limit(3)
                ->pluck('language')
                ->map(function ($language) {
                    return [
                        'type' => 'language',
                        'text' => $language,
                        'icon' => 'code-bracket-square'
                    ];
                });

            // 用户建议
            $userSuggestions = User::where('name', 'like', "%{$query}%")
                ->limit(3)
                ->get(['id', 'name'])
                ->map(function ($user) {
                    return [
                        'type' => 'user',
                        'text' => $user->name,
                        'icon' => 'user',
                        'id' => $user->id
                    ];
                });

            return collect()
                ->merge($gistSuggestions)
                ->merge($tagSuggestions)
                ->merge($languageSuggestions)
                ->merge($userSuggestions)
                ->take(10)
                ->values();
        });

        return response()->json($suggestions);
    }

    /**
     * 热门搜索
     */
    public function trending()
    {
        $trending = Cache::remember('trending_searches', 3600, function () {
            return DB::table('search_history')
                ->select('query', DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('query')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->pluck('query');
        });

        return response()->json($trending);
    }

    /**
     * 记录搜索历史
     */
    private function recordSearchHistory($query)
    {
        if (strlen($query) < 2) {
            return;
        }

        DB::table('search_history')->insert([
            'query' => $query,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * 获取用户搜索历史
     */
    public function history()
    {
        if (!Auth::check()) {
            return response()->json([]);
        }

        $history = DB::table('search_history')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->pluck('query')
            ->unique()
            ->values();

        return response()->json($history);
    }

    /**
     * 清除用户搜索历史
     */
    public function clearHistory()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        DB::table('search_history')
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json(['success' => true]);
    }
}
