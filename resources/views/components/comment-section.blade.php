<div id="comments-section" class="comments-section">
    <div class="comments-header">
        <h3 class="comments-title">
            评论 ({{ $comments->count() }})
        </h3>
    </div>

    @if($showForm)
        @auth
            <!-- 评论表单 -->
            <div class="comment-form-container">
                <form class="comment-form" data-gist-id="{{ $gist->id }}">
                    @csrf
                    <div class="comment-form-header">
                        <div class="comment-avatar @if(!Auth::user()->avatar_url) {{ \App\Helpers\AvatarHelper::getAvatarClass(Auth::user()->name) }} @endif">
                            @if(Auth::user()->avatar_url)
                                <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}">
                            @else
                                {{ \App\Helpers\AvatarHelper::getInitials(Auth::user()->name) }}
                            @endif
                        </div>
                        <span class="comment-author">{{ Auth::user()->name }}</span>
                    </div>
                    <div class="comment-form-body">
                        <textarea name="content"
                                  class="comment-textarea"
                                  placeholder="写下你的评论..."
                                  rows="3"
                                  maxlength="1000"
                                  required></textarea>
                    </div>

                    <div class="comment-form-footer">
                        <div class="comment-form-info">
                            <span class="char-count">0/1000</span>
                        </div>
                        <div class="comment-form-actions">
                            <button type="button" class="btn-cancel">取消</button>
                            <button type="submit" class="btn-submit">发表评论</button>
                        </div>
                    </div>
                </form>
            </div>
        @else
            <div class="comment-login-prompt">
                <p>请 <a href="{{ route('login') }}" class="login-link">登录</a> 后发表评论</p>
            </div>
        @endauth
    @endif

    <!-- 评论列表 -->
    <div class="comments-list">
        @forelse($comments as $comment)
            @include('partials.comment-item', ['comment' => $comment, 'gist' => $gist])
        @empty
            <div class="no-comments">
                <p>还没有评论，来发表第一个评论吧！</p>
            </div>
        @endforelse
    </div>

    @if($comments->count() >= $limit)
        <div class="comments-load-more">
            <button type="button"
                    class="load-more-btn"
                    data-gist-id="{{ $gist->id }}"
                    data-offset="{{ $limit }}">
                加载更多评论
            </button>
        </div>
    @endif
</div>