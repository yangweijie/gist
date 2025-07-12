<?php

namespace App\Providers;

use App\Services\LocalizationService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class LocalizationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(LocalizationService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerBladeDirectives();
        $this->shareViewData();
    }

    /**
     * 注册 Blade 指令
     */
    private function registerBladeDirectives(): void
    {
        // @locale 指令 - 获取当前语言
        Blade::directive('locale', function () {
            return "<?php echo app()->getLocale(); ?>";
        });

        // @localeInfo 指令 - 获取当前语言信息
        Blade::directive('localeInfo', function ($expression) {
            return "<?php echo app(App\Services\LocalizationService::class)->getCurrentLocaleInfo()[{$expression}] ?? ''; ?>";
        });

        // @formatDate 指令 - 格式化日期
        Blade::directive('formatDate', function ($expression) {
            return "<?php echo app(App\Services\LocalizationService::class)->formatDate({$expression}); ?>";
        });

        // @formatNumber 指令 - 格式化数字
        Blade::directive('formatNumber', function ($expression) {
            return "<?php echo app(App\Services\LocalizationService::class)->formatNumber({$expression}); ?>";
        });

        // @formatCurrency 指令 - 格式化货币
        Blade::directive('formatCurrency', function ($expression) {
            return "<?php echo app(App\Services\LocalizationService::class)->formatCurrency({$expression}); ?>";
        });

        // @hreflang 指令 - 生成 hreflang 标签
        Blade::directive('hreflang', function ($expression) {
            return "<?php 
                \$tags = app(App\Services\LocalizationService::class)->generateHreflangTags({$expression});
                foreach (\$tags as \$tag) {
                    echo '<link rel=\"alternate\" hreflang=\"' . \$tag['hreflang'] . '\" href=\"' . \$tag['href'] . '\">' . PHP_EOL;
                }
            ?>";
        });

        // @rtl 指令 - 检查是否为从右到左的语言
        Blade::directive('rtl', function () {
            return "<?php echo app(App\Services\LocalizationService::class)->getCurrentLocaleInfo()['direction'] === 'rtl' ? 'rtl' : 'ltr'; ?>";
        });

        // @isRtl 指令 - 检查是否为 RTL
        Blade::directive('isRtl', function () {
            return "<?php echo app(App\Services\LocalizationService::class)->getCurrentLocaleInfo()['direction'] === 'rtl'; ?>";
        });

        // @localeFlag 指令 - 获取当前语言的国旗
        Blade::directive('localeFlag', function () {
            return "<?php echo app(App\Services\LocalizationService::class)->getCurrentLocaleInfo()['flag'] ?? '🌐'; ?>";
        });

        // @localeName 指令 - 获取当前语言的本地名称
        Blade::directive('localeName', function () {
            return "<?php echo app(App\Services\LocalizationService::class)->getCurrentLocaleInfo()['native'] ?? app()->getLocale(); ?>";
        });
    }

    /**
     * 共享视图数据
     */
    private function shareViewData(): void
    {
        View::composer('*', function ($view) {
            $localizationService = app(LocalizationService::class);
            
            $view->with([
                'currentLocale' => app()->getLocale(),
                'currentLocaleInfo' => $localizationService->getCurrentLocaleInfo(),
                'supportedLocales' => $localizationService->getEnabledLocales(),
                'showLanguageSwitcher' => $localizationService->shouldShowSwitcher('frontend'),
            ]);
        });
    }
}
