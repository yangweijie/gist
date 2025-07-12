<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- SEO Meta 标签 -->
    <x-seo-meta
        :title="$seoTitle ?? __('common.seo.default_title')"
        :description="$seoDescription ?? __('common.seo.site_description')"
        :keywords="$seoKeywords ?? __('common.seo.keywords')"
        :type="$seoType ?? 'website'"
        :image="$seoImage ?? asset('images/og-image.jpg')"
        :breadcrumbs="$breadcrumbs ?? null"
    />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/htmx-config.js'])

    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- 多语言支持 -->
    <script>
        window.translations = {
            'common': {
                'actions': 'Actions',
                'status': 'Status',
                'messages': 'Messages'
            },
            'gist': {
                'actions': 'Gist Actions',
                'success': 'Success',
                'errors': 'Errors',
                'hints': 'Hints'
            },
            'php_runner': {
                'status': 'PHP Runner Status',
                'errors': 'PHP Runner Errors',
                'success': 'PHP Runner Success'
            }
        };

        // 全局翻译函数
        window.__ = function(key, replacements = {}) {
            const keys = key.split('.');
            let value = window.translations;

            for (const k of keys) {
                if (value && typeof value === 'object' && k in value) {
                    value = value[k];
                } else {
                    return key; // 如果找不到翻译，返回原始键
                }
            }

            // 处理占位符替换
            if (typeof value === 'string' && Object.keys(replacements).length > 0) {
                for (const [placeholder, replacement] of Object.entries(replacements)) {
                    value = value.replace(new RegExp(':' + placeholder, 'g'), replacement);
                }
            }

            return value || key;
        };
    </script>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="{{ url('/') }}" class="text-xl font-bold text-gray-800">
                                {{ config('app.name', 'Gist Manager') }}
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <a href="{{ url('/') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                                {{ __('common.navigation.home') }}
                            </a>
                            <a href="{{ route('gists.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                                {{ __('common.navigation.gists') }}
                            </a>
                            <a href="{{ route('tags.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                                {{ __('common.navigation.tags') }}
                            </a>
                            <a href="{{ route('php-runner.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                                <i class="fas fa-play-circle mr-1"></i>{{ __('common.navigation.php_runner') }}
                            </a>
                            @auth
                                <a href="{{ route('gists.my') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                                    {{ __('gist.titles.my_gists') }}
                                </a>
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                                    {{ __('common.navigation.dashboard') }}
                                </a>
                            @endauth
                        </div>
                    </div>

                    <!-- Search Bar -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6 flex-1 max-w-lg mx-4">
                        <form action="{{ route('search') }}" method="GET" class="w-full">
                            <div class="relative">
                                <input type="text"
                                       name="q"
                                       placeholder="{{ __('gist.placeholders.search') }}"
                                       value="{{ request('q') }}"
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <a href="{{ route('search.advanced') }}"
                                   class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                   title="{{ __('common.actions.advanced_search') }}">
                                    <i class="fas fa-sliders-h"></i>
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Settings Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <!-- 桌面端语言切换器 -->
                        <div class="mr-4">
                            <x-language-switcher />
                        </div>

                        @auth
                            <div class="ml-3 relative">
                                <div class="flex items-center space-x-4">
                                    <span class="text-sm text-gray-700">{{ Auth::user()->name }}</span>
                                    <form method="POST" action="{{ route('logout') }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                                            {{ __('common.navigation.logout') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center space-x-4">
                                <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-gray-900">{{ __('common.navigation.login') }}</a>
                                <a href="{{ route('register') }}" class="text-sm text-gray-700 hover:text-gray-900">{{ __('common.navigation.register') }}</a>
                            </div>
                        @endauth
                    </div>

                    <!-- 移动端控制 -->
                    <div class="flex items-center sm:hidden">
                        <!-- 移动端语言切换器 -->
                        <x-mobile-language-switcher />

                        @auth
                            <div class="ml-2">
                                <form method="POST" action="{{ route('logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="ml-2 flex items-center space-x-2">
                                <a href="{{ route('login') }}" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                    </svg>
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            <!-- Flash Messages -->
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 mx-4 mt-4">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 mx-4 mt-4">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- 推送的脚本 -->
    @stack('scripts')

    <!-- 推送的样式 -->
    @stack('styles')
</body>
</html>
