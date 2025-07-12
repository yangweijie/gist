<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CodeBlock extends Component
{
    public string $language;
    public string $filename;
    public string $content;
    public bool $showLineNumbers;
    public bool $showToolbar;
    public bool $enableSearch;
    public bool $enableCopy;
    public string $title;
    public string $theme;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $language = 'text',
        string $filename = '',
        string $content = '',
        bool $showLineNumbers = true,
        bool $showToolbar = true,
        bool $enableSearch = true,
        bool $enableCopy = true,
        string $title = '',
        string $theme = 'default'
    ) {
        $this->language = $this->normalizeLanguage($language);
        $this->filename = $filename;
        $this->content = $content;
        $this->showLineNumbers = $showLineNumbers;
        $this->showToolbar = $showToolbar;
        $this->enableSearch = $enableSearch;
        $this->enableCopy = $enableCopy;
        $this->title = $title;
        $this->theme = $theme;
    }

    /**
     * 标准化语言名称
     */
    protected function normalizeLanguage(string $language): string
    {
        $languageMap = [
            'php' => 'php',
            'javascript' => 'javascript',
            'js' => 'javascript',
            'typescript' => 'typescript',
            'ts' => 'typescript',
            'python' => 'python',
            'py' => 'python',
            'java' => 'java',
            'c' => 'c',
            'cpp' => 'cpp',
            'c++' => 'cpp',
            'csharp' => 'csharp',
            'c#' => 'csharp',
            'ruby' => 'ruby',
            'rb' => 'ruby',
            'go' => 'go',
            'rust' => 'rust',
            'rs' => 'rust',
            'swift' => 'swift',
            'sql' => 'sql',
            'bash' => 'bash',
            'shell' => 'bash',
            'sh' => 'bash',
            'markdown' => 'markdown',
            'md' => 'markdown',
            'json' => 'json',
            'xml' => 'xml',
            'html' => 'markup',
            'css' => 'css',
            'scss' => 'scss',
            'sass' => 'sass',
            'yaml' => 'yaml',
            'yml' => 'yaml',
            'toml' => 'toml',
            'ini' => 'ini',
            'text' => 'text',
            'txt' => 'text',
        ];

        return $languageMap[strtolower($language)] ?? 'text';
    }

    /**
     * 获取语言显示名称
     */
    public function getLanguageLabel(): string
    {
        $labels = [
            'php' => 'PHP',
            'javascript' => 'JavaScript',
            'typescript' => 'TypeScript',
            'python' => 'Python',
            'java' => 'Java',
            'c' => 'C',
            'cpp' => 'C++',
            'csharp' => 'C#',
            'ruby' => 'Ruby',
            'go' => 'Go',
            'rust' => 'Rust',
            'swift' => 'Swift',
            'sql' => 'SQL',
            'bash' => 'Bash',
            'markdown' => 'Markdown',
            'json' => 'JSON',
            'xml' => 'XML',
            'markup' => 'HTML',
            'css' => 'CSS',
            'scss' => 'SCSS',
            'sass' => 'Sass',
            'yaml' => 'YAML',
            'toml' => 'TOML',
            'ini' => 'INI',
            'text' => 'Text',
        ];

        return $labels[$this->language] ?? ucfirst($this->language);
    }

    /**
     * 获取文件图标
     */
    public function getFileIcon(): string
    {
        $icons = [
            'php' => '🐘',
            'javascript' => '🟨',
            'typescript' => '🔷',
            'python' => '🐍',
            'java' => '☕',
            'c' => '⚙️',
            'cpp' => '⚙️',
            'csharp' => '🔷',
            'ruby' => '💎',
            'go' => '🐹',
            'rust' => '🦀',
            'swift' => '🍎',
            'sql' => '🗄️',
            'bash' => '💻',
            'markdown' => '📝',
            'json' => '📋',
            'xml' => '📄',
            'markup' => '🌐',
            'css' => '🎨',
            'scss' => '🎨',
            'sass' => '🎨',
            'yaml' => '⚙️',
            'toml' => '⚙️',
            'ini' => '⚙️',
            'text' => '📄',
        ];

        return $icons[$this->language] ?? '📄';
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.code-block');
    }
}
