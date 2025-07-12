@php
    $seoService = app(App\Services\SeoLocalizationService::class);
    
    // 合并默认数据和传入的数据
    $seoData = array_merge([
        'title' => config('app.name'),
        'description' => __('common.messages.app_description'),
        'keywords' => __('common.seo.keywords'),
        'author' => config('app.name'),
        'type' => 'website',
        'image' => asset('images/og-image.jpg'),
        'url' => request()->url(),
    ], $attributes->getAttributes());
    
    // 生成各种 SEO 标签
    $hreflangTags = $seoService->generateHreflangTags($seoData['url']);
    $openGraphTags = $seoService->generateOpenGraphTags($seoData);
    $jsonLd = $seoService->generateJsonLd($seoData);
    $canonicalUrl = $seoService->getCanonicalUrl($seoData['url']);
@endphp

<!-- 基本 Meta 标签 -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- SEO Meta 标签 -->
<title>{{ $seoData['title'] }}</title>
<meta name="description" content="{{ $seoData['description'] }}">
<meta name="keywords" content="{{ $seoData['keywords'] }}">
<meta name="author" content="{{ $seoData['author'] }}">
<meta name="robots" content="index, follow">
<meta name="language" content="{{ app()->getLocale() }}">

<!-- Canonical URL -->
<link rel="canonical" href="{{ $canonicalUrl }}">

<!-- Hreflang 标签 -->
@foreach($hreflangTags as $tag)
    <link rel="alternate" hreflang="{{ $tag['hreflang'] }}" href="{{ $tag['href'] }}">
@endforeach

<!-- Open Graph 标签 -->
@foreach($openGraphTags as $property => $content)
    @if(is_array($content))
        @foreach($content as $item)
            <meta property="{{ $property }}" content="{{ $item }}">
        @endforeach
    @else
        <meta property="{{ $property }}" content="{{ $content }}">
    @endif
@endforeach

<!-- Twitter Card 标签 -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoData['title'] }}">
<meta name="twitter:description" content="{{ $seoData['description'] }}">
<meta name="twitter:image" content="{{ $seoData['image'] }}">
<meta name="twitter:url" content="{{ $seoData['url'] }}">

<!-- 移动端优化 -->
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">

<!-- 主题颜色 -->
<meta name="theme-color" content="#3B82F6">
<meta name="msapplication-TileColor" content="#3B82F6">

<!-- 图标 -->
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
<link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">

<!-- DNS 预取 -->
<link rel="dns-prefetch" href="//fonts.googleapis.com">
<link rel="dns-prefetch" href="//cdnjs.cloudflare.com">

<!-- JSON-LD 结构化数据 -->
<script type="application/ld+json">
{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

<!-- 语言检测脚本 -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 设置 HTML lang 属性
    document.documentElement.lang = '{{ app()->getLocale() }}';
    
    // 设置文档方向（为未来的 RTL 语言支持做准备）
    document.documentElement.dir = '{{ app(App\Services\LocalizationService::class)->getCurrentLocaleInfo()['direction'] ?? 'ltr' }}';
    
    // 语言切换时的 SEO 优化
    window.addEventListener('beforeunload', function() {
        // 保存当前页面状态，用于语言切换后的恢复
        sessionStorage.setItem('lastPageState', JSON.stringify({
            url: window.location.href,
            scrollPosition: window.scrollY,
            timestamp: Date.now()
        }));
    });
    
    // 检查是否是语言切换后的页面加载
    const lastPageState = sessionStorage.getItem('lastPageState');
    if (lastPageState) {
        try {
            const state = JSON.parse(lastPageState);
            // 如果是最近的语言切换（5秒内），恢复滚动位置
            if (Date.now() - state.timestamp < 5000) {
                window.scrollTo(0, state.scrollPosition);
            }
            sessionStorage.removeItem('lastPageState');
        } catch (e) {
            // 忽略解析错误
        }
    }
});
</script>

<!-- 搜索引擎优化提示 -->
@if(app()->environment('production'))
    <!-- Google Analytics 或其他分析工具可以在这里添加 -->
    @if(config('services.google_analytics.id'))
        <!-- Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google_analytics.id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ config('services.google_analytics.id') }}', {
                'custom_map': {
                    'custom_dimension_1': 'language'
                }
            });
            
            // 跟踪语言切换事件
            gtag('event', 'page_view', {
                'language': '{{ app()->getLocale() }}',
                'custom_dimension_1': '{{ app()->getLocale() }}'
            });
        </script>
    @endif
@endif

<!-- 结构化数据：面包屑导航 -->
@if(isset($breadcrumbs) && is_array($breadcrumbs))
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            @foreach($breadcrumbs as $index => $breadcrumb)
                {
                    "@type": "ListItem",
                    "position": {{ $index + 1 }},
                    "name": "{{ $breadcrumb['name'] }}",
                    "item": "{{ $breadcrumb['url'] }}"
                }@if(!$loop->last),@endif
            @endforeach
        ]
    }
    </script>
@endif

<!-- 多语言网站声明 -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "{{ config('app.name') }}",
    "url": "{{ config('app.url') }}",
    "potentialAction": {
        "@type": "SearchAction",
        "target": {
            "@type": "EntryPoint",
            "urlTemplate": "{{ route('gists.index') }}?search={search_term_string}"
        },
        "query-input": "required name=search_term_string"
    },
    "inLanguage": [
        @foreach(config('localization.supported_locales', []) as $locale => $config)
            @if($config['enabled'] ?? true)
                "{{ $locale }}"@if(!$loop->last),@endif
            @endif
        @endforeach
    ]
}
</script>
