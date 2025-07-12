<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Tag::query();

        // 搜索功能
        if ($request->filled('search')) {
            $query->byName($request->search);
        }

        // 筛选功能
        if ($request->filled('featured')) {
            $query->featured();
        }

        if ($request->filled('min_usage')) {
            $query->withMinUsage($request->min_usage);
        }

        // 排序
        $sortBy = $request->get('sort', 'popular');
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name');
                break;
            case 'created':
                $query->orderBy('created_at', 'desc');
                break;
            case 'usage':
                $query->orderBy('usage_count', 'desc');
                break;
            default:
                $query->popular();
        }

        $tags = $query->withCount('gists')->paginate(20);
        $popularTags = Tag::getPopularTags(10);
        $featuredTags = Tag::featured()->withCount('gists')->get();

        return view('tags.index', compact('tags', 'popularTags', 'featuredTags'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $colors = Tag::getAvailableColors();
        return view('tags.create', compact('colors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:tags,name',
            'description' => 'nullable|string|max:255',
            'color' => ['required', Rule::in(array_keys(Tag::getAvailableColors()))],
            'is_featured' => 'boolean',
        ]);

        Tag::create([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color,
            'is_featured' => $request->boolean('is_featured'),
            'usage_count' => 0,
        ]);

        return redirect()->route('tags.index')
            ->with('success', '标签创建成功！');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag, Request $request)
    {
        // 获取使用该标签的 Gist
        $query = $tag->gists()
            ->with(['user', 'tags'])
            ->where('is_public', true);

        // 如果用户已登录，也显示其私有 Gist
        if (Auth::check()) {
            $query->orWhere('user_id', Auth::id());
        }

        // 搜索功能
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // 排序
        $sortBy = $request->get('sort', 'created_at');
        switch ($sortBy) {
            case 'popular':
                $query->orderBy('likes_count', 'desc');
                break;
            case 'views':
                $query->orderBy('views_count', 'desc');
                break;
            case 'updated':
                $query->orderBy('updated_at', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $gists = $query->paginate(12)->appends($request->query());
        $relatedTags = $tag->getRelatedTags();

        return view('tags.show', compact('tag', 'gists', 'relatedTags'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag)
    {
        $colors = Tag::getAvailableColors();
        return view('tags.edit', compact('tag', 'colors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('tags', 'name')->ignore($tag->id)],
            'description' => 'nullable|string|max:255',
            'color' => ['required', Rule::in(array_keys(Tag::getAvailableColors()))],
            'is_featured' => 'boolean',
        ]);

        $tag->update([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color,
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return redirect()->route('tags.index')
            ->with('success', '标签更新成功！');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        // 检查是否有 Gist 使用该标签
        if ($tag->gists()->count() > 0) {
            return redirect()->route('tags.index')
                ->with('error', '无法删除该标签，因为还有 Gist 在使用它。');
        }

        $tag->delete();

        return redirect()->route('tags.index')
            ->with('success', '标签删除成功！');
    }

    /**
     * 获取标签建议 (AJAX)
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $tags = Tag::searchTags($query, 10);

        return response()->json($tags->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'color' => $tag->color,
                'usage_count' => $tag->usage_count,
            ];
        }));
    }

    /**
     * 标签云数据 (AJAX)
     */
    public function cloud()
    {
        $tags = Tag::withMinUsage(1)
            ->withCount('gists')
            ->orderBy('usage_count', 'desc')
            ->limit(50)
            ->get();

        return response()->json($tags->map(function ($tag) {
            return [
                'name' => $tag->name,
                'slug' => $tag->slug,
                'count' => $tag->usage_count,
                'color' => $tag->color,
                'url' => route('tags.show', $tag),
            ];
        }));
    }
}
