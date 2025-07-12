<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GitHub API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for GitHub API integration.
    |
    */

    'api_url' => env('GITHUB_API_URL', 'https://api.github.com'),

    'oauth' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect_uri' => env('GITHUB_REDIRECT_URI'),
        'scopes' => ['user:email', 'gist'],
    ],

    'rate_limit' => [
        // GitHub API rate limits
        'requests_per_hour' => 5000, // For authenticated requests
        'requests_per_hour_unauthenticated' => 60,
        
        // Cache settings for rate limit info
        'cache_ttl' => 300, // 5 minutes
    ],

    'cache' => [
        // Cache settings for API responses
        'ttl' => 3600, // 1 hour
        'prefix' => 'github_api_',
        
        // Cache keys
        'keys' => [
            'user_gists' => 'user_gists_{user_id}_{page}_{per_page}',
            'gist_detail' => 'gist_{gist_id}',
            'rate_limit' => 'rate_limit_{user_id}',
        ],
    ],

    'sync' => [
        // Sync job settings
        'timeout' => 300, // 5 minutes
        'tries' => 3,
        'backoff' => [60, 120, 300], // Retry delays in seconds
        
        // Batch settings
        'batch_size' => 30, // Gists per page
        'max_pages' => 100, // Maximum pages to fetch
        
        // Sync intervals
        'auto_sync_interval' => 3600, // 1 hour
        'full_sync_interval' => 86400, // 24 hours
    ],

    'gist' => [
        // Default settings for new gists
        'default_public' => true,
        'default_description' => 'Created via Gist Manager',
        
        // File settings
        'max_file_size' => 1048576, // 1MB
        'allowed_extensions' => [
            'txt', 'md', 'php', 'js', 'html', 'css', 'json', 'xml',
            'py', 'rb', 'java', 'c', 'cpp', 'cs', 'go', 'rs', 'swift',
            'sql', 'sh', 'yml', 'yaml', 'toml', 'ini', 'conf',
        ],
        
        // Language mapping
        'language_mapping' => [
            'php' => 'PHP',
            'js' => 'JavaScript',
            'html' => 'HTML',
            'css' => 'CSS',
            'py' => 'Python',
            'rb' => 'Ruby',
            'java' => 'Java',
            'c' => 'C',
            'cpp' => 'C++',
            'cs' => 'C#',
            'go' => 'Go',
            'rs' => 'Rust',
            'swift' => 'Swift',
            'sql' => 'SQL',
            'sh' => 'Shell',
            'md' => 'Markdown',
            'json' => 'JSON',
            'xml' => 'XML',
            'yml' => 'YAML',
            'yaml' => 'YAML',
            'toml' => 'TOML',
            'txt' => 'Text',
        ],
    ],

    'webhooks' => [
        // Webhook settings for real-time sync
        'enabled' => env('GITHUB_WEBHOOKS_ENABLED', false),
        'secret' => env('GITHUB_WEBHOOK_SECRET'),
        'events' => ['gist'],
    ],

    'features' => [
        // Feature flags
        'auto_sync' => env('GITHUB_AUTO_SYNC', true),
        'cache_enabled' => env('GITHUB_CACHE_ENABLED', true),
        'rate_limit_check' => env('GITHUB_RATE_LIMIT_CHECK', true),
        'webhook_sync' => env('GITHUB_WEBHOOK_SYNC', false),
    ],
];
