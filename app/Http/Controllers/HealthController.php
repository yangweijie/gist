<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Gist;

class HealthController extends Controller
{
    /**
     * 系统健康检查
     */
    public function check(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
            'memory' => $this->checkMemory(),
            'disk' => $this->checkDisk(),
        ];

        $overall = collect($checks)->every(fn($check) => $check['status'] === 'ok');

        return response()->json([
            'status' => $overall ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
            'version' => config('app.version', '1.0.0'),
            'environment' => app()->environment(),
        ], $overall ? 200 : 503);
    }

    /**
     * 简单的健康检查（用于负载均衡器）
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * 详细的系统状态
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'application' => [
                'name' => config('app.name'),
                'version' => config('app.version', '1.0.0'),
                'environment' => app()->environment(),
                'debug' => config('app.debug'),
                'timezone' => config('app.timezone'),
                'locale' => app()->getLocale(),
            ],
            'system' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server_time' => now()->toISOString(),
                'uptime' => $this->getUptime(),
            ],
            'database' => [
                'connection' => config('database.default'),
                'users_count' => User::count(),
                'gists_count' => Gist::count(),
            ],
            'performance' => [
                'memory_usage' => $this->formatBytes(memory_get_usage(true)),
                'memory_peak' => $this->formatBytes(memory_get_peak_usage(true)),
                'memory_limit' => ini_get('memory_limit'),
            ],
        ]);
    }

    /**
     * 检查数据库连接
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $time = microtime(true);
            DB::select('SELECT 1');
            $responseTime = round((microtime(true) - $time) * 1000, 2);

            return [
                'status' => 'ok',
                'message' => 'Database connection successful',
                'response_time_ms' => $responseTime,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * 检查缓存系统
     */
    private function checkCache(): array
    {
        try {
            $key = 'health_check_' . time();
            $value = 'test_value';
            
            Cache::put($key, $value, 60);
            $retrieved = Cache::get($key);
            Cache::forget($key);

            if ($retrieved === $value) {
                return [
                    'status' => 'ok',
                    'message' => 'Cache system working',
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Cache value mismatch',
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cache system failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * 检查存储系统
     */
    private function checkStorage(): array
    {
        try {
            $testFile = 'health_check_' . time() . '.txt';
            $testContent = 'health check test';

            Storage::put($testFile, $testContent);
            $retrieved = Storage::get($testFile);
            Storage::delete($testFile);

            if ($retrieved === $testContent) {
                return [
                    'status' => 'ok',
                    'message' => 'Storage system working',
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Storage content mismatch',
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Storage system failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * 检查队列系统
     */
    private function checkQueue(): array
    {
        try {
            // 简单检查队列配置
            $connection = config('queue.default');
            
            return [
                'status' => 'ok',
                'message' => 'Queue system configured',
                'connection' => $connection,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Queue system failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * 检查内存使用
     */
    private function checkMemory(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->parseBytes(ini_get('memory_limit'));
        $memoryPercent = ($memoryUsage / $memoryLimit) * 100;

        $status = $memoryPercent > 90 ? 'warning' : 'ok';
        if ($memoryPercent > 95) {
            $status = 'error';
        }

        return [
            'status' => $status,
            'message' => sprintf('Memory usage: %s (%.1f%%)', $this->formatBytes($memoryUsage), $memoryPercent),
            'usage_bytes' => $memoryUsage,
            'limit_bytes' => $memoryLimit,
            'usage_percent' => round($memoryPercent, 1),
        ];
    }

    /**
     * 检查磁盘空间
     */
    private function checkDisk(): array
    {
        $path = storage_path();
        $freeBytes = disk_free_space($path);
        $totalBytes = disk_total_space($path);
        $usedPercent = (($totalBytes - $freeBytes) / $totalBytes) * 100;

        $status = $usedPercent > 90 ? 'warning' : 'ok';
        if ($usedPercent > 95) {
            $status = 'error';
        }

        return [
            'status' => $status,
            'message' => sprintf('Disk usage: %.1f%%', $usedPercent),
            'free_bytes' => $freeBytes,
            'total_bytes' => $totalBytes,
            'used_percent' => round($usedPercent, 1),
        ];
    }

    /**
     * 获取系统运行时间
     */
    private function getUptime(): string
    {
        if (function_exists('sys_getloadavg')) {
            $uptime = file_get_contents('/proc/uptime');
            if ($uptime) {
                $seconds = (int) explode(' ', $uptime)[0];
                return $this->formatUptime($seconds);
            }
        }
        
        return 'Unknown';
    }

    /**
     * 格式化运行时间
     */
    private function formatUptime(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        return sprintf('%d days, %d hours, %d minutes', $days, $hours, $minutes);
    }

    /**
     * 格式化字节数
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * 解析字节数字符串
     */
    private function parseBytes(string $val): int
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $val = (int) $val;
        
        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        
        return $val;
    }
}
