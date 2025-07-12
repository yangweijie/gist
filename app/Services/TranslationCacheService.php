<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class TranslationCacheService
{
    private const CACHE_PREFIX = 'translations:';
    private const CACHE_TTL = 3600; // 1 hour
    private const METADATA_KEY = 'translations:metadata';

    /**
     * 获取翻译（带缓存）
     */
    public function getTranslation(string $locale, string $group, ?string $key = null): mixed
    {
        $cacheKey = $this->getCacheKey($locale, $group);
        
        // 尝试从缓存获取
        $translations = Cache::get($cacheKey);
        
        if ($translations === null) {
            // 缓存未命中，从文件加载
            $translations = $this->loadTranslationFromFile($locale, $group);
            
            if ($translations !== null) {
                // 存入缓存
                $this->cacheTranslation($locale, $group, $translations);
            }
        }
        
        // 返回特定键或整个组
        if ($key !== null && is_array($translations)) {
            return data_get($translations, $key);
        }
        
        return $translations;
    }

    /**
     * 预加载常用翻译
     */
    public function preloadCommonTranslations(string $locale): void
    {
        $commonGroups = ['common', 'auth', 'validation'];
        
        foreach ($commonGroups as $group) {
            $this->getTranslation($locale, $group);
        }
        
        Log::info("Preloaded common translations for locale: {$locale}");
    }

    /**
     * 批量加载翻译
     */
    public function batchLoadTranslations(string $locale, array $groups): array
    {
        $results = [];
        $missingGroups = [];
        
        // 检查缓存
        foreach ($groups as $group) {
            $cacheKey = $this->getCacheKey($locale, $group);
            $cached = Cache::get($cacheKey);
            
            if ($cached !== null) {
                $results[$group] = $cached;
            } else {
                $missingGroups[] = $group;
            }
        }
        
        // 批量加载缺失的翻译
        if (!empty($missingGroups)) {
            $loaded = $this->batchLoadFromFiles($locale, $missingGroups);
            
            foreach ($loaded as $group => $translations) {
                $results[$group] = $translations;
                $this->cacheTranslation($locale, $group, $translations);
            }
        }
        
        return $results;
    }

    /**
     * 缓存翻译
     */
    private function cacheTranslation(string $locale, string $group, array $translations): void
    {
        $cacheKey = $this->getCacheKey($locale, $group);
        $filePath = $this->getTranslationFilePath($locale, $group);
        
        // 获取文件修改时间作为版本
        $version = File::exists($filePath) ? File::lastModified($filePath) : time();
        
        // 缓存翻译数据
        Cache::put($cacheKey, $translations, self::CACHE_TTL);
        
        // 更新元数据
        $this->updateCacheMetadata($locale, $group, $version);
    }

    /**
     * 从文件加载翻译
     */
    private function loadTranslationFromFile(string $locale, string $group): ?array
    {
        $filePath = $this->getTranslationFilePath($locale, $group);
        
        if (!File::exists($filePath)) {
            return null;
        }
        
        try {
            $translations = include $filePath;
            return is_array($translations) ? $translations : null;
        } catch (\Exception $e) {
            Log::error("Failed to load translation file: {$filePath}", [
                'error' => $e->getMessage(),
                'locale' => $locale,
                'group' => $group,
            ]);
            return null;
        }
    }

    /**
     * 批量从文件加载翻译
     */
    private function batchLoadFromFiles(string $locale, array $groups): array
    {
        $results = [];
        
        foreach ($groups as $group) {
            $translations = $this->loadTranslationFromFile($locale, $group);
            if ($translations !== null) {
                $results[$group] = $translations;
            }
        }
        
        return $results;
    }

    /**
     * 清除翻译缓存
     */
    public function clearCache(?string $locale = null, ?string $group = null): void
    {
        if ($locale && $group) {
            // 清除特定翻译
            $cacheKey = $this->getCacheKey($locale, $group);
            Cache::forget($cacheKey);
        } elseif ($locale) {
            // 清除特定语言的所有翻译
            $this->clearLocaleCache($locale);
        } else {
            // 清除所有翻译缓存
            $this->clearAllCache();
        }
        
        Log::info('Translation cache cleared', [
            'locale' => $locale,
            'group' => $group,
        ]);
    }

    /**
     * 清除特定语言的缓存
     */
    private function clearLocaleCache(string $locale): void
    {
        $metadata = Cache::get(self::METADATA_KEY, []);
        
        foreach ($metadata as $key => $data) {
            if (str_starts_with($key, self::CACHE_PREFIX . $locale . ':')) {
                Cache::forget($key);
                unset($metadata[$key]);
            }
        }
        
        Cache::put(self::METADATA_KEY, $metadata, self::CACHE_TTL);
    }

    /**
     * 清除所有翻译缓存
     */
    private function clearAllCache(): void
    {
        $metadata = Cache::get(self::METADATA_KEY, []);
        
        foreach (array_keys($metadata) as $key) {
            Cache::forget($key);
        }
        
        Cache::forget(self::METADATA_KEY);
    }

    /**
     * 检查缓存是否过期
     */
    public function isCacheStale(string $locale, string $group): bool
    {
        $filePath = $this->getTranslationFilePath($locale, $group);
        
        if (!File::exists($filePath)) {
            return true;
        }
        
        $fileModified = File::lastModified($filePath);
        $metadata = Cache::get(self::METADATA_KEY, []);
        $cacheKey = $this->getCacheKey($locale, $group);
        
        $cachedVersion = $metadata[$cacheKey]['version'] ?? 0;
        
        return $fileModified > $cachedVersion;
    }

    /**
     * 刷新过期的缓存
     */
    public function refreshStaleCache(): int
    {
        $refreshed = 0;
        $metadata = Cache::get(self::METADATA_KEY, []);
        
        foreach ($metadata as $cacheKey => $data) {
            if (str_starts_with($cacheKey, self::CACHE_PREFIX)) {
                $parts = explode(':', str_replace(self::CACHE_PREFIX, '', $cacheKey));
                if (count($parts) >= 2) {
                    $locale = $parts[0];
                    $group = $parts[1];
                    
                    if ($this->isCacheStale($locale, $group)) {
                        $this->clearCache($locale, $group);
                        $this->getTranslation($locale, $group); // 重新加载
                        $refreshed++;
                    }
                }
            }
        }
        
        Log::info("Refreshed {$refreshed} stale translation caches");
        return $refreshed;
    }

    /**
     * 获取缓存统计信息
     */
    public function getCacheStats(): array
    {
        $metadata = Cache::get(self::METADATA_KEY, []);
        $stats = [
            'total_cached' => 0,
            'by_locale' => [],
            'cache_size' => 0,
            'hit_rate' => 0,
        ];
        
        foreach ($metadata as $cacheKey => $data) {
            if (str_starts_with($cacheKey, self::CACHE_PREFIX)) {
                $parts = explode(':', str_replace(self::CACHE_PREFIX, '', $cacheKey));
                if (count($parts) >= 2) {
                    $locale = $parts[0];
                    $stats['total_cached']++;
                    $stats['by_locale'][$locale] = ($stats['by_locale'][$locale] ?? 0) + 1;
                }
            }
        }
        
        return $stats;
    }

    /**
     * 预热缓存
     */
    public function warmupCache(array $locales = null): void
    {
        $locales = $locales ?: array_keys(config('localization.supported_locales', []));
        $groups = ['common', 'auth', 'gist', 'tag', 'php-runner', 'filament'];
        
        foreach ($locales as $locale) {
            foreach ($groups as $group) {
                $this->getTranslation($locale, $group);
            }
        }
        
        Log::info('Translation cache warmed up', [
            'locales' => $locales,
            'groups' => $groups,
        ]);
    }

    /**
     * 获取缓存键
     */
    private function getCacheKey(string $locale, string $group): string
    {
        return self::CACHE_PREFIX . $locale . ':' . $group;
    }

    /**
     * 获取翻译文件路径
     */
    private function getTranslationFilePath(string $locale, string $group): string
    {
        return resource_path("lang/{$locale}/{$group}.php");
    }

    /**
     * 更新缓存元数据
     */
    private function updateCacheMetadata(string $locale, string $group, int $version): void
    {
        $metadata = Cache::get(self::METADATA_KEY, []);
        $cacheKey = $this->getCacheKey($locale, $group);
        
        $metadata[$cacheKey] = [
            'locale' => $locale,
            'group' => $group,
            'version' => $version,
            'cached_at' => time(),
        ];
        
        Cache::put(self::METADATA_KEY, $metadata, self::CACHE_TTL);
    }

    /**
     * 获取内存使用情况
     */
    public function getMemoryUsage(): array
    {
        return [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'formatted' => [
                'current' => $this->formatBytes(memory_get_usage(true)),
                'peak' => $this->formatBytes(memory_get_peak_usage(true)),
            ],
        ];
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
