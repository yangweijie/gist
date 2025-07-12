<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class LocaleController extends Controller
{
    /**
     * 切换语言
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        // 获取本地化服务
        $localizationService = app(\App\Services\LocalizationService::class);

        // 验证语言代码
        if (!$localizationService->isLocaleSupported($locale)) {
            return redirect()->back()->with('error', __('common.messages.invalid_locale'));
        }

        // 保存到 Session
        Session::put('locale', $locale);

        // 如果用户已登录，保存到用户设置
        if (Auth::check()) {
            try {
                $user = Auth::user();
                $user->locale = $locale;
                $user->save();
            } catch (\Exception $e) {
                // 如果用户表没有locale字段，忽略错误
                logger()->warning('Failed to update user locale: ' . $e->getMessage());
            }
        }

        // 重定向回原页面
        return redirect()->back()->with('success', __('common.messages.locale_switched'));
    }
    
    /**
     * 获取当前语言信息
     */
    public function current(): array
    {
        $currentLocale = app()->getLocale();
        $supportedLocales = config('app.supported_locales', ['zh', 'en']);
        
        return [
            'current' => $currentLocale,
            'supported' => $supportedLocales,
            'languages' => [
                'zh' => [
                    'code' => 'zh',
                    'name' => '中文',
                    'native' => '中文',
                    'flag' => '🇨🇳',
                ],
                'en' => [
                    'code' => 'en',
                    'name' => 'English',
                    'native' => 'English',
                    'flag' => '🇺🇸',
                ],
            ],
        ];
    }
    
    /**
     * API 接口：切换语言
     */
    public function apiSwitch(Request $request)
    {
        // 获取本地化服务
        $localizationService = app(\App\Services\LocalizationService::class);
        $supportedLocales = array_keys($localizationService->getEnabledLocales());

        $request->validate([
            'locale' => 'required|string|in:' . implode(',', $supportedLocales),
        ]);

        $locale = $request->input('locale');

        // 保存到 Session
        Session::put('locale', $locale);

        // 如果用户已登录，保存到用户设置
        if (Auth::check()) {
            try {
                $user = Auth::user();
                $user->locale = $locale;
                $user->save();
            } catch (\Exception $e) {
                // 如果用户表没有locale字段，忽略错误
                logger()->warning('Failed to update user locale: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('common.messages.locale_switched'),
            'locale' => $locale,
        ]);
    }
}
