<?php

namespace App\Http\Controllers;

use App\Models\Gist;
use App\Models\Tag;
use App\Services\GitHubService;
use App\Http\Requests\GistRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GistController extends Controller
{
    protected GitHubService $githubService;

    public function __construct(GitHubService $githubService)
    {
        $this->githubService = $githubService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Gist::with(['user', 'tags'])
            ->where('is_public', true)
            ->orWhere('user_id', Auth::id());

        // 搜索功能
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
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

        $gists = $query->paginate(12)->appends(request()->query());

        // 获取筛选选项数据
        $languages = Gist::select('language')
            ->distinct()
            ->whereNotNull('language')
            ->pluck('language')
            ->sort();

        $tags = Tag::popular()->limit(20)->get();

        return view('gists.index', compact('gists', 'languages', 'tags'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tags = Tag::orderBy('name')->get();
        $languages = config('github.gist.language_mapping', []);

        return view('gists.create', compact('tags', 'languages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GistRequest $request)
    {
        try {
            DB::beginTransaction();

            // 创建本地 Gist
            $gist = Gist::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'description' => $request->description,
                'content' => $request->content,
                'language' => $request->language,
                'filename' => $request->filename ?: $this->generateFilename($request->language),
                'is_public' => $request->boolean('is_public'),
                'is_synced' => false,
            ]);

            // 处理标签
            if ($request->filled('tags')) {
                $this->syncTags($gist, $request->tags);
            }

            // 如果用户选择同步到 GitHub
            if ($request->boolean('sync_to_github') && Auth::user()->github_token) {
                try {
                    $githubData = $this->githubService->formatGistForGitHub($gist);
                    $githubGist = $this->githubService->createGist(Auth::user(), $githubData);

                    // 更新本地 Gist 的 GitHub 信息
                    $gist->update([
                        'github_gist_id' => $githubGist['id'],
                        'is_synced' => true,
                        'github_created_at' => now(),
                        'github_updated_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to sync gist to GitHub', [
                        'gist_id' => $gist->id,
                        'error' => $e->getMessage(),
                    ]);
                    // 不阻止本地创建，只是记录错误
                }
            }

            DB::commit();

            return redirect()->route('gists.show', $gist)
                ->with('success', __('gist.success.created'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create gist', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', __('gist.errors.create_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Gist $gist)
    {
        // 检查访问权限
        if (!$gist->is_public && $gist->user_id !== Auth::id()) {
            abort(403, __('gist.errors.no_permission'));
        }

        // 增加浏览次数
        $gist->increment('views_count');

        // 加载关联数据
        $gist->load(['user', 'tags', 'likes', 'comments.user', 'favorites']);

        // 检查当前用户是否已点赞和收藏
        $userLiked = Auth::check() ? $gist->likes()->where('user_id', Auth::id())->exists() : false;
        $userFavorited = Auth::check() ? $gist->favorites()->where('user_id', Auth::id())->exists() : false;

        return view('gists.show', compact('gist', 'userLiked', 'userFavorited'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Gist $gist)
    {
        // 检查编辑权限
        if ($gist->user_id !== Auth::id()) {
            abort(403, __('gist.errors.no_permission'));
        }

        $tags = Tag::orderBy('name')->get();
        $languages = config('github.gist.language_mapping', []);
        $selectedTags = $gist->tags->pluck('id')->toArray();

        return view('gists.edit', compact('gist', 'tags', 'languages', 'selectedTags'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GistRequest $request, Gist $gist)
    {
        // 检查编辑权限
        if ($gist->user_id !== Auth::id()) {
            abort(403, __('gist.errors.no_permission'));
        }

        try {
            DB::beginTransaction();

            // 更新本地 Gist
            $gist->update([
                'title' => $request->title,
                'description' => $request->description,
                'content' => $request->content,
                'language' => $request->language,
                'filename' => $request->filename ?: $this->generateFilename($request->language),
                'is_public' => $request->boolean('is_public'),
            ]);

            // 处理标签
            if ($request->filled('tags')) {
                $this->syncTags($gist, $request->tags);
            } else {
                $gist->tags()->detach();
            }

            // 如果用户选择同步到 GitHub 且 Gist 已关联 GitHub
            if ($request->boolean('sync_to_github') && $gist->github_gist_id && Auth::user()->github_token) {
                try {
                    $githubData = $this->githubService->formatGistForGitHub($gist);
                    $this->githubService->updateGist(Auth::user(), $gist->github_gist_id, $githubData);

                    $gist->update([
                        'is_synced' => true,
                        'github_updated_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to sync gist update to GitHub', [
                        'gist_id' => $gist->id,
                        'github_gist_id' => $gist->github_gist_id,
                        'error' => $e->getMessage(),
                    ]);
                    // 不阻止本地更新，只是记录错误
                }
            }

            DB::commit();

            return redirect()->route('gists.show', $gist)
                ->with('success', __('gist.success.updated'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update gist', [
                'gist_id' => $gist->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', __('gist.errors.update_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gist $gist)
    {
        // 检查删除权限
        if ($gist->user_id !== Auth::id()) {
            abort(403, '您没有权限删除此 Gist');
        }

        try {
            DB::beginTransaction();

            // 如果 Gist 关联了 GitHub，询问是否同时删除 GitHub 上的 Gist
            if ($gist->github_gist_id && Auth::user()->github_token) {
                try {
                    $this->githubService->deleteGist(Auth::user(), $gist->github_gist_id);
                } catch (\Exception $e) {
                    Log::error('Failed to delete gist from GitHub', [
                        'gist_id' => $gist->id,
                        'github_gist_id' => $gist->github_gist_id,
                        'error' => $e->getMessage(),
                    ]);
                    // 继续删除本地 Gist，但记录错误
                }
            }

            // 删除本地 Gist（软删除）
            $gist->delete();

            DB::commit();

            return redirect()->route('gists.index')
                ->with('success', __('gist.success.deleted'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete gist', [
                'gist_id' => $gist->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', __('gist.errors.delete_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * 我的 Gists 页面
     */
    public function myGists(Request $request)
    {
        $query = Gist::with(['tags'])
            ->where('user_id', Auth::id());

        // 搜索功能
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 可见性筛选
        if ($request->filled('visibility')) {
            $visibility = $request->get('visibility');
            if ($visibility === 'public') {
                $query->where('is_public', true);
            } elseif ($visibility === 'private') {
                $query->where('is_public', false);
            }
        }

        // 同步状态筛选
        if ($request->filled('sync_status')) {
            $syncStatus = $request->get('sync_status');
            if ($syncStatus === 'synced') {
                $query->where('is_synced', true);
            } elseif ($syncStatus === 'local') {
                $query->where('is_synced', false);
            }
        }

        $gists = $query->orderBy('updated_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        return view('gists.my-gists', compact('gists'));
    }

    /**
     * 批量操作
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,make_public,make_private,sync_to_github',
            'gist_ids' => 'required|array',
            'gist_ids.*' => 'exists:gists,id',
        ]);

        $gistIds = $request->gist_ids;
        $action = $request->action;

        // 确保用户只能操作自己的 Gists
        $gists = Gist::whereIn('id', $gistIds)
            ->where('user_id', Auth::id())
            ->get();

        if ($gists->isEmpty()) {
            return redirect()->back()
                ->with('error', '没有找到可操作的 Gist');
        }

        try {
            DB::beginTransaction();

            $successCount = 0;
            $errorCount = 0;

            foreach ($gists as $gist) {
                try {
                    switch ($action) {
                        case 'delete':
                            $gist->delete();
                            break;
                        case 'make_public':
                            $gist->update(['is_public' => true]);
                            break;
                        case 'make_private':
                            $gist->update(['is_public' => false]);
                            break;
                        case 'sync_to_github':
                            if (Auth::user()->github_token) {
                                $this->syncGistToGitHub($gist);
                            }
                            break;
                    }
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error('Bulk action failed for gist', [
                        'gist_id' => $gist->id,
                        'action' => $action,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            $message = "批量操作完成：成功 {$successCount} 个";
            if ($errorCount > 0) {
                $message .= "，失败 {$errorCount} 个";
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', '批量操作失败：' . $e->getMessage());
        }
    }

    /**
     * 同步标签
     */
    protected function syncTags(Gist $gist, array $tagNames): void
    {
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) {
                continue;
            }

            // 查找或创建标签
            $tag = Tag::firstOrCreate(
                ['name' => $tagName],
                ['slug' => Str::slug($tagName)]
            );

            $tagIds[] = $tag->id;
        }

        // 同步标签关联
        $gist->tags()->sync($tagIds);

        // 更新标签使用次数
        foreach ($tagIds as $tagId) {
            $tag = Tag::find($tagId);
            if ($tag) {
                $tag->increment('usage_count');
            }
        }
    }

    /**
     * 生成文件名
     */
    protected function generateFilename(string $language): string
    {
        $extensions = [
            'php' => 'php',
            'javascript' => 'js',
            'python' => 'py',
            'html' => 'html',
            'css' => 'css',
            'java' => 'java',
            'c' => 'c',
            'cpp' => 'cpp',
            'ruby' => 'rb',
            'go' => 'go',
            'rust' => 'rs',
            'swift' => 'swift',
            'sql' => 'sql',
            'shell' => 'sh',
            'markdown' => 'md',
            'json' => 'json',
            'xml' => 'xml',
            'yaml' => 'yml',
        ];

        $extension = $extensions[strtolower($language)] ?? 'txt';
        return 'gist.' . $extension;
    }

    /**
     * 同步 Gist 到 GitHub
     */
    protected function syncGistToGitHub(Gist $gist): void
    {
        if (!Auth::user()->github_token) {
            throw new \Exception('用户没有 GitHub token');
        }

        if ($gist->github_gist_id) {
            // 更新现有 GitHub Gist
            $githubData = $this->githubService->formatGistForGitHub($gist);
            $this->githubService->updateGist(Auth::user(), $gist->github_gist_id, $githubData);
        } else {
            // 创建新的 GitHub Gist
            $githubData = $this->githubService->formatGistForGitHub($gist);
            $githubGist = $this->githubService->createGist(Auth::user(), $githubData);

            $gist->update([
                'github_gist_id' => $githubGist['id'],
                'github_created_at' => now(),
            ]);
        }

        $gist->update([
            'is_synced' => true,
            'github_updated_at' => now(),
        ]);
    }
}
