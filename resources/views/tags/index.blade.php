@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- 页面标题 -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">标签管理</h1>
                <p class="text-gray-600 mt-2">管理和浏览所有标签</p>
            </div>
            @auth
                <a href="{{ route('tags.create') }}" 
                   class="bg-indigo-600 text-white px-6 py-3 rounded-md hover:bg-indigo-700 transition-colors">
                    创建新标签
                </a>
            @endauth
        </div>

        <!-- 搜索和筛选 -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('tags.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- 搜索框 -->
                    <div>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="搜索标签..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- 最小使用次数 -->
                    <div>
                        <select name="min_usage" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">所有标签</option>
                            <option value="1" {{ request('min_usage') === '1' ? 'selected' : '' }}>至少使用 1 次</option>
                            <option value="5" {{ request('min_usage') === '5' ? 'selected' : '' }}>至少使用 5 次</option>
                            <option value="10" {{ request('min_usage') === '10' ? 'selected' : '' }}>至少使用 10 次</option>
                        </select>
                    </div>

                    <!-- 特色标签 -->
                    <div>
                        <select name="featured" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">所有标签</option>
                            <option value="1" {{ request('featured') === '1' ? 'selected' : '' }}>仅特色标签</option>
                        </select>
                    </div>

                    <!-- 排序 -->
                    <div>
                        <select name="sort" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>按使用次数</option>
                            <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>按名称</option>
                            <option value="created" {{ request('sort') === 'created' ? 'selected' : '' }}>按创建时间</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors">
                        筛选
                    </button>
                    @if(request()->hasAny(['search', 'min_usage', 'featured', 'sort']))
                        <a href="{{ route('tags.index') }}" class="text-gray-500 hover:text-gray-700">
                            清除筛选
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- 特色标签 -->
        @if($featuredTags->count() > 0)
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">特色标签</h2>
                <div class="flex flex-wrap gap-3">
                    @foreach($featuredTags as $tag)
                        <a href="{{ route('tags.show', $tag) }}" 
                           class="inline-flex items-center gap-2 {{ $tag->color_class }} px-3 py-2 rounded-md hover:opacity-80 transition-opacity">
                            <span class="font-medium">{{ $tag->name }}</span>
                            <span class="text-xs opacity-75">({{ $tag->gists_count }})</span>
                            <span class="text-xs">⭐</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- 标签列表 -->
        @if($tags->count() > 0)
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        所有标签 ({{ $tags->total() }})
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($tags as $tag)
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between mb-2">
                                    <a href="{{ route('tags.show', $tag) }}" 
                                       class="flex items-center gap-2">
                                        <span class="{{ $tag->color_class }} px-2 py-1 rounded text-sm font-medium">
                                            {{ $tag->name }}
                                        </span>
                                        @if($tag->is_featured)
                                            <span class="text-yellow-500 text-sm">⭐</span>
                                        @endif
                                    </a>
                                    
                                    @auth
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('tags.edit', $tag) }}" 
                                               class="text-gray-400 hover:text-gray-600 transition-colors"
                                               title="编辑">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            <form method="POST" action="{{ route('tags.destroy', $tag) }}" 
                                                  class="inline" 
                                                  onsubmit="return confirm('确定要删除这个标签吗？')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-gray-400 hover:text-red-600 transition-colors"
                                                        title="删除">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    @endauth
                                </div>
                                
                                @if($tag->description)
                                    <p class="text-gray-600 text-sm mb-2">{{ $tag->description }}</p>
                                @endif
                                
                                <div class="flex items-center justify-between text-sm text-gray-500">
                                    <span>{{ $tag->gists_count }} 个 Gist</span>
                                    <span>{{ $tag->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 分页 -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $tags->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-gray-500 text-lg mb-4">
                    @if(request()->hasAny(['search', 'min_usage', 'featured']))
                        没有找到符合条件的标签
                    @else
                        还没有任何标签
                    @endif
                </div>
                @auth
                    <a href="{{ route('tags.create') }}" 
                       class="bg-indigo-600 text-white px-6 py-3 rounded-md hover:bg-indigo-700 transition-colors">
                        创建第一个标签
                    </a>
                @endauth
            </div>
        @endif
    </div>
</div>
@endsection
