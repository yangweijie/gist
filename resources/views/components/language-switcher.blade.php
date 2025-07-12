@php
    $localizationService = app(App\Services\LocalizationService::class);
    $currentLocale = app()->getLocale();
    $currentLocaleInfo = $localizationService->getCurrentLocaleInfo();
    $languages = $localizationService->getEnabledLocales();
@endphp

<div class="relative inline-block text-left language-switcher"
     data-language-switcher
     data-dropdown
     x-data="{
         open: false,
         switching: false,
         currentLocale: '{{ $currentLocale }}',
         init() {
             this.$watch('open', value => {
                 if (value) {
                     this.$nextTick(() => {
                         this.$refs.dropdown.focus();
                     });
                 }
             });
         }
     }"
     @click.away="open = false"
     @keydown.escape="open = false">

    <div>
        <button @click="open = !open"
                type="button"
                data-dropdown-trigger
                class="group inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm px-3 py-2 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transition-all duration-200 ease-in-out transform hover:scale-105"
                :class="{ 'ring-2 ring-blue-500': open }"
                id="language-menu-button"
                :aria-expanded="open"
                aria-haspopup="true"
                :disabled="switching">

            <!-- 加载状态 -->
            <div x-show="switching" class="mr-2">
                <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <!-- 正常状态 -->
            <div x-show="!switching" class="flex items-center">
                <span class="mr-2 text-lg transition-transform duration-200 group-hover:scale-110">{{ $currentLocaleInfo['flag'] }}</span>
                <span class="hidden sm:inline">{{ $currentLocaleInfo['native'] }}</span>
                <span class="sm:hidden">{{ $currentLocaleInfo['code'] }}</span>
                <svg class="-mr-1 ml-2 h-4 w-4 transition-transform duration-200"
                     :class="{ 'rotate-180': open }"
                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </div>
        </button>
    </div>

    <div x-show="open"
         x-ref="dropdown"
         data-dropdown-menu
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95 translate-y-1"
         x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="transform opacity-0 scale-95 translate-y-1"
         class="origin-top-right absolute right-0 mt-2 w-64 rounded-xl shadow-xl bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 dark:ring-gray-700 focus:outline-none z-50 backdrop-blur-sm"
         role="menu"
         aria-orientation="vertical"
         aria-labelledby="language-menu-button"
         tabindex="-1"
         @keydown.arrow-down.prevent="$focus.wrap().next()"
         @keydown.arrow-up.prevent="$focus.wrap().previous()">

        <div class="py-2" role="none">
            <!-- 标题 -->
            <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    {{ __('common.language.select_language') }}
                </p>
            </div>

            @foreach($languages as $locale => $language)
                <a href="{{ route('locale.switch', $locale) }}"
                   onclick="this.style.pointerEvents='none'; this.style.opacity='0.5'; localStorage.setItem('user-selected-language', '{{ $locale }}');"
                   class="group flex items-center px-4 py-3 text-sm transition-all duration-200 ease-in-out {{ $currentLocale === $locale ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700' }}"
                   role="menuitem"
                   tabindex="-1">

                    <!-- 国旗图标 -->
                    <span class="mr-3 text-xl transition-transform duration-200 group-hover:scale-110">{{ $language['flag'] }}</span>

                    <!-- 语言信息 -->
                    <div class="flex flex-col flex-1">
                        <span class="font-medium">{{ $language['native'] }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $language['name'] }}</span>
                    </div>

                    <!-- 当前语言标识 -->
                    @if($currentLocale === $locale)
                        <div class="flex items-center">
                            <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-1 text-xs font-medium text-blue-600 dark:text-blue-400">{{ __('common.language.current') }}</span>
                        </div>
                    @else
                        <!-- 切换提示 -->
                        <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    @endif
                </a>
            @endforeach

            <!-- 底部信息 -->
            <div class="px-4 py-2 border-t border-gray-100 dark:border-gray-700 mt-1">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    <svg class="inline h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    {{ __('common.language.auto_detected') }}
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// 增强的语言切换功能
document.addEventListener('DOMContentLoaded', function() {
    // 语言切换动画
    const languageSwitcher = document.querySelector('[x-data*="switching"]');

    if (languageSwitcher) {
        // 添加键盘导航支持
        languageSwitcher.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const button = this.querySelector('button');
                if (button) button.click();
            }
        });

        // 添加触摸设备支持
        let touchStartY = 0;
        languageSwitcher.addEventListener('touchstart', function(e) {
            touchStartY = e.touches[0].clientY;
        });

        languageSwitcher.addEventListener('touchend', function(e) {
            const touchEndY = e.changedTouches[0].clientY;
            const diff = touchStartY - touchEndY;

            // 向上滑动打开菜单
            if (diff > 50) {
                const button = this.querySelector('button');
                if (button && !button.getAttribute('aria-expanded')) {
                    button.click();
                }
            }
        });
    }

    // 语言切换进度指示
    function showLanguageSwitchProgress() {
        const progressBar = document.createElement('div');
        progressBar.className = 'fixed top-0 left-0 w-full h-1 bg-blue-500 z-50 transition-all duration-1000';
        progressBar.style.transform = 'scaleX(0)';
        progressBar.style.transformOrigin = 'left';

        document.body.appendChild(progressBar);

        // 动画进度条
        setTimeout(() => {
            progressBar.style.transform = 'scaleX(1)';
        }, 10);

        // 移除进度条
        setTimeout(() => {
            progressBar.remove();
        }, 1000);
    }

    // AJAX 语言切换（增强版）
    window.switchLanguageAjax = function(locale) {
        showLanguageSwitchProgress();

        fetch('/api/locale/switch', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ locale: locale })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // 显示成功消息
                showNotification('{{ __("common.messages.locale_switched") }}', 'success');

                // 平滑刷新页面
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                throw new Error(data.message || 'Language switch failed');
            }
        })
        .catch(error => {
            console.error('Language switch error:', error);
            showNotification('{{ __("common.messages.error") }}', 'error');

            // 回退到普通链接跳转
            setTimeout(() => {
                window.location.href = `/locale/${locale}`;
            }, 1000);
        });
    };

    // 通知系统
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-x-full ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        notification.textContent = message;

        document.body.appendChild(notification);

        // 滑入动画
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);

        // 自动移除
        setTimeout(() => {
            notification.style.transform = 'translateX(full)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // 语言偏好检测和建议（仅在用户明确需要时显示）
    function detectAndSuggestLanguage() {
        // 检查用户是否已经手动选择过语言
        if (localStorage.getItem('user-selected-language') ||
            sessionStorage.getItem('language-suggestion-dismissed')) {
            return;
        }

        const browserLang = navigator.language || navigator.userLanguage;
        const currentLang = document.documentElement.lang || 'zh';

        // 只在语言差异明显且用户可能需要时显示建议
        // 并且只在首次访问时显示
        const isFirstVisit = !localStorage.getItem('language-suggestion-shown');

        if (isFirstVisit) {
            if (browserLang.startsWith('en') && currentLang === 'zh') {
                showLanguageSuggestion('en', 'English');
            } else if (browserLang.startsWith('zh') && currentLang === 'en') {
                showLanguageSuggestion('zh', '中文');
            }
        }
    }

    function showLanguageSuggestion(locale, name) {
        // 检查是否已经显示过建议或用户已经选择过语言
        if (localStorage.getItem('language-suggestion-shown') ||
            localStorage.getItem('user-selected-language')) {
            return;
        }

        const suggestion = document.createElement('div');
        suggestion.className = 'fixed bottom-4 right-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-4 z-50 max-w-sm transform translate-y-full transition-transform duration-300';
        suggestion.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm text-gray-700 dark:text-gray-200">
                        检测到您的浏览器语言是 <strong>${name}</strong>，是否切换？
                    </p>
                    <div class="mt-2 flex space-x-2">
                        <button onclick="switchToLanguage('${locale}')" class="text-xs bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition-colors">
                            切换
                        </button>
                        <button onclick="dismissLanguageSuggestion()" class="text-xs bg-gray-300 text-gray-700 px-3 py-1 rounded hover:bg-gray-400 transition-colors">
                            保持当前
                        </button>
                    </div>
                </div>
                <button onclick="dismissLanguageSuggestion()" class="ml-2 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        `;

        document.body.appendChild(suggestion);

        // 滑入动画
        setTimeout(() => {
            suggestion.style.transform = 'translateY(0)';
        }, 100);

        // 标记已显示
        localStorage.setItem('language-suggestion-shown', 'true');

        // 10秒后自动消失
        setTimeout(() => {
            if (suggestion.parentNode) {
                dismissLanguageSuggestion();
            }
        }, 10000);
    }

    window.switchToLanguage = function(locale) {
        // 记录用户主动选择了语言
        localStorage.setItem('user-selected-language', locale);
        dismissLanguageSuggestion();
        window.location.href = `/locale/${locale}`;
    };

    window.dismissLanguageSuggestion = function() {
        const suggestion = document.querySelector('.fixed.bottom-4.right-4');
        if (suggestion) {
            // 滑出动画
            suggestion.style.transform = 'translateY(full)';
            setTimeout(() => {
                suggestion.remove();
            }, 300);
        }
        // 记录用户已经看过建议
        sessionStorage.setItem('language-suggestion-dismissed', 'true');
    };

    // 初始化语言检测（延迟执行，避免打扰用户）
    // 只在页面完全加载后且用户停留一段时间后才显示建议
    setTimeout(() => {
        // 检查用户是否还在页面上（避免快速浏览时显示）
        if (document.hasFocus()) {
            detectAndSuggestLanguage();
        }
    }, 5000); // 增加到5秒，给用户更多时间适应页面
});
</script>

@push('styles')
<style>
/* 语言切换器自定义样式 */
.language-switcher-dropdown {
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

/* 深色模式适配 */
@media (prefers-color-scheme: dark) {
    .language-switcher-dropdown {
        background-color: rgba(31, 41, 55, 0.9);
    }
}

/* 移动端优化 */
@media (max-width: 640px) {
    .language-switcher-dropdown {
        width: calc(100vw - 2rem);
        right: 1rem;
        left: 1rem;
    }
}

/* 高对比度模式支持 */
@media (prefers-contrast: high) {
    .language-switcher-dropdown {
        border-width: 2px;
    }
}

/* 减少动画模式支持 */
@media (prefers-reduced-motion: reduce) {
    .language-switcher-dropdown * {
        transition: none !important;
        animation: none !important;
    }
}
</style>
@endpush
@endpush
