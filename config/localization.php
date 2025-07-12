<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 支持的语言
    |--------------------------------------------------------------------------
    |
    | 应用程序支持的语言列表，包含语言代码、名称、本地名称等信息
    |
    */
    'supported_locales' => [
        'zh' => [
            'code' => 'zh',
            'name' => 'Chinese',
            'native' => '中文',
            'flag' => '🇨🇳',
            'direction' => 'ltr', // left-to-right
            'enabled' => true,
            'region' => 'CN',
            'currency' => 'CNY',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',
        ],
        'en' => [
            'code' => 'en',
            'name' => 'English',
            'native' => 'English',
            'flag' => '🇺🇸',
            'direction' => 'ltr',
            'enabled' => true,
            'region' => 'US',
            'currency' => 'USD',
            'date_format' => 'm/d/Y',
            'time_format' => 'g:i A',
        ],
        'ja' => [
            'code' => 'ja',
            'name' => 'Japanese',
            'native' => '日本語',
            'flag' => '🇯🇵',
            'direction' => 'ltr',
            'enabled' => false, // 待翻译完成后启用
            'region' => 'JP',
            'currency' => 'JPY',
            'date_format' => 'Y/m/d',
            'time_format' => 'H:i',
        ],
        'ko' => [
            'code' => 'ko',
            'name' => 'Korean',
            'native' => '한국어',
            'flag' => '🇰🇷',
            'direction' => 'ltr',
            'enabled' => false, // 待翻译完成后启用
            'region' => 'KR',
            'currency' => 'KRW',
            'date_format' => 'Y.m.d',
            'time_format' => 'H:i',
        ],
        'fr' => [
            'code' => 'fr',
            'name' => 'French',
            'native' => 'Français',
            'flag' => '🇫🇷',
            'direction' => 'ltr',
            'enabled' => false, // 待翻译完成后启用
            'region' => 'FR',
            'currency' => 'EUR',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
        ],
        'de' => [
            'code' => 'de',
            'name' => 'German',
            'native' => 'Deutsch',
            'flag' => '🇩🇪',
            'direction' => 'ltr',
            'enabled' => false, // 待翻译完成后启用
            'region' => 'DE',
            'currency' => 'EUR',
            'date_format' => 'd.m.Y',
            'time_format' => 'H:i',
        ],
        'es' => [
            'code' => 'es',
            'name' => 'Spanish',
            'native' => 'Español',
            'flag' => '🇪🇸',
            'direction' => 'ltr',
            'enabled' => false, // 待翻译完成后启用
            'region' => 'ES',
            'currency' => 'EUR',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
        ],
        'pt' => [
            'code' => 'pt',
            'name' => 'Portuguese',
            'native' => 'Português',
            'flag' => '🇵🇹',
            'direction' => 'ltr',
            'enabled' => false, // 待翻译完成后启用
            'region' => 'PT',
            'currency' => 'EUR',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
        ],
        'ru' => [
            'code' => 'ru',
            'name' => 'Russian',
            'native' => 'Русский',
            'flag' => '🇷🇺',
            'direction' => 'ltr',
            'enabled' => false, // 待翻译完成后启用
            'region' => 'RU',
            'currency' => 'RUB',
            'date_format' => 'd.m.Y',
            'time_format' => 'H:i',
        ],
        'ar' => [
            'code' => 'ar',
            'name' => 'Arabic',
            'native' => 'العربية',
            'flag' => '🇸🇦',
            'direction' => 'rtl', // 从右到左
            'enabled' => false, // 待翻译完成后启用
            'region' => 'SA',
            'currency' => 'SAR',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 默认语言
    |--------------------------------------------------------------------------
    |
    | 当无法确定用户语言偏好时使用的默认语言
    |
    */
    'default_locale' => env('APP_LOCALE', 'zh'),

    /*
    |--------------------------------------------------------------------------
    | 回退语言
    |--------------------------------------------------------------------------
    |
    | 当翻译键在当前语言中不存在时使用的回退语言
    |
    */
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | 语言检测
    |--------------------------------------------------------------------------
    |
    | 配置语言自动检测的行为
    |
    */
    'detection' => [
        // 是否启用浏览器语言检测
        'browser_detection' => env('LOCALE_BROWSER_DETECTION', true),

        // 是否启用 IP 地理位置检测
        'ip_detection' => env('LOCALE_IP_DETECTION', false),

        // 是否为新用户自动设置语言偏好
        'auto_set_user_locale' => env('LOCALE_AUTO_SET_USER', true),

        // 是否在 URL 中显示语言代码
        'show_in_url' => env('LOCALE_SHOW_IN_URL', false),

        // 是否记住访客的语言选择（使用 Cookie）
        'remember_guest_locale' => env('LOCALE_REMEMBER_GUEST', true),

        // Cookie 过期时间（天）
        'cookie_lifetime' => env('LOCALE_COOKIE_LIFETIME', 365),
    ],

    /*
    |--------------------------------------------------------------------------
    | 日期和时间格式
    |--------------------------------------------------------------------------
    |
    | 不同语言的日期时间格式配置
    |
    */
    'date_formats' => [
        'zh' => [
            'date' => 'Y年m月d日',
            'datetime' => 'Y年m月d日 H:i',
            'time' => 'H:i',
            'short_date' => 'm-d',
            'long_date' => 'Y年m月d日 l',
        ],
        'en' => [
            'date' => 'M j, Y',
            'datetime' => 'M j, Y g:i A',
            'time' => 'g:i A',
            'short_date' => 'M j',
            'long_date' => 'l, F j, Y',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 数字格式
    |--------------------------------------------------------------------------
    |
    | 不同语言的数字格式配置
    |
    */
    'number_formats' => [
        'zh' => [
            'decimal_separator' => '.',
            'thousands_separator' => ',',
            'currency_symbol' => '¥',
            'currency_position' => 'before', // before 或 after
        ],
        'en' => [
            'decimal_separator' => '.',
            'thousands_separator' => ',',
            'currency_symbol' => '$',
            'currency_position' => 'before',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 时区配置
    |--------------------------------------------------------------------------
    |
    | 不同语言对应的默认时区
    |
    */
    'timezones' => [
        'zh' => 'Asia/Shanghai',
        'en' => 'UTC',
    ],

    /*
    |--------------------------------------------------------------------------
    | 语言切换设置
    |--------------------------------------------------------------------------
    |
    | 语言切换相关的配置
    |
    */
    'switcher' => [
        // 是否在前台显示语言切换器
        'show_frontend' => env('LOCALE_SHOW_FRONTEND_SWITCHER', true),
        
        // 是否在后台显示语言切换器
        'show_backend' => env('LOCALE_SHOW_BACKEND_SWITCHER', true),
        
        // 语言切换器的显示样式
        'style' => env('LOCALE_SWITCHER_STYLE', 'dropdown'), // dropdown, flags, text
        
        // 是否显示国旗图标
        'show_flags' => env('LOCALE_SHOW_FLAGS', true),
        
        // 是否显示语言名称
        'show_names' => env('LOCALE_SHOW_NAMES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | 缓存设置
    |--------------------------------------------------------------------------
    |
    | 语言相关的缓存配置
    |
    */
    'cache' => [
        // 是否缓存翻译
        'enabled' => env('LOCALE_CACHE_ENABLED', true),
        
        // 缓存过期时间（分钟）
        'ttl' => env('LOCALE_CACHE_TTL', 1440), // 24 hours
        
        // 缓存键前缀
        'prefix' => env('LOCALE_CACHE_PREFIX', 'locale'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO 设置
    |--------------------------------------------------------------------------
    |
    | 多语言 SEO 相关配置
    |
    */
    'seo' => [
        // 是否生成 hreflang 标签
        'generate_hreflang' => env('LOCALE_GENERATE_HREFLANG', true),

        // 是否在 sitemap 中包含多语言页面
        'include_in_sitemap' => env('LOCALE_INCLUDE_IN_SITEMAP', true),

        // 默认语言是否使用无前缀的 URL
        'default_no_prefix' => env('LOCALE_DEFAULT_NO_PREFIX', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | 性能优化设置
    |--------------------------------------------------------------------------
    |
    | 翻译系统的性能优化配置
    |
    */
    'performance' => [
        // 懒加载配置
        'lazy_loading' => [
            'enabled' => env('LOCALE_LAZY_LOADING', true),
            'preload_critical' => env('LOCALE_PRELOAD_CRITICAL', true),
            'smart_prediction' => env('LOCALE_SMART_PREDICTION', true),
            'batch_size' => env('LOCALE_BATCH_SIZE', 5),
        ],

        // 性能监控
        'monitoring' => [
            'enabled' => env('LOCALE_MONITORING_ENABLED', true),
            'log_metrics' => env('LOCALE_LOG_METRICS', false),
            'store_metrics' => env('LOCALE_STORE_METRICS', true),
        ],

        // 性能阈值
        'thresholds' => [
            'execution_time' => env('LOCALE_THRESHOLD_TIME', 100), // 毫秒
            'memory_used' => env('LOCALE_THRESHOLD_MEMORY', 5 * 1024 * 1024), // 5MB
            'cache_hit_rate' => env('LOCALE_THRESHOLD_CACHE_HIT', 80), // 80%
        ],

        // 预加载配置
        'preload' => [
            'enabled' => env('LOCALE_PRELOAD_ENABLED', true),
            'groups' => ['common', 'auth'], // 预加载的翻译组
            'locales' => [], // 空数组表示所有启用的语言
        ],

        // 缓存预热
        'warmup' => [
            'enabled' => env('LOCALE_WARMUP_ENABLED', false),
            'schedule' => env('LOCALE_WARMUP_SCHEDULE', 'daily'), // daily, hourly, weekly
            'groups' => ['common', 'auth', 'gist', 'tag', 'php-runner', 'filament'],
        ],
    ],
];
