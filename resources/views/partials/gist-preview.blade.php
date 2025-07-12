<div class="gist-preview">
    <!-- 头部信息 -->
    <div class="border-b border-gray-200 pb-4 mb-4">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">{{ $gist->title }}</h2>
                @if($gist->description)
                    <p class="text-gray-600 mb-3">{{ $gist->description }}</p>
                @endif
                
                <!-- 作者信息 -->
                <div class="flex items-center text-sm text-gray-500">
                    @if($gist->user->avatar_url)
                        <img src="{{ $gist->user->avatar_url }}" alt="{{ $gist->user->name }}" 
                             class="w-6 h-6 rounded-full mr-2">
                    @endif
                    <span class="font-medium text-gray-900">{{ $gist->user->name }}</span>
                    <span class="mx-2">•</span>
                    <span>{{ $gist->created_at->diffForHumans() }}</span>
                    @if($gist->updated_at->ne($gist->created_at))
                        <span class="mx-2">•</span>
                        <span>更新于 {{ $gist->updated_at->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
            
            <!-- 操作按钮 -->
            <div class="flex items-center space-x-2 ml-4">
                <a href="{{ route('gists.show', $gist) }}" 
                   class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700 transition-colors">
                    查看详情
                </a>
                @can('update', $gist)
                    <a href="{{ route('gists.edit', $gist) }}" 
                       class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700 transition-colors">
                        编辑
                    </a>
                @endcan
            </div>
        </div>
        
        <!-- 标签 -->
        @if($gist->tags->count() > 0)
            <div class="flex flex-wrap gap-2 mt-3">
                @foreach($gist->tags as $tag)
                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                        {{ $tag->name }}
                    </span>
                @endforeach
            </div>
        @endif
        
        <!-- 统计信息 -->
        <div class="flex items-center space-x-6 text-sm text-gray-500 mt-3">
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

    <!-- 代码预览 -->
    <div class="code-preview">
        <div class="flex justify-between items-center mb-3">
            <div class="flex items-center">
                <span class="bg-gray-100 text-gray-800 text-sm px-3 py-1 rounded-full mr-3">
                    {{ $gist->language }}
                </span>
                <span class="text-gray-600 text-sm">{{ $gist->filename }}</span>
            </div>
            <button onclick="copyPreviewCode()" 
                    class="text-gray-500 hover:text-gray-700 transition-colors text-sm">
                复制代码
            </button>
        </div>
        
        <!-- 代码内容（截取前20行） -->
        <div class="bg-gray-50 rounded-lg p-4 max-h-96 overflow-y-auto">
            <pre class="text-sm"><code id="preview-code">{{ Str::limit($gist->content, 1000) }}@if(strlen($gist->content) > 1000)

... (代码已截取，查看完整内容请点击"查看详情")@endif</code></pre>
        </div>
    </div>
</div>

<script>
function copyPreviewCode() {
    const code = document.getElementById('preview-code').textContent;
    navigator.clipboard.writeText(code).then(function() {
        // 显示复制成功提示
        if (window.htmxConfig) {
            window.htmxConfig.showNotification('success', '代码已复制到剪贴板');
        }
    });
}
</script>

<style>
.gist-preview {
    max-width: 800px;
    max-height: 80vh;
    overflow-y: auto;
}

.code-preview pre {
    white-space: pre-wrap;
    word-break: break-word;
    font-family: 'Monaco', 'Cascadia Code', 'Roboto Mono', monospace;
    line-height: 1.5;
}

.code-preview code {
    color: #333;
}
</style>
