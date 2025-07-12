<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Services\GeoLocationService;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // 获取支持的语言列表，确保不为 null
            $localesConfig = config('localization.supported_locales');
            if (!is_array($localesConfig)) {
                $localesConfig = ['zh' => [], 'en' => []];
            }
            $supportedLocales = array_keys($localesConfig);

            // 优先级：URL参数 > Session > 用户设置 > Cookie > 浏览器语言 > 默认语言
            $locale = $this->determineLocale($request, $supportedLocales);

            // 设置应用语言
            App::setLocale($locale);

            // 保存到 Session（添加错误处理）
            try {
                Session::put('locale', $locale);
            } catch (\Exception $e) {
                // 忽略 session 写入错误，继续处理请求
                if (app()->environment('local')) {
                    logger()->warning('Failed to save locale to session: ' . $e->getMessage());
                }
            }

            // 保存到 Cookie（为访客用户记住语言选择）
            if (config('localization.detection.remember_guest_locale', true)) {
                try {
                    $this->saveLocaleToCookie($locale);
                } catch (\Exception $e) {
                    // 忽略 cookie 写入错误
                    if (app()->environment('local')) {
                        logger()->warning('Failed to save locale to cookie: ' . $e->getMessage());
                    }
                }
            }

            // 如果用户已登录，保存到用户设置
            if ($request->user() && config('localization.detection.auto_set_user_locale', true)) {
                try {
                    $this->saveUserLocale($request->user(), $locale);
                } catch (\Exception $e) {
                    // 忽略数据库更新错误
                    if (app()->environment('local')) {
                        logger()->warning('Failed to update user locale: ' . $e->getMessage());
                    }
                }
            }

            return $next($request);
        } catch (\Exception $e) {
            // 如果语言设置失败，使用默认语言继续处理
            if (app()->environment('local')) {
                logger()->error('Locale middleware error: ' . $e->getMessage());
            }
            App::setLocale(config('localization.default_locale', config('app.locale', 'zh')));
            return $next($request);
        }
    }
    
    /**
     * 确定应该使用的语言
     */
    private function determineLocale(Request $request, array $supportedLocales): string
    {
        // 1. 检查 URL 参数
        if ($request->has('lang')) {
            $urlLocale = $request->get('lang');
            if (in_array($urlLocale, $supportedLocales)) {
                return $urlLocale;
            }
            // 如果 URL 参数存在但不支持，直接返回默认语言
            return config('localization.default_locale', config('app.locale', 'zh'));
        }
        
        // 2. 检查 Session
        $sessionLocale = Session::get('locale');
        if ($sessionLocale && in_array($sessionLocale, $supportedLocales)) {
            return $sessionLocale;
        }

        // 3. 检查用户设置
        if ($request->user() && $request->user()->locale) {
            $userLocale = $request->user()->locale;
            if (in_array($userLocale, $supportedLocales)) {
                return $userLocale;
            }
        }

        // 4. 检查 Cookie
        $cookieLocale = $this->getCookieLocale($request, $supportedLocales);
        if ($cookieLocale) {
            return $cookieLocale;
        }

        // 5. 检查浏览器语言
        if (config('localization.detection.browser_detection', true)) {
            $browserLocale = $this->getBrowserLocale($request, $supportedLocales);
            if ($browserLocale) {
                return $browserLocale;
            }
        }

        // 6. 检查 IP 地理位置（可选）
        if (config('localization.detection.ip_detection', false)) {
            $ipLocale = $this->getIpLocale($request, $supportedLocales);
            if ($ipLocale) {
                return $ipLocale;
            }
        }

        // 7. 返回默认语言
        return config('localization.default_locale', config('app.locale', 'zh'));
    }
    
    /**
     * 从浏览器 Accept-Language 头获取语言
     */
    private function getBrowserLocale(Request $request, array $supportedLocales): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');
        
        if (!$acceptLanguage) {
            return null;
        }
        
        // 解析 Accept-Language 头
        $languages = [];
        foreach (explode(',', $acceptLanguage) as $lang) {
            $parts = explode(';', trim($lang));
            $locale = trim($parts[0]);
            $quality = 1.0;
            
            if (isset($parts[1]) && strpos($parts[1], 'q=') === 0) {
                $quality = (float) substr($parts[1], 2);
            }
            
            $languages[$locale] = $quality;
        }
        
        // 按质量排序
        arsort($languages);
        
        // 查找匹配的语言
        foreach ($languages as $locale => $quality) {
            // 完全匹配
            if (in_array($locale, $supportedLocales)) {
                return $locale;
            }
            
            // 语言代码匹配（如 zh-CN 匹配 zh）
            $langCode = substr($locale, 0, 2);
            if (in_array($langCode, $supportedLocales)) {
                return $langCode;
            }
        }
        
        return null;
    }
    
    /**
     * 保存用户语言设置
     */
    private function saveUserLocale($user, string $locale): void
    {
        // 只有当语言发生变化时才更新数据库
        if ($user->locale !== $locale) {
            $user->update(['locale' => $locale]);
        }
    }

    /**
     * 从 Cookie 获取语言设置
     */
    private function getCookieLocale(Request $request, array $supportedLocales): ?string
    {
        $cookieLocale = $request->cookie('locale');

        if ($cookieLocale && in_array($cookieLocale, $supportedLocales)) {
            return $cookieLocale;
        }

        return null;
    }

    /**
     * 保存语言设置到 Cookie
     */
    private function saveLocaleToCookie(string $locale): void
    {
        $lifetime = config('localization.detection.cookie_lifetime', 365) * 24 * 60; // 转换为分钟

        cookie()->queue(cookie('locale', $locale, $lifetime));
    }

    /**
     * 通过 IP 地址检测语言
     */
    private function getIpLocale(Request $request, array $supportedLocales): ?string
    {
        try {
            $geoService = app(GeoLocationService::class);
            $detectedLocale = $geoService->detectLocale($supportedLocales);

            if ($detectedLocale && in_array($detectedLocale, $supportedLocales)) {
                return $detectedLocale;
            }
        } catch (\Exception $e) {
            // 静默处理 IP 检测失败，不影响正常流程
            Log::debug('IP locale detection failed', ['error' => $e->getMessage()]);
        }

        return null;
    }
}
