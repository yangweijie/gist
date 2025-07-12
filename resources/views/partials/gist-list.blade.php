@if($gists->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" data-append-target>
        @foreach($gists as $gist)
            @include('partials.gist-card', ['gist' => $gist])
        @endforeach
    </div>

    <!-- 分页或加载更多 -->
    @if($gists->hasMorePages())
        <div class="mt-8 text-center">
            <button 
                hx-get="{{ route('htmx.gists.load-more') }}"
                hx-vals='{"page": {{ $gists->currentPage() + 1 }}, "search": "{{ request('search') }}", "language": "{{ request('language') }}", "tag": "{{ request('tag') }}", "sort": "{{ request('sort') }}"}'
                hx-target="[data-append-target]"
                hx-swap="beforeend"
                hx-indicator="#loading-more"
                class="bg-indigo-600 text-white px-6 py-3 rounded-md hover:bg-indigo-700 transition-colors">
                加载更多
            </button>
            <div id="loading-more" class="htmx-indicator mt-4">
                <div class="inline-flex items-center">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-indigo-600 mr-2"></div>
                    加载中...
                </div>
            </div>
        </div>
    @endif
@else
    <div class="text-center py-12">
        <div class="text-gray-500 text-lg mb-4">
            @if(request()->hasAny(['search', 'language', 'tag']))
                没有找到符合条件的 Gist
            @else
                还没有任何 Gist
            @endif
        </div>
        @auth
            <a href="{{ route('gists.create') }}" 
               class="bg-indigo-600 text-white px-6 py-3 rounded-md hover:bg-indigo-700 transition-colors">
                @if(request()->hasAny(['search', 'language', 'tag']))
                    创建新 Gist
                @else
                    创建第一个 Gist
                @endif
            </a>
        @endauth
    </div>
@endif
