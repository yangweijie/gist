<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WarmupTranslationCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translation:warmup
                            {--locales= : Comma-separated list of locales to warmup}
                            {--groups= : Comma-separated list of groups to warmup}
                            {--force : Force warmup even if cache exists}
                            {--stats : Show detailed statistics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warmup translation cache for better performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔥 Warming up translation cache...');
        $this->newLine();

        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        // 获取服务实例
        $cacheService = app(\App\Services\TranslationCacheService::class);

        // 解析参数
        $locales = $this->parseLocales();
        $groups = $this->parseGroups();
        $force = $this->option('force');
        $showStats = $this->option('stats');

        $this->line("📋 Target locales: " . implode(', ', $locales));
        $this->line("📋 Target groups: " . implode(', ', $groups));
        $this->newLine();

        // 清除现有缓存（如果强制）
        if ($force) {
            $this->warn('🗑️  Clearing existing cache...');
            $cacheService->clearCache();
        }

        // 预热缓存
        $results = $this->warmupCache($cacheService, $locales, $groups);

        // 计算性能指标
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);

        $executionTime = ($endTime - $startTime) * 1000; // 毫秒
        $memoryUsed = $endMemory - $startMemory;

        // 显示结果
        $this->displayResults($results, $executionTime, $memoryUsed, $showStats);

        return 0;
    }

    /**
     * 解析语言参数
     */
    private function parseLocales(): array
    {
        $localesOption = $this->option('locales');

        if ($localesOption) {
            return array_map('trim', explode(',', $localesOption));
        }

        // 使用配置中的语言
        $supportedLocales = config('localization.supported_locales', []);
        return array_keys(array_filter($supportedLocales, fn($config) => $config['enabled'] ?? false));
    }

    /**
     * 解析组参数
     */
    private function parseGroups(): array
    {
        $groupsOption = $this->option('groups');

        if ($groupsOption) {
            return array_map('trim', explode(',', $groupsOption));
        }

        // 使用配置中的组
        return config('localization.performance.warmup.groups', [
            'common', 'auth', 'gist', 'tag', 'php-runner', 'filament'
        ]);
    }

    /**
     * 预热缓存
     */
    private function warmupCache($cacheService, array $locales, array $groups): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'details' => [],
        ];

        $totalTasks = count($locales) * count($groups);
        $currentTask = 0;

        foreach ($locales as $locale) {
            $this->line("🌍 Processing locale: <info>{$locale}</info>");

            foreach ($groups as $group) {
                $currentTask++;
                $this->line("  📄 Loading group: {$group} ({$currentTask}/{$totalTasks})");

                try {
                    $translation = $cacheService->getTranslation($locale, $group);

                    if ($translation !== null) {
                        $results['success']++;
                        $results['details'][] = [
                            'locale' => $locale,
                            'group' => $group,
                            'status' => 'success',
                            'keys' => is_array($translation) ? count($this->flattenArray($translation)) : 0,
                        ];
                        $this->line("    ✅ Loaded {$group} for {$locale}");
                    } else {
                        $results['skipped']++;
                        $results['details'][] = [
                            'locale' => $locale,
                            'group' => $group,
                            'status' => 'skipped',
                            'reason' => 'File not found',
                        ];
                        $this->line("    ⚠️  Skipped {$group} for {$locale} (file not found)");
                    }
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['details'][] = [
                        'locale' => $locale,
                        'group' => $group,
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                    ];
                    $this->line("    ❌ Failed {$group} for {$locale}: {$e->getMessage()}");
                }
            }
        }

        return $results;
    }

    /**
     * 显示结果
     */
    private function displayResults(array $results, float $executionTime, int $memoryUsed, bool $showStats): void
    {
        $this->newLine();
        $this->info('📊 Warmup Results:');

        $this->table(
            ['Metric', 'Count'],
            [
                ['Successful', $results['success']],
                ['Failed', $results['failed']],
                ['Skipped', $results['skipped']],
                ['Total', $results['success'] + $results['failed'] + $results['skipped']],
            ]
        );

        $this->newLine();
        $this->info('⚡ Performance Metrics:');
        $this->line("Execution Time: " . round($executionTime, 2) . " ms");
        $this->line("Memory Used: " . $this->formatBytes($memoryUsed));
        $this->line("Peak Memory: " . $this->formatBytes(memory_get_peak_usage(true)));

        if ($showStats) {
            $this->displayDetailedStats($results);
        }

        if ($results['success'] > 0) {
            $this->newLine();
            $this->info("✅ Cache warmup completed successfully!");
            $this->line("🚀 Translation performance should be improved.");
        }

        if ($results['failed'] > 0) {
            $this->newLine();
            $this->warn("⚠️  Some translations failed to load. Check the logs for details.");
        }
    }

    /**
     * 显示详细统计
     */
    private function displayDetailedStats(array $results): void
    {
        $this->newLine();
        $this->info('📈 Detailed Statistics:');

        $byLocale = [];
        $byGroup = [];
        $totalKeys = 0;

        foreach ($results['details'] as $detail) {
            $locale = $detail['locale'];
            $group = $detail['group'];
            $status = $detail['status'];

            // 按语言统计
            if (!isset($byLocale[$locale])) {
                $byLocale[$locale] = ['success' => 0, 'failed' => 0, 'skipped' => 0, 'keys' => 0];
            }
            $byLocale[$locale][$status]++;

            if (isset($detail['keys'])) {
                $byLocale[$locale]['keys'] += $detail['keys'];
                $totalKeys += $detail['keys'];
            }

            // 按组统计
            if (!isset($byGroup[$group])) {
                $byGroup[$group] = ['success' => 0, 'failed' => 0, 'skipped' => 0];
            }
            $byGroup[$group][$status]++;
        }

        // 显示按语言统计
        $this->line("\n📍 By Locale:");
        $localeData = [];
        foreach ($byLocale as $locale => $stats) {
            $localeData[] = [
                $locale,
                $stats['success'],
                $stats['failed'],
                $stats['skipped'],
                $stats['keys'],
            ];
        }

        $this->table(
            ['Locale', 'Success', 'Failed', 'Skipped', 'Keys'],
            $localeData
        );

        // 显示按组统计
        $this->line("\n📁 By Group:");
        $groupData = [];
        foreach ($byGroup as $group => $stats) {
            $groupData[] = [
                $group,
                $stats['success'],
                $stats['failed'],
                $stats['skipped'],
            ];
        }

        $this->table(
            ['Group', 'Success', 'Failed', 'Skipped'],
            $groupData
        );

        $this->line("\n🔢 Total Translation Keys: {$totalKeys}");
    }

    /**
     * 扁平化数组
     */
    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**
     * 格式化字节
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
