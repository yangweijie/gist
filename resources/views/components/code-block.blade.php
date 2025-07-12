<div class="code-block-wrapper" data-language="{{ $language }}" data-theme="{{ $theme }}">
    @if($title || $filename)
        <div class="code-block-header">
            <div class="code-block-info">
                @if($filename)
                    <span class="code-file-icon">{{ $getFileIcon() }}</span>
                    <span class="code-filename">{{ $filename }}</span>
                @endif
                @if($title)
                    <span class="code-title">{{ $title }}</span>
                @endif
            </div>
            <div class="code-language-label">
                {{ $getLanguageLabel() }}
            </div>
        </div>
    @else
        <div class="code-language-label">
            {{ $getLanguageLabel() }}
        </div>
    @endif

    @if($enableSearch)
        <div class="code-search-container" style="display: none;">
            <div class="code-search-box">
                <input type="text" class="code-search" placeholder="在代码中搜索...">
                <div class="search-results">
                    <span class="search-count">0/0</span>
                    <button class="search-prev" title="上一个" type="button">↑</button>
                    <button class="search-next" title="下一个" type="button">↓</button>
                    <button class="search-close" title="关闭" type="button">×</button>
                </div>
            </div>
        </div>
    @endif

    <pre class="language-{{ $language }}{{ $showLineNumbers ? ' line-numbers' : '' }}"
         tabindex="0"
         @if($enableSearch) title="按 Ctrl+F 搜索代码" @endif><code class="language-{{ $language }}">{{ trim($content) }}</code></pre>

    @if($showToolbar)
        <div class="code-toolbar-custom">
            <div class="toolbar-buttons">
                @if($enableCopy)
                    <button class="toolbar-btn copy-btn" title="复制代码" type="button">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        复制
                    </button>
                @endif

                <button class="toolbar-btn select-all-btn" title="全选代码" type="button">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 11H5a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-4"></path>
                        <rect x="9" y="3" width="12" height="12" rx="2" ry="2"></rect>
                    </svg>
                    全选
                </button>

                @if($enableSearch)
                    <button class="toolbar-btn search-btn" title="搜索代码 (Ctrl+F)" type="button">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        搜索
                    </button>
                @endif

                <button class="toolbar-btn fullscreen-btn" title="全屏查看" type="button">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                    </svg>
                    全屏
                </button>

                <button class="toolbar-btn theme-btn" title="切换主题" type="button">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                    主题
                </button>
            </div>
        </div>
    @endif
</div>

<style>
/* 代码块头部样式 */
.code-block-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 1rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    font-size: 14px;
}

.code-block-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.code-file-icon {
    font-size: 16px;
}

.code-filename {
    font-family: monospace;
    font-weight: 500;
    color: #495057;
}

.code-title {
    color: #6c757d;
    font-style: italic;
}

/* 自定义工具栏样式 */
.code-toolbar-custom {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    z-index: 10;
}

.toolbar-buttons {
    display: flex;
    gap: 0.25rem;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.code-block-wrapper:hover .toolbar-buttons {
    opacity: 1;
}

.toolbar-btn {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.toolbar-btn:hover {
    background: rgba(0, 0, 0, 0.9);
}

.toolbar-btn svg {
    width: 14px;
    height: 14px;
}

/* 复制成功状态 */
.toolbar-btn.copy-success {
    background: #28a745;
}

.toolbar-btn.copy-success::after {
    content: '已复制!';
    position: absolute;
    top: -30px;
    left: 50%;
    transform: translateX(-50%);
    background: #28a745;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 11px;
    white-space: nowrap;
    z-index: 1000;
}

/* 响应式设计 */
@media (max-width: 768px) {
    .code-block-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .toolbar-buttons {
        position: static;
        opacity: 1;
        justify-content: flex-end;
        padding: 0.5rem;
        background: rgba(0, 0, 0, 0.05);
        border-top: 1px solid #e9ecef;
    }

    .code-toolbar-custom {
        position: static;
    }

    .toolbar-btn {
        font-size: 11px;
        padding: 0.2rem 0.4rem;
    }
}
</style>