<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PHP Runner - Minimal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .editor-section {
            margin-bottom: 20px;
        }
        #code-editor {
            width: 100%;
            height: 300px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            resize: vertical;
        }
        .button-group {
            margin: 10px 0;
        }
        button {
            background: #007cba;
            color: white;
            border: none;
            padding: 10px 20px;
            margin-right: 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #005a87;
        }
        button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .output-section {
            margin-top: 20px;
        }
        #output {
            background: #1e1e1e;
            color: #f0f0f0;
            padding: 15px;
            border-radius: 4px;
            min-height: 200px;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
            overflow-y: auto;
        }
        .status {
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .loading {
            display: none;
        }
        .loading.show {
            display: inline-block;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>PHP 在线运行器 - 最小化版本</h1>
        
        <div class="editor-section">
            <h3>PHP 代码编辑器</h3>
            <textarea id="code-editor" placeholder="在这里输入 PHP 代码..."><?php
echo "Hello, World!";
echo "\n";
echo "当前时间: " . date("Y-m-d H:i:s");
?></textarea>
        </div>
        
        <div class="button-group">
            <button id="run-btn">▶ 运行代码</button>
            <button id="validate-btn">✓ 验证代码</button>
            <button id="clear-btn">🗑 清空代码</button>
            <button id="examples-btn">📚 示例代码</button>
            <button id="fullscreen-btn">⛶ 全屏模式</button>
            <span class="loading" id="loading">⟳ 处理中...</span>
        </div>
        
        <div id="status-area"></div>
        
        <div class="output-section">
            <h3>输出结果</h3>
            <div id="output">等待代码执行...</div>
        </div>
    </div>

    <script>
        // PHP 运行器 JavaScript
        class PhpRunner {
            constructor() {
                this.isLoading = false;
                this.initializeElements();
                this.bindEvents();
            }
            
            initializeElements() {
                this.codeEditor = document.getElementById('code-editor');
                this.runBtn = document.getElementById('run-btn');
                this.validateBtn = document.getElementById('validate-btn');
                this.clearBtn = document.getElementById('clear-btn');
                this.examplesBtn = document.getElementById('examples-btn');
                this.fullscreenBtn = document.getElementById('fullscreen-btn');
                this.output = document.getElementById('output');
                this.statusArea = document.getElementById('status-area');
                this.loading = document.getElementById('loading');
            }
            
            bindEvents() {
                this.runBtn.addEventListener('click', () => this.runCode());
                this.validateBtn.addEventListener('click', () => this.validateCode());
                this.clearBtn.addEventListener('click', () => this.clearCode());
                this.examplesBtn.addEventListener('click', () => this.showExamples());
                this.fullscreenBtn.addEventListener('click', () => this.toggleFullscreen());
                
                // 键盘快捷键
                this.codeEditor.addEventListener('keydown', (e) => {
                    if (e.ctrlKey && e.key === 'Enter') {
                        e.preventDefault();
                        this.runCode();
                    }
                });
            }
            
            showStatus(message, type = 'info') {
                this.statusArea.innerHTML = `<div class="status ${type}">${message}</div>`;
                setTimeout(() => {
                    this.statusArea.innerHTML = '';
                }, 5000);
            }
            
            setLoading(loading) {
                this.isLoading = loading;
                this.loading.classList.toggle('show', loading);
                this.runBtn.disabled = loading;
                this.validateBtn.disabled = loading;
            }
            
            async runCode() {
                if (this.isLoading) return;
                
                const code = this.codeEditor.value.trim();
                if (!code) {
                    this.showStatus('请输入要运行的代码', 'error');
                    return;
                }
                
                this.setLoading(true);
                this.output.textContent = '正在执行代码...';
                
                try {
                    // 模拟代码执行（实际应该调用 PHP WASM 或后端 API）
                    await this.simulateCodeExecution(code);
                } catch (error) {
                    this.output.textContent = `执行错误: ${error.message}`;
                    this.showStatus('代码执行失败', 'error');
                } finally {
                    this.setLoading(false);
                }
            }
            
            async validateCode() {
                if (this.isLoading) return;
                
                const code = this.codeEditor.value.trim();
                if (!code) {
                    this.showStatus('请输入要验证的代码', 'error');
                    return;
                }
                
                this.setLoading(true);
                
                try {
                    const response = await fetch('/php-runner/validate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ code: code })
                    });
                    
                    const result = await response.text();
                    
                    if (response.ok) {
                        this.showStatus('代码验证通过', 'success');
                        this.output.textContent = `验证结果: ${result}`;
                    } else {
                        this.showStatus('代码验证失败', 'error');
                        this.output.textContent = `验证错误: ${result}`;
                    }
                } catch (error) {
                    this.showStatus('验证请求失败', 'error');
                    this.output.textContent = `网络错误: ${error.message}`;
                } finally {
                    this.setLoading(false);
                }
            }
            
            async simulateCodeExecution(code) {
                // 模拟执行延迟
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // 简单的代码分析和模拟输出
                let output = '';
                
                if (code.includes('echo')) {
                    const echoMatches = code.match(/echo\s+["']([^"']+)["']/g);
                    if (echoMatches) {
                        echoMatches.forEach(match => {
                            const text = match.match(/["']([^"']+)["']/)[1];
                            output += text + '\n';
                        });
                    }
                }
                
                if (code.includes('date(')) {
                    output += new Date().toLocaleString() + '\n';
                }
                
                if (code.includes('phpinfo()')) {
                    output += 'PHP 信息 (模拟)\nPHP Version: 8.3.0\nSystem: Browser WASM\n';
                }
                
                if (!output) {
                    output = '代码执行完成 (无输出)';
                }
                
                this.output.textContent = output;
                this.showStatus('代码执行成功', 'success');
            }
            
            clearCode() {
                this.codeEditor.value = '';
                this.output.textContent = '等待代码执行...';
                this.showStatus('代码已清空', 'info');
            }
            
            showExamples() {
                const examples = [
                    '<?php\necho "Hello, World!";\n?>',
                    '<?php\nfor ($i = 1; $i <= 5; $i++) {\n    echo "数字: $i\\n";\n}\n?>',
                    '<?php\n$arr = [1, 2, 3, 4, 5];\nforeach ($arr as $num) {\n    echo $num * 2 . "\\n";\n}\n?>',
                    '<?php\nfunction fibonacci($n) {\n    if ($n <= 1) return $n;\n    return fibonacci($n-1) + fibonacci($n-2);\n}\necho "斐波那契数列第10项: " . fibonacci(10);\n?>'
                ];
                
                const randomExample = examples[Math.floor(Math.random() * examples.length)];
                this.codeEditor.value = randomExample;
                this.showStatus('已加载示例代码', 'success');
            }
            
            toggleFullscreen() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen();
                    this.fullscreenBtn.textContent = '⛶ 退出全屏';
                } else {
                    document.exitFullscreen();
                    this.fullscreenBtn.textContent = '⛶ 全屏模式';
                }
            }
        }
        
        // 初始化 PHP 运行器
        document.addEventListener('DOMContentLoaded', () => {
            window.phpRunner = new PhpRunner();
            console.log('PHP Runner initialized successfully');
        });
    </script>
</body>
</html>
