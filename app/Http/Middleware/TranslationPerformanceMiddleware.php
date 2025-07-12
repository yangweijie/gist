<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TranslationPerformanceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 开始性能监控
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        // 获取服务实例
        $cacheService = app(\App\Services\TranslationCacheService::class);
        $lazyLoader = app(\App\Services\TranslationLazyLoader::class);

        // 预加载关键翻译（如果启用）
        if (config('localization.performance.preload_critical', true)) {
            $lazyLoader->preloadCritical();
        }

        // 智能预测和加载
        if (config('localization.performance.smart_prediction', true)) {
            $routeName = $request->route()?->getName() ?? 'unknown';
            $lazyLoader->predictAndLoad($routeName);
        }

        // 处理请求
        $response = $next($request);

        // 计算性能指标
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);

        $metrics = [
            'execution_time' => ($endTime - $startTime) * 1000, // 毫秒
            'memory_used' => $endMemory - $startMemory,
            'memory_peak' => memory_get_peak_usage(true),
            'locale' => app()->getLocale(),
            'route' => $request->route()?->getName(),
            'url' => $request->url(),
            'method' => $request->method(),
        ];

        // 记录性能数据
        $this->recordPerformanceMetrics($metrics, $cacheService, $lazyLoader);

        // 添加性能头部（开发环境）
        if (app()->environment('local', 'development')) {
            $response->headers->set('X-Translation-Time', round($metrics['execution_time'], 2) . 'ms');
            $response->headers->set('X-Translation-Memory', $this->formatBytes($metrics['memory_used']));
            $response->headers->set('X-Translation-Locale', $metrics['locale']);
        }

        return $response;
    }

    /**
     * 记录性能指标
     */
    private function recordPerformanceMetrics(array $metrics, $cacheService, $lazyLoader): void
    {
        // 获取详细统计
        $cacheStats = $cacheService->getCacheStats();
        $loadStats = $lazyLoader->getLoadStats();

        $fullMetrics = array_merge($metrics, [
            'cache_stats' => $cacheStats,
            'load_stats' => $loadStats,
            'timestamp' => now()->toISOString(),
        ]);

        // 记录到日志（如果启用）
        if (config('localization.performance.log_metrics', false)) {
            Log::info('Translation Performance Metrics', $fullMetrics);
        }

        // 存储到缓存（用于监控面板）
        if (config('localization.performance.store_metrics', true)) {
            $this->storeMetricsForMonitoring($fullMetrics);
        }

        // 检查性能阈值
        $this->checkPerformanceThresholds($metrics);
    }

    /**
     * 存储指标用于监控
     */
    private function storeMetricsForMonitoring(array $metrics): void
    {
        $key = 'translation_metrics:' . date('Y-m-d-H');
        $existing = Cache::get($key, []);

        $existing[] = $metrics;

        // 只保留最近100条记录
        if (count($existing) > 100) {
            $existing = array_slice($existing, -100);
        }

        Cache::put($key, $existing, 3600); // 1小时
    }

    /**
     * 检查性能阈值
     */
    private function checkPerformanceThresholds(array $metrics): void
    {
        $thresholds = config('localization.performance.thresholds', [
            'execution_time' => 100, // 100ms
            'memory_used' => 5 * 1024 * 1024, // 5MB
        ]);

        $warnings = [];

        if ($metrics['execution_time'] > $thresholds['execution_time']) {
            $warnings[] = "Translation execution time exceeded threshold: {$metrics['execution_time']}ms > {$thresholds['execution_time']}ms";
        }

        if ($metrics['memory_used'] > $thresholds['memory_used']) {
            $warnings[] = "Translation memory usage exceeded threshold: " .
                         $this->formatBytes($metrics['memory_used']) . " > " .
                         $this->formatBytes($thresholds['memory_used']);
        }

        if (!empty($warnings)) {
            Log::warning('Translation Performance Warning', [
                'warnings' => $warnings,
                'metrics' => $metrics,
                'url' => request()->url(),
            ]);
        }
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
