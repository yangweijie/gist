@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- 页面标题 -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">编辑 Gist</h1>
            <p class="text-gray-600 mt-2">修改你的代码片段</p>
        </div>

        <!-- 编辑表单 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <form action="{{ route('gists.update', $gist) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- 标题 -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        标题 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" name="title" value="{{ old('title', $gist->title) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-500 @enderror"
                           placeholder="为你的 Gist 起个名字...">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 描述 -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        描述
                    </label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-500 @enderror"
                              placeholder="简单描述一下这个代码片段...">{{ old('description', $gist->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 语言和文件名 -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="language" class="block text-sm font-medium text-gray-700 mb-2">
                            编程语言 <span class="text-red-500">*</span>
                        </label>
                        <select id="language" name="language" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('language') border-red-500 @enderror">
                            <option value="">选择语言</option>
                            @foreach($languages as $key => $value)
                                <option value="{{ $key }}" {{ old('language', $gist->language) === $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                        @error('language')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="filename" class="block text-sm font-medium text-gray-700 mb-2">
                            文件名
                        </label>
                        <input type="text" id="filename" name="filename" value="{{ old('filename', $gist->filename) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('filename') border-red-500 @enderror"
                               placeholder="自动生成">
                        @error('filename')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- 代码内容 -->
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                        代码内容 <span class="text-red-500">*</span>
                    </label>
                    <textarea id="content" name="content" rows="20" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-sm @error('content') border-red-500 @enderror"
                              placeholder="在这里输入你的代码...">{{ old('content', $gist->content) }}</textarea>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 标签 -->
                <div>
                    <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">
                        标签
                    </label>
                    <input type="text" id="tags" name="tags" 
                           value="{{ old('tags', $gist->tags->pluck('name')->implode(', ')) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('tags') border-red-500 @enderror"
                           placeholder="用逗号分隔多个标签，如：php, laravel, web">
                    <p class="mt-1 text-sm text-gray-500">用逗号分隔多个标签</p>
                    @error('tags')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 设置选项 -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">设置</h3>
                    
                    <!-- 可见性 -->
                    <div class="flex items-center">
                        <input type="checkbox" id="is_public" name="is_public" value="1" 
                               {{ old('is_public', $gist->is_public) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="is_public" class="ml-2 block text-sm text-gray-900">
                            公开 Gist（其他用户可以查看）
                        </label>
                    </div>

                    <!-- GitHub 同步 -->
                    @if(Auth::user()->github_token)
                        <div class="flex items-center">
                            <input type="checkbox" id="sync_to_github" name="sync_to_github" value="1" 
                                   {{ old('sync_to_github') ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="sync_to_github" class="ml-2 block text-sm text-gray-900">
                                同步更新到 GitHub Gist
                                @if($gist->github_gist_id)
                                    <span class="text-green-600 text-xs ml-1">(已关联)</span>
                                @endif
                            </label>
                        </div>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        要同步到 GitHub，请先 
                                        <a href="{{ route('auth.github.bind') }}" class="font-medium underline">绑定 GitHub 账户</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- 提交按钮 -->
                <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                    <div class="flex space-x-4">
                        <a href="{{ route('gists.show', $gist) }}" 
                           class="text-gray-600 hover:text-gray-800 transition-colors">
                            取消
                        </a>
                        @can('delete', $gist)
                            <form action="{{ route('gists.destroy', $gist) }}" method="POST" class="inline"
                                  onsubmit="return confirm('确定要删除这个 Gist 吗？此操作不可恢复。')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 transition-colors">
                                    删除
                                </button>
                            </form>
                        @endcan
                    </div>
                    <button type="submit" 
                            class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                        更新 Gist
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// 根据语言自动生成文件名
document.getElementById('language').addEventListener('change', function() {
    const language = this.value;
    const filenameInput = document.getElementById('filename');
    
    if (language && !filenameInput.value) {
        const extensions = {
            'php': 'php',
            'javascript': 'js',
            'python': 'py',
            'html': 'html',
            'css': 'css',
            'java': 'java',
            'c': 'c',
            'cpp': 'cpp',
            'ruby': 'rb',
            'go': 'go',
            'rust': 'rs',
            'swift': 'swift',
            'sql': 'sql',
            'shell': 'sh',
            'markdown': 'md',
            'json': 'json',
            'xml': 'xml',
            'yaml': 'yml'
        };
        
        const extension = extensions[language] || 'txt';
        filenameInput.value = 'gist.' + extension;
    }
});
</script>
@endsection
