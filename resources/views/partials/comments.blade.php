<div class="comments-container">
    @forelse($comments as $comment)
        @include('partials.comment-item', ['comment' => $comment, 'gist' => $gist])
    @empty
        <div class="no-comments">
            <p>还没有评论，来发表第一个评论吧！</p>
        </div>
    @endforelse

    @if($comments->hasMorePages())
        <div class="comments-pagination">
            {{ $comments->links() }}
        </div>
    @endif
</div>
