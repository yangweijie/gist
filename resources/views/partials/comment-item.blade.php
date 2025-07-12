<div class="comment-item" data-comment-id="{{ $comment->id }}">
    <div class="comment-content">
        <div class="comment-header">
            <img src="{{ $comment->user->avatar_url ?? '/images/default-avatar.png' }}" 
                 alt="{{ $comment->user->name }}" 
                 class="comment-avatar">
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
                    <img src="{{ Auth::user()->avatar_url ?? '/images/default-avatar.png' }}" 
                         alt="{{ Auth::user()->name }}" 
                         class="comment-avatar">
                    <span class="reply-to">回复 {{ $comment->user->name }}:</span>
                </div>
                <div class="reply-form-body">
                    <textarea name="content" 
                              class="reply-textarea" 
                              placeholder="写下你的回复..."
                              rows="2"
                              maxlength="1000"
                              required></textarea>
                    <div class="reply-form-footer">
                        <div class="reply-form-actions">
                            <button type="button" class="btn-cancel-reply">取消</button>
                            <button type="submit" class="btn-submit-reply">发表回复</button>
                        </div>
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
                            <img src="{{ $reply->user->avatar_url ?? '/images/default-avatar.png' }}" 
                                 alt="{{ $reply->user->name }}" 
                                 class="reply-avatar">
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

<style>
.comment-item {
    border-bottom: 1px solid #e5e7eb;
    padding: 1rem 0;
}

.comment-content {
    margin-bottom: 0.5rem;
}

.comment-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}

.comment-avatar {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    object-fit: cover;
}

.comment-meta {
    flex: 1;
}

.comment-author {
    font-weight: 500;
    color: #374151;
    margin-right: 0.5rem;
}

.comment-time {
    color: #6b7280;
    font-size: 0.875rem;
}

.comment-actions {
    display: flex;
    gap: 0.25rem;
}

.comment-action-btn {
    padding: 0.25rem;
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    border-radius: 0.25rem;
    transition: all 0.2s;
}

.comment-action-btn:hover {
    color: #ef4444;
    background: #fef2f2;
}

.comment-body {
    margin-left: 2.75rem;
    margin-bottom: 0.5rem;
}

.comment-text {
    color: #374151;
    line-height: 1.5;
    white-space: pre-wrap;
    word-break: break-word;
}

.comment-footer {
    margin-left: 2.75rem;
}

.reply-btn {
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    font-size: 0.875rem;
    padding: 0.25rem 0;
    transition: color 0.2s;
}

.reply-btn:hover {
    color: #374151;
}

/* 回复样式 */
.replies-list {
    margin-left: 2.75rem;
    margin-top: 0.5rem;
    border-left: 2px solid #e5e7eb;
    padding-left: 1rem;
}

.reply-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.reply-item:last-child {
    border-bottom: none;
}

.reply-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.reply-avatar {
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
    object-fit: cover;
}

.reply-meta {
    flex: 1;
}

.reply-author {
    font-weight: 500;
    color: #374151;
    margin-right: 0.5rem;
    font-size: 0.875rem;
}

.reply-time {
    color: #6b7280;
    font-size: 0.75rem;
}

.reply-actions {
    display: flex;
    gap: 0.25rem;
}

.reply-text {
    color: #374151;
    line-height: 1.5;
    white-space: pre-wrap;
    word-break: break-word;
    font-size: 0.875rem;
}

/* 回复表单样式 */
.reply-form-container {
    margin-left: 2.75rem;
    margin-top: 0.5rem;
    padding: 0.75rem;
    background: #f9fafb;
    border-radius: 0.5rem;
}

.reply-form-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.reply-to {
    font-size: 0.875rem;
    color: #6b7280;
}

.reply-textarea {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem;
    font-size: 0.875rem;
    resize: vertical;
    min-height: 60px;
}

.reply-form-footer {
    margin-top: 0.5rem;
    display: flex;
    justify-content: flex-end;
}

.reply-form-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-cancel-reply,
.btn-submit-reply {
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel-reply {
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    color: #374151;
}

.btn-cancel-reply:hover {
    background: #e5e7eb;
}

.btn-submit-reply {
    background: #3b82f6;
    border: 1px solid #3b82f6;
    color: white;
}

.btn-submit-reply:hover {
    background: #2563eb;
}

.btn-submit-reply:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
