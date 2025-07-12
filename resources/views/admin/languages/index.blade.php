@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- 页面标题 -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ __('common.navigation.languages') }}
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                {{ __('admin.languages.description') }}
            </p>
        </div>

        <!-- 统计卡片 -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('admin.languages.total_languages') }}</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ count($languages) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('admin.languages.enabled_languages') }}</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $enabledCount }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('admin.languages.translation_progress') }}</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $averageProgress }}%</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('admin.languages.total_keys') }}</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalKeys }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 操作按钮 -->
        <div class="mb-6 flex flex-wrap gap-4">
            <button onclick="checkIntegrity()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ __('admin.languages.check_integrity') }}
            </button>

            <button onclick="syncTranslations()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                {{ __('admin.languages.sync_translations') }}
            </button>

            <button onclick="exportTranslations()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                {{ __('admin.languages.export_translations') }}
            </button>

            <button onclick="generateSitemap()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                </svg>
                {{ __('admin.languages.generate_sitemap') }}
            </button>
        </div>

        <!-- 语言列表 -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('admin.languages.language_list') }}
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('admin.languages.language') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('admin.languages.status') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('admin.languages.progress') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('admin.languages.files') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('admin.languages.keys') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('admin.languages.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($languages as $locale => $config)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-2xl mr-3">{{ $config['flag'] }}</span>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $config['native'] }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $config['name'] }} ({{ $locale }})
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($config['enabled'])
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            {{ __('common.status.enabled') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                            {{ __('common.status.disabled') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $progress = $languageStats[$locale]['progress'] ?? 0;
                                    @endphp
                                    <div class="flex items-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 dark:bg-gray-700 mr-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $progress }}%"></div>
                                        </div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $progress }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $languageStats[$locale]['files'] ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $languageStats[$locale]['keys'] ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button onclick="editLanguage('{{ $locale }}')" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            {{ __('common.actions.edit') }}
                                        </button>
                                        <button onclick="checkLanguage('{{ $locale }}')" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                            {{ __('admin.languages.check') }}
                                        </button>
                                        @if(!$config['enabled'])
                                            <button onclick="enableLanguage('{{ $locale }}')" class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300">
                                                {{ __('common.actions.enable') }}
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 添加新语言按钮 -->
        <div class="mt-6 text-center">
            <button onclick="showAddLanguageModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg flex items-center mx-auto">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                {{ __('admin.languages.add_language') }}
            </button>
        </div>
    </div>
</div>

<!-- 操作结果模态框 -->
<div id="resultModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-2xl w-full mx-4 max-h-96 overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 id="resultTitle" class="text-lg font-semibold text-gray-900 dark:text-white"></h3>
            <button onclick="closeResultModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="resultContent" class="text-sm text-gray-600 dark:text-gray-400"></div>
    </div>
</div>

@push('scripts')
<script>
// 检查翻译完整性
async function checkIntegrity() {
    showLoading('{{ __("admin.languages.checking_integrity") }}');
    
    try {
        const response = await fetch('/admin/languages/check-integrity', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
        });
        
        const result = await response.json();
        showResult('{{ __("admin.languages.integrity_check_result") }}', formatIntegrityResult(result));
    } catch (error) {
        showResult('{{ __("common.messages.error") }}', error.message);
    }
}

// 同步翻译
async function syncTranslations() {
    showLoading('{{ __("admin.languages.syncing_translations") }}');
    
    try {
        const response = await fetch('/admin/languages/sync', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });
        
        const result = await response.json();
        showResult('{{ __("admin.languages.sync_result") }}', result.message);
        
        // 刷新页面
        setTimeout(() => location.reload(), 2000);
    } catch (error) {
        showResult('{{ __("common.messages.error") }}', error.message);
    }
}

// 导出翻译
async function exportTranslations() {
    try {
        const response = await fetch('/admin/languages/export', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'translations.json';
            a.click();
            window.URL.revokeObjectURL(url);
        }
    } catch (error) {
        showResult('{{ __("common.messages.error") }}', error.message);
    }
}

// 生成 Sitemap
async function generateSitemap() {
    showLoading('{{ __("admin.languages.generating_sitemap") }}');
    
    try {
        const response = await fetch('/admin/languages/generate-sitemap', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });
        
        const result = await response.json();
        showResult('{{ __("admin.languages.sitemap_result") }}', result.message);
    } catch (error) {
        showResult('{{ __("common.messages.error") }}', error.message);
    }
}

// 显示加载状态
function showLoading(message) {
    document.getElementById('resultTitle').textContent = '{{ __("common.messages.loading") }}';
    document.getElementById('resultContent').innerHTML = `<p>${message}</p>`;
    document.getElementById('resultModal').classList.remove('hidden');
    document.getElementById('resultModal').classList.add('flex');
}

// 显示结果
function showResult(title, content) {
    document.getElementById('resultTitle').textContent = title;
    document.getElementById('resultContent').innerHTML = content;
    document.getElementById('resultModal').classList.remove('hidden');
    document.getElementById('resultModal').classList.add('flex');
}

// 关闭结果模态框
function closeResultModal() {
    document.getElementById('resultModal').classList.add('hidden');
    document.getElementById('resultModal').classList.remove('flex');
}

// 格式化完整性检查结果
function formatIntegrityResult(result) {
    let html = '<div class="space-y-4">';
    
    // 摘要
    html += '<div class="bg-blue-50 dark:bg-blue-900 p-4 rounded">';
    html += '<h4 class="font-semibold mb-2">{{ __("admin.languages.summary") }}</h4>';
    html += `<p>{{ __("admin.languages.total_locales") }}: ${result.summary.total_locales}</p>`;
    html += `<p>{{ __("admin.languages.total_files") }}: ${result.summary.total_files}</p>`;
    html += `<p>{{ __("admin.languages.total_keys") }}: ${result.summary.total_keys}</p>`;
    html += `<p>{{ __("admin.languages.missing_keys") }}: ${result.summary.missing_keys}</p>`;
    html += '</div>';
    
    // 问题
    if (result.issues && result.issues.length > 0) {
        html += '<div class="bg-red-50 dark:bg-red-900 p-4 rounded">';
        html += '<h4 class="font-semibold mb-2">{{ __("admin.languages.issues") }}</h4>';
        html += '<ul class="list-disc list-inside space-y-1">';
        result.issues.forEach(issue => {
            html += `<li class="text-sm">${issue.message}</li>`;
        });
        html += '</ul>';
        html += '</div>';
    } else {
        html += '<div class="bg-green-50 dark:bg-green-900 p-4 rounded">';
        html += '<p class="text-green-800 dark:text-green-200">{{ __("admin.languages.no_issues") }}</p>';
        html += '</div>';
    }
    
    html += '</div>';
    return html;
}

// 点击外部关闭模态框
document.getElementById('resultModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeResultModal();
    }
});
</script>
@endpush
@endsection
