/**
 * 评论功能 JavaScript
 */

class CommentManager {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.initCharacterCount();
    }

    bindEvents() {
        // 评论表单提交
        document.addEventListener('submit', (e) => {
            if (e.target.classList.contains('comment-form')) {
                e.preventDefault();
                this.submitComment(e.target);
            }
        });

        // 回复表单提交
        document.addEventListener('submit', (e) => {
            if (e.target.classList.contains('reply-form')) {
                e.preventDefault();
                this.submitReply(e.target);
            }
        });

        // 回复按钮点击
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('reply-btn')) {
                e.preventDefault();
                this.toggleReplyForm(e.target);
            }
        });

        // 取消回复按钮
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-cancel-reply')) {
                e.preventDefault();
                this.hideReplyForm(e.target);
            }
        });

        // 取消评论按钮
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-cancel')) {
                e.preventDefault();
                this.clearCommentForm(e.target);
            }
        });

        // 删除评论按钮
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('delete-comment-btn')) {
                e.preventDefault();
                this.deleteComment(e.target);
            }
        });

        // 字符计数
        document.addEventListener('input', (e) => {
            if (e.target.classList.contains('comment-textarea') || 
                e.target.classList.contains('reply-textarea')) {
                this.updateCharacterCount(e.target);
            }
        });

        // 加载更多评论
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('load-more-btn')) {
                e.preventDefault();
                this.loadMoreComments(e.target);
            }
        });
    }

    initCharacterCount() {
        // 初始化字符计数
        document.querySelectorAll('.comment-textarea, .reply-textarea').forEach(textarea => {
            this.updateCharacterCount(textarea);
        });
    }

    updateCharacterCount(textarea) {
        const maxLength = textarea.getAttribute('maxlength') || 1000;
        const currentLength = textarea.value.length;
        const charCountElement = textarea.closest('form').querySelector('.char-count');
        
        if (charCountElement) {
            charCountElement.textContent = `${currentLength}/${maxLength}`;
            
            // 接近限制时改变颜色
            if (currentLength > maxLength * 0.9) {
                charCountElement.style.color = '#ef4444';
            } else if (currentLength > maxLength * 0.8) {
                charCountElement.style.color = '#f59e0b';
            } else {
                charCountElement.style.color = '#6b7280';
            }
        }
    }

    async submitComment(form) {
        const submitBtn = form.querySelector('.btn-submit');
        const textarea = form.querySelector('.comment-textarea');
        const content = textarea.value.trim();

        if (!content) {
            this.showMessage('请输入评论内容', 'error');
            return;
        }

        // 禁用提交按钮
        submitBtn.disabled = true;
        submitBtn.textContent = '发表中...';

        try {
            const formData = new FormData(form);
            const gistId = form.dataset.gistId;

            const response = await fetch(`/social/comments/${gistId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const result = await response.json();

            if (response.ok) {
                this.showMessage('评论发表成功', 'success');
                this.clearCommentForm(form);
                this.refreshComments(gistId);
            } else {
                this.showMessage(result.message || '评论发表失败', 'error');
            }
        } catch (error) {
            console.error('Submit comment error:', error);
            this.showMessage('网络错误，请稍后重试', 'error');
        } finally {
            // 恢复提交按钮
            submitBtn.disabled = false;
            submitBtn.textContent = '发表评论';
        }
    }

    async submitReply(form) {
        const submitBtn = form.querySelector('.btn-submit-reply');
        const textarea = form.querySelector('.reply-textarea');
        const content = textarea.value.trim();

        if (!content) {
            this.showMessage('请输入回复内容', 'error');
            return;
        }

        // 禁用提交按钮
        submitBtn.disabled = true;
        submitBtn.textContent = '回复中...';

        try {
            const formData = new FormData(form);
            const gistId = form.dataset.gistId;
            const parentId = form.dataset.parentId;

            // 添加parent_id到表单数据
            if (parentId) {
                formData.append('parent_id', parentId);
            }

            const response = await fetch(`/social/comments/${gistId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const result = await response.json();

            if (response.ok) {
                this.showMessage('回复发表成功', 'success');
                this.hideReplyForm(form);
                this.refreshComments(gistId);
            } else {
                this.showMessage(result.message || '回复发表失败', 'error');
            }
        } catch (error) {
            console.error('Submit reply error:', error);
            this.showMessage('网络错误，请稍后重试', 'error');
        } finally {
            // 恢复提交按钮
            submitBtn.disabled = false;
            submitBtn.textContent = '发表回复';
        }
    }

    toggleReplyForm(button) {
        const commentItem = button.closest('.comment-item');
        const replyFormContainer = commentItem.querySelector('.reply-form-container');
        
        if (replyFormContainer) {
            const isVisible = replyFormContainer.style.display !== 'none';
            
            if (isVisible) {
                replyFormContainer.style.display = 'none';
            } else {
                // 隐藏其他回复表单
                document.querySelectorAll('.reply-form-container').forEach(container => {
                    container.style.display = 'none';
                });
                
                replyFormContainer.style.display = 'block';
                const textarea = replyFormContainer.querySelector('.reply-textarea');
                if (textarea) {
                    textarea.focus();
                }
            }
        }
    }

    hideReplyForm(element) {
        const replyFormContainer = element.closest('.reply-form-container');
        if (replyFormContainer) {
            replyFormContainer.style.display = 'none';
            const textarea = replyFormContainer.querySelector('.reply-textarea');
            if (textarea) {
                textarea.value = '';
            }
        }
    }

    clearCommentForm(element) {
        const form = element.closest('.comment-form');
        if (form) {
            const textarea = form.querySelector('.comment-textarea');
            if (textarea) {
                textarea.value = '';
                this.updateCharacterCount(textarea);
            }
        }
    }

    async deleteComment(button) {
        if (!confirm('确定要删除这条评论吗？')) {
            return;
        }

        const commentId = button.dataset.commentId;
        
        try {
            const response = await fetch(`/social/comments/${commentId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            const result = await response.json();

            if (response.ok) {
                this.showMessage('评论删除成功', 'success');
                // 移除评论元素
                const commentItem = button.closest('.comment-item, .reply-item');
                if (commentItem) {
                    commentItem.remove();
                }
            } else {
                this.showMessage(result.message || '删除失败', 'error');
            }
        } catch (error) {
            console.error('Delete comment error:', error);
            this.showMessage('网络错误，请稍后重试', 'error');
        }
    }

    async loadMoreComments(button) {
        const gistId = button.dataset.gistId;
        const offset = parseInt(button.dataset.offset) || 0;

        button.disabled = true;
        button.textContent = '加载中...';

        try {
            const response = await fetch(`/social/comments/${gistId}?offset=${offset}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const result = await response.json();

            if (response.ok && result.html) {
                // 插入新评论
                const commentsList = document.querySelector('.comments-list');
                if (commentsList) {
                    commentsList.insertAdjacentHTML('beforeend', result.html);
                }

                // 更新偏移量
                button.dataset.offset = offset + result.count;

                // 如果没有更多评论，隐藏按钮
                if (!result.hasMore) {
                    button.parentElement.remove();
                }
            } else {
                this.showMessage('加载失败', 'error');
            }
        } catch (error) {
            console.error('Load more comments error:', error);
            this.showMessage('网络错误，请稍后重试', 'error');
        } finally {
            button.disabled = false;
            button.textContent = '加载更多评论';
        }
    }

    async refreshComments(gistId) {
        try {
            const response = await fetch(`/social/comments/${gistId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const result = await response.json();

            if (response.ok && result.html) {
                const commentsSection = document.querySelector('.comments-list');
                if (commentsSection) {
                    commentsSection.innerHTML = result.html;
                }
            }
        } catch (error) {
            console.error('Refresh comments error:', error);
        }
    }

    showMessage(message, type = 'info') {
        // 创建消息提示
        const messageEl = document.createElement('div');
        messageEl.className = `message-toast message-${type}`;
        messageEl.textContent = message;
        
        // 添加样式
        Object.assign(messageEl.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            padding: '12px 20px',
            borderRadius: '6px',
            color: 'white',
            fontWeight: '500',
            zIndex: '9999',
            transform: 'translateX(100%)',
            transition: 'transform 0.3s ease'
        });

        // 设置背景色
        switch (type) {
            case 'success':
                messageEl.style.backgroundColor = '#10b981';
                break;
            case 'error':
                messageEl.style.backgroundColor = '#ef4444';
                break;
            default:
                messageEl.style.backgroundColor = '#3b82f6';
        }

        document.body.appendChild(messageEl);

        // 显示动画
        setTimeout(() => {
            messageEl.style.transform = 'translateX(0)';
        }, 10);

        // 自动移除
        setTimeout(() => {
            messageEl.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (messageEl.parentNode) {
                    messageEl.parentNode.removeChild(messageEl);
                }
            }, 300);
        }, 3000);
    }
}

// 初始化评论管理器
document.addEventListener('DOMContentLoaded', () => {
    new CommentManager();
});

export default CommentManager;
