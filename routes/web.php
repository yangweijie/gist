<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\GitHubController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// 健康检查路由
Route::get('/health', [App\Http\Controllers\HealthController::class, 'check']);
Route::get('/health/ping', [App\Http\Controllers\HealthController::class, 'ping']);
Route::get('/health/status', [App\Http\Controllers\HealthController::class, 'status']);

// 简单的测试路由
Route::get('/test', function () {
    return response()->json(['status' => 'ok', 'message' => 'Test route working']);
});

// PHP 运行器测试路由
Route::get('/php-runner-test', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'PHP Runner test route working',
        'features' => [
            'code_validation' => true,
            'code_execution' => true,
            'security_checks' => true,
            'gist_integration' => true
        ]
    ]);
});

// 超简单的 JSON 测试路由
Route::get('/simple-json', function () {
    return response()->json(['simple' => 'json', 'test' => true]);
});

// 最小化的 PHP 运行器页面
Route::get('/php-runner-minimal', function () {
    return view('php-runner.minimal');
});

// 认证路由
Route::middleware('guest')->group(function () {
    // 登录
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // 注册
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // GitHub OAuth
    Route::get('/auth/github', [GitHubController::class, 'redirectToGitHub'])->name('auth.github');
    Route::get('/auth/github/callback', [GitHubController::class, 'handleGitHubCallback'])->name('auth.github.callback');
});

// 登出路由（需要认证）
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// GitHub 绑定路由（需要认证）
Route::middleware('auth')->group(function () {
    Route::get('/auth/github/bind', [GitHubController::class, 'bindGitHub'])->name('auth.github.bind');
    Route::get('/auth/github/bind/callback', [GitHubController::class, 'handleBindCallback'])->name('auth.github.bind.callback');
    Route::post('/auth/github/unbind', [GitHubController::class, 'unbindGitHub'])->name('auth.github.unbind');
});

// Gist 相关路由（注意：具体路由必须在通配符路由之前）
Route::get('/gists', [App\Http\Controllers\GistController::class, 'index'])->name('gists.index');
// 将 gists/create 移到这里，在 {gist} 通配符路由之前
Route::get('/gists/create', [App\Http\Controllers\GistController::class, 'create'])->name('gists.create')->middleware('auth');
Route::get('/gists/{gist}', [App\Http\Controllers\GistController::class, 'show'])->name('gists.show');

// 标签相关路由
Route::get('/tags', [App\Http\Controllers\TagController::class, 'index'])->name('tags.index');
Route::get('/tags/suggestions', [App\Http\Controllers\TagController::class, 'suggestions'])->name('tags.suggestions');
Route::get('/tags/cloud', [App\Http\Controllers\TagController::class, 'cloud'])->name('tags.cloud');
Route::get('/tags/{tag}', [App\Http\Controllers\TagController::class, 'show'])->name('tags.show');

// 公开的 HTMX API 路由
Route::prefix('htmx')->name('htmx.')->group(function () {
    Route::get('/gists/search', [App\Http\Controllers\Api\HtmxGistController::class, 'search'])->name('gists.search');
    Route::get('/gists/load-more', [App\Http\Controllers\Api\HtmxGistController::class, 'loadMore'])->name('gists.load-more');
    Route::get('/gists/{gist}/preview', [App\Http\Controllers\Api\HtmxGistController::class, 'preview'])->name('gists.preview');
    Route::get('/search/suggestions', [App\Http\Controllers\Api\HtmxGistController::class, 'searchSuggestions'])->name('search.suggestions');
    Route::get('/filter/options', [App\Http\Controllers\Api\HtmxGistController::class, 'getFilterOptions'])->name('filter.options');
    Route::get('/stats', [App\Http\Controllers\Api\HtmxGistController::class, 'getStats'])->name('stats');
});

// 社交功能路由
Route::prefix('social')->name('social.')->group(function () {
    // 点赞相关
    Route::get('/likes/{gist}/status', [App\Http\Controllers\Social\LikeController::class, 'status'])->name('likes.status');
    Route::post('/likes/batch-status', [App\Http\Controllers\Social\LikeController::class, 'batchStatus'])->name('likes.batch-status');

    // 收藏相关
    Route::get('/favorites/{gist}/status', [App\Http\Controllers\Social\FavoriteController::class, 'status'])->name('favorites.status');
    Route::post('/favorites/batch-status', [App\Http\Controllers\Social\FavoriteController::class, 'batchStatus'])->name('favorites.batch-status');

    // 评论相关
    Route::get('/comments/{gist}', [App\Http\Controllers\Social\CommentController::class, 'index'])->name('comments.index');
});

