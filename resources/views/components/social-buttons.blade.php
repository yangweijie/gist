<div class="social-buttons {{ $vertical ? 'social-buttons-vertical' : 'social-buttons-horizontal' }} social-buttons-{{ $size }}"
     data-gist-id="{{ $gist->id }}">

    <!-- 点赞按钮 -->
    <button type="button"
            class="social-btn like-btn {{ $socialData['is_liked'] ? 'active' : '' }}"
            data-action="like"
            data-gist-id="{{ $gist->id }}"
            title="{{ $socialData['is_liked'] ? '取消点赞' : '点赞' }}">
        <svg class="social-icon" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
        </svg>
        @if($showCounts)
            <span class="social-count like-count">{{ $socialData['like_count'] }}</span>
        @endif
    </button>

    <!-- 收藏按钮 -->
    <button type="button"
            class="social-btn favorite-btn {{ $socialData['is_favorited'] ? 'active' : '' }}"
            data-action="favorite"
            data-gist-id="{{ $gist->id }}"
            title="{{ $socialData['is_favorited'] ? '取消收藏' : '收藏' }}">
        <svg class="social-icon" fill="currentColor" viewBox="0 0 20 20">
            <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"></path>
        </svg>
        @if($showCounts)
            <span class="social-count favorite-count">{{ $socialData['favorite_count'] }}</span>
        @endif
    </button>

    <!-- 评论按钮 -->
    <button type="button"
            class="social-btn comment-btn"
            data-action="comment"
            data-gist-id="{{ $gist->id }}"
            title="查看评论">
        <svg class="social-icon" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"></path>
        </svg>
        @if($showCounts)
            <span class="social-count comment-count">{{ $socialData['comment_count'] }}</span>
        @endif
    </button>

    <!-- 分享按钮 -->
    <button type="button"
            class="social-btn share-btn"
            data-action="share"
            data-gist-id="{{ $gist->id }}"
            data-url="{{ route('gists.show', $gist) }}"
            data-title="{{ $gist->title }}"
            title="分享">
        <svg class="social-icon" fill="currentColor" viewBox="0 0 20 20">
            <path d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z"></path>
        </svg>
        <span class="social-label">分享</span>
    </button>
</div>

<style>
.social-buttons {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.social-buttons-vertical {
    flex-direction: column;
    align-items: stretch;
}

.social-buttons-horizontal {
    flex-direction: row;
}

.social-btn {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.5rem;
    background: transparent;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.social-btn:hover {
    background: #f9fafb;
    border-color: #d1d5db;
}

.social-btn.active {
    color: #ef4444;
    border-color: #ef4444;
    background: #fef2f2;
}

.social-btn.like-btn.active {
    color: #ef4444;
    border-color: #ef4444;
    background: #fef2f2;
}

.social-btn.favorite-btn.active {
    color: #f59e0b;
    border-color: #f59e0b;
    background: #fffbeb;
}

.social-icon {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
}

.social-count {
    font-weight: 500;
    min-width: 1rem;
    text-align: center;
}

.social-label {
    font-weight: 500;
}

/* 尺寸变体 */
.social-buttons-small .social-btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.social-buttons-small .social-icon {
    width: 0.875rem;
    height: 0.875rem;
}

.social-buttons-large .social-btn {
    padding: 0.75rem 1rem;
    font-size: 1rem;
}

.social-buttons-large .social-icon {
    width: 1.25rem;
    height: 1.25rem;
}

/* 加载状态 */
.social-btn.loading {
    opacity: 0.6;
    pointer-events: none;
}

.social-btn.loading .social-icon {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* 响应式设计 */
@media (max-width: 640px) {
    .social-buttons-horizontal {
        flex-wrap: wrap;
    }

    .social-btn {
        flex: 1;
        min-width: 0;
        justify-content: center;
    }

    .social-count {
        display: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 绑定社交按钮事件
    document.addEventListener('click', function(e) {
        const socialBtn = e.target.closest('.social-btn');
        if (!socialBtn) return;

        const action = socialBtn.dataset.action;
        const gistId = socialBtn.dataset.gistId;

        switch (action) {
            case 'like':
                handleLike(socialBtn, gistId);
                break;
            case 'favorite':
                handleFavorite(socialBtn, gistId);
                break;
            case 'comment':
                handleComment(socialBtn, gistId);
                break;
            case 'share':
                handleShare(socialBtn);
                break;
        }
    });

    function handleLike(btn, gistId) {
        if (btn.classList.contains('loading')) return;

        btn.classList.add('loading');

        fetch(`/social/likes/${gistId}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                btn.classList.toggle('active', data.is_liked);
                const countElement = btn.querySelector('.like-count');
                if (countElement) {
                    countElement.textContent = data.like_count;
                }
                btn.title = data.is_liked ? '取消点赞' : '点赞';

                if (window.htmxConfig) {
                    window.htmxConfig.showNotification('success', data.message);
                }
            } else {
                if (window.htmxConfig) {
                    window.htmxConfig.showNotification('error', data.message);
                }
            }
        })
        .catch(error => {
            console.error('点赞失败:', error);
            if (window.htmxConfig) {
                window.htmxConfig.showNotification('error', '操作失败，请重试');
            }
        })
        .finally(() => {
            btn.classList.remove('loading');
        });
    }

    function handleFavorite(btn, gistId) {
        if (btn.classList.contains('loading')) return;

        btn.classList.add('loading');

        fetch(`/social/favorites/${gistId}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                btn.classList.toggle('active', data.is_favorited);
                const countElement = btn.querySelector('.favorite-count');
                if (countElement) {
                    countElement.textContent = data.favorite_count;
                }
                btn.title = data.is_favorited ? '取消收藏' : '收藏';

                if (window.htmxConfig) {
                    window.htmxConfig.showNotification('success', data.message);
                }
            } else {
                if (window.htmxConfig) {
                    window.htmxConfig.showNotification('error', data.message);
                }
            }
        })
        .catch(error => {
            console.error('收藏失败:', error);
            if (window.htmxConfig) {
                window.htmxConfig.showNotification('error', '操作失败，请重试');
            }
        })
        .finally(() => {
            btn.classList.remove('loading');
        });
    }

    function handleComment(btn, gistId) {
        // 滚动到评论区域或打开评论模态框
        const commentsSection = document.getElementById('comments-section');
        if (commentsSection) {
            commentsSection.scrollIntoView({ behavior: 'smooth' });
        } else {
            // 如果没有评论区域，可以打开模态框或跳转到详情页
            window.location.href = `/gists/${gistId}#comments`;
        }
    }

    function handleShare(btn) {
        const url = btn.dataset.url;
        const title = btn.dataset.title;

        if (navigator.share) {
            navigator.share({
                title: title,
                url: url
            }).catch(console.error);
        } else {
            // 复制链接到剪贴板
            navigator.clipboard.writeText(url).then(() => {
                if (window.htmxConfig) {
                    window.htmxConfig.showNotification('success', '链接已复制到剪贴板');
                }
            }).catch(() => {
                // 降级方案：显示分享链接
                prompt('复制链接:', url);
            });
        }
    }
});
</script>