<?php

return [
    // 页面标题
    'titles' => [
        'index' => 'PHP 在线运行器',
        'editor' => 'PHP 代码编辑器',
        'examples' => '代码示例',
        'help' => '使用帮助',
    ],

    // 编辑器相关
    'editor' => [
        'placeholder' => '在此输入您的 PHP 代码...',
        'run_code' => '运行代码',
        'clear_code' => '清空代码',
        'reset_code' => '重置代码',
        'save_code' => '保存代码',
        'load_example' => '加载示例',
        'format_code' => '格式化代码',
        'validate_syntax' => '语法检查',
        'full_screen' => '全屏模式',
        'exit_full_screen' => '退出全屏',
        'line_numbers' => '显示行号',
        'word_wrap' => '自动换行',
        'theme' => '编辑器主题',
        'font_size' => '字体大小',
    ],

    // 运行状态
    'status' => [
        'ready' => '准备就绪',
        'running' => '正在运行代码...',
        'completed' => '代码运行完成',
        'error' => '运行出错',
        'timeout' => '运行超时',
        'stopped' => '已停止',
        'loading' => '正在加载 PHP 环境...',
        'initializing' => '正在初始化...',
    ],

    // 输出相关
    'output' => [
        'title' => '运行结果',
        'stdout' => '标准输出',
        'stderr' => '错误输出',
        'execution_time' => '执行时间',
        'memory_usage' => '内存使用',
        'clear_output' => '清空输出',
        'copy_output' => '复制输出',
        'download_output' => '下载输出',
        'no_output' => '没有输出',
        'error_occurred' => '发生错误',
        'syntax_error' => '语法错误',
        'runtime_error' => '运行时错误',
        'fatal_error' => '致命错误',
    ],

    // 示例代码
    'examples' => [
        'hello_world' => [
            'title' => 'Hello World',
            'description' => '基础的 PHP 输出',
        ],
        'arrays' => [
            'title' => '数组操作',
            'description' => 'PHP 数组的基本操作',
        ],
        'functions' => [
            'title' => '函数定义',
            'description' => '定义和使用函数',
        ],
        'classes' => [
            'title' => '类和对象',
            'description' => 'PHP 面向对象编程',
        ],
        'loops' => [
            'title' => '循环结构',
            'description' => 'for、while、foreach 循环',
        ],
        'conditionals' => [
            'title' => '条件语句',
            'description' => 'if、else、switch 条件判断',
        ],
        'strings' => [
            'title' => '字符串处理',
            'description' => '字符串操作和处理',
        ],
        'files' => [
            'title' => '文件操作',
            'description' => '文件读写和处理',
        ],
        'database' => [
            'title' => '数据库操作',
            'description' => 'PDO 数据库连接和查询',
        ],
        'json' => [
            'title' => 'JSON 处理',
            'description' => 'JSON 编码和解码',
        ],
    ],

    // 按钮和操作
    'actions' => [
        'run' => '运行',
        'stop' => '停止',
        'clear' => '清空',
        'reset' => '重置',
        'save' => '保存',
        'load' => '加载',
        'share' => '分享',
        'download' => '下载',
        'copy' => '复制',
        'format' => '格式化',
        'validate' => '验证',
        'help' => '帮助',
        'settings' => '设置',
        'examples' => '示例',
        'new_file' => '新建文件',
        'open_file' => '打开文件',
        'save_as_gist' => '保存为 Gist',
    ],

    // 设置选项
    'settings' => [
        'auto_run' => '自动运行',
        'show_line_numbers' => '显示行号',
        'word_wrap' => '自动换行',
        'syntax_highlighting' => '语法高亮',
        'auto_complete' => '自动补全',
        'bracket_matching' => '括号匹配',
        'code_folding' => '代码折叠',
        'minimap' => '小地图',
        'vim_mode' => 'Vim 模式',
        'emacs_mode' => 'Emacs 模式',
    ],

    // 主题选项
    'themes' => [
        'light' => '浅色主题',
        'dark' => '深色主题',
        'monokai' => 'Monokai',
        'github' => 'GitHub',
        'solarized_light' => 'Solarized Light',
        'solarized_dark' => 'Solarized Dark',
        'dracula' => 'Dracula',
        'material' => 'Material',
    ],

    // 错误消息
    'errors' => [
        'syntax_error' => '语法错误：:message',
        'runtime_error' => '运行时错误：:message',
        'fatal_error' => '致命错误：:message',
        'timeout_error' => '代码执行超时（超过 :seconds 秒）',
        'memory_error' => '内存不足',
        'security_error' => '代码包含不安全的函数或操作',
        'network_error' => '网络连接错误',
        'server_error' => '服务器错误',
        'invalid_code' => '无效的 PHP 代码',
        'empty_code' => '代码不能为空',
        'too_large' => '代码文件过大',
        'unsupported_function' => '不支持的函数：:function',
    ],

    // 成功消息
    'success' => [
        'code_executed' => '代码执行成功',
        'code_saved' => '代码保存成功',
        'code_shared' => '代码分享成功',
        'output_copied' => '输出已复制到剪贴板',
        'settings_saved' => '设置保存成功',
        'example_loaded' => '示例代码加载成功',
        'syntax_valid' => '语法检查通过',
        'code_formatted' => '代码格式化完成',
    ],

    // 帮助信息
    'help' => [
        'title' => '使用帮助',
        'getting_started' => '快速开始',
        'keyboard_shortcuts' => '键盘快捷键',
        'supported_functions' => '支持的函数',
        'limitations' => '使用限制',
        'examples_guide' => '示例指南',
        'troubleshooting' => '故障排除',
        'faq' => '常见问题',
        'contact' => '联系我们',
    ],

    // 快捷键
    'shortcuts' => [
        'run_code' => 'Ctrl+Enter - 运行代码',
        'save_code' => 'Ctrl+S - 保存代码',
        'clear_code' => 'Ctrl+L - 清空代码',
        'format_code' => 'Ctrl+Shift+F - 格式化代码',
        'full_screen' => 'F11 - 全屏模式',
        'find' => 'Ctrl+F - 查找',
        'replace' => 'Ctrl+H - 替换',
        'comment' => 'Ctrl+/ - 注释/取消注释',
        'indent' => 'Tab - 缩进',
        'unindent' => 'Shift+Tab - 取消缩进',
    ],

    // 限制说明
    'limitations' => [
        'execution_time' => '最大执行时间：30 秒',
        'memory_limit' => '内存限制：128MB',
        'file_operations' => '不支持文件系统操作',
        'network_access' => '不支持网络访问',
        'database_access' => '不支持数据库连接',
        'system_calls' => '不支持系统调用',
        'dangerous_functions' => '禁用危险函数',
        'output_limit' => '输出限制：1MB',
    ],

    // 提示信息
    'hints' => [
        'wasm_loading' => 'PHP WebAssembly 环境正在加载，请稍候...',
        'first_run_slow' => '首次运行可能较慢，请耐心等待',
        'save_before_exit' => '离开前请保存您的代码',
        'use_examples' => '可以从示例开始学习',
        'syntax_check' => '运行前建议先进行语法检查',
        'share_code' => '可以将代码保存为 Gist 分享给他人',
        'keyboard_shortcuts' => '使用键盘快捷键可以提高效率',
        'error_details' => '点击错误信息查看详细说明',
    ],
];
