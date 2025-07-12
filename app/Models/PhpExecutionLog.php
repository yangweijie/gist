<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PhpExecutionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gist_id',
        'code',
        'output',
        'error',
        'execution_time_ms',
        'memory_usage_bytes',
        'ip_address',
        'user_agent',
        'is_successful',
        'metadata',
    ];

    protected $casts = [
        'is_successful' => 'boolean',
        'metadata' => 'array',
        'execution_time_ms' => 'integer',
        'memory_usage_bytes' => 'integer',
    ];

    /**
     * 关联到用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 关联到 Gist
     */
    public function gist(): BelongsTo
    {
        return $this->belongsTo(Gist::class);
    }

    /**
     * 获取格式化的执行时间
     */
    public function getFormattedExecutionTimeAttribute(): string
    {
        if (!$this->execution_time_ms) {
            return 'N/A';
        }

        if ($this->execution_time_ms < 1000) {
            return $this->execution_time_ms . ' ms';
        }

        return round($this->execution_time_ms / 1000, 2) . ' s';
    }

    /**
     * 获取格式化的内存使用
     */
    public function getFormattedMemoryUsageAttribute(): string
    {
        if (!$this->memory_usage_bytes) {
            return 'N/A';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->memory_usage_bytes;
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * 获取执行状态文本
     */
    public function getStatusTextAttribute(): string
    {
        return $this->is_successful ? '成功' : '失败';
    }

    /**
     * 获取代码预览（前100个字符）
     */
    public function getCodePreviewAttribute(): string
    {
        return strlen($this->code) > 100
            ? substr($this->code, 0, 100) . '...'
            : $this->code;
    }

    /**
     * 获取输出预览（前200个字符）
     */
    public function getOutputPreviewAttribute(): string
    {
        if (!$this->output) {
            return '';
        }

        return strlen($this->output) > 200
            ? substr($this->output, 0, 200) . '...'
            : $this->output;
    }

    /**
     * 作用域：成功的执行
     */
    public function scopeSuccessful($query)
    {
        return $query->where('is_successful', true);
    }

    /**
     * 作用域：失败的执行
     */
    public function scopeFailed($query)
    {
        return $query->where('is_successful', false);
    }

    /**
     * 作用域：特定用户的执行
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * 作用域：特定 Gist 的执行
     */
    public function scopeForGist($query, $gistId)
    {
        return $query->where('gist_id', $gistId);
    }

    /**
     * 作用域：最近的执行
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
