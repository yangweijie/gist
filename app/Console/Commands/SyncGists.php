<?php

namespace App\Console\Commands;

use App\Jobs\SyncGistJob;
use App\Models\User;
use Illuminate\Console\Command;

class SyncGists extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gist:sync
                            {--user= : Sync gists for specific user ID}
                            {--full : Perform full sync (ignore last sync time)}
                            {--queue : Run sync jobs in queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync GitHub Gists for users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user');
        $fullSync = $this->option('full');
        $useQueue = $this->option('queue');

        if ($userId) {
            // 同步特定用户
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }

            if (!$user->github_token) {
                $this->error("User {$user->name} does not have GitHub token.");
                return 1;
            }

            $this->syncUser($user, $fullSync, $useQueue);
        } else {
            // 同步所有有 GitHub token 的用户
            $users = User::whereNotNull('github_token')->get();

            if ($users->isEmpty()) {
                $this->info('No users with GitHub tokens found.');
                return 0;
            }

            $this->info("Found {$users->count()} users with GitHub tokens.");

            $bar = $this->output->createProgressBar($users->count());
            $bar->start();

            foreach ($users as $user) {
                $this->syncUser($user, $fullSync, $useQueue);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        $this->info('Gist sync process completed.');
        return 0;
    }

    /**
     * 同步单个用户的 Gists
     */
    protected function syncUser(User $user, bool $fullSync, bool $useQueue): void
    {
        try {
            if ($useQueue) {
                // 使用队列异步处理
                SyncGistJob::dispatch($user, $fullSync);
                $this->line("Queued sync job for user: {$user->name}");
            } else {
                // 同步处理
                $job = new SyncGistJob($user, $fullSync);
                $job->handle(app(\App\Services\GitHubService::class));
                $this->line("Synced gists for user: {$user->name}");
            }
        } catch (\Exception $e) {
            $this->error("Failed to sync gists for user {$user->name}: {$e->getMessage()}");
        }
    }
}
