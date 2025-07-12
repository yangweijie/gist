@foreach($gists as $gist)
    @include('partials.gist-card', ['gist' => $gist])
@endforeach

@if($gists->hasMorePages())
    <!-- 更新加载更多按钮的页码 -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loadMoreBtn = document.querySelector('[hx-get*="load-more"]');
            if (loadMoreBtn) {
                const currentVals = JSON.parse(loadMoreBtn.getAttribute('hx-vals') || '{}');
                currentVals.page = {{ $gists->currentPage() + 1 }};
                loadMoreBtn.setAttribute('hx-vals', JSON.stringify(currentVals));
            }
        });
    </script>
@else
    <!-- 移除加载更多按钮 -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loadMoreBtn = document.querySelector('[hx-get*="load-more"]');
            if (loadMoreBtn) {
                loadMoreBtn.parentElement.remove();
            }
        });
    </script>
@endif
