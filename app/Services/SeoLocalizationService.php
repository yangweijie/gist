<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;

class SeoLocalizationService
{
    /**
     * 生成 hreflang 标签
     */
    public function generateHreflangTags(?string $url = null): array
    {
        if (!config('localization.seo.generate_hreflang', true)) {
            return [];
        }

        $url = $url ?: request()->url();
        $supportedLocales = config('localization.supported_locales', []);

        // 确保 $supportedLocales 是数组
        if (!is_array($supportedLocales)) {
            $supportedLocales = [
                'zh' => ['enabled' => true, 'name' => 'Chinese'],
                'en' => ['enabled' => true, 'name' => 'English'],
            ];
        }

        $tags = [];

        foreach ($supportedLocales as $locale => $config) {
            if (!($config['enabled'] ?? true)) {
                continue;
            }

            $localizedUrl = $this->getLocalizedUrl($url, $locale);
            
            $tags[] = [
                'hreflang' => $locale,
                'href' => $localizedUrl,
                'locale' => $locale,
                'name' => $config['name'] ?? $locale,
                'native' => $config['native'] ?? $locale,
            ];
        }

        // 添加 x-default 标签
        $defaultLocale = config('localization.default_locale', 'zh');
        $tags[] = [
            'hreflang' => 'x-default',
            'href' => $this->getLocalizedUrl($url, $defaultLocale),
            'locale' => $defaultLocale,
            'name' => 'Default',
            'native' => 'Default',
        ];

        return $tags;
    }

    /**
     * 获取本地化的 URL
     */
    public function getLocalizedUrl(string $url, string $locale): string
    {
        $currentLocale = App::getLocale();
        
        // 如果是当前语言，直接返回
        if ($locale === $currentLocale) {
            return $url;
        }

        // 解析 URL
        $parsedUrl = parse_url($url);
        $query = $parsedUrl['query'] ?? '';
        
        // 移除现有的 lang 参数
        parse_str($query, $queryParams);
        unset($queryParams['lang']);
        
        // 添加新的语言参数
        $queryParams['lang'] = $locale;
        
        // 重建 URL
        $newQuery = http_build_query($queryParams);
        $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
        
        if (isset($parsedUrl['port'])) {
            $baseUrl .= ':' . $parsedUrl['port'];
        }
        
        $baseUrl .= $parsedUrl['path'] ?? '/';
        
        if ($newQuery) {
            $baseUrl .= '?' . $newQuery;
        }
        
        return $baseUrl;
    }

    /**
     * 生成多语言 sitemap 数据
     */
    public function generateSitemapData(): array
    {
        if (!config('localization.seo.include_in_sitemap', true)) {
            return [];
        }

        $supportedLocales = config('localization.supported_locales', []);
        $routes = $this->getLocalizableRoutes();
        $sitemapData = [];

        foreach ($routes as $route) {
            $routeData = [
                'url' => $route['url'],
                'lastmod' => $route['lastmod'] ?? now()->toISOString(),
                'changefreq' => $route['changefreq'] ?? 'weekly',
                'priority' => $route['priority'] ?? '0.8',
                'alternates' => [],
            ];

            // 为每个语言生成备用 URL
            foreach ($supportedLocales as $locale => $config) {
                if (!($config['enabled'] ?? true)) {
                    continue;
                }

                $routeData['alternates'][] = [
                    'hreflang' => $locale,
                    'href' => $this->getLocalizedUrl($route['url'], $locale),
                ];
            }

            $sitemapData[] = $routeData;
        }

        return $sitemapData;
    }

    /**
     * 获取可本地化的路由
     */
    private function getLocalizableRoutes(): array
    {
        $routes = [];
        $routeCollection = Route::getRoutes();

        foreach ($routeCollection as $route) {
            $name = $route->getName();
            
            // 跳过 API 路由和管理路由
            if (!$name || str_starts_with($name, 'api.') || str_starts_with($name, 'admin.')) {
                continue;
            }

            // 跳过需要参数的路由（暂时）
            if (count($route->parameterNames()) > 0) {
                continue;
            }

            try {
                $url = route($name);
                $routes[] = [
                    'name' => $name,
                    'url' => $url,
                    'methods' => $route->methods(),
                    'priority' => $this->getRoutePriority($name),
                    'changefreq' => $this->getRouteChangeFreq($name),
                ];
            } catch (\Exception $e) {
                // 跳过无法生成 URL 的路由
                continue;
            }
        }

        return $routes;
    }

