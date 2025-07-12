@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800">
    <div class="container mx-auto px-4 py-8">
        <!-- 页面标题 -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-6xl font-bold text-gray-900 dark:text-white mb-4">
                🌍 {{ __('common.demo.i18n_title') }}
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                {{ __('common.demo.i18n_description') }}
            </p>
            
            <!-- 语言切换器 -->
            <div class="mt-8 flex justify-center">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">
                        {{ __('common.language.select_language') }}
                    </h3>
                    <x-language-switcher />
                </div>
            </div>
        </div>

        <!-- 功能展示网格 -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            <!-- 智能语言检测 -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center mb-4">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white ml-4">
                        {{ __('common.demo.smart_detection') }}
                    </h3>
                </div>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    {{ __('common.demo.smart_detection_desc') }}
                </p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('common.demo.current_locale') }}:</span>
                        <span class="font-medium">@locale (@localeName)</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('common.demo.browser_lang') }}:</span>
                        <span class="font-medium" id="browserLang">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('common.demo.detection_method') }}:</span>
                        <span class="font-medium">{{ session('locale_detection_method', 'default') }}</span>
                    </div>
                </div>
            </div>

            <!-- 实时翻译 -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center mb-4">
                    <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white ml-4">
                        {{ __('common.demo.real_time_translation') }}
                    </h3>
                </div>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    {{ __('common.demo.real_time_desc') }}
                </p>
                <div class="space-y-3">
                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="text-sm text-gray-500 mb-1">{{ __('common.demo.sample_text') }}:</div>
                        <div class="font-medium">{{ __('common.demo.welcome_message', ['name' => 'Developer']) }}</div>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="text-sm text-gray-500 mb-1">{{ __('common.demo.date_format') }}:</div>
                        <div class="font-medium">@formatDate(now(), 'datetime')</div>
                    </div>
                </div>
            </div>

            <!-- SEO 优化 -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center mb-4">
                    <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white ml-4">
                        {{ __('common.demo.seo_optimization') }}
                    </h3>
                </div>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    {{ __('common.demo.seo_desc') }}
                </p>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span>{{ __('common.demo.hreflang_tags') }}</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span>{{ __('common.demo.multilingual_sitemap') }}</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span>{{ __('common.demo.structured_data') }}</span>
                    </div>
                </div>
            </div>

            <!-- 性能优化 -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center mb-4">
                    <div class="p-3 bg-orange-100 dark:bg-orange-900 rounded-lg">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white ml-4">
                        {{ __('common.demo.performance_optimization') }}
                    </h3>
                </div>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    {{ __('common.demo.performance_desc') }}
                </p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('common.demo.cache_enabled') }}:</span>
                        <span class="font-medium">{{ config('localization.cache.enabled') ? '✅' : '❌' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('common.demo.lazy_loading') }}:</span>
                        <span class="font-medium">{{ config('localization.performance.lazy_loading.enabled') ? '✅' : '❌' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('common.demo.preload_critical') }}:</span>
                        <span class="font-medium">{{ config('localization.performance.preload.enabled') ? '✅' : '❌' }}</span>
                    </div>
                </div>
            </div>

            <!-- 管理工具 -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center mb-4">
                    <div class="p-3 bg-red-100 dark:bg-red-900 rounded-lg">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white ml-4">
                        {{ __('common.demo.management_tools') }}
                    </h3>
                </div>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    {{ __('common.demo.management_desc') }}
                </p>
                <div class="space-y-2">
                    <button onclick="runCommand('translation:manage stats')" class="w-full text-left p-2 bg-gray-50 dark:bg-gray-700 rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                        <span class="text-sm font-medium">{{ __('common.demo.check_stats') }}</span>
                    </button>
                    <button onclick="runCommand('translation:check-integrity')" class="w-full text-left p-2 bg-gray-50 dark:bg-gray-700 rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                        <span class="text-sm font-medium">{{ __('common.demo.check_integrity') }}</span>
                    </button>
                    <button onclick="runCommand('translation:warmup')" class="w-full text-left p-2 bg-gray-50 dark:bg-gray-700 rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                        <span class="text-sm font-medium">{{ __('common.demo.warmup_cache') }}</span>
                    </button>
                </div>
            </div>

            <!-- 多语言支持 -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center mb-4">
                    <div class="p-3 bg-indigo-100 dark:bg-indigo-900 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white ml-4">
                        {{ __('common.demo.language_support') }}
                    </h3>
                </div>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    {{ __('common.demo.language_support_desc') }}
                </p>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    @foreach(config('localization.supported_locales', []) as $locale => $config)
                        <div class="flex items-center p-2 bg-gray-50 dark:bg-gray-700 rounded">
                            <span class="text-lg mr-2">{{ $config['flag'] }}</span>
                            <div>
                                <div class="font-medium">{{ $config['native'] }}</div>
                                <div class="text-xs text-gray-500">{{ $config['name'] }}</div>
                            </div>
                            @if($config['enabled'])
                                <span class="ml-auto text-green-500">✓</span>
                            @else
                                <span class="ml-auto text-gray-400">○</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 技术规格 -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 mb-12">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">
                {{ __('common.demo.technical_specs') }}
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-2">
                        {{ count(config('localization.supported_locales', [])) }}
                    </div>
                    <div class="text-gray-600 dark:text-gray-300">{{ __('common.demo.supported_languages') }}</div>
                </div>
                
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600 dark:text-green-400 mb-2">
                        500+
                    </div>
                    <div class="text-gray-600 dark:text-gray-300">{{ __('common.demo.translation_keys') }}</div>
                </div>
                
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600 dark:text-purple-400 mb-2">
                        6
                    </div>
                    <div class="text-gray-600 dark:text-gray-300">{{ __('common.demo.detection_methods') }}</div>
                </div>
                
                <div class="text-center">
                    <div class="text-3xl font-bold text-orange-600 dark:text-orange-400 mb-2">
                        100ms
                    </div>
                    <div class="text-gray-600 dark:text-gray-300">{{ __('common.demo.avg_load_time') }}</div>
                </div>
            </div>
        </div>

        <!-- 行动号召 -->
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                {{ __('common.demo.ready_to_start') }}
            </h2>
            <p class="text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto">
                {{ __('common.demo.start_description') }}
            </p>
            
            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('gists.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold transition-colors duration-200">
                    {{ __('common.demo.explore_gists') }}
                </a>
                <a href="{{ route('tags.index') }}" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold transition-colors duration-200">
                    {{ __('common.demo.browse_tags') }}
                </a>
                <a href="{{ route('php-runner.index') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-8 py-3 rounded-lg font-semibold transition-colors duration-200">
                    {{ __('common.demo.try_php_runner') }}
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 命令执行结果模态框 -->
<div id="commandModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-4xl w-full mx-4 max-h-96 overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 id="commandTitle" class="text-lg font-semibold text-gray-900 dark:text-white"></h3>
            <button onclick="closeCommandModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="commandOutput" class="bg-gray-900 text-green-400 p-4 rounded font-mono text-sm overflow-x-auto"></div>
    </div>
</div>

@push('scripts')
<script>
// 检测浏览器语言
document.addEventListener('DOMContentLoaded', function() {
    const browserLang = navigator.language || navigator.userLanguage;
    document.getElementById('browserLang').textContent = browserLang;
});

// 运行命令演示
async function runCommand(command) {
    document.getElementById('commandTitle').textContent = `Running: ${command}`;
    document.getElementById('commandOutput').innerHTML = '<div class="text-yellow-400">Executing command...</div>';
    document.getElementById('commandModal').classList.remove('hidden');
    document.getElementById('commandModal').classList.add('flex');
    
    try {
        const response = await fetch('/demo/run-command', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ command: command }),
        });
        
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('commandOutput').innerHTML = 
                `<div class="text-green-400">✅ Command executed successfully</div>
                 <pre class="mt-2 whitespace-pre-wrap">${result.output}</pre>`;
        } else {
            document.getElementById('commandOutput').innerHTML = 
                `<div class="text-red-400">❌ Command failed</div>
                 <pre class="mt-2 whitespace-pre-wrap">${result.error}</pre>`;
        }
    } catch (error) {
        document.getElementById('commandOutput').innerHTML = 
            `<div class="text-red-400">❌ Network error</div>
             <pre class="mt-2">${error.message}</pre>`;
    }
}

// 关闭命令模态框
function closeCommandModal() {
    document.getElementById('commandModal').classList.add('hidden');
    document.getElementById('commandModal').classList.remove('flex');
}

// 点击外部关闭模态框
document.getElementById('commandModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCommandModal();
    }
});
</script>
@endpush
@endsection
