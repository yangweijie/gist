<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeoLocationService
{
    /**
     * 根据 IP 地址获取国家代码
     */
    public function getCountryByIp(string $ip): ?string
    {
        // 跳过本地 IP
        if ($this->isLocalIp($ip)) {
            return null;
        }

        // 检查缓存
        $cacheKey = "geo_location:{$ip}";
        $cachedResult = Cache::get($cacheKey);
        
        if ($cachedResult !== null) {
            return $cachedResult ?: null;
        }

        try {
            // 使用免费的 IP 地理位置 API
            $response = Http::timeout(5)->get("http://ip-api.com/json/{$ip}", [
                'fields' => 'status,countryCode',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'success') {
                    $countryCode = strtolower($data['countryCode'] ?? '');
                    
                    // 缓存结果 24 小时
                    Cache::put($cacheKey, $countryCode, now()->addHours(24));
                    
                    return $countryCode;
                }
            }
        } catch (\Exception $e) {
            Log::warning('GeoLocation API failed', [
                'ip' => $ip,
                'error' => $e->getMessage(),
            ]);
        }

        // 缓存失败结果（避免重复请求）
        Cache::put($cacheKey, '', now()->addHours(1));
        
        return null;
    }

    /**
     * 根据国家代码推断语言
     */
    public function getLocaleByCountry(string $countryCode): ?string
    {
        $countryToLocale = [
            'cn' => 'zh',
            'tw' => 'zh',
            'hk' => 'zh',
            'mo' => 'zh',
            'sg' => 'zh', // 新加坡也有很多中文用户
            'us' => 'en',
            'gb' => 'en',
            'ca' => 'en',
            'au' => 'en',
            'nz' => 'en',
            'ie' => 'en',
            'za' => 'en',
            // 可以根据需要添加更多国家
        ];

        return $countryToLocale[strtolower($countryCode)] ?? null;
    }

    /**
     * 根据 IP 地址推断语言
     */
    public function getLocaleByIp(string $ip): ?string
    {
        $countryCode = $this->getCountryByIp($ip);
        
        if ($countryCode) {
            return $this->getLocaleByCountry($countryCode);
        }
        
        return null;
    }

    /**
     * 检查是否为本地 IP
     */
    private function isLocalIp(string $ip): bool
    {
        // 检查是否为本地或私有 IP
        return !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    /**
     * 获取客户端真实 IP
     */
    public function getRealIp(): string
    {
        // 检查各种可能的 IP 头
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_FORWARDED_FOR',      // 代理
            'HTTP_X_FORWARDED',          // 代理
            'HTTP_X_CLUSTER_CLIENT_IP',  // 集群
            'HTTP_FORWARDED_FOR',        // 代理
            'HTTP_FORWARDED',            // 代理
            'HTTP_CLIENT_IP',            // 代理
            'REMOTE_ADDR',               // 标准
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return request()->ip() ?? '127.0.0.1';
    }

    /**
     * 智能语言检测（结合多种方式）
     */
    public function detectLocale(array $supportedLocales = ['zh', 'en']): ?string
    {
        // 1. 尝试通过 IP 地址检测
        $ip = $this->getRealIp();
        $ipLocale = $this->getLocaleByIp($ip);
        
        if ($ipLocale && in_array($ipLocale, $supportedLocales)) {
            return $ipLocale;
        }

        // 2. 可以在这里添加其他检测方式
        // 例如：时区检测、用户代理检测等

        return null;
    }

    /**
     * 批量检测多个 IP 的地理位置
     */
    public function batchDetectCountries(array $ips): array
    {
        $results = [];
        
        foreach ($ips as $ip) {
            $results[$ip] = $this->getCountryByIp($ip);
        }
        
        return $results;
    }

    /**
     * 清除地理位置缓存
     */
    public function clearCache(?string $ip = null): void
    {
        if ($ip) {
            Cache::forget("geo_location:{$ip}");
        } else {
            // 清除所有地理位置缓存（需要根据缓存驱动实现）
            // 这里简化处理
            Cache::flush();
        }
    }

    /**
     * 获取支持的国家列表
     */
    public function getSupportedCountries(): array
    {
        return [
            'cn' => ['name' => 'China', 'locale' => 'zh'],
            'tw' => ['name' => 'Taiwan', 'locale' => 'zh'],
            'hk' => ['name' => 'Hong Kong', 'locale' => 'zh'],
            'mo' => ['name' => 'Macau', 'locale' => 'zh'],
            'sg' => ['name' => 'Singapore', 'locale' => 'zh'],
            'us' => ['name' => 'United States', 'locale' => 'en'],
            'gb' => ['name' => 'United Kingdom', 'locale' => 'en'],
            'ca' => ['name' => 'Canada', 'locale' => 'en'],
            'au' => ['name' => 'Australia', 'locale' => 'en'],
            'nz' => ['name' => 'New Zealand', 'locale' => 'en'],
            'ie' => ['name' => 'Ireland', 'locale' => 'en'],
            'za' => ['name' => 'South Africa', 'locale' => 'en'],
        ];
    }
}
