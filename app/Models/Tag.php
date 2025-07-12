<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'usage_count',
        'is_featured',
    ];

    protected $casts = [
        'usage_count' => 'integer',
        'is_featured' => 'boolean',
    ];

    // 关联关系
    public function gists(): BelongsToMany
    {
        return $this->belongsToMany(Gist::class, 'gist_tags');
    }

    // 模型事件
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name')) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    // 作用域
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePopular($query)
    {
        return $query->orderBy('usage_count', 'desc');
    }

    public function scopeByName($query, $name)
    {
        return $query->where('name', 'like', "%{$name}%");
    }

    public function scopeWithMinUsage($query, $minUsage = 1)
    {
        return $query->where('usage_count', '>=', $minUsage);
    }

    // 访问器
    public function getDisplayNameAttribute()
    {
        return $this->name;
    }

    public function getColorClassAttribute()
    {
        $colors = [
            'blue' => 'bg-blue-100 text-blue-800',
            'green' => 'bg-green-100 text-green-800',
            'yellow' => 'bg-yellow-100 text-yellow-800',
            'red' => 'bg-red-100 text-red-800',
            'purple' => 'bg-purple-100 text-purple-800',
            'pink' => 'bg-pink-100 text-pink-800',
            'indigo' => 'bg-indigo-100 text-indigo-800',
            'gray' => 'bg-gray-100 text-gray-800',
        ];

        return $colors[$this->color] ?? $colors['blue'];
    }

    // 静态方法
    public static function getPopularTags($limit = 20)
    {
        return static::popular()
            ->withMinUsage(1)
            ->limit($limit)
            ->get();
    }

    public static function searchTags($query, $limit = 10)
    {
        return static::byName($query)
            ->popular()
            ->limit($limit)
            ->get();
    }

    public static function getAvailableColors()
    {
        return [
            'blue' => '蓝色',
            'green' => '绿色',
            'yellow' => '黄色',
            'red' => '红色',
            'purple' => '紫色',
            'pink' => '粉色',
            'indigo' => '靛蓝',
            'gray' => '灰色',
        ];
    }

    // 实例方法
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    public function decrementUsage()
    {
        if ($this->usage_count > 0) {
            $this->decrement('usage_count');
        }
    }

    public function getRelatedTags($limit = 5)
    {
        // 获取与当前标签经常一起使用的标签
        return static::whereHas('gists', function ($query) {
                $query->whereHas('tags', function ($subQuery) {
                    $subQuery->where('tags.id', $this->id);
                });
            })
            ->where('id', '!=', $this->id)
            ->withCount('gists')
            ->orderBy('gists_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
