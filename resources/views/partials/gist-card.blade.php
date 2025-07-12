<div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow gist-card" 
     data-gist-id="{{ $gist->id }}">
    <div class="p-6">
        <!-- Gist 标题 -->
        <div class="flex justify-between items-start mb-2">
            <h3 class="text-lg font-semibold text-gray-900 flex-1">
                <a href="{{ route('gists.show', $gist) }}" class="hover:text-indigo-600">
                    {{ $gist->title }}
                </a>
            </h3>
            
            <!-- 快速操作按钮 -->
            @auth
                @if($gist->user_id === Auth::id())
                    <div class="flex items-center space-x-2 ml-4">
                        <!-- 预览按钮 -->
                        <button 
                            hx-get="{{ route('htmx.gists.preview', $gist) }}"
                            hx-target="#modal-content"
                            hx-trigger="click"
                            data-modal="true"
                            class="text-gray-400 hover:text-gray-600 transition-colors"
                            title="预览">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                        
                        <!-- 可见性切换 -->
                        <button 
                            hx-post="{{ route('htmx.gists.toggle-visibility', $gist) }}"
                            hx-target="closest .gist-card"
                            hx-swap="outerHTML"
                            class="text-gray-400 hover:text-gray-600 transition-colors"
                            title="{{ $gist->is_public ? '设为私有' : '设为公开' }}">
                            @if($gist->is_public)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                                </svg>
                            @endif
                        </button>
                    </div>
                @endif
            @endauth
        </div>

        <!-- 描述 -->
        @if($gist->description)
            <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                {{ $gist->description }}
            </p>
        @endif

        <!-- 语言和文件名 -->
        <div class="flex items-center text-sm text-gray-500 mb-3">
            <span class="bg-gray-100 px-2 py-1 rounded text-xs font-medium mr-2">
                {{ $gist->language }}
            </span>
            <span>{{ $gist->filename }}</span>
        </div>

        <!-- 标签 -->
        @if($gist->tags->count() > 0)
            <div class="flex flex-wrap gap-1 mb-3">
                @foreach($gist->tags->take(3) as $tag)
                    <button 
                        hx-get="{{ route('htmx.gists.search') }}"
                        hx-vals='{"tag": "{{ $tag->slug }}"}'
                        hx-target="#gist-list-container"
                        hx-push-url="true"
                        class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded hover:bg-blue-200 transition-colors">
                        {{ $tag->name }}
                    </button>
                @endforeach
                @if($gist->tags->count() > 3)
                    <span class="text-gray-500 text-xs">+{{ $gist->tags->count() - 3 }}</span>
                @endif
            </div>
        @endif

        <!-- 统计信息 -->
        <div class="flex items-center justify-between text-sm text-gray-500">
            <div class="flex items-center space-x-4">
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    {{ $gist->views_count }}
                </span>
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    {{ $gist->likes_count }}
                </span>
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    {{ $gist->comments_count }}
                </span>
            </div>
            <div class="flex items-center space-x-2">
                @if($gist->is_synced)
                    <span class="text-green-600 text-xs">🔗 GitHub</span>
                @endif
                @if(!$gist->is_public)
                    <span class="text-orange-600 text-xs">🔒 私有</span>
                @endif
            </div>
        </div>

        <!-- 作者和时间 -->
        <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
            <div class="flex items-center">
                @if($gist->user->avatar_url)
                    <img src="{{ $gist->user->avatar_url }}" alt="{{ $gist->user->name }}" 
                         class="w-6 h-6 rounded-full mr-2">
                @endif
                <button 
                    hx-get="{{ route('htmx.gists.search') }}"
                    hx-vals='{"user": "{{ $gist->user->name }}"}'
                    hx-target="#gist-list-container"
                    hx-push-url="true"
                    class="hover:text-gray-700 transition-colors">
                    {{ $gist->user->name }}
                </button>
            </div>
            <span title="{{ $gist->created_at->format('Y-m-d H:i:s') }}">
                {{ $gist->created_at->diffForHumans() }}
            </span>
        </div>
    </div>
</div>
