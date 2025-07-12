<div class="comment-item" data-comment-id="{{ $comment->id }}">
    <div class="comment-content">
        <div class="comment-header">
            <div class="comment-avatar @if(!$comment->user->avatar_url) {{ \App\Helpers\AvatarHelper::getAvatarClass($comment->user->name) }} @endif">
                @if($comment->user->avatar_url)
                    <img src="{{ $comment->user->avatar_url }}" alt="{{ $comment->user->name }}">
                @else
                    {{ \App\Helpers\AvatarHelper::getInitials($comment->user->name) }}
                @endif
            </div>
            <div class="comment-meta">
                <span class="comment-author">{{ $comment->user->name }}</span>
                <span class="comment-time" title="{{ $comment->created_at->format('Y-m-d H:i:s') }}">
                    {{ $comment->created_at->diffForHumans() }}
                </span>
            </div>
            @auth
                @if($comment->user_id === Auth::id())
                    <div class="comment-actions">
                        <button type="button" 
                                class="comment-action-btn delete-comment-btn"
                                data-comment-id="{{ $comment->id }}"
                                title="删除评论">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                @endif
            @endauth
        </div>
        
        <div class="comment-body">
            <p class="comment-text">{{ $comment->content }}</p>
        </div>
        
        <div class="comment-footer">
            @auth
                @if($comment->canReply())
                    <button type="button" 
                            class="reply-btn"
                            data-comment-id="{{ $comment->id }}">
                        回复
                    </button>
                @endif
            @endauth
        </div>
    </div>

    <!-- 回复表单（隐藏） -->
    @auth
        <div class="reply-form-container" style="display: none;">
            <form class="reply-form" data-parent-id="{{ $comment->id }}" data-gist-id="{{ $gist->id }}">
                @csrf
                <div class="reply-form-header">
                    <div class="reply-avatar @if(!Auth::user()->avatar_url) {{ \App\Helpers\AvatarHelper::getAvatarClass(Auth::user()->name) }} @endif">
                        @if(Auth::user()->avatar_url)
                            <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}">
                        @else
                            {{ \App\Helpers\AvatarHelper::getInitials(Auth::user()->name) }}
                        @endif
                    </div>
                    <span class="reply-to">回复 {{ $comment->user->name }}:</span>
                </div>
                <div class="reply-form-body">
                    <textarea name="content"
                              class="reply-textarea"
                              placeholder="写下你的回复..."
                              rows="2"
                              maxlength="1000"
                              required></textarea>
                </div>

                <div class="reply-form-footer">
                    <div class="comment-form-info">
                        <span class="char-count">0/1000</span>
                    </div>
                    <div class="reply-form-actions">
                        <button type="button" class="btn-cancel-reply">取消</button>
                        <button type="submit" class="btn-submit-reply">发表回复</button>
                    </div>
                </div>
            </form>
        </div>
    @endauth

    <!-- 回复列表 -->
    @if($comment->replies->count() > 0)
        <div class="replies-list">
            @foreach($comment->replies as $reply)
                <div class="reply-item" data-comment-id="{{ $reply->id }}">
                    <div class="reply-content">
                        <div class="reply-header">
                            <div class="reply-avatar @if(!$reply->user->avatar_url) {{ \App\Helpers\AvatarHelper::getAvatarClass($reply->user->name) }} @endif">
                                @if($reply->user->avatar_url)
                                    <img src="{{ $reply->user->avatar_url }}" alt="{{ $reply->user->name }}">
                                @else
                                    {{ \App\Helpers\AvatarHelper::getInitials($reply->user->name) }}
                                @endif
                            </div>
                            <div class="reply-meta">
                                <span class="reply-author">{{ $reply->user->name }}</span>
                                <span class="reply-time" title="{{ $reply->created_at->format('Y-m-d H:i:s') }}">
                                    {{ $reply->created_at->diffForHumans() }}
                                </span>
                            </div>
                            @auth
                                @if($reply->user_id === Auth::id())
                                    <div class="reply-actions">
                                        <button type="button" 
                                                class="comment-action-btn delete-comment-btn"
                                                data-comment-id="{{ $reply->id }}"
                                                title="删除回复">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @endif
                            @endauth
                        </div>
                        
                        <div class="reply-body">
                            <p class="reply-text">{{ $reply->content }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
