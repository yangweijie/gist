@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <!-- Gist 头部信息 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <!-- 标题和描述 -->
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $gist->title }}</h1>
                        @if($gist->description)
                            <p class="text-gray-600 mb-4">{{ $gist->description }}</p>
                        @endif

                        <!-- 作者信息 -->
                        <div class="flex items-center text-sm text-gray-500 mb-4">
                            @if($gist->user->avatar_url)
                                <img src="{{ $gist->user->avatar_url }}" alt="{{ $gist->user->name }}" 
                                     class="w-8 h-8 rounded-full mr-3">
                            @endif
                            <div>
                                <span class="font-medium text-gray-900">{{ $gist->user->name }}</span>
                                <span class="mx-2">•</span>
                                <span>创建于 {{ $gist->created_at->format('Y-m-d H:i') }}</span>
                                @if($gist->updated_at->ne($gist->created_at))
                                    <span class="mx-2">•</span>
                                    <span>更新于 {{ $gist->updated_at->format('Y-m-d H:i') }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- 标签 -->
                        @if($gist->tags->count() > 0)
                            <div class="flex flex-wrap gap-2 mb-4">
                                @foreach($gist->tags as $tag)
                                    <a href="{{ route('gists.index', ['tag' => $tag->slug]) }}" 
                                       class="bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full hover:bg-blue-200 transition-colors">
                                        {{ $tag->name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        <!-- 统计信息 -->
                        <div class="flex items-center space-x-6 text-sm text-gray-500">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                {{ $gist->views_count }} 次浏览
                            </span>
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                {{ $gist->likes_count }} 个赞
                            </span>
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                {{ $gist->comments_count }} 条评论
                            </span>
                            @if($gist->is_synced)
                                <span class="flex items-center text-green-600">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                    已同步到 GitHub
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- 操作按钮 -->
                    <div class="flex items-center space-x-3 ml-6">
                        @auth
                            <!-- 点赞按钮 -->
                            <button onclick="toggleLike()" 
                                    class="flex items-center px-3 py-2 rounded-md border transition-colors {{ $userLiked ? 'bg-red-50 border-red-200 text-red-700' : 'bg-gray-50 border-gray-200 text-gray-700 hover:bg-gray-100' }}">
                                <svg class="w-4 h-4 mr-1" fill="{{ $userLiked ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                {{ $userLiked ? '取消赞' : '点赞' }}
                            </button>

                            <!-- 收藏按钮 -->
                            <button onclick="toggleFavorite()" 
                                    class="flex items-center px-3 py-2 rounded-md border transition-colors {{ $userFavorited ? 'bg-yellow-50 border-yellow-200 text-yellow-700' : 'bg-gray-50 border-gray-200 text-gray-700 hover:bg-gray-100' }}">
                                <svg class="w-4 h-4 mr-1" fill="{{ $userFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                </svg>
                                {{ $userFavorited ? '取消收藏' : '收藏' }}
                            </button>
                        @endauth

                        <!-- PHP 在线运行按钮 -->
                        @if(strtolower($gist->language) === 'php')
                            <a href="{{ route('php-runner.gist', $gist) }}"
                               class="flex items-center px-3 py-2 bg-green-50 border border-green-200 text-green-700 rounded-md hover:bg-green-100 transition-colors">
                                <i class="fas fa-play mr-1"></i>
                                在线运行
                            </a>
                        @endif

                        @can('update', $gist)
                            <a href="{{ route('gists.edit', $gist) }}"
                               class="flex items-center px-3 py-2 bg-indigo-50 border border-indigo-200 text-indigo-700 rounded-md hover:bg-indigo-100 transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                编辑
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <!-- 代码内容 -->
        <div class="mb-6">
            <x-code-block
                :language="$gist->language"
                :filename="$gist->filename"
                :content="$gist->content"
                :title="$gist->title"
                :show-line-numbers="true"
                :show-toolbar="true"
                :enable-search="true"
                :enable-copy="true"
            />
        </div>

        <!-- 社交按钮 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <x-social-buttons :gist="$gist" :show-counts="true" size="large" />
            </div>
        </div>

        <!-- 评论区域 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <x-comment-section :gist="$gist" :limit="10" :show-form="true" />
            </div>
        </div>
    </div>
</div>


@endsection
