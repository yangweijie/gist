<?php

namespace App\Services;

use App\Models\User;
use App\Models\Gist;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GitHubService
{
    protected $baseUrl;
    protected $cachePrefix;
    protected $cacheTtl;

    public function __construct()
    {
        $this->baseUrl = config('github.api_url');
        $this->cachePrefix = config('github.cache.prefix');
        $this->cacheTtl = config('github.cache.ttl');
    }

    /**
     * 获取用户的 GitHub Gist 列表
     */
    public function getUserGists(User $user, int $page = 1, int $perPage = 30): array
    {
        if (!$user->github_token) {
            throw new \Exception('User does not have GitHub token');
        }

        $cacheKey = $this->cachePrefix . "user_gists_{$user->id}_{$page}_{$perPage}";
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($user, $page, $perPage) {
            try {
                $response = Http::withToken($user->github_token)
                    ->get("{$this->baseUrl}/gists", [
                        'page' => $page,
                        'per_page' => $perPage,
                    ]);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error('GitHub API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'user_id' => $user->id,
                ]);

                throw new \Exception('Failed to fetch gists from GitHub: ' . $response->status());
            } catch (\Exception $e) {
                Log::error('GitHub Service Error', [
                    'message' => $e->getMessage(),
                    'user_id' => $user->id,
                ]);
                throw $e;
            }
        });
    }

    /**
     * 获取单个 Gist 详情
     */
    public function getGist(User $user, string $gistId): array
    {
        if (!$user->github_token) {
            throw new \Exception('User does not have GitHub token');
        }

        $cacheKey = $this->cachePrefix . "gist_{$gistId}";
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($user, $gistId) {
            try {
                $response = Http::withToken($user->github_token)
                    ->get("{$this->baseUrl}/gists/{$gistId}");

                if ($response->successful()) {
                    return $response->json();
                }

                if ($response->status() === 404) {
                    throw new \Exception('Gist not found');
                }

                Log::error('GitHub API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'gist_id' => $gistId,
                    'user_id' => $user->id,
                ]);

                throw new \Exception('Failed to fetch gist from GitHub: ' . $response->status());
            } catch (\Exception $e) {
                Log::error('GitHub Service Error', [
                    'message' => $e->getMessage(),
                    'gist_id' => $gistId,
                    'user_id' => $user->id,
                ]);
                throw $e;
            }
        });
    }

    /**
     * 创建新的 Gist
     */
    public function createGist(User $user, array $gistData): array
    {
        if (!$user->github_token) {
            throw new \Exception('User does not have GitHub token');
        }

        try {
            $response = Http::withToken($user->github_token)
                ->post("{$this->baseUrl}/gists", $gistData);

            if ($response->successful()) {
                // 清除相关缓存
                $this->clearUserGistsCache($user);
                return $response->json();
            }

            Log::error('GitHub API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'user_id' => $user->id,
                'data' => $gistData,
            ]);

            throw new \Exception('Failed to create gist on GitHub: ' . $response->status());
        } catch (\Exception $e) {
            Log::error('GitHub Service Error', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
                'data' => $gistData,
            ]);
            throw $e;
        }
    }

    /**
     * 更新 Gist
     */
    public function updateGist(User $user, string $gistId, array $gistData): array
    {
        if (!$user->github_token) {
            throw new \Exception('User does not have GitHub token');
        }

        try {
            $response = Http::withToken($user->github_token)
                ->patch("{$this->baseUrl}/gists/{$gistId}", $gistData);

            if ($response->successful()) {
                // 清除相关缓存
                $this->clearGistCache($gistId);
                $this->clearUserGistsCache($user);
                return $response->json();
            }

            if ($response->status() === 404) {
                throw new \Exception('Gist not found');
            }

            Log::error('GitHub API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'gist_id' => $gistId,
                'user_id' => $user->id,
                'data' => $gistData,
            ]);

            throw new \Exception('Failed to update gist on GitHub: ' . $response->status());
        } catch (\Exception $e) {
            Log::error('GitHub Service Error', [
                'message' => $e->getMessage(),
                'gist_id' => $gistId,
                'user_id' => $user->id,
                'data' => $gistData,
            ]);
            throw $e;
        }
    }

    /**
     * 删除 Gist
     */
    public function deleteGist(User $user, string $gistId): bool
    {
        if (!$user->github_token) {
            throw new \Exception('User does not have GitHub token');
        }

        try {
            $response = Http::withToken($user->github_token)
                ->delete("{$this->baseUrl}/gists/{$gistId}");

            if ($response->successful()) {
                // 清除相关缓存
                $this->clearGistCache($gistId);
                $this->clearUserGistsCache($user);
                return true;
            }

            if ($response->status() === 404) {
                throw new \Exception('Gist not found');
            }

            Log::error('GitHub API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'gist_id' => $gistId,
                'user_id' => $user->id,
            ]);

            throw new \Exception('Failed to delete gist on GitHub: ' . $response->status());
        } catch (\Exception $e) {
            Log::error('GitHub Service Error', [
                'message' => $e->getMessage(),
                'gist_id' => $gistId,
                'user_id' => $user->id,
            ]);
            throw $e;
        }
    }

    /**
     * 检查 API 限制
     */
    public function getRateLimit(User $user): array
    {
        if (!$user->github_token) {
            throw new \Exception('User does not have GitHub token');
        }

        try {
            $response = Http::withToken($user->github_token)
                ->get("{$this->baseUrl}/rate_limit");

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('Failed to get rate limit: ' . $response->status());
        } catch (\Exception $e) {
            Log::error('GitHub Service Error', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            throw $e;
        }
    }

    /**
     * 清除用户 Gist 缓存
     */
    protected function clearUserGistsCache(User $user): void
    {
        $pattern = $this->cachePrefix . "user_gists_{$user->id}_*";
        // 注意：这里简化处理，实际项目中可能需要更复杂的缓存清理逻辑
        Cache::forget($pattern);
    }

    /**
     * 清除单个 Gist 缓存
     */
    protected function clearGistCache(string $gistId): void
    {
        Cache::forget($this->cachePrefix . "gist_{$gistId}");
    }

    /**
     * 格式化 Gist 数据用于本地存储
     */
    public function formatGistForLocal(array $githubGist): array
    {
        $files = $githubGist['files'] ?? [];
        $firstFile = reset($files);

        return [
            'github_gist_id' => $githubGist['id'],
            'title' => $githubGist['description'] ?: 'Untitled',
            'description' => $githubGist['description'],
            'content' => $firstFile['content'] ?? '',
            'language' => $this->normalizeLanguage($firstFile['language'] ?? 'text'),
            'filename' => $firstFile['filename'] ?? 'untitled',
            'is_public' => $githubGist['public'],
            'is_synced' => true,
            'github_created_at' => Carbon::parse($githubGist['created_at']),
            'github_updated_at' => Carbon::parse($githubGist['updated_at']),
        ];
    }

    /**
     * 格式化本地 Gist 数据用于 GitHub API
     */
    public function formatGistForGitHub(Gist $gist): array
    {
        return [
            'description' => $gist->description ?: $gist->title,
            'public' => $gist->is_public,
            'files' => [
                $gist->filename => [
                    'content' => $gist->content,
                ],
            ],
        ];
    }

    /**
     * 标准化编程语言名称
     */
    protected function normalizeLanguage(?string $language): string
    {
        if (!$language) {
            return 'text';
        }

        $mapping = config('github.gist.language_mapping', []);
        $normalized = strtolower($language);

        return $mapping[$normalized] ?? $language;
    }

    /**
     * 检查是否需要同步（基于更新时间）
     */
    public function shouldSync(Gist $localGist, array $githubGist): bool
    {
        if (!$localGist->github_updated_at) {
            return true; // 如果本地没有 GitHub 更新时间，需要同步
        }

        $githubUpdatedAt = Carbon::parse($githubGist['updated_at']);
        return $githubUpdatedAt->gt($localGist->github_updated_at);
    }

    /**
     * 批量同步用户的 Gists
     */
    public function syncUserGists(User $user, bool $fullSync = false): array
    {
        $stats = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        try {
            $page = 1;
            $perPage = config('github.sync.batch_size', 30);
            $maxPages = config('github.sync.max_pages', 100);
            $lastSyncTime = $fullSync ? null : $user->updated_at;

            while ($page <= $maxPages) {
                $githubGists = $this->getUserGists($user, $page, $perPage);

                if (empty($githubGists)) {
                    break;
                }

                foreach ($githubGists as $githubGist) {
                    $stats['total']++;

                    try {
                        // 检查是否需要同步
                        if (!$fullSync && $lastSyncTime) {
                            $gistUpdatedAt = Carbon::parse($githubGist['updated_at']);
                            if ($gistUpdatedAt->lte($lastSyncTime)) {
                                $stats['skipped']++;
                                continue;
                            }
                        }

                        $result = $this->syncSingleGist($user, $githubGist);
                        $stats[$result]++;

                    } catch (\Exception $e) {
                        $stats['errors']++;
                        Log::error('Failed to sync single gist', [
                            'github_gist_id' => $githubGist['id'],
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                if (count($githubGists) < $perPage) {
                    break; // 最后一页
                }

                $page++;
            }

        } catch (\Exception $e) {
            Log::error('Failed to sync user gists', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        return $stats;
    }

    /**
     * 同步单个 Gist
     */
    protected function syncSingleGist(User $user, array $githubGist): string
    {
        $localGist = Gist::where('github_gist_id', $githubGist['id'])
            ->where('user_id', $user->id)
            ->first();

        $gistData = $this->formatGistForLocal($githubGist);
        $gistData['user_id'] = $user->id;

        if ($localGist) {
            // 检查是否需要更新
            if ($this->shouldSync($localGist, $githubGist)) {
                $localGist->update($gistData);
                return 'updated';
            } else {
                return 'skipped';
            }
        } else {
            Gist::create($gistData);
            return 'created';
        }
    }

    /**
     * 检查 API 速率限制
     */
    public function checkRateLimit(User $user): bool
    {
        if (!config('github.features.rate_limit_check', true)) {
            return true;
        }

        try {
            $rateLimit = $this->getRateLimit($user);
            $remaining = $rateLimit['rate']['remaining'] ?? 0;
            $limit = $rateLimit['rate']['limit'] ?? 5000;

            // 如果剩余请求数少于 10%，返回 false
            return $remaining > ($limit * 0.1);

        } catch (\Exception $e) {
            Log::warning('Failed to check rate limit', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return true; // 如果无法检查，假设可以继续
        }
    }
}
