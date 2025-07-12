<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate
                            {--output=public/sitemap.xml : Output file path}
                            {--include-gists : Include individual gist pages}
                            {--include-users : Include user profile pages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate multilingual sitemap for SEO optimization';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🗺️  Generating multilingual sitemap...');

        $outputPath = $this->option('output');
        $seoService = app(\App\Services\SeoLocalizationService::class);

        // 生成 sitemap 数据
        $sitemapData = $this->generateSitemapData();

        // 生成 XML
        $xml = $this->generateSitemapXml($sitemapData);

        // 写入文件
        $fullPath = base_path($outputPath);
        $directory = dirname($fullPath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($fullPath, $xml);

        $this->info("✅ Sitemap generated successfully: {$outputPath}");
        $this->line("📊 Total URLs: " . count($sitemapData));

        // 生成 sitemap index（如果有多个语言）
        $this->generateSitemapIndex($outputPath);
    }

    /**
     * 生成 sitemap 数据
     */
    private function generateSitemapData(): array
    {
        $seoService = app(\App\Services\SeoLocalizationService::class);
        $data = [];

        // 基础页面
        $basePages = [
            ['url' => url('/'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => route('gists.index'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => route('tags.index'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['url' => route('php-runner.index'), 'priority' => '0.8', 'changefreq' => 'monthly'],
        ];

        foreach ($basePages as $page) {
            $data[] = array_merge($page, [
                'lastmod' => now()->toISOString(),
                'alternates' => $seoService->generateHreflangTags($page['url']),
            ]);
        }

        // 包含 Gist 页面
        if ($this->option('include-gists')) {
            $this->addGistPages($data);
        }

        // 包含用户页面
        if ($this->option('include-users')) {
            $this->addUserPages($data);
        }

        return $data;
    }

    /**
     * 添加 Gist 页面
     */
    private function addGistPages(array &$data): void
    {
        $this->line('📝 Adding Gist pages...');

        $gists = \App\Models\Gist::where('is_public', true)
            ->select('id', 'updated_at')
            ->get();

        foreach ($gists as $gist) {
            try {
                $url = route('gists.show', $gist->id);
                $seoService = app(\App\Services\SeoLocalizationService::class);

                $data[] = [
                    'url' => $url,
                    'lastmod' => $gist->updated_at->toISOString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                    'alternates' => $seoService->generateHreflangTags($url),
                ];
            } catch (\Exception $e) {
                $this->warn("Failed to generate URL for Gist {$gist->id}: {$e->getMessage()}");
            }
        }

        $this->line("✅ Added {$gists->count()} Gist pages");
    }

    /**
     * 添加用户页面
     */
    private function addUserPages(array &$data): void
    {
        $this->line('👥 Adding user pages...');

        $users = \App\Models\User::where('is_active', true)
            ->select('id', 'updated_at')
            ->get();

        foreach ($users as $user) {
            try {
                $url = route('users.show', $user->id);
                $seoService = app(\App\Services\SeoLocalizationService::class);

                $data[] = [
                    'url' => $url,
                    'lastmod' => $user->updated_at->toISOString(),
                    'changefreq' => 'monthly',
                    'priority' => '0.6',
                    'alternates' => $seoService->generateHreflangTags($url),
                ];
            } catch (\Exception $e) {
                // 如果用户页面路由不存在，跳过
                continue;
            }
        }

        $this->line("✅ Added {$users->count()} user pages");
    }

    /**
     * 生成 sitemap XML
     */
    private function generateSitemapXml(array $data): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . PHP_EOL;

        foreach ($data as $item) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($item['url']) . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . $item['lastmod'] . '</lastmod>' . PHP_EOL;
            $xml .= '    <changefreq>' . $item['changefreq'] . '</changefreq>' . PHP_EOL;
            $xml .= '    <priority>' . $item['priority'] . '</priority>' . PHP_EOL;

            // 添加多语言备用链接
            if (!empty($item['alternates'])) {
                foreach ($item['alternates'] as $alternate) {
                    $xml .= '    <xhtml:link rel="alternate" hreflang="' . $alternate['hreflang'] . '" href="' . htmlspecialchars($alternate['href']) . '" />' . PHP_EOL;
                }
            }

            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>' . PHP_EOL;

        return $xml;
    }

    /**
     * 生成 sitemap index
     */
    private function generateSitemapIndex(string $outputPath): void
    {
        $supportedLocales = config('localization.supported_locales', []);

        if (count($supportedLocales) <= 1) {
            return;
        }

        $this->line('📑 Generating sitemap index...');

        $indexPath = str_replace('.xml', '-index.xml', $outputPath);
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($supportedLocales as $locale => $config) {
            if (!($config['enabled'] ?? true)) {
                continue;
            }

            $localeFile = str_replace('.xml', "-{$locale}.xml", $outputPath);
            $url = config('app.url') . '/' . basename($localeFile);

            $xml .= '  <sitemap>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($url) . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . now()->toISOString() . '</lastmod>' . PHP_EOL;
            $xml .= '  </sitemap>' . PHP_EOL;
        }

        $xml .= '</sitemapindex>' . PHP_EOL;

        file_put_contents(base_path($indexPath), $xml);
        $this->info("✅ Sitemap index generated: {$indexPath}");
    }
}
