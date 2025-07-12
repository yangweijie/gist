<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class LocalizationService
{
    /**
     * 获取支持的语言列表
     */
    public function getSupportedLocales(): array
    {
        $locales = config('localization.supported_locales');

        // 确保返回值是数组
        if (!is_array($locales)) {
            return [
                'zh' => [
                    'code' => 'zh',
                    'name' => 'Chinese',
                    'native' => '中文',
                    'flag' => '🇨🇳',
                    'enabled' => true,
                ],
                'en' => [
                    'code' => 'en',
                    'name' => 'English',
                    'native' => 'English',
                    'flag' => '🇺🇸',
                    'enabled' => true,
                ],
            ];
        }

        return $locales;
    }

    /**
     * 获取启用的语言列表
     */
    public function getEnabledLocales(): array
    {
        $locales = $this->getSupportedLocales();
        
        return array_filter($locales, function ($locale) {
            return $locale['enabled'] ?? true;
        });
    }

    /**
     * 检查语言是否支持
     */
    public function isLocaleSupported(string $locale): bool
    {
        $supportedLocales = $this->getSupportedLocales();
        
        return isset($supportedLocales[$locale]) && ($supportedLocales[$locale]['enabled'] ?? true);
    }

    /**
     * 获取语言信息
     */
    public function getLocaleInfo(string $locale): ?array
    {
        $supportedLocales = $this->getSupportedLocales();
        
        return $supportedLocales[$locale] ?? null;
    }

    /**
     * 获取当前语言信息
     */
    public function getCurrentLocaleInfo(): array
    {
        $currentLocale = App::getLocale();
        
        return $this->getLocaleInfo($currentLocale) ?? [
            'code' => $currentLocale,
            'name' => $currentLocale,
            'native' => $currentLocale,
            'flag' => '🌐',
            'direction' => 'ltr',
            'enabled' => true,
        ];
    }

    /**
     * 格式化日期
     */
    public function formatDate(Carbon $date, string $format = 'date', ?string $locale = null): string
    {
        $locale = $locale ?? App::getLocale();
        $formats = config("localization.date_formats.{$locale}", []);
        
        if (!isset($formats[$format])) {
            // 回退到默认格式
            $defaultFormats = config('localization.date_formats.en', []);
            $formatString = $defaultFormats[$format] ?? 'Y-m-d';
        } else {
            $formatString = $formats[$format];
        }
        
        return $date->format($formatString);
    }

    /**
     * 格式化数字
     */
    public function formatNumber(float $number, int $decimals = 0, ?string $locale = null): string
    {
        $locale = $locale ?? App::getLocale();
        $formats = config("localization.number_formats.{$locale}", []);
        
        $decimalSeparator = $formats['decimal_separator'] ?? '.';
        $thousandsSeparator = $formats['thousands_separator'] ?? ',';
        
        return number_format($number, $decimals, $decimalSeparator, $thousandsSeparator);
    }

    /**
     * 格式化货币
     */
    public function formatCurrency(float $amount, ?string $locale = null): string
    {
        $locale = $locale ?? App::getLocale();
        $formats = config("localization.number_formats.{$locale}", []);
        
        $symbol = $formats['currency_symbol'] ?? '$';
        $position = $formats['currency_position'] ?? 'before';
        $formattedNumber = $this->formatNumber($amount, 2, $locale);
        
        if ($position === 'before') {
            return $symbol . $formattedNumber;
        } else {
            return $formattedNumber . $symbol;
        }
    }

    /**
     * 获取语言对应的时区
     */
    public function getTimezone(?string $locale = null): string
    {
        $locale = $locale ?? App::getLocale();
        $timezones = config('localization.timezones', []);
        
        return $timezones[$locale] ?? config('app.timezone', 'UTC');
    }

    /**
     * 生成 hreflang 标签
     */
    public function generateHreflangTags(string $currentUrl): array
    {
        if (!config('localization.seo.generate_hreflang', true)) {
            return [];
        }

        $tags = [];
        $enabledLocales = $this->getEnabledLocales();
        
        foreach ($enabledLocales as $locale => $info) {
            $url = $this->getLocalizedUrl($currentUrl, $locale);
            $tags[] = [
                'hreflang' => $locale,
                'href' => $url,
            ];
        }
        
        return $tags;
    }

    /**
     * 获取本地化的 URL
     */
    public function getLocalizedUrl(string $url, string $locale): string
    {
        // 简单实现，实际项目中可能需要更复杂的 URL 处理
        $query = parse_url($url, PHP_URL_QUERY);
        $separator = $query ? '&' : '?';
        
        return $url . $separator . 'lang=' . $locale;
    }

    /**
     * 缓存翻译
     */
    public function cacheTranslation(string $key, string $locale, string $value): void
    {
        if (!config('localization.cache.enabled', true)) {
            return;
        }

        $cacheKey = $this->getCacheKey($key, $locale);
        $ttl = config('localization.cache.ttl', 1440); // 24 hours in minutes
        
        Cache::put($cacheKey, $value, now()->addMinutes($ttl));
    }

    /**
     * 获取缓存的翻译
     */
    public function getCachedTranslation(string $key, string $locale): ?string
    {
        if (!config('localization.cache.enabled', true)) {
            return null;
        }

        $cacheKey = $this->getCacheKey($key, $locale);
        
        return Cache::get($cacheKey);
    }

    /**
     * 清除翻译缓存
     */
    public function clearTranslationCache(?string $locale = null): void
    {
        $prefix = config('localization.cache.prefix', 'locale');
        
        if ($locale) {
            // 清除特定语言的缓存
            $pattern = "{$prefix}:{$locale}:*";
        } else {
            // 清除所有语言的缓存
            $pattern = "{$prefix}:*";
        }
        
        // 注意：这里需要根据实际的缓存驱动实现
        // Redis 可以使用 KEYS 命令，但在生产环境中要谨慎使用
        Cache::flush(); // 简单实现，清除所有缓存
    }

    /**
     * 生成缓存键
     */
    private function getCacheKey(string $key, string $locale): string
    {
        $prefix = config('localization.cache.prefix', 'locale');
        
        return "{$prefix}:{$locale}:" . md5($key);
    }

    /**
     * 获取语言切换器配置
     */
    public function getSwitcherConfig(): array
    {
        return config('localization.switcher', []);
    }

    /**
     * 检查是否应该显示语言切换器
     */
    public function shouldShowSwitcher(string $context = 'frontend'): bool
    {
        $config = $this->getSwitcherConfig();
        $key = "show_{$context}";
        
        return $config[$key] ?? true;
    }
}
