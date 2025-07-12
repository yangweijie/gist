<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Gist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'github_gist_id',
        'title',
        'description',
        'content',
        'language',
        'filename',
        'is_public',
        'is_synced',
        'views_count',
        'likes_count',
        'comments_count',
        'favorites_count',
        'github_created_at',
        'github_updated_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_synced' => 'boolean',
        'views_count' => 'integer',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'favorites_count' => 'integer',
        'github_created_at' => 'datetime',
        'github_updated_at' => 'datetime',
    ];

    // 关联关系
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'gist_tags');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    // 作用域
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    public function scopePopular($query)
    {
        return $query->orderBy('likes_count', 'desc');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
