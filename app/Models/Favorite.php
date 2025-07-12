<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Favorite extends Model
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
        $favorite = static::where('user_id', $userId)
                         ->where('gist_id', $gistId)
                         ->first();

        if ($favorite) {
            $favorite->delete();
            return false; // 取消收藏
        } else {
            static::create([
                'user_id' => $userId,
                'gist_id' => $gistId,
            ]);
            return true; // 收藏
        }
    }

    public static function isFavorited($userId, $gistId)
    {
        return static::where('user_id', $userId)
                    ->where('gist_id', $gistId)
                    ->exists();
    }

    public static function getFavoriteCount($gistId)
    {
        return static::where('gist_id', $gistId)->count();
    }

    public static function getUserFavorites($userId, $limit = null)
    {
        $query = static::where('user_id', $userId)
                      ->with(['gist.user', 'gist.tags'])
                      ->orderBy('created_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
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

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
