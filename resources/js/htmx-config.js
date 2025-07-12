// HTMX 配置和扩展功能

document.addEventListener('DOMContentLoaded', function() {
    // 配置 HTMX
    htmx.config.globalViewTransitions = true;
    htmx.config.scrollBehavior = 'smooth';
    htmx.config.defaultSwapStyle = 'innerHTML';
    htmx.config.defaultSwapDelay = 0;
    htmx.config.defaultSettleDelay = 20;

    // 全局加载指示器
    let loadingIndicator = null;

    // 创建加载指示器
    function createLoadingIndicator() {
        if (loadingIndicator) return loadingIndicator;
        
        loadingIndicator = document.createElement('div');
        loadingIndicator.id = 'htmx-loading';
        loadingIndicator.className = 'htmx-loading-indicator';
        loadingIndicator.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner"></div>
                <span>加载中...</span>
            </div>
        `;
        document.body.appendChild(loadingIndicator);
        return loadingIndicator;
    }

    // 显示加载指示器
    function showLoading() {
        const indicator = createLoadingIndicator();
        indicator.style.display = 'flex';
    }

    // 隐藏加载指示器
    function hideLoading() {
        if (loadingIndicator) {
            loadingIndicator.style.display = 'none';
        }
    }

    // HTMX 事件监听
    document.addEventListener('htmx:beforeRequest', function(evt) {
        // 显示加载状态
        showLoading();
        
        // 为表单添加加载状态
        const element = evt.detail.elt;
        if (element.tagName === 'FORM' || element.closest('form')) {
            const form = element.tagName === 'FORM' ? element : element.closest('form');
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.dataset.originalText = submitBtn.textContent;
                submitBtn.textContent = window.__('common.status.processing') || '处理中...';
            }
        }
    });

    document.addEventListener('htmx:afterRequest', function(evt) {
        // 隐藏加载状态
        hideLoading();
        
        // 恢复表单状态
        const element = evt.detail.elt;
        if (element.tagName === 'FORM' || element.closest('form')) {
            const form = element.tagName === 'FORM' ? element : element.closest('form');
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && submitBtn.dataset.originalText) {
                submitBtn.disabled = false;
                submitBtn.textContent = submitBtn.dataset.originalText;
                delete submitBtn.dataset.originalText;
            }
        }
    });

    document.addEventListener('htmx:responseError', function(evt) {
        hideLoading();
        
        // 显示错误消息
        const errorMsg = evt.detail.xhr.responseText || window.__('common.messages.server_error') || '请求失败，请重试';
        showNotification('error', errorMsg);
    });

    document.addEventListener('htmx:sendError', function(evt) {
        hideLoading();
        showNotification('error', window.__('common.messages.network_error') || '网络错误，请检查连接');
    });

    document.addEventListener('htmx:afterSwap', function(evt) {
        // 重新初始化代码高亮
        if (window.codeHighlighter) {
            window.codeHighlighter.highlightAll();
        }
        
        // 重新初始化其他组件
        initializeComponents(evt.detail.target);
    });

    // 通知系统
    function showNotification(type, message) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // 自动移除
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    // 初始化组件
    function initializeComponents(container = document) {
        // 初始化搜索防抖
        const searchInputs = container.querySelectorAll('[data-search-debounce]');
        searchInputs.forEach(input => {
            let timeout;
            input.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    htmx.trigger(input, 'search');
                }, parseInt(input.dataset.searchDebounce) || 300);
            });
        });

        // 初始化确认对话框
        const confirmElements = container.querySelectorAll('[data-confirm]');
        confirmElements.forEach(element => {
            element.addEventListener('htmx:confirm', function(evt) {
                evt.preventDefault();
                if (confirm(element.dataset.confirm)) {
                    evt.detail.issueRequest();
                }
            });
        });

        // 初始化模态框
        const modalTriggers = container.querySelectorAll('[data-modal]');
        modalTriggers.forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                openModal(trigger.dataset.modal);
            });
        });
    }

    // 模态框管理
    function openModal(url) {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="modal-container">
                <div class="modal-header">
                    <button class="modal-close" onclick="closeModal(this)">×</button>
                </div>
                <div class="modal-content">
                    <div class="loading-spinner">
                        <div class="spinner"></div>
                        <span>加载中...</span>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        document.body.classList.add('modal-open');
        
        // 加载内容
        htmx.ajax('GET', url, {
            target: modal.querySelector('.modal-content'),
            swap: 'innerHTML'
        });
    }

    // 关闭模态框
    window.closeModal = function(element) {
        const modal = element.closest('.modal-overlay');
        if (modal) {
            modal.remove();
            document.body.classList.remove('modal-open');
        }
    };

    // 点击遮罩关闭模态框
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            closeModal(e.target);
        }
    });

    // ESC 键关闭模态框
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.querySelector('.modal-overlay');
            if (modal) {
                closeModal(modal);
            }
        }
    });

    // 无限滚动
    function initInfiniteScroll() {
        const scrollContainer = document.querySelector('[data-infinite-scroll]');
        if (!scrollContainer) return;

        let loading = false;
        
        function checkScroll() {
            if (loading) return;
            
            const { scrollTop, scrollHeight, clientHeight } = scrollContainer;
            if (scrollTop + clientHeight >= scrollHeight - 100) {
                const nextPageUrl = scrollContainer.dataset.nextPage;
                if (nextPageUrl) {
                    loading = true;
                    htmx.ajax('GET', nextPageUrl, {
                        target: scrollContainer.querySelector('[data-append-target]'),
                        swap: 'beforeend'
                    }).then(() => {
                        loading = false;
                    });
                }
            }
        }

        scrollContainer.addEventListener('scroll', checkScroll);
    }

    // 初始化所有组件
    initializeComponents();
    initInfiniteScroll();

    // 暴露全局函数
    window.htmxConfig = {
        showNotification,
        openModal,
        closeModal,
        initializeComponents
    };
});

// 样式
const style = document.createElement('style');
style.textContent = `
.htmx-loading-indicator {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loading-spinner {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.spinner {
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    max-width: 400px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    animation: slideIn 0.3s ease-out;
}

.notification-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.notification-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.notification-content {
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    margin-left: 1rem;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9998;
}

.modal-container {
    background: white;
    border-radius: 8px;
    max-width: 90vw;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-header {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
}

.modal-content {
    padding: 1rem;
    max-height: 70vh;
    overflow-y: auto;
}

.modal-open {
    overflow: hidden;
}

.htmx-request {
    opacity: 0.7;
    pointer-events: none;
}

.htmx-swapping {
    opacity: 0;
    transition: opacity 0.2s ease-out;
}
`;
document.head.appendChild(style);
