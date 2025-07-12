<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gist_id',
    ];

    // 关联关系
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gist(): BelongsTo
    {
        return $this->belongsTo(Gist::class);
    }

    // 静态方法
    public static function toggle($userId, $gistId)
    {
        $like = static::where('user_id', $userId)
                     ->where('gist_id', $gistId)
                     ->first();

        if ($like) {
            $like->delete();
            return false; // 取消点赞
        } else {
            static::create([
                'user_id' => $userId,
                'gist_id' => $gistId,
            ]);
            return true; // 点赞
        }
    }

    public static function isLiked($userId, $gistId)
    {
        return static::where('user_id', $userId)
                    ->where('gist_id', $gistId)
                    ->exists();
    }

    public static function getLikeCount($gistId)
    {
        return static::where('gist_id', $gistId)->count();
    }

    // 作用域
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByGist($query, $gistId)
    {
        return $query->where('gist_id', $gistId);
    }
}
