@extends('layouts.app')

@section('title', 'PHP 在线运行器')

@section('head')
<style>
    .php-runner-container {
        height: calc(100vh - 120px);
        min-height: 600px;
    }
    
    .code-editor {
        height: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
    }
    
    .output-panel {
        height: 100%;
        background: #1f2937;
        color: #f9fafb;
        border-radius: 0.5rem;
        overflow-y: auto;
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    }
    
    .loading-spinner {
        display: none;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .example-card {
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .example-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
    
    .status-ready { background-color: #10b981; }
    .status-loading { background-color: #f59e0b; }
    .status-error { background-color: #ef4444; }
</style>
@endsection

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- 页面标题 -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">PHP 在线运行器</h1>
                <p class="text-gray-600">在浏览器中直接运行 PHP 代码，基于 WebAssembly 技术</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center text-sm text-gray-600">
                    <span class="status-indicator status-ready" id="php-status"></span>
                    <span id="php-status-text">准备就绪</span>
                </div>
                @if(isset($gist))
                    <a href="{{ route('gists.show', $gist) }}" class="text-indigo-600 hover:text-indigo-800">
                        <i class="fas fa-arrow-left mr-1"></i>返回 Gist
                    </a>
                @endif
            </div>
        </div>
    </div>
    
    <!-- 工具栏 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-4">
        <div class="p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <button id="run-btn" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-play mr-2"></i>运行代码
                    </button>
                    <button id="clear-btn" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        <i class="fas fa-trash mr-2"></i>清空输出
                    </button>
                    <button id="validate-btn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-check mr-2"></i>语法检查
                    </button>
                </div>
                <div class="flex items-center space-x-4">
                    <button id="examples-btn" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-code mr-2"></i>示例代码
                    </button>
                    <button id="fullscreen-btn" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        <i class="fas fa-expand mr-2"></i>全屏
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 主要内容区域 -->
    <div class="php-runner-container">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 h-full">
            <!-- 代码编辑器 -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-3 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">PHP 代码</h3>
                </div>
                <div class="p-4 h-full">
                    <textarea id="code-editor" class="w-full h-full resize-none font-mono text-sm border-0 focus:ring-0" 
                              placeholder="在这里输入 PHP 代码...">@if(isset($gist)){{ $gist->content }}@else<?php
echo "Hello, World!";
echo "\n";
echo "当前时间: " . date("Y-m-d H:i:s");
?>@endif</textarea>
                </div>
            </div>
            
            <!-- 输出面板 -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-3 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">输出结果</h3>
                    <div class="flex items-center space-x-2">
                        <span id="execution-time" class="text-sm text-gray-500"></span>
                        <div class="loading-spinner" id="loading-spinner">
                            <i class="fas fa-spinner"></i>
                        </div>
                    </div>
                </div>
                <div class="output-panel p-4">
                    <pre id="output-content" class="whitespace-pre-wrap text-sm">等待运行代码...</pre>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 示例代码模态框 -->
<div id="examples-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[80vh] overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-900">示例代码</h3>
                    <button id="close-examples" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 overflow-y-auto max-h-[60vh]">
                <div id="examples-grid" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- 动态加载示例 -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 全屏模式样式 -->
<style>
    .fullscreen-mode {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 9999;
        background: white;
        padding: 1rem;
    }
    
    .fullscreen-mode .php-runner-container {
        height: calc(100vh - 120px);
    }
</style>

<script type="module">
import { PHP } from '@php-wasm/web';

class PhpRunner {
    constructor() {
        this.php = null;
        this.isLoading = false;
        this.isFullscreen = false;
        this.init();
    }
    
    async init() {
        try {
            this.updateStatus('loading', '正在加载 PHP...');
            
            // 加载 PHP WASM
            this.php = await PHP.load('8.0', {
                requestHandler: {
                    documentRoot: '/tmp',
                },
            });
            
            this.updateStatus('ready', '准备就绪');
            this.bindEvents();
            this.loadExamples();
            
        } catch (error) {
            console.error('Failed to load PHP:', error);
            this.updateStatus('error', '加载失败');
            this.showError('PHP 加载失败: ' + error.message);
        }
    }
    
    bindEvents() {
        // 运行按钮
        document.getElementById('run-btn').addEventListener('click', () => {
            this.runCode();
        });
        
        // 清空按钮
        document.getElementById('clear-btn').addEventListener('click', () => {
            this.clearOutput();
        });
        
        // 语法检查按钮
        document.getElementById('validate-btn').addEventListener('click', () => {
            this.validateCode();
        });
        
        // 示例按钮
        document.getElementById('examples-btn').addEventListener('click', () => {
            this.showExamples();
        });
        
        // 全屏按钮
        document.getElementById('fullscreen-btn').addEventListener('click', () => {
            this.toggleFullscreen();
        });
        
        // 关闭示例模态框
        document.getElementById('close-examples').addEventListener('click', () => {
            this.hideExamples();
        });
        
        // 键盘快捷键
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 'Enter':
                        e.preventDefault();
                        this.runCode();
                        break;
                    case 'l':
                        e.preventDefault();
                        this.clearOutput();
                        break;
                    case 'k':
                        e.preventDefault();
                        this.validateCode();
                        break;
                }
            }
            
            if (e.key === 'Escape') {
                if (this.isFullscreen) {
                    this.toggleFullscreen();
                }
                this.hideExamples();
            }
        });
    }
    
    async runCode() {
        if (this.isLoading || !this.php) {
            return;
        }
        
        const code = document.getElementById('code-editor').value.trim();
        if (!code) {
            this.showError('请输入要运行的代码');
            return;
        }
        
        this.isLoading = true;
        this.updateStatus('loading', '正在运行...');
        this.showLoading(true);
        
        const startTime = performance.now();
        
        try {
            // 创建临时文件
            const filename = '/tmp/script.php';
            this.php.writeFile(filename, code);
            
            // 运行 PHP 代码
            const result = await this.php.run({
                scriptPath: filename
            });
            
            const endTime = performance.now();
            const executionTime = Math.round(endTime - startTime);
            
            // 显示结果
            this.showOutput(result.text, result.errors, executionTime);
            
            // 记录执行日志
            this.logExecution(code, result.text, result.errors, executionTime);
            
        } catch (error) {
            console.error('PHP execution error:', error);
            this.showError('执行错误: ' + error.message);
        } finally {
            this.isLoading = false;
            this.updateStatus('ready', '准备就绪');
            this.showLoading(false);
        }
    }
    
    async validateCode() {
        const code = document.getElementById('code-editor').value.trim();
        if (!code) {
            this.showError('请输入要检查的代码');
            return;
        }
        
        try {
            const response = await fetch('/php-runner/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ code })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showSuccess('代码语法正确！');
            } else {
                this.showError(result.error || '语法检查失败');
            }
            
        } catch (error) {
            console.error('Validation error:', error);
            this.showError('语法检查失败: ' + error.message);
        }
    }
    
    clearOutput() {
        document.getElementById('output-content').textContent = '等待运行代码...';
        document.getElementById('execution-time').textContent = '';
    }
    
    showOutput(output, errors, executionTime) {
        const outputElement = document.getElementById('output-content');
        let content = '';
        
        if (output) {
            content += output;
        }
        
        if (errors) {
            content += '\n--- 错误信息 ---\n' + errors;
        }
        
        if (!content.trim()) {
            content = '(无输出)';
        }
        
        outputElement.textContent = content;
        document.getElementById('execution-time').textContent = `执行时间: ${executionTime}ms`;
    }
    
    showError(message) {
        const outputElement = document.getElementById('output-content');
        outputElement.innerHTML = `<span style="color: #ef4444;">错误: ${message}</span>`;
    }
    
    showSuccess(message) {
        const outputElement = document.getElementById('output-content');
        outputElement.innerHTML = `<span style="color: #10b981;">✓ ${message}</span>`;
    }
    
    showLoading(show) {
        const spinner = document.getElementById('loading-spinner');
        spinner.style.display = show ? 'block' : 'none';
    }
    
    updateStatus(status, text) {
        const indicator = document.getElementById('php-status');
        const statusText = document.getElementById('php-status-text');
        
        indicator.className = `status-indicator status-${status}`;
        statusText.textContent = text;
    }
    
    async loadExamples() {
        try {
            const response = await fetch('/php-runner/examples');
            this.examples = await response.json();
        } catch (error) {
            console.error('Failed to load examples:', error);
            this.examples = [];
        }
    }
    
    showExamples() {
        const modal = document.getElementById('examples-modal');
        const grid = document.getElementById('examples-grid');
        
        if (this.examples && this.examples.length > 0) {
            grid.innerHTML = this.examples.map(example => `
                <div class="example-card bg-gray-50 p-4 rounded-lg border border-gray-200" data-code="${encodeURIComponent(example.code)}">
                    <h4 class="font-semibold text-gray-900 mb-2">${example.title}</h4>
                    <p class="text-sm text-gray-600 mb-3">${example.description}</p>
                    <div class="text-xs text-gray-500">
                        <i class="fas fa-code mr-1"></i>点击使用此示例
                    </div>
                </div>
            `).join('');
            
            // 绑定示例点击事件
            grid.querySelectorAll('.example-card').forEach(card => {
                card.addEventListener('click', () => {
                    const code = decodeURIComponent(card.dataset.code);
                    document.getElementById('code-editor').value = code;
                    this.hideExamples();
                });
            });
        } else {
            grid.innerHTML = '<p class="text-gray-500 text-center col-span-2">暂无示例代码</p>';
        }
        
        modal.classList.remove('hidden');
    }
    
    hideExamples() {
        document.getElementById('examples-modal').classList.add('hidden');
    }
    
    toggleFullscreen() {
        const container = document.querySelector('.container');
        const btn = document.getElementById('fullscreen-btn');
        
        if (this.isFullscreen) {
            container.classList.remove('fullscreen-mode');
            btn.innerHTML = '<i class="fas fa-expand mr-2"></i>全屏';
            this.isFullscreen = false;
        } else {
            container.classList.add('fullscreen-mode');
            btn.innerHTML = '<i class="fas fa-compress mr-2"></i>退出全屏';
            this.isFullscreen = true;
        }
    }
    
    async logExecution(code, output, error, executionTime) {
        try {
            await fetch('/php-runner/log', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    code,
                    output,
                    error,
                    execution_time: executionTime,
                    memory_usage: 'N/A'
                })
            });
        } catch (error) {
            console.error('Failed to log execution:', error);
        }
    }
}

// 初始化 PHP 运行器
document.addEventListener('DOMContentLoaded', () => {
    new PhpRunner();
});
</script>
@endsection
