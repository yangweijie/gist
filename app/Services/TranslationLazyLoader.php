<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class TranslationLazyLoader
{
    private TranslationCacheService $cacheService;
    private array $loadedGroups = [];
    private array $loadingQueue = [];
    private bool $autoLoadEnabled = true;

    public function __construct(TranslationCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * 懒加载翻译
     */
    public function lazyLoad(string $key, array $replace = [], string $locale = null): string
    {
        $locale = $locale ?: App::getLocale();
        
        // 解析键名
        [$group, $item] = $this->parseKey($key);
        
        // 检查是否已加载
        if (!$this->isGroupLoaded($locale, $group)) {
            $this->loadGroup($locale, $group);
        }
        
        // 获取翻译
        $translation = $this->cacheService->getTranslation($locale, $group, $item);
        
        if ($translation === null) {
            // 翻译不存在，记录并返回键名
            $this->logMissingTranslation($locale, $key);
            return $key;
        }
        
        // 处理参数替换
        return $this->makeReplacements($translation, $replace);
    }

    /**
     * 批量懒加载
     */
    public function batchLazyLoad(array $keys, string $locale = null): array
    {
        $locale = $locale ?: App::getLocale();
        $results = [];
        $groupsToLoad = [];
        
        // 分析需要加载的组
        foreach ($keys as $key) {
            [$group, $item] = $this->parseKey($key);
            
            if (!$this->isGroupLoaded($locale, $group)) {
                $groupsToLoad[] = $group;
            }
        }
        
        // 批量加载组
        if (!empty($groupsToLoad)) {
            $this->batchLoadGroups($locale, array_unique($groupsToLoad));
        }
        
        // 获取所有翻译
        foreach ($keys as $key) {
            [$group, $item] = $this->parseKey($key);
            $translation = $this->cacheService->getTranslation($locale, $group, $item);
            $results[$key] = $translation ?: $key;
        }
        
        return $results;
    }

    /**
     * 预加载关键翻译组
     */
    public function preloadCritical(string $locale = null): void
    {
        $locale = $locale ?: App::getLocale();
        $criticalGroups = $this->getCriticalGroups();
        
        $this->batchLoadGroups($locale, $criticalGroups);
        
        Log::info("Preloaded critical translation groups for locale: {$locale}", [
            'groups' => $criticalGroups,
        ]);
    }

    /**
     * 按需加载翻译组
     */
    public function loadOnDemand(string $group, string $locale = null): bool
    {
        $locale = $locale ?: App::getLocale();
        
        if ($this->isGroupLoaded($locale, $group)) {
            return true;
        }
        
        return $this->loadGroup($locale, $group);
    }

    /**
     * 加载翻译组
     */
    private function loadGroup(string $locale, string $group): bool
    {
        $groupKey = "{$locale}:{$group}";
        
        // 避免重复加载
        if (in_array($groupKey, $this->loadingQueue)) {
            return false;
        }
        
        $this->loadingQueue[] = $groupKey;
        
        try {
            $translations = $this->cacheService->getTranslation($locale, $group);
            
            if ($translations !== null) {
                $this->markGroupLoaded($locale, $group);
                $this->removeFromQueue($groupKey);
                return true;
            }
        } catch (\Exception $e) {
            Log::error("Failed to load translation group: {$group}", [
                'locale' => $locale,
                'error' => $e->getMessage(),
            ]);
        }
        
        $this->removeFromQueue($groupKey);
        return false;
    }

    /**
     * 批量加载翻译组
     */
    private function batchLoadGroups(string $locale, array $groups): void
    {
        $unloadedGroups = array_filter($groups, function ($group) use ($locale) {
            return !$this->isGroupLoaded($locale, $group);
        });
        
        if (empty($unloadedGroups)) {
            return;
        }
        
        try {
            $this->cacheService->batchLoadTranslations($locale, $unloadedGroups);
            
            foreach ($unloadedGroups as $group) {
                $this->markGroupLoaded($locale, $group);
            }
            
            Log::debug("Batch loaded translation groups", [
                'locale' => $locale,
                'groups' => $unloadedGroups,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to batch load translation groups", [
                'locale' => $locale,
                'groups' => $unloadedGroups,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 智能预测需要的翻译
     */
    public function predictAndLoad(string $currentRoute, string $locale = null): void
    {
        $locale = $locale ?: App::getLocale();
        $predictedGroups = $this->predictRequiredGroups($currentRoute);
        
        if (!empty($predictedGroups)) {
            $this->batchLoadGroups($locale, $predictedGroups);
            
            Log::debug("Predicted and loaded translation groups", [
                'route' => $currentRoute,
                'locale' => $locale,
                'groups' => $predictedGroups,
            ]);
        }
    }

    /**
     * 预测所需的翻译组
     */
    private function predictRequiredGroups(string $route): array
    {
        $routeGroupMap = [
            'auth.*' => ['auth', 'common'],
            'gists.*' => ['gist', 'common', 'tag'],
            'tags.*' => ['tag', 'common'],
            'php-runner.*' => ['php-runner', 'common'],
            'admin.*' => ['filament', 'common'],
            'dashboard' => ['common', 'gist'],
        ];
        
        foreach ($routeGroupMap as $pattern => $groups) {
            if (fnmatch($pattern, $route)) {
                return $groups;
            }
        }
        
        return ['common']; // 默认加载通用翻译
    }

    /**
     * 获取关键翻译组
     */
    private function getCriticalGroups(): array
    {
        return ['common', 'auth'];
    }

    /**
     * 解析翻译键
     */
    private function parseKey(string $key): array
    {
        $segments = explode('.', $key, 2);
        
        if (count($segments) === 2) {
            return [$segments[0], $segments[1]];
        }
        
        return ['common', $key];
    }

    /**
     * 检查组是否已加载
     */
    private function isGroupLoaded(string $locale, string $group): bool
    {
        return isset($this->loadedGroups["{$locale}:{$group}"]);
    }

    /**
     * 标记组为已加载
     */
    private function markGroupLoaded(string $locale, string $group): void
    {
        $this->loadedGroups["{$locale}:{$group}"] = true;
    }

    /**
     * 从加载队列中移除
     */
    private function removeFromQueue(string $groupKey): void
    {
        $index = array_search($groupKey, $this->loadingQueue);
        if ($index !== false) {
            unset($this->loadingQueue[$index]);
        }
    }

    /**
     * 处理参数替换
     */
    private function makeReplacements(string $line, array $replace): string
    {
        if (empty($replace)) {
            return $line;
        }
        
        $replace = $this->sortReplacements($replace);
        
        foreach ($replace as $key => $value) {
            $line = str_replace(
                [':' . $key, ':' . strtoupper($key), ':' . ucfirst($key)],
                [$value, strtoupper($value), ucfirst($value)],
                $line
            );
        }
        
        return $line;
    }

    /**
     * 排序替换参数
     */
    private function sortReplacements(array $replace): array
    {
        return collect($replace)->sortBy(function ($value, $key) {
            return mb_strlen($key) * -1;
        })->all();
    }

    /**
     * 记录缺失的翻译
     */
    private function logMissingTranslation(string $locale, string $key): void
    {
        Log::warning("Missing translation", [
            'locale' => $locale,
            'key' => $key,
            'url' => request()->url(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * 获取加载统计
     */
    public function getLoadStats(): array
    {
        return [
            'loaded_groups' => count($this->loadedGroups),
            'loading_queue' => count($this->loadingQueue),
            'auto_load_enabled' => $this->autoLoadEnabled,
            'loaded_groups_detail' => array_keys($this->loadedGroups),
            'memory_usage' => $this->cacheService->getMemoryUsage(),
        ];
    }

    /**
     * 清理加载状态
     */
    public function cleanup(): void
    {
        $this->loadedGroups = [];
        $this->loadingQueue = [];
        
        Log::debug("Translation lazy loader cleaned up");
    }

    /**
     * 启用/禁用自动加载
     */
    public function setAutoLoad(bool $enabled): void
    {
        $this->autoLoadEnabled = $enabled;
    }

    /**
     * 获取性能指标
     */
    public function getPerformanceMetrics(): array
    {
        return [
            'cache_stats' => $this->cacheService->getCacheStats(),
            'load_stats' => $this->getLoadStats(),
            'memory_usage' => $this->cacheService->getMemoryUsage(),
        ];
    }
}
