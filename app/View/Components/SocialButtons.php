<?php

namespace App\View\Components;

use App\Models\Gist;
use App\Models\Like;
use App\Models\Favorite;
use App\Models\Comment;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Facades\Auth;

class SocialButtons extends Component
{
    public Gist $gist;
    public bool $showCounts;
    public string $size;
    public bool $vertical;

    /**
     * Create a new component instance.
     */
    public function __construct(
        Gist $gist,
        bool $showCounts = true,
        string $size = 'medium',
        bool $vertical = false
    ) {
        $this->gist = $gist;
        $this->showCounts = $showCounts;
        $this->size = $size;
        $this->vertical = $vertical;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $userId = Auth::id();

        $socialData = [
            'is_liked' => $userId ? Like::isLiked($userId, $this->gist->id) : false,
            'is_favorited' => $userId ? Favorite::isFavorited($userId, $this->gist->id) : false,
            'like_count' => $this->gist->likes_count ?? Like::getLikeCount($this->gist->id),
            'favorite_count' => $this->gist->favorites_count ?? Favorite::getFavoriteCount($this->gist->id),
            'comment_count' => $this->gist->comments_count ?? Comment::getCommentCount($this->gist->id),
        ];

        return view('components.social-buttons', [
            'socialData' => $socialData
        ]);
    }
}
