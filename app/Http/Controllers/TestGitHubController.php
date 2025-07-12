<?php

namespace App\Http\Controllers;

use App\Services\GitHubService;
use App\Jobs\SyncGistJob;
use Illuminate\Support\Facades\Auth;

class TestGitHubController extends Controller
{
    protected GitHubService $githubService;

    public function __construct(GitHubService $githubService)
    {
        $this->githubService = $githubService;
    }

    /**
     * 测试 GitHub API 连接
     */
    public function testConnection()
    {
        $user = Auth::user();

        if (!$user || !$user->github_token) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated or no GitHub token',
            ], 401);
        }

        try {
            // 测试获取速率限制
            $rateLimit = $this->githubService->getRateLimit($user);

            return response()->json([
                'success' => true,
                'message' => 'GitHub API connection successful',
                'rate_limit' => $rateLimit,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'GitHub API connection failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 测试获取用户 Gists
     */
    public function testGetGists()
    {
        $user = Auth::user();

        if (!$user || !$user->github_token) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated or no GitHub token',
            ], 401);
        }

        try {
            $gists = $this->githubService->getUserGists($user, 1, 5);

            return response()->json([
                'success' => true,
                'message' => 'Successfully fetched gists',
                'count' => count($gists),
                'gists' => array_map(function ($gist) {
                    return [
                        'id' => $gist['id'],
                        'description' => $gist['description'],
                        'public' => $gist['public'],
                        'created_at' => $gist['created_at'],
                        'updated_at' => $gist['updated_at'],
                        'files' => array_keys($gist['files'] ?? []),
                    ];
                }, $gists),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch gists: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 测试同步 Gists
     */
    public function testSync()
    {
        $user = Auth::user();

        if (!$user || !$user->github_token) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated or no GitHub token',
            ], 401);
        }

        try {
            // 使用队列异步同步
            SyncGistJob::dispatch($user, true); // 全量同步

            return response()->json([
                'success' => true,
                'message' => 'Sync job dispatched successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to dispatch sync job: ' . $e->getMessage(),
            ], 500);
        }
    }
}
