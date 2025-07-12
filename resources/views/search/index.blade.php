@extends('layouts.app')

@section('title', '搜索结果')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- 搜索表单 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <form method="GET" action="{{ route('search') }}" class="space-y-4">
                <div class="flex gap-4">
                    <div class="flex-1">
                        <input type="text" 
                               name="q" 
                               value="{{ $query }}" 
                               placeholder="搜索 Gist..." 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               id="search-input"
                               autocomplete="off">
                        
                        <!-- 搜索建议下拉框 -->
                        <div id="search-suggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 hidden">
                            <!-- 动态内容 -->
                        </div>
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-search mr-2"></i>搜索
                    </button>
                </div>
                
                <!-- 快速筛选 -->
                <div class="flex flex-wrap gap-2">
                    <select name="language" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                        <option value="">所有语言</option>
                        @foreach(\App\Models\Gist::distinct('language')->whereNotNull('language')->pluck('language') as $lang)
                            <option value="{{ $lang }}" {{ request('language') == $lang ? 'selected' : '' }}>
                                {{ $lang }}
                            </option>
                        @endforeach
                    </select>
                    
                    <select name="sort" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                        <option value="relevance" {{ request('sort') == 'relevance' ? 'selected' : '' }}>相关性</option>
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>最新</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>最旧</option>
                        <option value="most_liked" {{ request('sort') == 'most_liked' ? 'selected' : '' }}>最多点赞</option>
                        <option value="most_viewed" {{ request('sort') == 'most_viewed' ? 'selected' : '' }}>最多浏览</option>
                    </select>
                    
                    <a href="{{ route('search.advanced') }}" class="px-3 py-2 text-indigo-600 hover:text-indigo-800 text-sm">
                        高级搜索
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    @if(!empty($query))
        <!-- 搜索结果统计 -->
        <div class="mb-6">
            <p class="text-gray-600">
                找到 <span class="font-semibold">{{ number_format($totalResults) }}</span> 个结果
                @if($searchTime > 0)
                    (用时 {{ $searchTime }}ms)
                @endif
            </p>
        </div>
        
        @if($results->count() > 0)
            <!-- 搜索结果 -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach($results as $gist)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="text-lg font-semibold text-gray-900 line-clamp-2">
                                    <a href="{{ route('gists.show', $gist) }}" class="hover:text-indigo-600">
                                        {!! highlightSearchTerm($gist->title, $query) !!}
                                    </a>
                                </h3>
                                @if(!$gist->is_public)
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">私有</span>
                                @endif
                            </div>
                            
                            @if($gist->description)
                                <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                                    {!! highlightSearchTerm($gist->description, $query) !!}
                                </p>
                            @endif
                            
                            <div class="flex items-center justify-between text-sm text-gray-500 mb-3">
                                <div class="flex items-center">
                                    @if($gist->user->avatar_url)
                                        <img src="{{ $gist->user->avatar_url }}" alt="{{ $gist->user->name }}" class="w-5 h-5 rounded-full mr-2">
                                    @endif
                                    <span>{{ $gist->user->name }}</span>
                                </div>
                                <span>{{ $gist->created_at->diffForHumans() }}</span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3 text-sm text-gray-500">
                                    @if($gist->language)
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                            {{ $gist->language }}
                                        </span>
                                    @endif
                                    <span><i class="fas fa-eye mr-1"></i>{{ $gist->views_count }}</span>
                                    <span><i class="fas fa-heart mr-1"></i>{{ $gist->likes_count }}</span>
                                </div>
                            </div>
                            
                            @if($gist->tags->count() > 0)
                                <div class="mt-3 flex flex-wrap gap-1">
                                    @foreach($gist->tags->take(3) as $tag)
                                        <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                    @if($gist->tags->count() > 3)
                                        <span class="text-gray-500 text-xs">+{{ $gist->tags->count() - 3 }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- 分页 -->
            <div class="flex justify-center">
                {{ $results->links() }}
            </div>
        @else
            <!-- 无结果 -->
            <div class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">没有找到相关结果</h3>
                <p class="text-gray-600 mb-4">尝试使用不同的关键词或者<a href="{{ route('search.advanced') }}" class="text-indigo-600 hover:text-indigo-800">高级搜索</a></p>
            </div>
        @endif
    @else
        <!-- 搜索提示 -->
        <div class="text-center py-12">
            <div class="text-gray-400 text-6xl mb-4">
                <i class="fas fa-search"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">搜索代码片段</h3>
            <p class="text-gray-600 mb-4">输入关键词来搜索 Gist</p>
            
            <!-- 热门搜索 -->
            <div id="trending-searches" class="mt-6">
                <!-- 动态加载 -->
            </div>
        </div>
    @endif
</div>

<script>
// 搜索建议功能
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const suggestionsContainer = document.getElementById('search-suggestions');
    let debounceTimer;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();
        
        if (query.length < 2) {
            suggestionsContainer.classList.add('hidden');
            return;
        }
        
        debounceTimer = setTimeout(() => {
            fetch(`/search/suggestions?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(suggestions => {
                    if (suggestions.length > 0) {
                        showSuggestions(suggestions);
                    } else {
                        suggestionsContainer.classList.add('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error fetching suggestions:', error);
                    suggestionsContainer.classList.add('hidden');
                });
        }, 300);
    });
    
    function showSuggestions(suggestions) {
        const html = suggestions.map(suggestion => `
            <div class="px-4 py-2 hover:bg-gray-100 cursor-pointer flex items-center" 
                 onclick="selectSuggestion('${suggestion.text}')">
                <i class="fas fa-${suggestion.icon} mr-2 text-gray-400"></i>
                <span>${suggestion.text}</span>
                <span class="ml-auto text-xs text-gray-500">${suggestion.type}</span>
            </div>
        `).join('');
        
        suggestionsContainer.innerHTML = html;
        suggestionsContainer.classList.remove('hidden');
    }
    
    function selectSuggestion(text) {
        searchInput.value = text;
        suggestionsContainer.classList.add('hidden');
        searchInput.form.submit();
    }
    
    // 点击外部隐藏建议
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            suggestionsContainer.classList.add('hidden');
        }
    });
    
    // 加载热门搜索
    if (!document.querySelector('input[name="q"]').value) {
        fetch('/search/trending')
            .then(response => response.json())
            .then(trending => {
                if (trending.length > 0) {
                    const html = `
                        <div class="text-sm text-gray-600 mb-2">热门搜索：</div>
                        <div class="flex flex-wrap gap-2 justify-center">
                            ${trending.map(term => `
                                <a href="/search?q=${encodeURIComponent(term)}" 
                                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm transition-colors">
                                    ${term}
                                </a>
                            `).join('')}
                        </div>
                    `;
                    document.getElementById('trending-searches').innerHTML = html;
                }
            });
    }
});

// 全局函数
window.selectSuggestion = function(text) {
    document.getElementById('search-input').value = text;
    document.getElementById('search-suggestions').classList.add('hidden');
    document.getElementById('search-input').form.submit();
};
</script>
@endsection
