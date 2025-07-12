@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- 标签信息 -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <span class="{{ $tag->color_class }} px-4 py-2 rounded-lg text-lg font-semibold">
                        {{ $tag->name }}
                    </span>
                    @if($tag->is_featured)
                        <span class="text-yellow-500 text-xl" title="特色标签">⭐</span>
                    @endif
                </div>
                
                @auth
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('tags.edit', $tag) }}" 
                           class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors">
                            编辑标签
                        </a>
                        <form method="POST" action="{{ route('tags.destroy', $tag) }}" 
                              class="inline" 
                              onsubmit="return confirm('确定要删除这个标签吗？这将影响所有使用该标签的 Gist。')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors">
                                删除标签
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
            
            @if($tag->description)
                <p class="text-gray-600 mb-4">{{ $tag->description }}</p>
            @endif
            
            <div class="flex items-center space-x-6 text-sm text-gray-500">
                <span>{{ $gists->total() }} 个 Gist</span>
                <span>创建于 {{ $tag->created_at->format('Y-m-d') }}</span>
                @if($tag->updated_at->ne($tag->created_at))
                    <span>更新于 {{ $tag->updated_at->diffForHumans() }}</span>
                @endif
            </div>
        </div>

        <!-- 相关标签 -->
        @if($relatedTags->count() > 0)
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">相关标签</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($relatedTags as $relatedTag)
                        <a href="{{ route('tags.show', $relatedTag) }}" 
                           class="{{ $relatedTag->color_class }} px-3 py-1 rounded-md hover:opacity-80 transition-opacity">
                            {{ $relatedTag->name }}
                            <span class="text-xs opacity-75">({{ $relatedTag->gists_count }})</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- 搜索和筛选 -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('tags.show', $tag) }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- 搜索框 -->
                    <div>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="在该标签的 Gist 中搜索..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- 排序 -->
                    <div>
                        <select name="sort" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>最新创建</option>
                            <option value="updated" {{ request('sort') === 'updated' ? 'selected' : '' }}>最近更新</option>
                            <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>最受欢迎</option>
                            <option value="views" {{ request('sort') === 'views' ? 'selected' : '' }}>浏览最多</option>
                        </select>
                    </div>

                    <!-- 提交按钮 -->
                    <div>
                        <button type="submit" class="w-full bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors">
                            筛选
                        </button>
                    </div>
                </div>

                @if(request()->hasAny(['search', 'sort']))
                    <div class="text-center">
                        <a href="{{ route('tags.show', $tag) }}" class="text-gray-500 hover:text-gray-700">
                            清除筛选
                        </a>
                    </div>
                @endif
            </form>
        </div>

        <!-- Gist 列表 -->
        @if($gists->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($gists as $gist)
                    @include('partials.gist-card', ['gist' => $gist])
                @endforeach
            </div>

            <!-- 分页 -->
            <div class="mt-8">
                {{ $gists->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-gray-500 text-lg mb-4">
                    @if(request()->hasAny(['search']))
                        在标签 "{{ $tag->name }}" 中没有找到符合条件的 Gist
                    @else
                        标签 "{{ $tag->name }}" 下还没有任何 Gist
                    @endif
                </div>
                @auth
                    <a href="{{ route('gists.create') }}" 
                       class="bg-indigo-600 text-white px-6 py-3 rounded-md hover:bg-indigo-700 transition-colors">
                        创建新 Gist
                    </a>
                @endauth
            </div>
        @endif
    </div>
</div>
@endsection
