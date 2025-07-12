import Prism from 'prismjs';

// 导入核心语言支持
import 'prismjs/components/prism-php';
import 'prismjs/components/prism-javascript';
import 'prismjs/components/prism-python';
import 'prismjs/components/prism-java';
import 'prismjs/components/prism-c';
import 'prismjs/components/prism-cpp';
import 'prismjs/components/prism-csharp';
import 'prismjs/components/prism-ruby';
import 'prismjs/components/prism-go';
import 'prismjs/components/prism-rust';
import 'prismjs/components/prism-swift';
import 'prismjs/components/prism-sql';
import 'prismjs/components/prism-bash';
import 'prismjs/components/prism-markdown';
import 'prismjs/components/prism-json';
import 'prismjs/components/prism-xml-doc';
import 'prismjs/components/prism-yaml';
import 'prismjs/components/prism-css';
import 'prismjs/components/prism-scss';
import 'prismjs/components/prism-typescript';

// 导入插件
import 'prismjs/plugins/line-numbers/prism-line-numbers';
import 'prismjs/plugins/copy-to-clipboard/prism-copy-to-clipboard';
import 'prismjs/plugins/toolbar/prism-toolbar';
import 'prismjs/plugins/show-language/prism-show-language';

// 导入样式
import 'prismjs/themes/prism.css';
import 'prismjs/plugins/line-numbers/prism-line-numbers.css';
import 'prismjs/plugins/toolbar/prism-toolbar.css';

class CodeHighlighter {
    constructor() {
        this.currentTheme = localStorage.getItem('code-theme') || 'default';
        this.isFullscreen = false;
        this.init();
    }

    init() {
        this.setupPrism();
        this.bindEvents();
        this.loadTheme();
    }

    setupPrism() {
        // 配置 Prism
        Prism.manual = true;
        
        // 配置工具栏
        Prism.plugins.toolbar.registerButton('fullscreen', {
            text: window.__('common.actions.fullscreen') || '全屏',
            onClick: (env) => {
                this.toggleFullscreen(env.element);
            }
        });

        Prism.plugins.toolbar.registerButton('select-all', {
            text: window.__('common.actions.select_all') || '全选',
            onClick: (env) => {
                this.selectAllCode(env.element);
            }
        });

        Prism.plugins.toolbar.registerButton('theme-toggle', {
            text: window.__('common.actions.theme') || '主题',
            onClick: () => {
                this.showThemeSelector();
            }
        });
    }

