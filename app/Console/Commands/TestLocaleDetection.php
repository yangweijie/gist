<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestLocaleDetection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locale:test {ip?} {--browser-lang=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test locale detection functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌍 Testing Locale Detection Functionality');
        $this->newLine();

        // 测试 IP 地理位置检测
        $ip = $this->argument('ip') ?? '8.8.8.8';
        $this->testIpDetection($ip);

        // 测试浏览器语言检测
        $browserLang = $this->option('browser-lang') ?? 'zh-CN,zh;q=0.9,en;q=0.8';
        $this->testBrowserDetection($browserLang);

        // 测试配置
        $this->testConfiguration();

        $this->newLine();
        $this->info('✅ Locale detection test completed!');
    }

    private function testIpDetection(string $ip): void
    {
        $this->info("🔍 Testing IP Detection for: {$ip}");

        try {
            $geoService = app(\App\Services\GeoLocationService::class);

            $country = $geoService->getCountryByIp($ip);
            $locale = $geoService->getLocaleByIp($ip);

            $this->line("  Country: " . ($country ?: 'Unknown'));
            $this->line("  Detected Locale: " . ($locale ?: 'None'));

            if ($locale) {
                $this->info("  ✅ IP detection successful");
            } else {
                $this->warn("  ⚠️  No locale detected from IP");
            }
        } catch (\Exception $e) {
            $this->error("  ❌ IP detection failed: " . $e->getMessage());
        }

        $this->newLine();
    }

    private function testBrowserDetection(string $acceptLanguage): void
    {
        $this->info("🌐 Testing Browser Language Detection");
        $this->line("  Accept-Language: {$acceptLanguage}");

        // 模拟浏览器语言解析
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

        arsort($languages);

        $supportedLocales = ['zh', 'en'];
        $detectedLocale = null;

        foreach ($languages as $locale => $quality) {
            if (in_array($locale, $supportedLocales)) {
                $detectedLocale = $locale;
                break;
            }

            $langCode = substr($locale, 0, 2);
            if (in_array($langCode, $supportedLocales)) {
                $detectedLocale = $langCode;
                break;
            }
        }

        $this->line("  Parsed Languages: " . json_encode($languages));
        $this->line("  Detected Locale: " . ($detectedLocale ?: 'None'));

        if ($detectedLocale) {
            $this->info("  ✅ Browser detection successful");
        } else {
            $this->warn("  ⚠️  No supported locale detected");
        }

        $this->newLine();
    }

    private function testConfiguration(): void
    {
        $this->info("⚙️  Testing Configuration");

        $config = [
            'Default Locale' => config('localization.default_locale'),
            'Fallback Locale' => config('localization.fallback_locale'),
            'Browser Detection' => config('localization.detection.browser_detection') ? 'Enabled' : 'Disabled',
            'IP Detection' => config('localization.detection.ip_detection') ? 'Enabled' : 'Disabled',
            'Remember Guest' => config('localization.detection.remember_guest_locale') ? 'Enabled' : 'Disabled',
            'Cookie Lifetime' => config('localization.detection.cookie_lifetime') . ' days',
        ];

        foreach ($config as $key => $value) {
            $this->line("  {$key}: {$value}");
        }

        $supportedLocales = config('localization.supported_locales');
        $this->line("  Supported Locales: " . implode(', ', array_keys($supportedLocales)));

        $this->newLine();
    }
}
