<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TranslationManager extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translation:manage
                            {action : Action to perform (check|export|import|sync|stats)}
                            {--locale= : Specific locale to work with}
                            {--file= : Specific file to work with}
                            {--format=json : Export format (json|csv|xlsx)}
                            {--output= : Output file path}
                            {--missing-only : Only show missing translations}
                            {--fix : Automatically fix issues where possible}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage translation files - check, export, import, sync translations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        match ($action) {
            'check' => $this->checkTranslations(),
            'export' => $this->exportTranslations(),
            'import' => $this->importTranslations(),
            'sync' => $this->syncTranslations(),
            'stats' => $this->showStats(),
            default => $this->error("Unknown action: {$action}. Available actions: check, export, import, sync, stats")
        };
    }

    /**
     * 检查翻译完整性
     */
    private function checkTranslations(): void
    {
        $this->info('🔍 Checking translation completeness...');
        $this->newLine();

        $supportedLocales = config('localization.supported_locales', ['zh' => [], 'en' => []]);
        $locales = array_keys($supportedLocales);

        if ($specificLocale = $this->option('locale')) {
            $locales = [$specificLocale];
        }

        $langPath = resource_path('lang');
        $issues = [];
        $stats = [];

        foreach ($locales as $locale) {
            $this->line("📋 Checking locale: <info>{$locale}</info>");

            $localePath = "{$langPath}/{$locale}";
            if (!is_dir($localePath)) {
                $issues[] = "Missing locale directory: {$locale}";
                continue;
            }

            $files = glob("{$localePath}/*.php");
            $localeStats = [
                'files' => count($files),
                'keys' => 0,
                'missing' => 0,
                'empty' => 0,
            ];

            foreach ($files as $file) {
                $filename = basename($file, '.php');
                $translations = include $file;

                if ($this->option('file') && $filename !== $this->option('file')) {
                    continue;
                }

                $flatTranslations = $this->flattenArray($translations);
                $localeStats['keys'] += count($flatTranslations);

                // 检查其他语言是否有对应的文件和键
                foreach ($locales as $otherLocale) {
                    if ($otherLocale === $locale) continue;

                    $otherFile = "{$langPath}/{$otherLocale}/{$filename}.php";
                    if (!file_exists($otherFile)) {
                        $issues[] = "Missing file: {$otherLocale}/{$filename}.php";
                        continue;
                    }

                    $otherTranslations = include $otherFile;
                    $otherFlat = $this->flattenArray($otherTranslations);

                    foreach ($flatTranslations as $key => $_) {
                        if (!isset($otherFlat[$key])) {
                            $issues[] = "Missing key '{$key}' in {$otherLocale}/{$filename}.php";
                            $localeStats['missing']++;
                        } elseif (empty($otherFlat[$key])) {
                            $issues[] = "Empty value for key '{$key}' in {$otherLocale}/{$filename}.php";
                            $localeStats['empty']++;
                        }
                    }
                }
            }

            $stats[$locale] = $localeStats;
        }

        // 显示统计信息
        $this->newLine();
        $this->info('📊 Translation Statistics:');
        $this->table(
            ['Locale', 'Files', 'Keys', 'Missing', 'Empty'],
            collect($stats)->map(fn($stat, $locale) => [
                $locale,
                $stat['files'],
                $stat['keys'],
                $stat['missing'],
                $stat['empty'],
            ])->toArray()
        );

        // 显示问题
        if (!empty($issues)) {
            $this->newLine();
            $this->error('❌ Found ' . count($issues) . ' issues:');

            if ($this->option('missing-only')) {
                $issues = array_filter($issues, fn($issue) => str_contains($issue, 'Missing'));
            }

            foreach ($issues as $issue) {
                $this->line("  • {$issue}");
            }

            if ($this->option('fix')) {
                $this->fixIssues($issues);
            }
        } else {
            $this->info('✅ All translations are complete!');
        }
    }

    /**
     * 导出翻译
     */
    private function exportTranslations(): void
    {
        $this->info('📤 Exporting translations...');

        $format = $this->option('format');
        $output = $this->option('output') ?: storage_path("app/translations.{$format}");
        $locale = $this->option('locale');

        $translations = $this->collectTranslations($locale);

        match ($format) {
            'json' => $this->exportToJson($translations, $output),
            'csv' => $this->exportToCsv($translations, $output),
            'xlsx' => $this->exportToXlsx($translations, $output),
            default => $this->error("Unsupported format: {$format}")
        };

        $this->info("✅ Translations exported to: {$output}");
    }

    /**
     * 导入翻译
     */
    private function importTranslations(): void
    {
        $this->info('📥 Importing translations...');

        $file = $this->option('file');
        if (!$file || !file_exists($file)) {
            $this->error('Please specify a valid file with --file option');
            return;
        }

        $format = $this->option('format') ?: pathinfo($file, PATHINFO_EXTENSION);

        $translations = match ($format) {
            'json' => $this->importFromJson($file),
            'csv' => $this->importFromCsv($file),
            'xlsx' => $this->importFromXlsx($file),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}")
        };

        $this->writeTranslations($translations);
        $this->info('✅ Translations imported successfully!');
    }

    /**
     * 同步翻译
     */
    private function syncTranslations(): void
    {
        $this->info('🔄 Syncing translations...');

        $sourceLocale = 'zh'; // 主语言
        $targetLocales = array_keys(config('localization.supported_locales', []));
        $targetLocales = array_filter($targetLocales, fn($locale) => $locale !== $sourceLocale);

        if ($specificLocale = $this->option('locale')) {
            $targetLocales = [$specificLocale];
        }

        $langPath = resource_path('lang');
        $sourceFiles = glob("{$langPath}/{$sourceLocale}/*.php");

        foreach ($sourceFiles as $sourceFile) {
            $filename = basename($sourceFile, '.php');
            $sourceTranslations = include $sourceFile;
            $sourceFlat = $this->flattenArray($sourceTranslations);

            foreach ($targetLocales as $targetLocale) {
                $targetFile = "{$langPath}/{$targetLocale}/{$filename}.php";

                if (!file_exists($targetFile)) {
                    $this->warn("Creating missing file: {$targetLocale}/{$filename}.php");
                    $this->createTranslationFile($targetFile, $sourceTranslations);
                    continue;
                }

                $targetTranslations = include $targetFile;
                $targetFlat = $this->flattenArray($targetTranslations);
                $updated = false;

                foreach ($sourceFlat as $key => $value) {
                    if (!isset($targetFlat[$key])) {
                        $this->line("Adding missing key '{$key}' to {$targetLocale}/{$filename}.php");
                        $targetTranslations = $this->setNestedValue($targetTranslations, $key, $value);
                        $updated = true;
                    }
                }

                if ($updated) {
                    $this->writeTranslationFile($targetFile, $targetTranslations);
                }
            }
        }

        $this->info('✅ Translation sync completed!');
    }

    /**
     * 显示统计信息
     */
    private function showStats(): void
    {
        $this->info('📊 Translation Statistics');
        $this->newLine();

        $supportedLocales = config('localization.supported_locales', []);
        $langPath = resource_path('lang');

        $stats = [];

        foreach ($supportedLocales as $locale => $config) {
            $localePath = "{$langPath}/{$locale}";

            if (!is_dir($localePath)) {
                $stats[$locale] = [
                    'files' => 0,
                    'keys' => 0,
                    'size' => 0,
                    'status' => '❌ Missing'
                ];
                continue;
            }

            $files = glob("{$localePath}/*.php");
            $totalKeys = 0;
            $totalSize = 0;

            foreach ($files as $file) {
                $translations = include $file;
                $totalKeys += count($this->flattenArray($translations));
                $totalSize += filesize($file);
            }

            $stats[$locale] = [
                'files' => count($files),
                'keys' => $totalKeys,
                'size' => $this->formatBytes($totalSize),
                'status' => '✅ Active'
            ];
        }

        $this->table(
            ['Locale', 'Files', 'Keys', 'Size', 'Status'],
            collect($stats)->map(fn($stat, $locale) => [
                $locale,
                $stat['files'],
                $stat['keys'],
                $stat['size'],
                $stat['status'],
            ])->toArray()
        );

        // 显示配置信息
        $this->newLine();
        $this->info('⚙️ Configuration:');
        $this->line('Default Locale: ' . config('localization.default_locale'));
        $this->line('Fallback Locale: ' . config('localization.fallback_locale'));
        $this->line('Browser Detection: ' . (config('localization.detection.browser_detection') ? 'Enabled' : 'Disabled'));
        $this->line('IP Detection: ' . (config('localization.detection.ip_detection') ? 'Enabled' : 'Disabled'));
    }

    /**
     * 扁平化数组
     */
    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**
     * 修复问题
     */
    private function fixIssues(array $issues): void
    {
        $this->info('🔧 Attempting to fix issues...');

        foreach ($issues as $issue) {
            if (str_contains($issue, 'Missing file:')) {
                // 创建缺失的文件
                preg_match('/Missing file: (.+)/', $issue, $matches);
                if (isset($matches[1])) {
                    $filePath = resource_path("lang/{$matches[1]}");
                    $this->createTranslationFile($filePath, []);
                    $this->line("✅ Created: {$matches[1]}");
                }
            }
        }
    }

    /**
     * 收集翻译
     */
    private function collectTranslations(?string $locale = null): array
    {
        $translations = [];
        $locales = $locale ? [$locale] : array_keys(config('localization.supported_locales', []));

        foreach ($locales as $loc) {
            $localePath = resource_path("lang/{$loc}");
            if (!is_dir($localePath)) continue;

            $files = glob("{$localePath}/*.php");
            foreach ($files as $file) {
                $filename = basename($file, '.php');
                $translations[$loc][$filename] = include $file;
            }
        }

        return $translations;
    }

    /**
     * 导出为 JSON
     */
    private function exportToJson(array $translations, string $output): void
    {
        file_put_contents($output, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * 导出为 CSV
     */
    private function exportToCsv(array $translations, string $output): void
    {
        $handle = fopen($output, 'w');

        // 写入标题行
        $locales = array_keys($translations);
        fputcsv($handle, array_merge(['Key', 'File'], $locales));

        // 收集所有键
        $allKeys = [];
        foreach ($translations as $locale => $files) {
            foreach ($files as $filename => $trans) {
                $flat = $this->flattenArray($trans);
                foreach ($flat as $key => $value) {
                    $allKeys["{$filename}.{$key}"] = $filename;
                }
            }
        }

        // 写入数据行
        foreach ($allKeys as $fullKey => $filename) {
            $key = substr($fullKey, strlen($filename) + 1);
            $row = [$key, $filename];

            foreach ($locales as $locale) {
                $value = data_get($translations, "{$locale}.{$filename}.{$key}", '');
                $row[] = $value;
            }

            fputcsv($handle, $row);
        }

        fclose($handle);
    }

    /**
     * 导出为 XLSX (简化版，实际需要 PhpSpreadsheet)
     */
    private function exportToXlsx(array $translations, string $output): void
    {
        $this->warn('XLSX export requires PhpSpreadsheet package. Exporting as CSV instead.');
        $this->exportToCsv($translations, str_replace('.xlsx', '.csv', $output));
    }

    /**
     * 从 JSON 导入
     */
    private function importFromJson(string $file): array
    {
        return json_decode(file_get_contents($file), true);
    }

    /**
     * 从 CSV 导入
     */
    private function importFromCsv(string $file): array
    {
        $translations = [];
        $handle = fopen($file, 'r');
        $headers = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            $key = $row[0];
            $filename = $row[1];

            for ($i = 2; $i < count($headers); $i++) {
                $locale = $headers[$i];
                $value = $row[$i] ?? '';

                if (!empty($value)) {
                    $translations[$locale][$filename] = $this->setNestedValue(
                        $translations[$locale][$filename] ?? [],
                        $key,
                        $value
                    );
                }
            }
        }

        fclose($handle);
        return $translations;
    }

    /**
     * 从 XLSX 导入
     */
    private function importFromXlsx(string $file): array
    {
        $this->warn('XLSX import requires PhpSpreadsheet package. Please convert to CSV first.');
        return [];
    }

    /**
     * 写入翻译文件
     */
    private function writeTranslations(array $translations): void
    {
        foreach ($translations as $locale => $files) {
            foreach ($files as $filename => $trans) {
                $filePath = resource_path("lang/{$locale}/{$filename}.php");
                $this->writeTranslationFile($filePath, $trans);
            }
        }
    }

    /**
     * 创建翻译文件
     */
    private function createTranslationFile(string $filePath, array $translations): void
    {
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $this->writeTranslationFile($filePath, $translations);
    }

    /**
     * 写入翻译文件
     */
    private function writeTranslationFile(string $filePath, array $translations): void
    {
        $content = "<?php\n\nreturn " . var_export($translations, true) . ";\n";
        file_put_contents($filePath, $content);
    }

    /**
     * 设置嵌套值
     */
    private function setNestedValue(array $array, string $key, $value): array
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $k) {
            if (!isset($current[$k]) || !is_array($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }

        $current = $value;
        return $array;
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