    bindEvents() {
        // 监听页面加载完成
        document.addEventListener('DOMContentLoaded', () => {
            this.highlightAll();
        });

        // 监听动态内容加载（HTMX）
        document.addEventListener('htmx:afterSwap', () => {
            this.highlightAll();
        });

        // 监听 ESC 键退出全屏
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isFullscreen) {
                this.exitFullscreen();
            }
        });

        // 监听代码搜索
        document.addEventListener('input', (e) => {
            if (e.target.classList.contains('code-search')) {
                this.searchInCode(e.target);
            }
        });
    }

    highlightAll() {
        // 高亮所有代码块
        const codeBlocks = document.querySelectorAll('pre[class*="language-"]:not(.highlighted)');
        
        codeBlocks.forEach(block => {
            // 添加行号支持
            if (!block.classList.contains('line-numbers')) {
                block.classList.add('line-numbers');
            }
            
            // 标记为已处理
            block.classList.add('highlighted');
            
            // 执行高亮
            Prism.highlightElement(block.querySelector('code'));
            
            // 添加搜索功能
            this.addSearchToBlock(block);
        });
    }

    addSearchToBlock(block) {
        const wrapper = block.closest('.code-block-wrapper');
        if (!wrapper || wrapper.querySelector('.code-search-container')) {
            return;
        }

        const searchContainer = document.createElement('div');
        searchContainer.className = 'code-search-container';
        const searchPlaceholder = window.__('common.actions.search_in_code') || '在代码中搜索...';
        const prevTitle = window.__('common.actions.previous') || '上一个';
        const nextTitle = window.__('common.actions.next') || '下一个';
        const closeTitle = window.__('common.actions.close') || '关闭';

        searchContainer.innerHTML = `
            <div class="code-search-box">
                <input type="text" class="code-search" placeholder="${searchPlaceholder}">
                <div class="search-results">
                    <span class="search-count">0/0</span>
                    <button class="search-prev" title="${prevTitle}">↑</button>
                    <button class="search-next" title="${nextTitle}">↓</button>
                    <button class="search-close" title="${closeTitle}">×</button>
                </div>
            </div>
        `;

        wrapper.insertBefore(searchContainer, block);
        this.bindSearchEvents(searchContainer, block);
    }

    bindSearchEvents(searchContainer, codeBlock) {
        const searchInput = searchContainer.querySelector('.code-search');
        const searchCount = searchContainer.querySelector('.search-count');
        const prevBtn = searchContainer.querySelector('.search-prev');
        const nextBtn = searchContainer.querySelector('.search-next');
        const closeBtn = searchContainer.querySelector('.search-close');

        let currentMatches = [];
        let currentIndex = -1;

        searchInput.addEventListener('input', () => {
            this.performSearch(searchInput.value, codeBlock, searchCount, currentMatches);
            currentIndex = currentMatches.length > 0 ? 0 : -1;
            this.highlightMatch(currentMatches, currentIndex);
        });

        prevBtn.addEventListener('click', () => {
            if (currentMatches.length > 0) {
                currentIndex = (currentIndex - 1 + currentMatches.length) % currentMatches.length;
                this.highlightMatch(currentMatches, currentIndex);
            }
        });

        nextBtn.addEventListener('click', () => {
            if (currentMatches.length > 0) {
                currentIndex = (currentIndex + 1) % currentMatches.length;
                this.highlightMatch(currentMatches, currentIndex);
            }
        });

        closeBtn.addEventListener('click', () => {
            searchContainer.style.display = 'none';
            this.clearSearchHighlights(codeBlock);
        });

        // Ctrl+F 快捷键
        codeBlock.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                searchContainer.style.display = 'block';
                searchInput.focus();
            }
        });
    }

    performSearch(query, codeBlock, countElement, matches) {
        this.clearSearchHighlights(codeBlock);
        matches.length = 0;

        if (!query.trim()) {
            countElement.textContent = '0/0';
            return;
        }

        const codeElement = codeBlock.querySelector('code');
        const text = codeElement.textContent;
        const regex = new RegExp(query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
        let match;

        while ((match = regex.exec(text)) !== null) {
            matches.push({
                start: match.index,
                end: match.index + match[0].length,
                text: match[0]
            });
        }

        countElement.textContent = `${matches.length > 0 ? 1 : 0}/${matches.length}`;
        this.highlightSearchMatches(codeElement, matches);
    }

    highlightSearchMatches(codeElement, matches) {
        if (matches.length === 0) return;

        const text = codeElement.textContent;
        let html = '';
        let lastIndex = 0;

        matches.forEach((match, index) => {
            html += this.escapeHtml(text.substring(lastIndex, match.start));
            html += `<mark class="search-highlight" data-index="${index}">${this.escapeHtml(match.text)}</mark>`;
            lastIndex = match.end;
        });

        html += this.escapeHtml(text.substring(lastIndex));
        codeElement.innerHTML = html;
    }

    highlightMatch(matches, index) {
        const highlights = document.querySelectorAll('.search-highlight');
        highlights.forEach((highlight, i) => {
            highlight.classList.toggle('current', i === index);
        });

        if (index >= 0 && highlights[index]) {
            highlights[index].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    clearSearchHighlights(codeBlock) {
        const highlights = codeBlock.querySelectorAll('.search-highlight');
        highlights.forEach(highlight => {
            highlight.outerHTML = highlight.textContent;
        });
    }

    selectAllCode(element) {
        const codeElement = element.querySelector('code');
        if (codeElement) {
            const range = document.createRange();
            range.selectNodeContents(codeElement);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
        }
    }

    toggleFullscreen(element) {
        const wrapper = element.closest('.code-block-wrapper');
        if (!wrapper) return;

        if (this.isFullscreen) {
            this.exitFullscreen();
        } else {
            this.enterFullscreen(wrapper);
        }
    }

    enterFullscreen(wrapper) {
        wrapper.classList.add('fullscreen');
        document.body.classList.add('code-fullscreen');
        this.isFullscreen = true;

        // 创建全屏覆盖层
        const overlay = document.createElement('div');
        overlay.className = 'code-fullscreen-overlay';
        overlay.appendChild(wrapper.cloneNode(true));
        
        // 添加关闭按钮
        const closeBtn = document.createElement('button');
        closeBtn.className = 'fullscreen-close';
        closeBtn.innerHTML = '×';
        closeBtn.onclick = () => this.exitFullscreen();
        overlay.appendChild(closeBtn);

        document.body.appendChild(overlay);
    }

    exitFullscreen() {
        const overlay = document.querySelector('.code-fullscreen-overlay');
        if (overlay) {
            overlay.remove();
        }
        
        document.body.classList.remove('code-fullscreen');
        document.querySelectorAll('.code-block-wrapper').forEach(wrapper => {
            wrapper.classList.remove('fullscreen');
        });
        
        this.isFullscreen = false;
    }

    loadTheme() {
        this.applyTheme(this.currentTheme);
    }

    applyTheme(themeName) {
        // 移除现有主题
        document.querySelectorAll('link[data-prism-theme]').forEach(link => {
            link.remove();
        });

        // 加载新主题
        if (themeName !== 'default') {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = `/css/prism-themes/${themeName}.css`;
            link.setAttribute('data-prism-theme', themeName);
            document.head.appendChild(link);
        }

        this.currentTheme = themeName;
        localStorage.setItem('code-theme', themeName);
    }

    showThemeSelector() {
        const themes = [
            { name: 'default', label: window.__('common.themes.default') || '默认' },
            { name: 'dark', label: window.__('common.themes.dark') || '暗色' },
            { name: 'tomorrow', label: 'Tomorrow' },
            { name: 'twilight', label: 'Twilight' },
            { name: 'okaidia', label: 'Okaidia' },
            { name: 'funky', label: 'Funky' }
        ];

        const selector = document.createElement('div');
        selector.className = 'theme-selector';
        selector.innerHTML = `
            <div class="theme-selector-content">
                <h3>选择代码主题</h3>
                <div class="theme-options">
                    ${themes.map(theme => `
                        <button class="theme-option ${theme.name === this.currentTheme ? 'active' : ''}" 
                                data-theme="${theme.name}">
                            ${theme.label}
                        </button>
                    `).join('')}
                </div>
                <button class="theme-selector-close">关闭</button>
            </div>
        `;

        document.body.appendChild(selector);

        // 绑定事件
        selector.addEventListener('click', (e) => {
            if (e.target.classList.contains('theme-option')) {
                const themeName = e.target.dataset.theme;
                this.applyTheme(themeName);
                
                // 更新选中状态
                selector.querySelectorAll('.theme-option').forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.theme === themeName);
                });
            } else if (e.target.classList.contains('theme-selector-close') || 
                       e.target.classList.contains('theme-selector')) {
                selector.remove();
            }
        });
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// 初始化代码高亮器
const codeHighlighter = new CodeHighlighter();

// 导出供全局使用
window.CodeHighlighter = CodeHighlighter;
window.codeHighlighter = codeHighlighter;

export default CodeHighlighter;
