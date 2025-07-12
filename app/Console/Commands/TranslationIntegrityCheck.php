<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TranslationIntegrityCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translation:check-integrity
                            {--fix : Automatically fix missing translations}
                            {--report=console : Output format (console|json|html)}
                            {--output= : Output file path for reports}
                            {--strict : Strict mode - fail on any missing translations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check translation file integrity and completeness across all supported languages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking translation integrity...');
        $this->newLine();

        $results = $this->performIntegrityCheck();

        // 输出结果
        $this->outputResults($results);

        // 自动修复（如果启用）
        if ($this->option('fix') && !empty($results['issues'])) {
            $this->fixIssues($results['issues']);
        }

        // 生成报告
        if ($this->option('output')) {
            $this->generateReport($results);
        }

        // 严格模式检查
        if ($this->option('strict') && !empty($results['issues'])) {
            $this->error('❌ Translation integrity check failed in strict mode');
            return 1;
        }

        $this->newLine();
        $this->info('✅ Translation integrity check completed');
        return 0;
    }

    /**
     * 执行完整性检查
     */
    private function performIntegrityCheck(): array
    {
        $supportedLocales = config('localization.supported_locales', []);
        $langPath = resource_path('lang');

        $results = [
            'summary' => [
                'total_locales' => count($supportedLocales),
                'total_files' => 0,
                'total_keys' => 0,
                'missing_files' => 0,
                'missing_keys' => 0,
                'empty_values' => 0,
                'duplicate_keys' => 0,
            ],
            'locales' => [],
            'issues' => [],
            'recommendations' => [],
        ];

        // 检查每个语言
        foreach ($supportedLocales as $locale => $config) {
            if (!($config['enabled'] ?? true)) {
                continue;
            }

            $this->line("🔍 Checking locale: <info>{$locale}</info>");
            $localeResults = $this->checkLocale($locale, $langPath);

            $results['locales'][$locale] = $localeResults;
            $results['summary']['total_files'] += $localeResults['file_count'];
            $results['summary']['total_keys'] += $localeResults['key_count'];
            $results['summary']['missing_files'] += count($localeResults['missing_files']);
            $results['summary']['missing_keys'] += count($localeResults['missing_keys']);
            $results['summary']['empty_values'] += count($localeResults['empty_values']);
            $results['summary']['duplicate_keys'] += count($localeResults['duplicate_keys']);

            $results['issues'] = array_merge($results['issues'], $localeResults['issues']);
        }

        // 交叉检查语言一致性
        $this->crossCheckLocales($results);

        // 生成建议
        $this->generateRecommendations($results);

        return $results;
    }

    /**
     * 检查单个语言
     */
    private function checkLocale(string $locale, string $langPath): array
    {
        $localePath = "{$langPath}/{$locale}";
        $results = [
            'locale' => $locale,
            'path' => $localePath,
            'exists' => is_dir($localePath),
            'file_count' => 0,
            'key_count' => 0,
            'files' => [],
            'missing_files' => [],
            'missing_keys' => [],
            'empty_values' => [],
            'duplicate_keys' => [],
            'issues' => [],
        ];

        if (!$results['exists']) {
            $results['issues'][] = [
                'type' => 'missing_directory',
                'severity' => 'critical',
                'message' => "Language directory missing: {$localePath}",
                'locale' => $locale,
            ];
            return $results;
        }

        // 获取所有 PHP 文件
        $files = glob("{$localePath}/*.php");
        $results['file_count'] = count($files);

        foreach ($files as $file) {
            $filename = basename($file, '.php');
            $fileResults = $this->checkTranslationFile($file, $locale, $filename);

            $results['files'][$filename] = $fileResults;
            $results['key_count'] += $fileResults['key_count'];
            $results['empty_values'] = array_merge($results['empty_values'], $fileResults['empty_values']);
            $results['duplicate_keys'] = array_merge($results['duplicate_keys'], $fileResults['duplicate_keys']);
            $results['issues'] = array_merge($results['issues'], $fileResults['issues']);
        }

        return $results;
    }

    /**
     * 检查翻译文件
     */
    private function checkTranslationFile(string $filePath, string $locale, string $filename): array
    {
        $results = [
            'file' => $filename,
            'path' => $filePath,
            'key_count' => 0,
            'empty_values' => [],
            'duplicate_keys' => [],
            'issues' => [],
        ];

        try {
            $translations = include $filePath;

            if (!is_array($translations)) {
                $results['issues'][] = [
                    'type' => 'invalid_format',
                    'severity' => 'critical',
                    'message' => "File does not return an array: {$filePath}",
                    'locale' => $locale,
                    'file' => $filename,
                ];
                return $results;
            }

            $flatTranslations = $this->flattenArray($translations);
            $results['key_count'] = count($flatTranslations);

            // 检查空值
            foreach ($flatTranslations as $key => $value) {
                if (empty($value) && $value !== '0') {
                    $results['empty_values'][] = [
                        'key' => $key,
                        'file' => $filename,
                        'locale' => $locale,
                    ];
                }
            }

            // 检查重复键（在嵌套数组中）
            $this->checkDuplicateKeys($translations, $results, $locale, $filename);

        } catch (\Exception $e) {
            $results['issues'][] = [
                'type' => 'parse_error',
                'severity' => 'critical',
                'message' => "Failed to parse file: {$e->getMessage()}",
                'locale' => $locale,
                'file' => $filename,
                'path' => $filePath,
            ];
        }

        return $results;
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
     * 检查重复键
     */
    private function checkDuplicateKeys(array $array, array &$results, string $locale, string $filename, string $prefix = ''): void
    {
        $keys = [];

        foreach ($array as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (isset($keys[$key])) {
                $results['duplicate_keys'][] = [
                    'key' => $fullKey,
                    'file' => $filename,
                    'locale' => $locale,
                ];
            }

            $keys[$key] = true;

            if (is_array($value)) {
                $this->checkDuplicateKeys($value, $results, $locale, $filename, $fullKey);
            }
        }
    }

    /**
     * 交叉检查语言一致性
     */
    private function crossCheckLocales(array &$results): void
    {
        $this->line('🔄 Cross-checking locale consistency...');

        $locales = array_keys($results['locales']);

        if (count($locales) < 2) {
            return;
        }

        // 检查文件一致性
        foreach ($locales as $locale) {
            $files = array_keys($results['locales'][$locale]['files']);

            foreach ($locales as $otherLocale) {
                if ($locale === $otherLocale) continue;

                $otherFiles = array_keys($results['locales'][$otherLocale]['files']);

                // 检查缺失的文件
                $missingFiles = array_diff($files, $otherFiles);
                foreach ($missingFiles as $missingFile) {
                    $results['issues'][] = [
                        'type' => 'missing_file',
                        'severity' => 'high',
                        'message' => "File '{$missingFile}.php' exists in {$locale} but missing in {$otherLocale}",
                        'locale' => $otherLocale,
                        'file' => $missingFile,
                        'reference_locale' => $locale,
                    ];
                }
            }
        }

        // 检查键一致性
        $this->checkKeyConsistency($results, $locales);
    }

    /**
     * 检查键一致性
     */
    private function checkKeyConsistency(array &$results, array $locales): void
    {
        $baseLocale = $locales[0]; // 使用第一个语言作为基准

        foreach (array_keys($results['locales'][$baseLocale]['files']) as $filename) {
            $baseFile = resource_path("lang/{$baseLocale}/{$filename}.php");

            if (!file_exists($baseFile)) continue;

            $baseTranslations = include $baseFile;
            $baseKeys = $this->flattenArray($baseTranslations);

            foreach ($locales as $locale) {
                if ($locale === $baseLocale) continue;

                $localeFile = resource_path("lang/{$locale}/{$filename}.php");

                if (!file_exists($localeFile)) continue;

                $localeTranslations = include $localeFile;
                $localeKeys = $this->flattenArray($localeTranslations);

                // 检查缺失的键
                $missingKeys = array_diff_key($baseKeys, $localeKeys);
                foreach ($missingKeys as $key => $value) {
                    $results['issues'][] = [
                        'type' => 'missing_key',
                        'severity' => 'medium',
                        'message' => "Key '{$key}' exists in {$baseLocale}/{$filename}.php but missing in {$locale}/{$filename}.php",
                        'locale' => $locale,
                        'file' => $filename,
                        'key' => $key,
                        'reference_locale' => $baseLocale,
                        'reference_value' => $value,
                    ];
                }

                // 检查多余的键
                $extraKeys = array_diff_key($localeKeys, $baseKeys);
                foreach ($extraKeys as $key => $value) {
                    $results['issues'][] = [
                        'type' => 'extra_key',
                        'severity' => 'low',
                        'message' => "Key '{$key}' exists in {$locale}/{$filename}.php but not in {$baseLocale}/{$filename}.php",
                        'locale' => $locale,
                        'file' => $filename,
                        'key' => $key,
                        'value' => $value,
                        'reference_locale' => $baseLocale,
                    ];
                }
            }
        }
    }

    /**
     * 生成建议
     */
    private function generateRecommendations(array &$results): void
    {
        $issues = $results['issues'];
        $recommendations = [];

        // 基于问题类型生成建议
        $issueTypes = array_count_values(array_column($issues, 'type'));

        if (isset($issueTypes['missing_key']) && $issueTypes['missing_key'] > 10) {
            $recommendations[] = [
                'type' => 'sync_translations',
                'priority' => 'high',
                'message' => 'Consider running translation sync to add missing keys automatically',
                'command' => 'php artisan translation:manage sync',
            ];
        }

        if (isset($issueTypes['empty_value']) && $issueTypes['empty_value'] > 5) {
            $recommendations[] = [
                'type' => 'review_empty_values',
                'priority' => 'medium',
                'message' => 'Review and fill empty translation values',
                'action' => 'Manual review required',
            ];
        }

        if (isset($issueTypes['missing_file'])) {
            $recommendations[] = [
                'type' => 'create_missing_files',
                'priority' => 'high',
                'message' => 'Create missing translation files to maintain consistency',
                'command' => 'php artisan translation:manage sync',
            ];
        }

        $results['recommendations'] = $recommendations;
    }

    /**
     * 输出结果
     */
    private function outputResults(array $results): void
    {
        $format = $this->option('report');

        switch ($format) {
            case 'json':
                $this->outputJsonResults($results);
                break;
            case 'html':
                $this->outputHtmlResults($results);
                break;
            default:
                $this->outputConsoleResults($results);
        }
    }

    /**
     * 控制台输出结果
     */
    private function outputConsoleResults(array $results): void
    {
        // 摘要
        $this->newLine();
        $this->info('📊 Summary:');
        $summary = $results['summary'];

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Locales', $summary['total_locales']],
                ['Total Files', $summary['total_files']],
                ['Total Keys', $summary['total_keys']],
                ['Missing Files', $summary['missing_files']],
                ['Missing Keys', $summary['missing_keys']],
                ['Empty Values', $summary['empty_values']],
                ['Duplicate Keys', $summary['duplicate_keys']],
            ]
        );

        // 问题详情
        if (!empty($results['issues'])) {
            $this->newLine();
            $this->error('❌ Issues Found:');

            $groupedIssues = [];
            foreach ($results['issues'] as $issue) {
                $groupedIssues[$issue['severity']][] = $issue;
            }

            foreach (['critical', 'high', 'medium', 'low'] as $severity) {
                if (empty($groupedIssues[$severity])) continue;

                $this->newLine();
                $this->line("<fg=red>🔴 {$severity} issues (" . count($groupedIssues[$severity]) . "):</>");

                foreach ($groupedIssues[$severity] as $issue) {
                    $this->line("  • {$issue['message']}");
                }
            }
        }

        // 建议
        if (!empty($results['recommendations'])) {
            $this->newLine();
            $this->info('💡 Recommendations:');

            foreach ($results['recommendations'] as $rec) {
                $priority = $rec['priority'] === 'high' ? '<fg=red>HIGH</>' :
                           ($rec['priority'] === 'medium' ? '<fg=yellow>MEDIUM</>' : '<fg=green>LOW</>');

                $this->line("  [{$priority}] {$rec['message']}");

                if (isset($rec['command'])) {
                    $this->line("    Command: <fg=cyan>{$rec['command']}</>");
                }
            }
        }
    }

    /**
     * JSON 输出结果
     */
    private function outputJsonResults(array $results): void
    {
        $this->line(json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * HTML 输出结果
     */
    private function outputHtmlResults(array $results): void
    {
        $this->warn('HTML output format is not yet implemented. Using console format.');
        $this->outputConsoleResults($results);
    }

    /**
     * 修复问题
     */
    private function fixIssues(array $issues): void
    {
        $this->newLine();
        $this->info('🔧 Attempting to fix issues...');

        $fixed = 0;

        foreach ($issues as $issue) {
            switch ($issue['type']) {
                case 'missing_file':
                    if ($this->createMissingFile($issue)) {
                        $fixed++;
                    }
                    break;

                case 'missing_key':
                    if ($this->addMissingKey($issue)) {
                        $fixed++;
                    }
                    break;
            }
        }

        $this->info("✅ Fixed {$fixed} issues automatically");

        if ($fixed < count($issues)) {
            $remaining = count($issues) - $fixed;
            $this->warn("⚠️  {$remaining} issues require manual attention");
        }
    }

    /**
     * 创建缺失的文件
     */
    private function createMissingFile(array $issue): bool
    {
        $locale = $issue['locale'];
        $filename = $issue['file'];
        $referenceLocale = $issue['reference_locale'] ?? 'zh';

        $targetFile = resource_path("lang/{$locale}/{$filename}.php");
        $referenceFile = resource_path("lang/{$referenceLocale}/{$filename}.php");

        if (!file_exists($referenceFile)) {
            return false;
        }

        // 创建目录
        $directory = dirname($targetFile);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // 复制参考文件作为模板
        $referenceContent = include $referenceFile;
        $content = "<?php\n\nreturn " . var_export($referenceContent, true) . ";\n";

        file_put_contents($targetFile, $content);

        $this->line("✅ Created: {$locale}/{$filename}.php");
        return true;
    }

    /**
     * 添加缺失的键
     */
    private function addMissingKey(array $issue): bool
    {
        $locale = $issue['locale'];
        $filename = $issue['file'];
        $key = $issue['key'];
        $referenceValue = $issue['reference_value'];

        $targetFile = resource_path("lang/{$locale}/{$filename}.php");

        if (!file_exists($targetFile)) {
            return false;
        }

        $translations = include $targetFile;

        // 设置嵌套键值
        $keys = explode('.', $key);
        $current = &$translations;

        foreach ($keys as $k) {
            if (!isset($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }

        $current = $referenceValue;

        // 写回文件
        $content = "<?php\n\nreturn " . var_export($translations, true) . ";\n";
        file_put_contents($targetFile, $content);

        $this->line("✅ Added key '{$key}' to {$locale}/{$filename}.php");
        return true;
    }

    /**
     * 生成报告
     */
    private function generateReport(array $results): void
    {
        $outputPath = $this->option('output');
        $format = $this->option('report');

        $content = match ($format) {
            'json' => json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'html' => $this->generateHtmlReport($results),
            default => $this->generateTextReport($results),
        };

        file_put_contents($outputPath, $content);
        $this->info("📄 Report saved to: {$outputPath}");
    }

    /**
     * 生成文本报告
     */
    private function generateTextReport(array $results): string
    {
        $report = "Translation Integrity Report\n";
        $report .= "Generated: " . now()->toDateTimeString() . "\n";
        $report .= str_repeat("=", 50) . "\n\n";

        $report .= "SUMMARY:\n";
        foreach ($results['summary'] as $key => $value) {
            $report .= "  " . ucwords(str_replace('_', ' ', $key)) . ": {$value}\n";
        }

        $report .= "\nISSUES:\n";
        foreach ($results['issues'] as $issue) {
            $report .= "  [{$issue['severity']}] {$issue['message']}\n";
        }

        $report .= "\nRECOMMENDATIONS:\n";
        foreach ($results['recommendations'] as $rec) {
            $report .= "  [{$rec['priority']}] {$rec['message']}\n";
            if (isset($rec['command'])) {
                $report .= "    Command: {$rec['command']}\n";
            }
        }

        return $report;
    }

    /**
     * 生成 HTML 报告
     */
    private function generateHtmlReport(array $results): string
    {
        // 简化的 HTML 报告
        return "<html><body><h1>Translation Integrity Report</h1><pre>" .
               htmlspecialchars($this->generateTextReport($results)) .
               "</pre></body></html>";
    }
}
