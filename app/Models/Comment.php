<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gist_id',
        'parent_id',
        'content',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    // 作用域
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeRootComments($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeByGist($query, $gistId)
    {
        return $query->where('gist_id', $gistId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // 访问器
    public function getIsReplyAttribute()
    {
        return !is_null($this->parent_id);
    }

    public function getDepthAttribute()
    {
        $depth = 0;
        $comment = $this;
        while ($comment->parent_id) {
            $depth++;
            $comment = $comment->parent;
        }
        return $depth;
    }

    // 实例方法
    public function approve()
    {
        $this->update(['is_approved' => true]);
    }

    public function reject()
    {
        $this->update(['is_approved' => false]);
    }

    public function canReply()
    {
        return $this->depth < 3; // 最多3层嵌套
    }

    public function getApprovedReplies()
    {
        return $this->replies()->approved()->recent()->get();
    }

    // 静态方法
    public static function getCommentCount($gistId)
    {
        return static::where('gist_id', $gistId)
                    ->approved()
                    ->count();
    }

    public static function getRecentComments($gistId, $limit = 5)
    {
        return static::where('gist_id', $gistId)
                    ->approved()
                    ->rootComments()
                    ->with(['user', 'replies.user'])
                    ->recent()
                    ->limit($limit)
                    ->get();
    }
}
