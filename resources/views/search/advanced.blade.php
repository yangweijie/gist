@extends('layouts.app')

@section('title', '高级搜索')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- 页面标题 -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">高级搜索</h1>
            <p class="text-gray-600">使用多个条件来精确搜索您需要的代码片段</p>
        </div>
        
        <!-- 高级搜索表单 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <form method="GET" action="{{ route('search') }}" class="space-y-6">
                    <!-- 基本搜索 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="q" class="block text-sm font-medium text-gray-700 mb-2">
                                关键词
                            </label>
                            <input type="text" 
                                   id="q" 
                                   name="q" 
                                   value="{{ request('q') }}"
                                   placeholder="搜索标题、描述、内容..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <p class="text-xs text-gray-500 mt-1">支持在标题、描述、内容中搜索</p>
                        </div>
                        
                        <div>
                            <label for="language" class="block text-sm font-medium text-gray-700 mb-2">
                                编程语言
                            </label>
                            <select id="language" name="language" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">所有语言</option>
                                @foreach($languages as $language)
                                    <option value="{{ $language }}" {{ request('language') == $language ? 'selected' : '' }}>
                                        {{ $language }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- 标签和用户 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">
                                标签
                            </label>
                            <select id="tags" name="tags[]" multiple class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" size="4">
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->name }}" 
                                            {{ in_array($tag->name, (array) request('tags', [])) ? 'selected' : '' }}>
                                        {{ $tag->name }} ({{ $tag->gists_count ?? 0 }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">按住 Ctrl/Cmd 选择多个标签</p>
                        </div>
                        
                        <div>
                            <label for="user" class="block text-sm font-medium text-gray-700 mb-2">
                                作者
                            </label>
                            <select id="user" name="user" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">所有用户</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- 日期范围 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">
                                创建日期从
                            </label>
                            <input type="date" 
                                   id="date_from" 
                                   name="date_from" 
                                   value="{{ request('date_from') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">
                                创建日期到
                            </label>
                            <input type="date" 
                                   id="date_to" 
                                   name="date_to" 
                                   value="{{ request('date_to') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    
                    <!-- 排序选项 -->
                    <div>
                        <label for="sort" class="block text-sm font-medium text-gray-700 mb-2">
                            排序方式
                        </label>
                        <select id="sort" name="sort" class="w-full md:w-auto px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="relevance" {{ request('sort') == 'relevance' ? 'selected' : '' }}>相关性</option>
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>最新创建</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>最早创建</option>
                            <option value="most_liked" {{ request('sort') == 'most_liked' ? 'selected' : '' }}>最多点赞</option>
                            <option value="most_viewed" {{ request('sort') == 'most_viewed' ? 'selected' : '' }}>最多浏览</option>
                        </select>
                    </div>
                    
                    <!-- 操作按钮 -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-gray-200">
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-search mr-2"></i>搜索
                        </button>
                        
                        <a href="{{ route('search.advanced') }}" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-200 transition-colors text-center">
                            <i class="fas fa-undo mr-2"></i>重置
                        </a>
                        
                        <a href="{{ route('search') }}" class="text-indigo-600 hover:text-indigo-800 px-6 py-3 text-center">
                            <i class="fas fa-arrow-left mr-2"></i>返回简单搜索
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- 搜索提示 -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-3">搜索提示</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800">
                <div>
                    <h4 class="font-medium mb-2">关键词搜索</h4>
                    <ul class="space-y-1">
                        <li>• 支持在标题、描述、内容中搜索</li>
                        <li>• 自动匹配标签和用户名</li>
                        <li>• 不区分大小写</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-medium mb-2">筛选条件</h4>
                    <ul class="space-y-1">
                        <li>• 可以组合多个条件</li>
                        <li>• 标签支持多选</li>
                        <li>• 日期范围可以只设置一边</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 标签多选优化
    const tagsSelect = document.getElementById('tags');
    if (tagsSelect) {
        // 可以在这里添加更好的多选组件，比如 Select2 或 Choices.js
    }
    
    // 日期验证
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');
    
    dateFrom.addEventListener('change', function() {
        if (dateTo.value && this.value > dateTo.value) {
            dateTo.value = this.value;
        }
    });
    
    dateTo.addEventListener('change', function() {
        if (dateFrom.value && this.value < dateFrom.value) {
            dateFrom.value = this.value;
        }
    });
});
</script>
@endsection