// 需要认证的页面
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Gist 管理路由（create 路由已移到上面，避免与 {gist} 冲突）
    Route::post('/gists', [App\Http\Controllers\GistController::class, 'store'])->name('gists.store');
    Route::get('/gists/{gist}/edit', [App\Http\Controllers\GistController::class, 'edit'])->name('gists.edit');
    Route::put('/gists/{gist}', [App\Http\Controllers\GistController::class, 'update'])->name('gists.update');
    Route::delete('/gists/{gist}', [App\Http\Controllers\GistController::class, 'destroy'])->name('gists.destroy');

    // 我的 Gists
    Route::get('/my-gists', [App\Http\Controllers\GistController::class, 'myGists'])->name('gists.my');

    // 批量操作
    Route::post('/gists/bulk-action', [App\Http\Controllers\GistController::class, 'bulkAction'])->name('gists.bulk-action');

    // 标签管理路由
    Route::get('/tags/create', [App\Http\Controllers\TagController::class, 'create'])->name('tags.create');
    Route::post('/tags', [App\Http\Controllers\TagController::class, 'store'])->name('tags.store');
    Route::get('/tags/{tag}/edit', [App\Http\Controllers\TagController::class, 'edit'])->name('tags.edit');
    Route::put('/tags/{tag}', [App\Http\Controllers\TagController::class, 'update'])->name('tags.update');
    Route::delete('/tags/{tag}', [App\Http\Controllers\TagController::class, 'destroy'])->name('tags.destroy');

    // 需要认证的 HTMX API 路由
    Route::prefix('htmx')->name('htmx.')->group(function () {
        Route::post('/gists/{gist}/quick-edit', [App\Http\Controllers\Api\HtmxGistController::class, 'quickEdit'])->name('gists.quick-edit');
        Route::post('/gists/{gist}/toggle-visibility', [App\Http\Controllers\Api\HtmxGistController::class, 'toggleVisibility'])->name('gists.toggle-visibility');
    });

    // 需要认证的社交功能路由
    Route::prefix('social')->name('social.')->group(function () {
        // 点赞相关
        Route::post('/likes/{gist}/toggle', [App\Http\Controllers\Social\LikeController::class, 'toggle'])->name('likes.toggle');
        Route::get('/likes/user', [App\Http\Controllers\Social\LikeController::class, 'userLikes'])->name('likes.user');

        // 收藏相关
        Route::post('/favorites/{gist}/toggle', [App\Http\Controllers\Social\FavoriteController::class, 'toggle'])->name('favorites.toggle');
        Route::get('/favorites', [App\Http\Controllers\Social\FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('/favorites/batch-destroy', [App\Http\Controllers\Social\FavoriteController::class, 'batchDestroy'])->name('favorites.batch-destroy');
        Route::get('/favorites/stats', [App\Http\Controllers\Social\FavoriteController::class, 'stats'])->name('favorites.stats');

        // 评论相关
        Route::post('/comments/{gist}', [App\Http\Controllers\Social\CommentController::class, 'store'])->name('comments.store');
        Route::delete('/comments/{comment}', [App\Http\Controllers\Social\CommentController::class, 'destroy'])->name('comments.destroy');
        Route::post('/comments/{comment}/approve', [App\Http\Controllers\Social\CommentController::class, 'approve'])->name('comments.approve');
        Route::post('/comments/{comment}/reject', [App\Http\Controllers\Social\CommentController::class, 'reject'])->name('comments.reject');
    });

    // GitHub API 测试路由
    Route::prefix('test/github')->group(function () {
        Route::get('/connection', [App\Http\Controllers\TestGitHubController::class, 'testConnection']);
        Route::get('/gists', [App\Http\Controllers\TestGitHubController::class, 'testGetGists']);
        Route::post('/sync', [App\Http\Controllers\TestGitHubController::class, 'testSync']);
    });
});

// 搜索功能路由
Route::get('/search', [App\Http\Controllers\SearchController::class, 'index'])->name('search');
Route::get('/search/advanced', [App\Http\Controllers\SearchController::class, 'advanced'])->name('search.advanced');
Route::get('/search/suggestions', [App\Http\Controllers\SearchController::class, 'suggestions'])->name('search.suggestions');
Route::get('/search/trending', [App\Http\Controllers\SearchController::class, 'trending'])->name('search.trending');

Route::middleware('auth')->group(function () {
    Route::get('/search/history', [App\Http\Controllers\SearchController::class, 'history'])->name('search.history');
    Route::delete('/search/history', [App\Http\Controllers\SearchController::class, 'clearHistory'])->name('search.clear-history');
});

// PHP 运行器路由
Route::get('/php-runner', [App\Http\Controllers\PhpRunnerController::class, 'index'])->name('php-runner.index');
Route::get('/php-runner/gist/{gist}', [App\Http\Controllers\PhpRunnerController::class, 'runGist'])->name('php-runner.gist');
Route::post('/php-runner/validate', [App\Http\Controllers\PhpRunnerController::class, 'validate'])->name('php-runner.validate');
Route::get('/php-runner/examples', [App\Http\Controllers\PhpRunnerController::class, 'examples'])->name('php-runner.examples');

Route::middleware('auth')->group(function () {
    Route::post('/php-runner/log', [App\Http\Controllers\PhpRunnerController::class, 'logExecution'])->name('php-runner.log');
});

// 语言切换路由
Route::get('/locale/{locale}', [App\Http\Controllers\LocaleController::class, 'switch'])->name('locale.switch');
