/**
 * 语言切换功能修复
 * 确保语言切换器在所有情况下都能正常工作
 */

document.addEventListener('DOMContentLoaded', function() {
    // 修复语言切换器的点击事件
    const languageSwitchers = document.querySelectorAll('[data-language-switcher]');
    
    languageSwitchers.forEach(switcher => {
        const links = switcher.querySelectorAll('a[href*="/locale/"]');
        
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                // 防止重复点击
                if (this.classList.contains('switching')) {
                    e.preventDefault();
                    return;
                }
                
                // 添加切换状态
                this.classList.add('switching');
                this.style.pointerEvents = 'none';
                this.style.opacity = '0.5';
                
                // 保存用户选择的语言
                const locale = this.href.split('/locale/')[1];
                if (locale) {
                    localStorage.setItem('user-selected-language', locale);
                }
                
                // 显示加载提示
                const loadingText = this.querySelector('.loading-text');
                if (loadingText) {
                    loadingText.style.display = 'inline';
                }
                
                // 如果5秒后还没有跳转，恢复按钮状态
                setTimeout(() => {
                    this.classList.remove('switching');
                    this.style.pointerEvents = '';
                    this.style.opacity = '';
                    if (loadingText) {
                        loadingText.style.display = 'none';
                    }
                }, 5000);
            });
        });
    });
    
    // 修复下拉菜单的关闭问题
    const dropdowns = document.querySelectorAll('[data-dropdown]');
    
    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');
        
        if (trigger && menu) {
            // 点击触发器切换菜单
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                const isOpen = menu.style.display === 'block';
                
                // 关闭所有其他下拉菜单
                document.querySelectorAll('[data-dropdown-menu]').forEach(m => {
                    if (m !== menu) {
                        m.style.display = 'none';
                    }
                });
                
                // 切换当前菜单
                menu.style.display = isOpen ? 'none' : 'block';
            });
            
            // 点击外部关闭菜单
            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target)) {
                    menu.style.display = 'none';
                }
            });
            
            // ESC键关闭菜单
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    menu.style.display = 'none';
                }
            });
        }
    });
    
    // 添加语言切换成功的提示
    function showLanguageSwitchNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300';
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // 显示动画
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        // 自动隐藏
        setTimeout(() => {
            notification.style.transform = 'translateX(full)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
    
    // 检查是否是语言切换后的页面加载
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('lang_switched') === '1') {
        showLanguageSwitchNotification('语言切换成功');
        
        // 清除URL参数
        const newUrl = window.location.pathname + window.location.search.replace(/[?&]lang_switched=1/, '');
        window.history.replaceState({}, '', newUrl);
    }
    
    // 为没有Alpine.js的情况提供备用方案
    if (typeof Alpine === 'undefined') {
        console.log('Alpine.js not loaded, using fallback for language switcher');
        
        // 简单的下拉菜单实现
        const simpleDropdowns = document.querySelectorAll('.language-switcher');
        simpleDropdowns.forEach(dropdown => {
            const button = dropdown.querySelector('button');
            const menu = dropdown.querySelector('[role="menu"]');
            
            if (button && menu) {
                button.addEventListener('click', function() {
                    const isHidden = menu.style.display === 'none' || !menu.style.display;
                    menu.style.display = isHidden ? 'block' : 'none';
                });
                
                // 初始隐藏菜单
                menu.style.display = 'none';
            }
        });
    }
});

// 全局函数：切换语言（用于内联事件处理器）
window.switchLanguage = function(locale) {
    localStorage.setItem('user-selected-language', locale);
    window.location.href = `/locale/${locale}`;
};

// 全局函数：AJAX语言切换
window.switchLanguageAjax = function(locale) {
    fetch('/api/locale/switch', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ locale: locale })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            localStorage.setItem('user-selected-language', locale);
            window.location.reload();
        } else {
            console.error('Language switch failed:', data.message);
        }
    })
    .catch(error => {
        console.error('Language switch error:', error);
        // 回退到普通链接切换
        window.location.href = `/locale/${locale}`;
    });
};
