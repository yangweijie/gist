<?php

return [
    // Common actions
    'actions' => [
        'create' => 'Create',
        'edit' => 'Edit',
        'update' => 'Update',
        'delete' => 'Delete',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'confirm' => 'Confirm',
        'submit' => 'Submit',
        'search' => 'Search',
        'filter' => 'Filter',
        'clear' => 'Clear',
        'reset' => 'Reset',
        'back' => 'Back',
        'next' => 'Next',
        'previous' => 'Previous',
        'close' => 'Close',
        'copy' => 'Copy',
        'download' => 'Download',
        'share' => 'Share',
        'view' => 'View',
        'run' => 'Run',
        'stop' => 'Stop',
        'refresh' => 'Refresh',
        'load_more' => 'Load More',
        'clear_filters' => 'Clear Filters',
        'get_started' => 'Get Started',
        'advanced_search' => 'Advanced Search',
        'fullscreen' => 'Fullscreen',
        'select_all' => 'Select All',
        'theme' => 'Theme',
        'search_in_code' => 'Search in code...',
        'next' => 'Next',
    ],

    // Common status
    'status' => [
        'loading' => 'Loading...',
        'saving' => 'Saving...',
        'deleting' => 'Deleting...',
        'processing' => 'Processing...',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'success' => 'Success',
        'error' => 'Error',
        'warning' => 'Warning',
        'info' => 'Info',
    ],

    // Common labels
    'labels' => [
        'title' => 'Title',
        'name' => 'Name',
        'description' => 'Description',
        'content' => 'Content',
        'language' => 'Language',
        'filename' => 'Filename',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'author' => 'Author',
        'tags' => 'Tags',
        'category' => 'Category',
        'status' => 'Status',
        'visibility' => 'Visibility',
        'public' => 'Public',
        'private' => 'Private',
        'draft' => 'Draft',
    ],

    // Time related
    'time' => [
        'just_now' => 'Just now',
        'minutes_ago' => ':count minutes ago',
        'hours_ago' => ':count hours ago',
        'days_ago' => ':count days ago',
        'weeks_ago' => ':count weeks ago',
        'months_ago' => ':count months ago',
        'years_ago' => ':count years ago',
        'today' => 'Today',
        'yesterday' => 'Yesterday',
        'tomorrow' => 'Tomorrow',
    ],

    // Count related
    'counts' => [
        'views' => 'Views',
        'likes' => 'Likes',
        'comments' => 'Comments',
        'favorites' => 'Favorites',
        'downloads' => 'Downloads',
        'shares' => 'Shares',
        'total' => 'Total',
        'items' => 'Items',
        'results' => 'Results',
        'no_results' => 'No Results',
        'empty' => 'No Data',
    ],

    // Navigation
    'navigation' => [
        'home' => 'Home',
        'dashboard' => 'Dashboard',
        'gists' => 'Gists',
        'tags' => 'Tags',
        'php_runner' => 'PHP Runner',
        'settings' => 'Settings',
        'profile' => 'Profile',
        'logout' => 'Logout',
        'login' => 'Login',
        'register' => 'Register',
    ],

    // Message types
    'messages' => [
        'success' => 'Operation successful!',
        'error' => 'Operation failed!',
        'warning' => 'Please note!',
        'info' => 'Information',
        'confirm_delete' => 'Are you sure you want to delete? This action cannot be undone.',
        'no_permission' => 'You do not have permission to perform this action.',
        'not_found' => 'The requested resource was not found.',
        'server_error' => 'Server error, please try again later.',
        'network_error' => 'Network error, please check your connection.',
        'app_description' => 'Manage and share your code snippets with GitHub Gist sync, powerful code display and online execution features.',
        'invalid_locale' => 'Unsupported language',
        'locale_switched' => 'Language switched successfully',
    ],

    // Language related
    'language' => [
        'select_language' => 'Select Language',
        'current' => 'Current',
        'auto_detected' => 'Auto-detected language preference',
        'suggestion_text' => 'We detected you might prefer',
        'switch_to' => 'Switch to',
        'browser_language' => 'Browser Language',
        'system_language' => 'System Language',
    ],

    // SEO related
    'seo' => [
        'keywords' => 'GitHub Gist, code snippets, code sharing, code management, PHP online runner, syntax highlighting, developer tools',
        'site_description' => 'Professional GitHub Gist management platform with code snippet sharing, online PHP code execution, syntax highlighting, tag management and more.',
        'default_title' => 'Gist Management Platform - Code Snippet Sharing & Management',
        'home_title' => 'Home - Gist Management Platform',
        'gists_title' => 'Gist List - Browse Code Snippets',
        'tags_title' => 'Tag Management - Code Categories',
        'php_runner_title' => 'PHP Online Runner - Execute Code Online',
    ],

    // Form related
    'form' => [
        'required' => 'Required',
        'optional' => 'Optional',
        'placeholder' => [
            'search' => 'Enter search keywords...',
            'title' => 'Enter title...',
            'description' => 'Enter description...',
            'tags' => 'Select tags...',
            'email' => 'Enter email address...',
            'password' => 'Enter password...',
        ],
        'validation' => [
            'required' => 'The :attribute field is required',
            'email' => 'Invalid email format',
            'min' => 'The :attribute must be at least :min characters',
            'max' => 'The :attribute may not be greater than :max characters',
            'unique' => 'The :attribute has already been taken',
            'confirmed' => 'Password confirmation does not match',
        ],
    ],

    // Pagination
    'pagination' => [
        'previous' => 'Previous',
        'next' => 'Next',
        'showing' => 'Showing :first to :last of :total results',
        'per_page' => 'Per Page',
        'go_to_page' => 'Go to page',
        'page' => 'Page',
    ],

    // Sorting
    'sorting' => [
        'sort_by' => 'Sort By',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'title' => 'Title',
        'popularity' => 'Popularity',
        'views' => 'Views',
        'likes' => 'Likes',
        'asc' => 'Ascending',
        'desc' => 'Descending',
        'newest' => 'Newest',
        'oldest' => 'Oldest',
        'popular' => 'Most Popular',
        'most_viewed' => 'Most Viewed',
    ],

    // File related
    'file' => [
        'upload' => 'Upload File',
        'choose_file' => 'Choose File',
        'drag_drop' => 'Drag and drop files here',
        'max_size' => 'Maximum file size',
        'allowed_types' => 'Allowed file types',
        'upload_success' => 'File uploaded successfully',
        'upload_failed' => 'File upload failed',
    ],

    // Theme related
    'themes' => [
        'default' => 'Default',
        'dark' => 'Dark',
        'light' => 'Light',
        'auto' => 'Auto',
    ],
];
