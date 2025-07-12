<?php

namespace App\View\Components;

use App\Models\Gist;
use App\Models\Comment;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CommentSection extends Component
{
    public Gist $gist;
    public int $limit;
    public bool $showForm;

    /**
     * Create a new component instance.
     */
    public function __construct(
        Gist $gist,
        int $limit = 10,
        bool $showForm = true
    ) {
        $this->gist = $gist;
        $this->limit = $limit;
        $this->showForm = $showForm;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $comments = Comment::where('gist_id', $this->gist->id)
            ->approved()
            ->rootComments()
            ->with(['user', 'replies' => function ($query) {
                $query->approved()->with('user')->orderBy('created_at', 'asc');
            }])
            ->orderBy('created_at', 'desc')
            ->limit($this->limit)
            ->get();

        return view('components.comment-section', [
            'comments' => $comments
        ]);
    }
}
