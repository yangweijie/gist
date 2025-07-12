<?php

return [
    // Page titles
    'titles' => [
        'index' => 'PHP Online Runner',
        'editor' => 'PHP Code Editor',
        'examples' => 'Code Examples',
        'help' => 'Help',
    ],

    // Editor related
    'editor' => [
        'placeholder' => 'Enter your PHP code here...',
        'run_code' => 'Run Code',
        'clear_code' => 'Clear Code',
        'reset_code' => 'Reset Code',
        'save_code' => 'Save Code',
        'load_example' => 'Load Example',
        'format_code' => 'Format Code',
        'validate_syntax' => 'Validate Syntax',
        'full_screen' => 'Full Screen',
        'exit_full_screen' => 'Exit Full Screen',
        'line_numbers' => 'Show Line Numbers',
        'word_wrap' => 'Word Wrap',
        'theme' => 'Editor Theme',
        'font_size' => 'Font Size',
    ],

    // Run status
    'status' => [
        'ready' => 'Ready',
        'running' => 'Running code...',
        'completed' => 'Code execution completed',
        'error' => 'Execution error',
        'timeout' => 'Execution timeout',
        'stopped' => 'Stopped',
        'loading' => 'Loading PHP environment...',
        'initializing' => 'Initializing...',
    ],

    // Output related
    'output' => [
        'title' => 'Execution Result',
        'stdout' => 'Standard Output',
        'stderr' => 'Error Output',
        'execution_time' => 'Execution Time',
        'memory_usage' => 'Memory Usage',
        'clear_output' => 'Clear Output',
        'copy_output' => 'Copy Output',
        'download_output' => 'Download Output',
        'no_output' => 'No output',
        'error_occurred' => 'An error occurred',
        'syntax_error' => 'Syntax Error',
        'runtime_error' => 'Runtime Error',
        'fatal_error' => 'Fatal Error',
    ],

    // Example code
    'examples' => [
        'hello_world' => [
            'title' => 'Hello World',
            'description' => 'Basic PHP output',
        ],
        'arrays' => [
            'title' => 'Array Operations',
            'description' => 'Basic PHP array operations',
        ],
        'functions' => [
            'title' => 'Function Definition',
            'description' => 'Defining and using functions',
        ],
        'classes' => [
            'title' => 'Classes and Objects',
            'description' => 'PHP object-oriented programming',
        ],
        'loops' => [
            'title' => 'Loop Structures',
            'description' => 'for, while, foreach loops',
        ],
        'conditionals' => [
            'title' => 'Conditional Statements',
            'description' => 'if, else, switch conditions',
        ],
        'strings' => [
            'title' => 'String Processing',
            'description' => 'String operations and processing',
        ],
        'files' => [
            'title' => 'File Operations',
            'description' => 'File reading and writing',
        ],
        'database' => [
            'title' => 'Database Operations',
            'description' => 'PDO database connections and queries',
        ],
        'json' => [
            'title' => 'JSON Processing',
            'description' => 'JSON encoding and decoding',
        ],
    ],

    // Actions
    'actions' => [
        'run' => 'Run',
        'stop' => 'Stop',
        'clear' => 'Clear',
        'reset' => 'Reset',
        'save' => 'Save',
        'load' => 'Load',
        'share' => 'Share',
        'download' => 'Download',
        'copy' => 'Copy',
        'format' => 'Format',
        'validate' => 'Validate',
        'help' => 'Help',
        'settings' => 'Settings',
        'examples' => 'Examples',
        'new_file' => 'New File',
        'open_file' => 'Open File',
        'save_as_gist' => 'Save as Gist',
    ],

    // Settings
    'settings' => [
        'auto_run' => 'Auto Run',
        'show_line_numbers' => 'Show Line Numbers',
        'word_wrap' => 'Word Wrap',
        'syntax_highlighting' => 'Syntax Highlighting',
        'auto_complete' => 'Auto Complete',
        'bracket_matching' => 'Bracket Matching',
        'code_folding' => 'Code Folding',
        'minimap' => 'Minimap',
        'vim_mode' => 'Vim Mode',
        'emacs_mode' => 'Emacs Mode',
    ],

    // Themes
    'themes' => [
        'light' => 'Light Theme',
        'dark' => 'Dark Theme',
        'monokai' => 'Monokai',
        'github' => 'GitHub',
        'solarized_light' => 'Solarized Light',
        'solarized_dark' => 'Solarized Dark',
        'dracula' => 'Dracula',
        'material' => 'Material',
    ],

    // Error messages
    'errors' => [
        'syntax_error' => 'Syntax error: :message',
        'runtime_error' => 'Runtime error: :message',
        'fatal_error' => 'Fatal error: :message',
        'timeout_error' => 'Code execution timeout (exceeded :seconds seconds)',
        'memory_error' => 'Out of memory',
        'security_error' => 'Code contains unsafe functions or operations',
        'network_error' => 'Network connection error',
        'server_error' => 'Server error',
        'invalid_code' => 'Invalid PHP code',
        'empty_code' => 'Code cannot be empty',
        'too_large' => 'Code file is too large',
        'unsupported_function' => 'Unsupported function: :function',
    ],

    // Success messages
    'success' => [
        'code_executed' => 'Code executed successfully',
        'code_saved' => 'Code saved successfully',
        'code_shared' => 'Code shared successfully',
        'output_copied' => 'Output copied to clipboard',
        'settings_saved' => 'Settings saved successfully',
        'example_loaded' => 'Example code loaded successfully',
        'syntax_valid' => 'Syntax check passed',
        'code_formatted' => 'Code formatted successfully',
    ],

    // Help
    'help' => [
        'title' => 'Help',
        'getting_started' => 'Getting Started',
        'keyboard_shortcuts' => 'Keyboard Shortcuts',
        'supported_functions' => 'Supported Functions',
        'limitations' => 'Limitations',
        'examples_guide' => 'Examples Guide',
        'troubleshooting' => 'Troubleshooting',
        'faq' => 'FAQ',
        'contact' => 'Contact Us',
    ],

    // Shortcuts
    'shortcuts' => [
        'run_code' => 'Ctrl+Enter - Run code',
        'save_code' => 'Ctrl+S - Save code',
        'clear_code' => 'Ctrl+L - Clear code',
        'format_code' => 'Ctrl+Shift+F - Format code',
        'full_screen' => 'F11 - Full screen mode',
        'find' => 'Ctrl+F - Find',
        'replace' => 'Ctrl+H - Replace',
        'comment' => 'Ctrl+/ - Comment/Uncomment',
        'indent' => 'Tab - Indent',
        'unindent' => 'Shift+Tab - Unindent',
    ],

    // Limitations
    'limitations' => [
        'execution_time' => 'Maximum execution time: 30 seconds',
        'memory_limit' => 'Memory limit: 128MB',
        'file_operations' => 'File system operations not supported',
        'network_access' => 'Network access not supported',
        'database_access' => 'Database connections not supported',
        'system_calls' => 'System calls not supported',
        'dangerous_functions' => 'Dangerous functions disabled',
        'output_limit' => 'Output limit: 1MB',
    ],

    // Hints
    'hints' => [
        'wasm_loading' => 'PHP WebAssembly environment is loading, please wait...',
        'first_run_slow' => 'First run may be slow, please be patient',
        'save_before_exit' => 'Please save your code before leaving',
        'use_examples' => 'You can start learning from examples',
        'syntax_check' => 'Recommend syntax check before running',
        'share_code' => 'You can save code as Gist to share with others',
        'keyboard_shortcuts' => 'Use keyboard shortcuts to improve efficiency',
        'error_details' => 'Click error message for detailed explanation',
    ],
];
