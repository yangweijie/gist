<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Gist;
use App\Services\GitHubService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncGistJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3;

    protected User $user;
    protected bool $fullSync;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, bool $fullSync = false)
    {
        $this->user = $user;
        $this->fullSync = $fullSync;
    }

    /**
     * Execute the job.
     */
    public function handle(GitHubService $githubService): void
    {
        try {
            Log::info('Starting Gist sync', [
                'user_id' => $this->user->id,
                'full_sync' => $this->fullSync,
            ]);

            if (!$this->user->github_token) {
                Log::warning('User has no GitHub token', ['user_id' => $this->user->id]);
                return;
            }

            // 获取用户的 GitHub Gists
            $page = 1;
            $perPage = 30;
            $syncedCount = 0;
            $lastSyncTime = $this->fullSync ? null : $this->user->updated_at;

            do {
                $githubGists = $githubService->getUserGists($this->user, $page, $perPage);

                if (empty($githubGists)) {
                    break;
                }

                foreach ($githubGists as $githubGist) {
                    // 如果不是全量同步，检查更新时间
                    if (!$this->fullSync && $lastSyncTime) {
                        $gistUpdatedAt = Carbon::parse($githubGist['updated_at']);
                        if ($gistUpdatedAt->lte($lastSyncTime)) {
                            continue; // 跳过未更新的 Gist
                        }
                    }

                    $this->syncSingleGist($githubGist, $githubService);
                    $syncedCount++;
                }

                $page++;
            } while (count($githubGists) === $perPage);

            Log::info('Gist sync completed', [
                'user_id' => $this->user->id,
                'synced_count' => $syncedCount,
            ]);

        } catch (\Exception $e) {
            Log::error('Gist sync failed', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * 同步单个 Gist
     */
    protected function syncSingleGist(array $githubGist, GitHubService $githubService): void
    {
        try {
            // 查找本地是否已存在该 Gist
            $localGist = Gist::where('github_gist_id', $githubGist['id'])
                ->where('user_id', $this->user->id)
                ->first();

            // 格式化 GitHub Gist 数据
            $gistData = $githubService->formatGistForLocal($githubGist);
            $gistData['user_id'] = $this->user->id;

            if ($localGist) {
                // 更新现有 Gist
                $localGist->update($gistData);
                Log::debug('Updated local gist', [
                    'gist_id' => $localGist->id,
                    'github_gist_id' => $githubGist['id'],
                ]);
            } else {
                // 创建新 Gist
                $localGist = Gist::create($gistData);
                Log::debug('Created new local gist', [
                    'gist_id' => $localGist->id,
                    'github_gist_id' => $githubGist['id'],
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to sync single gist', [
                'github_gist_id' => $githubGist['id'],
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SyncGistJob failed permanently', [
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
