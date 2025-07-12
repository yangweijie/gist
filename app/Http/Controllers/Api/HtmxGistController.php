<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gist;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HtmxGistController extends Controller
{
    /**
     * 搜索 Gists (HTMX)
     */
    public function search(Request $request)
    {
        $query = Gist::with(['user', 'tags'])
            ->where('is_public', true)
            ->orWhere('user_id', Auth::id());

        // 搜索功能（增强版）
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('filename', 'like', "%{$search}%")
                  ->orWhereHas('tags', function ($tagQuery) use ($search) {
                      $tagQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // 语言筛选
        if ($request->filled('language')) {
            $query->where('language', $request->get('language'));
        }

        // 标签筛选
        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->get('tag'));
            });
        }

        // 用户筛选
        if ($request->filled('user')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->get('user')}%");
            });
        }

        // 排序
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        switch ($sortBy) {
            case 'popular':
                $query->orderBy('likes_count', $sortOrder);
                break;
            case 'views':
                $query->orderBy('views_count', $sortOrder);
                break;
            case 'updated':
                $query->orderBy('updated_at', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }

        $gists = $query->paginate(12)->appends($request->query());

        return view('partials.gist-list', compact('gists'));
    }

    /**
     * 加载更多 Gists (无限滚动)
     */
    public function loadMore(Request $request)
    {
        $page = $request->get('page', 1);
        
        $query = Gist::with(['user', 'tags'])
            ->where('is_public', true)
            ->orWhere('user_id', Auth::id());

        // 应用相同的筛选条件
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('language')) {
            $query->where('language', $request->get('language'));
        }

        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->get('tag'));
            });
        }

        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        switch ($sortBy) {
            case 'popular':
                $query->orderBy('likes_count', $sortOrder);
                break;
            case 'views':
                $query->orderBy('views_count', $sortOrder);
                break;
            case 'updated':
                $query->orderBy('updated_at', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }

        $gists = $query->paginate(12, ['*'], 'page', $page);

        return view('partials.gist-cards', compact('gists'));
    }

    /**
     * 获取 Gist 预览 (模态框)
     */
    public function preview(Gist $gist)
    {
        // 检查访问权限
        if (!$gist->is_public && $gist->user_id !== Auth::id()) {
            abort(403, '您没有权限访问此 Gist');
        }

        return view('partials.gist-preview', compact('gist'));
    }

    /**
     * 快速编辑 Gist 标题和描述
     */
    public function quickEdit(Request $request, Gist $gist)
    {
        // 检查编辑权限
        if ($gist->user_id !== Auth::id()) {
            abort(403, '您没有权限编辑此 Gist');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $gist->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return view('partials.gist-card', compact('gist'));
    }

    /**
     * 切换 Gist 可见性
     */
    public function toggleVisibility(Gist $gist)
    {
        // 检查编辑权限
        if ($gist->user_id !== Auth::id()) {
            abort(403, '您没有权限编辑此 Gist');
        }

        $gist->update([
            'is_public' => !$gist->is_public,
        ]);

        return view('partials.gist-card', compact('gist'));
    }

    /**
     * 获取搜索建议
     */
    public function searchSuggestions(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        // 搜索 Gist 标题
        $gistSuggestions = Gist::where('is_public', true)
            ->where('title', 'like', "%{$query}%")
            ->limit(5)
            ->pluck('title')
            ->map(function ($title) {
                return ['type' => 'gist', 'text' => $title];
            });

        // 搜索标签
        $tagSuggestions = Tag::where('name', 'like', "%{$query}%")
            ->limit(5)
            ->pluck('name')
            ->map(function ($name) {
                return ['type' => 'tag', 'text' => $name];
            });

        // 搜索语言
        $languages = Gist::select('language')
            ->distinct()
            ->where('language', 'like', "%{$query}%")
            ->limit(3)
            ->pluck('language')
            ->map(function ($language) {
                return ['type' => 'language', 'text' => $language];
            });

        $suggestions = collect()
            ->merge($gistSuggestions)
            ->merge($tagSuggestions)
            ->merge($languages)
            ->take(10);

        return response()->json($suggestions);
    }

    /**
     * 获取筛选选项
     */
    public function getFilterOptions(Request $request)
    {
        $type = $request->get('type');
        
        switch ($type) {
            case 'languages':
                $options = Gist::select('language')
                    ->distinct()
                    ->whereNotNull('language')
                    ->orderBy('language')
                    ->pluck('language')
                    ->map(function ($language) {
                        return ['value' => $language, 'label' => $language];
                    });
                break;
                
            case 'tags':
                $options = Tag::orderBy('usage_count', 'desc')
                    ->limit(20)
                    ->get()
                    ->map(function ($tag) {
                        return ['value' => $tag->slug, 'label' => $tag->name];
                    });
                break;
                
            default:
                $options = collect();
        }

        return view('partials.filter-options', compact('options', 'type'));
    }

    /**
     * 获取统计信息
     */
    public function getStats()
    {
        $stats = [
            'total_gists' => Gist::where('is_public', true)->count(),
            'total_users' => \App\Models\User::count(),
            'total_languages' => Gist::distinct('language')->count(),
            'total_tags' => Tag::count(),
        ];

        return view('partials.stats', compact('stats'));
    }
}
