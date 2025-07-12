<?php

return [
    // Brand and titles
    'brand_name' => 'Gist Admin Panel',
    'dashboard' => 'Dashboard',
    'welcome' => 'Welcome to Gist Management System',

    // Navigation groups
    'navigation_groups' => [
        'content' => 'Content Management',
        'users' => 'User Management',
        'system' => 'System Management',
    ],

    // Resource titles
    'resources' => [
        'gist' => [
            'label' => 'Gist',
            'plural_label' => 'Gists',
            'navigation_label' => 'Gist Management',
            'breadcrumb' => 'Gist',
        ],
        'user' => [
            'label' => 'User',
            'plural_label' => 'Users',
            'navigation_label' => 'User Management',
            'breadcrumb' => 'User',
        ],
        'tag' => [
            'label' => 'Tag',
            'plural_label' => 'Tags',
            'navigation_label' => 'Tag Management',
            'breadcrumb' => 'Tag',
        ],
        'comment' => [
            'label' => 'Comment',
            'plural_label' => 'Comments',
            'navigation_label' => 'Comment Management',
            'breadcrumb' => 'Comment',
        ],
    ],

    // Page titles
    'pages' => [
        'dashboard' => [
            'title' => 'Dashboard',
            'navigation_label' => 'Dashboard',
        ],
        'settings' => [
            'title' => 'System Settings',
            'navigation_label' => 'Settings',
        ],
    ],

    // Form field labels
    'fields' => [
        // Common fields
        'id' => 'ID',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'actions' => 'Actions',

        // Gist fields
        'gist' => [
            'title' => 'Title',
            'description' => 'Description',
            'content' => 'Code Content',
            'language' => 'Programming Language',
            'filename' => 'Filename',
            'is_public' => 'Public',
            'user' => 'Author',
            'views_count' => 'Views',
            'likes_count' => 'Likes',
            'comments_count' => 'Comments',
            'favorites_count' => 'Favorites',
            'github_gist_id' => 'GitHub Gist ID',
            'github_url' => 'GitHub URL',
            'tags' => 'Tags',
        ],

        // User fields
        'user' => [
            'name' => 'Name',
            'email' => 'Email',
            'avatar_url' => 'Avatar',
            'github_id' => 'GitHub ID',
            'github_username' => 'GitHub Username',
            'github_token' => 'GitHub Token',
            'is_active' => 'Active Status',
            'email_verified_at' => 'Email Verified At',
            'gists_count' => 'Gists Count',
            'last_login_at' => 'Last Login At',
        ],

        // Tag fields
        'tag' => [
            'name' => 'Tag Name',
            'slug' => 'Slug',
            'description' => 'Description',
            'color' => 'Color',
            'usage_count' => 'Usage Count',
            'is_featured' => 'Featured',
        ],

        // Comment fields
        'comment' => [
            'content' => 'Content',
            'user' => 'Commenter',
            'gist' => 'Gist',
            'parent' => 'Parent Comment',
            'is_approved' => 'Approved',
            'replies_count' => 'Replies',
        ],

        // Settings fields
        'settings' => [
            'site_name' => 'Site Name',
            'site_description' => 'Site Description',
            'items_per_page' => 'Items Per Page',
            'enable_registration' => 'Enable Registration',
            'enable_comments' => 'Enable Comments',
            'auto_approve_comments' => 'Auto Approve Comments',
            'github_sync_enabled' => 'Enable GitHub Sync',
        ],
    ],

    // Form section titles
    'sections' => [
        'basic_info' => 'Basic Information',
        'content' => 'Content',
        'code_content' => 'Code Content',
        'metadata' => 'Metadata',
        'settings' => 'Settings',
        'github_info' => 'GitHub Information',
        'statistics' => 'Statistics',
        'permissions' => 'Permissions',
        'website_settings' => 'Website Settings',
        'feature_settings' => 'Feature Settings',
    ],

    // Action buttons
    'actions' => [
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'view' => 'View',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'reset' => 'Reset',
        'search' => 'Search',
        'filter' => 'Filter',
        'export' => 'Export',
        'import' => 'Import',
        'bulk_delete' => 'Bulk Delete',
        'approve' => 'Approve',
        'reject' => 'Reject',
        'activate' => 'Activate',
        'deactivate' => 'Deactivate',
        'sync' => 'Sync',
        'refresh' => 'Refresh',
    ],

    // Status and labels
    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'approved' => 'Approved',
        'pending' => 'Pending',
        'rejected' => 'Rejected',
        'public' => 'Public',
        'private' => 'Private',
        'featured' => 'Featured',
        'draft' => 'Draft',
        'published' => 'Published',
    ],

    // Messages
    'messages' => [
        'created' => 'Created successfully',
        'updated' => 'Updated successfully',
        'deleted' => 'Deleted successfully',
        'saved' => 'Saved successfully',
        'error' => 'Operation failed',
        'no_records' => 'No records found',
        'confirm_delete' => 'Are you sure you want to delete?',
        'bulk_delete_confirm' => 'Are you sure you want to delete :count items?',
        'operation_success' => 'Operation successful',
        'operation_failed' => 'Operation failed',
    ],

    // Filters
    'filters' => [
        'all' => 'All',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'public' => 'Public',
        'private' => 'Private',
        'featured' => 'Featured',
        'approved' => 'Approved',
        'pending' => 'Pending',
        'this_week' => 'This Week',
        'this_month' => 'This Month',
        'this_year' => 'This Year',
    ],

    // Table columns
    'table' => [
        'columns' => [
            'id' => 'ID',
            'title' => 'Title',
            'name' => 'Name',
            'email' => 'Email',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'actions' => 'Actions',
            'user' => 'User',
            'language' => 'Language',
            'views' => 'Views',
            'likes' => 'Likes',
            'comments' => 'Comments',
            'tags' => 'Tags',
        ],
        'empty_state' => [
            'heading' => 'No data',
            'description' => 'No records found.',
        ],
    ],

    // Widgets
    'widgets' => [
        'stats_overview' => [
            'total_gists' => 'Total Gists',
            'total_users' => 'Total Users',
            'total_views' => 'Total Views',
            'total_likes' => 'Total Likes',
            'public_gists' => 'Public Gists',
            'private_gists' => 'Private Gists',
            'active_users' => 'Active Users',
            'new_users_this_month' => 'New Users This Month',
        ],
        'latest_gists' => [
            'title' => 'Latest Gists',
            'view_all' => 'View All',
        ],
        'user_activity' => [
            'title' => 'User Activity',
            'recent_activities' => 'Recent Activities',
        ],
    ],

    // Validation messages
    'validation' => [
        'required' => 'The :attribute field is required',
        'email' => 'The :attribute must be a valid email address',
        'unique' => 'The :attribute has already been taken',
        'min' => 'The :attribute must be at least :min characters',
        'max' => 'The :attribute may not be greater than :max characters',
        'numeric' => 'The :attribute must be a number',
        'url' => 'The :attribute must be a valid URL',
    ],
];
