@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- 页面标题和操作 -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('gist.titles.index') }}</h1>
            @auth
                <a href="{{ route('gists.create') }}"
                   class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                    {{ __('gist.actions.create') }}
                </a>
            @endauth
        </div>

        <!-- 搜索和筛选 -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- 实时搜索框 -->
                    <div class="relative">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('gist.placeholders.search') }}"
                               data-search-debounce="500"
                               hx-get="{{ route('htmx.gists.search') }}"
                               hx-target="#gist-list-container"
                               hx-trigger="search, keyup changed delay:500ms"
                               hx-include="[name='language'], [name='tag'], [name='sort']"
                               hx-push-url="true"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">

                        <!-- 搜索建议下拉框 -->
                        <div id="search-suggestions" class="absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-md shadow-lg z-10 hidden">
                            <!-- 动态加载搜索建议 -->
                        </div>
                    </div>

                    <!-- 语言筛选 -->
                    <div>
                        <select name="language"
                                hx-get="{{ route('htmx.gists.search') }}"
                                hx-target="#gist-list-container"
                                hx-trigger="change"
                                hx-include="[name='search'], [name='tag'], [name='sort']"
                                hx-push-url="true"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">所有语言</option>
                            @foreach($languages as $language)
                                <option value="{{ $language }}" {{ request('language') === $language ? 'selected' : '' }}>
                                    {{ $language }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 标签筛选 -->
                    <div>
                        <select name="tag"
                                hx-get="{{ route('htmx.gists.search') }}"
                                hx-target="#gist-list-container"
                                hx-trigger="change"
                                hx-include="[name='search'], [name='language'], [name='sort']"
                                hx-push-url="true"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">所有标签</option>
                            @foreach($tags as $tag)
                                <option value="{{ $tag->slug }}" {{ request('tag') === $tag->slug ? 'selected' : '' }}>
                                    {{ $tag->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 排序 -->
                    <div>
                        <select name="sort"
                                hx-get="{{ route('htmx.gists.search') }}"
                                hx-target="#gist-list-container"
                                hx-trigger="change"
                                hx-include="[name='search'], [name='language'], [name='tag']"
                                hx-push-url="true"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>最新创建</option>
                            <option value="updated" {{ request('sort') === 'updated' ? 'selected' : '' }}>最近更新</option>
                            <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>最受欢迎</option>
                            <option value="views" {{ request('sort') === 'views' ? 'selected' : '' }}>浏览最多</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        <span hx-get="{{ route('htmx.stats') }}" hx-trigger="load" hx-swap="innerHTML">
                            加载统计信息...
                        </span>
                    </div>
                    @if(request()->hasAny(['search', 'language', 'tag', 'sort']))
                        <button
                            hx-get="{{ route('gists.index') }}"
                            hx-target="#gist-list-container"
                            hx-push-url="true"
                            class="text-gray-500 hover:text-gray-700 transition-colors">
                            清除筛选
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Gist 列表容器 -->
        <div id="gist-list-container">
            @include('partials.gist-list', ['gists' => $gists])
        </div>

        <!-- 模态框容器 -->
        <div id="modal-container"></div>
    </div>
</div>
@endsection