    /**
     * 获取路由优先级
     */
    private function getRoutePriority(string $routeName): string
    {
        $priorities = [
            'welcome' => '1.0',
            'gists.index' => '0.9',
            'tags.index' => '0.8',
            'php-runner.index' => '0.8',
            'login' => '0.7',
            'register' => '0.7',
        ];

        return $priorities[$routeName] ?? '0.5';
    }

    /**
     * 获取路由更新频率
     */
    private function getRouteChangeFreq(string $routeName): string
    {
        $frequencies = [
            'welcome' => 'daily',
            'gists.index' => 'daily',
            'tags.index' => 'weekly',
            'php-runner.index' => 'monthly',
            'login' => 'monthly',
            'register' => 'monthly',
        ];

        return $frequencies[$routeName] ?? 'weekly';
    }

    /**
     * 生成 Open Graph 多语言标签
     */
    public function generateOpenGraphTags(array $data = []): array
    {
        $currentLocale = App::getLocale();
        $localeInfo = config("localization.supported_locales.{$currentLocale}", []);
        
        $tags = [
            'og:locale' => $this->getOpenGraphLocale($currentLocale),
            'og:site_name' => $data['site_name'] ?? config('app.name'),
            'og:title' => $data['title'] ?? config('app.name'),
            'og:description' => $data['description'] ?? __('common.messages.app_description'),
            'og:url' => $data['url'] ?? request()->url(),
            'og:type' => $data['type'] ?? 'website',
        ];

        // 添加备用语言
        $supportedLocales = config('localization.supported_locales', []);

        // 确保 $supportedLocales 是数组
        if (!is_array($supportedLocales)) {
            $supportedLocales = [
                'zh' => ['enabled' => true],
                'en' => ['enabled' => true],
            ];
        }

        foreach ($supportedLocales as $locale => $config) {
            if ($locale !== $currentLocale && ($config['enabled'] ?? true)) {
                $tags['og:locale:alternate'][] = $this->getOpenGraphLocale($locale);
            }
        }

        return $tags;
    }

    /**
     * 转换为 Open Graph 语言代码
     */
    private function getOpenGraphLocale(string $locale): string
    {
        $mapping = [
            'zh' => 'zh_CN',
            'en' => 'en_US',
        ];

        return $mapping[$locale] ?? $locale;
    }

    /**
     * 生成 JSON-LD 结构化数据
     */
    public function generateJsonLd(array $data = []): array
    {
        $currentLocale = App::getLocale();
        
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => $data['type'] ?? 'WebSite',
            'name' => $data['name'] ?? config('app.name'),
            'description' => $data['description'] ?? __('common.messages.app_description'),
            'url' => $data['url'] ?? config('app.url'),
            'inLanguage' => $currentLocale,
        ];

        // 添加多语言版本
        $supportedLocales = config('localization.supported_locales', []);
        if (count($supportedLocales) > 1) {
            $jsonLd['availableLanguage'] = [];
            
            foreach ($supportedLocales as $locale => $config) {
                if ($config['enabled'] ?? true) {
                    $jsonLd['availableLanguage'][] = [
                        '@type' => 'Language',
                        'name' => $config['name'] ?? $locale,
                        'alternateName' => $config['native'] ?? $locale,
                        'identifier' => $locale,
                    ];
                }
            }
        }

        // 添加组织信息
        if (isset($data['organization'])) {
            $jsonLd['publisher'] = [
                '@type' => 'Organization',
                'name' => $data['organization']['name'] ?? config('app.name'),
                'url' => $data['organization']['url'] ?? config('app.url'),
            ];
        }

        return $jsonLd;
    }

    /**
     * 生成语言切换的 canonical URL
     */
    public function getCanonicalUrl(?string $url = null): string
    {
        $url = $url ?: request()->url();
        $defaultLocale = config('localization.default_locale', 'zh');
        
        // 如果当前是默认语言且配置为无前缀，返回无语言参数的 URL
        if (App::getLocale() === $defaultLocale && config('localization.seo.default_no_prefix', true)) {
            return $this->removeLanguageParameter($url);
        }
        
        return $url;
    }

    /**
     * 移除 URL 中的语言参数
     */
    private function removeLanguageParameter(string $url): string
    {
        $parsedUrl = parse_url($url);
        $query = $parsedUrl['query'] ?? '';
        
        parse_str($query, $queryParams);
        unset($queryParams['lang']);
        
        $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
        
        if (isset($parsedUrl['port'])) {
            $baseUrl .= ':' . $parsedUrl['port'];
        }
        
        $baseUrl .= $parsedUrl['path'] ?? '/';
        
        if (!empty($queryParams)) {
            $baseUrl .= '?' . http_build_query($queryParams);
        }
        
        return $baseUrl;
    }
}
