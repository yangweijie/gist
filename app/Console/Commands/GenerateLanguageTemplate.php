<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateLanguageTemplate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:generate
                            {locale : Target locale code (e.g., ja, ko, fr)}
                            {--source=zh : Source locale to copy from}
                            {--enable : Enable the locale after generation}
                            {--force : Overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate language template files for a new locale';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetLocale = $this->argument('locale');
        $sourceLocale = $this->option('source');
        $enable = $this->option('enable');
        $force = $this->option('force');

        $this->info("🌍 Generating language template for: {$targetLocale}");
        $this->newLine();

        // 验证目标语言
        if (!$this->validateLocale($targetLocale)) {
            return 1;
        }

        // 验证源语言
        if (!$this->validateSourceLocale($sourceLocale)) {
            return 1;
        }

        // 检查目标目录
        $targetPath = resource_path("lang/{$targetLocale}");
        if (is_dir($targetPath) && !$force) {
            $this->error("Language directory already exists: {$targetPath}");
            $this->line("Use --force to overwrite existing files");
            return 1;
        }

        // 创建语言文件
        $this->generateLanguageFiles($sourceLocale, $targetLocale, $force);

        // 启用语言（如果指定）
        if ($enable) {
            $this->enableLocale($targetLocale);
        }

        $this->newLine();
        $this->info("✅ Language template generated successfully!");
        $this->line("📁 Location: {$targetPath}");

        if (!$enable) {
            $this->warn("💡 Don't forget to enable the locale in config/localization.php");
        }

        return 0;
    }

    /**
     * 验证目标语言代码
     */
    private function validateLocale(string $locale): bool
    {
        if (!preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $locale)) {
            $this->error("Invalid locale format: {$locale}");
            $this->line("Expected format: 'ja', 'ko', 'zh-CN', etc.");
            return false;
        }

        $supportedLocales = config('localization.supported_locales', []);
        if (!isset($supportedLocales[$locale])) {
            $this->warn("Locale '{$locale}' is not defined in config/localization.php");
            $this->line("You may need to add it to the configuration first.");
        }

        return true;
    }

    /**
     * 验证源语言
     */
    private function validateSourceLocale(string $sourceLocale): bool
    {
        $sourcePath = resource_path("lang/{$sourceLocale}");
        if (!is_dir($sourcePath)) {
            $this->error("Source locale directory not found: {$sourcePath}");
            return false;
        }

        $files = glob("{$sourcePath}/*.php");
        if (empty($files)) {
            $this->error("No language files found in source locale: {$sourceLocale}");
            return false;
        }

        return true;
    }

    /**
     * 生成语言文件
     */
    private function generateLanguageFiles(string $sourceLocale, string $targetLocale, bool $force): void
    {
        $sourcePath = resource_path("lang/{$sourceLocale}");
        $targetPath = resource_path("lang/{$targetLocale}");

        // 创建目标目录
        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0755, true);
            $this->line("📁 Created directory: {$targetPath}");
        }

        // 获取源文件
        $sourceFiles = glob("{$sourcePath}/*.php");
        $this->line("📋 Found " . count($sourceFiles) . " source files");

        foreach ($sourceFiles as $sourceFile) {
            $filename = basename($sourceFile);
            $targetFile = "{$targetPath}/{$filename}";

            if (file_exists($targetFile) && !$force) {
                $this->warn("⚠️  Skipping existing file: {$filename}");
                continue;
            }

            $this->generateLanguageFile($sourceFile, $targetFile, $sourceLocale, $targetLocale);
            $this->line("✅ Generated: {$filename}");
        }
    }

    /**
     * 生成单个语言文件
     */
    private function generateLanguageFile(string $sourceFile, string $targetFile, string $sourceLocale, string $targetLocale): void
    {
        $sourceContent = include $sourceFile;

        // 添加翻译注释
        $header = "<?php\n\n";
        $header .= "/*\n";
        $header .= " * Language file for {$targetLocale}\n";
        $header .= " * Generated from {$sourceLocale} on " . now()->toDateTimeString() . "\n";
        $header .= " * \n";
        $header .= " * TODO: Translate all values to " . $this->getLanguageName($targetLocale) . "\n";
        $header .= " * Keep the array structure unchanged, only translate the values\n";
        $header .= " */\n\n";

        // 处理翻译内容
        $processedContent = $this->processTranslationContent($sourceContent, $targetLocale);

        $content = $header . "return " . $this->varExportPretty($processedContent) . ";\n";

        file_put_contents($targetFile, $content);
    }

    /**
     * 处理翻译内容
     */
    private function processTranslationContent(array $content, string $targetLocale): array
    {
        $processed = [];

        foreach ($content as $key => $value) {
            if (is_array($value)) {
                $processed[$key] = $this->processTranslationContent($value, $targetLocale);
            } else {
                // 添加翻译标记
                $processed[$key] = "TODO: {$value}";
            }
        }

        return $processed;
    }

    /**
     * 美化的 var_export
     */
    private function varExportPretty(array $array, int $indent = 0): string
    {
        $indentStr = str_repeat('    ', $indent);
        $result = "[\n";

        foreach ($array as $key => $value) {
            $result .= $indentStr . "    " . var_export($key, true) . " => ";

            if (is_array($value)) {
                $result .= $this->varExportPretty($value, $indent + 1);
            } else {
                $result .= var_export($value, true);
            }

            $result .= ",\n";
        }

        $result .= $indentStr . "]";
        return $result;
    }

    /**
     * 获取语言名称
     */
    private function getLanguageName(string $locale): string
    {
        $names = [
            'ja' => 'Japanese (日本語)',
            'ko' => 'Korean (한국어)',
            'fr' => 'French (Français)',
            'de' => 'German (Deutsch)',
            'es' => 'Spanish (Español)',
            'pt' => 'Portuguese (Português)',
            'ru' => 'Russian (Русский)',
            'ar' => 'Arabic (العربية)',
            'it' => 'Italian (Italiano)',
            'nl' => 'Dutch (Nederlands)',
            'sv' => 'Swedish (Svenska)',
            'da' => 'Danish (Dansk)',
            'no' => 'Norwegian (Norsk)',
            'fi' => 'Finnish (Suomi)',
            'pl' => 'Polish (Polski)',
            'cs' => 'Czech (Čeština)',
            'hu' => 'Hungarian (Magyar)',
            'tr' => 'Turkish (Türkçe)',
            'th' => 'Thai (ไทย)',
            'vi' => 'Vietnamese (Tiếng Việt)',
            'hi' => 'Hindi (हिन्दी)',
            'bn' => 'Bengali (বাংলা)',
        ];

        return $names[$locale] ?? ucfirst($locale);
    }

    /**
     * 启用语言
     */
    private function enableLocale(string $locale): void
    {
        $configPath = config_path('localization.php');

        if (!file_exists($configPath)) {
            $this->warn("Configuration file not found: {$configPath}");
            return;
        }

        $config = include $configPath;

        if (isset($config['supported_locales'][$locale])) {
            // 更新配置（这里只是示例，实际需要更复杂的配置文件修改）
            $this->line("💡 To enable the locale, set 'enabled' => true in config/localization.php");
            $this->line("   'supported_locales' => [");
            $this->line("       '{$locale}' => [");
            $this->line("           'enabled' => true,");
            $this->line("           // ... other settings");
            $this->line("       ],");
            $this->line("   ],");
        } else {
            $this->warn("Locale '{$locale}' not found in configuration");
        }
    }
}
