<?php

return [
    // Page titles
    'titles' => [
        'index' => 'Code Snippets',
        'create' => 'Create New Gist',
        'edit' => 'Edit Gist',
        'show' => 'Gist Details',
        'my_gists' => 'My Gists',
        'public_gists' => 'Public Gists',
        'private_gists' => 'Private Gists',
        'favorites' => 'My Favorites',
        'liked' => 'Liked Gists',
    ],

    // Form fields
    'fields' => [
        'title' => 'Title',
        'description' => 'Description',
        'content' => 'Code Content',
        'language' => 'Programming Language',
        'filename' => 'Filename',
        'visibility' => 'Visibility',
        'tags' => 'Tags',
        'is_public' => 'Public',
        'is_private' => 'Private',
        'sync_github' => 'Sync to GitHub',
    ],

    // Placeholders
    'placeholders' => [
        'title' => 'Enter Gist title...',
        'description' => 'Enter Gist description (optional)...',
        'content' => 'Enter your code...',
        'filename' => 'e.g., script.php',
        'search' => 'Search Gists...',
        'tags' => 'Select or enter tags...',
    ],

    // Actions
    'actions' => [
        'create' => 'Create Gist',
        'update' => 'Update Gist',
        'delete' => 'Delete Gist',
        'edit' => 'Edit',
        'view' => 'View',
        'copy' => 'Copy Code',
        'download' => 'Download',
        'share' => 'Share',
        'like' => 'Like',
        'unlike' => 'Unlike',
        'favorite' => 'Favorite',
        'unfavorite' => 'Unfavorite',
        'comment' => 'Comment',
        'run_code' => 'Run Code',
        'fork' => 'Fork',
        'embed' => 'Embed',
        'raw' => 'Raw',
        'preview' => 'Preview',
        'save_draft' => 'Save Draft',
        'publish' => 'Publish',
        'sync' => 'Sync',
        'import' => 'Import',
        'export' => 'Export',
    ],

    // Status
    'status' => [
        'public' => 'Public',
        'private' => 'Private',
        'draft' => 'Draft',
        'synced' => 'Synced',
        'not_synced' => 'Not Synced',
        'syncing' => 'Syncing',
        'sync_failed' => 'Sync Failed',
        'published' => 'Published',
        'unpublished' => 'Unpublished',
    ],

    // Statistics
    'stats' => [
        'views' => 'Views',
        'likes' => 'Likes',
        'comments' => 'Comments',
        'favorites' => 'Favorites',
        'forks' => 'Forks',
        'downloads' => 'Downloads',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'author' => 'Author',
        'language' => 'Language',
        'file_size' => 'File Size',
        'lines' => 'Lines',
    ],

    // Filters and sorting
    'filters' => [
        'all_languages' => 'All Languages',
        'all_tags' => 'All Tags',
        'all_users' => 'All Users',
        'public_only' => 'Public Only',
        'private_only' => 'Private Only',
        'with_tags' => 'With Tags',
        'without_tags' => 'Without Tags',
        'recently_updated' => 'Recently Updated',
        'most_popular' => 'Most Popular',
        'most_viewed' => 'Most Viewed',
        'newest_first' => 'Newest First',
        'oldest_first' => 'Oldest First',
    ],

    // Sorting options
    'sorting' => [
        'created_at' => 'Created At',
        'updated' => 'Recently Updated',
        'popular' => 'Most Popular',
        'views' => 'Most Viewed',
        'likes' => 'Most Liked',
        'comments' => 'Most Commented',
        'title' => 'Title',
        'language' => 'Language',
    ],

    // Success messages
    'success' => [
        'created' => 'Gist created successfully!',
        'updated' => 'Gist updated successfully!',
        'deleted' => 'Gist deleted successfully!',
        'copied' => 'Code copied to clipboard',
        'liked' => 'Liked successfully!',
        'unliked' => 'Unliked successfully!',
        'favorited' => 'Added to favorites!',
        'unfavorited' => 'Removed from favorites!',
        'synced' => 'Synced to GitHub successfully!',
        'imported' => 'Imported from GitHub successfully!',
        'forked' => 'Gist forked successfully!',
        'shared' => 'Share link copied',
    ],

    // Error messages
    'errors' => [
        'not_found' => 'Gist not found',
        'no_permission' => 'You do not have permission to access this Gist',
        'create_failed' => 'Failed to create Gist',
        'update_failed' => 'Failed to update Gist',
        'delete_failed' => 'Failed to delete Gist',
        'copy_failed' => 'Copy failed, please copy manually',
        'sync_failed' => 'Failed to sync to GitHub',
        'import_failed' => 'Failed to import from GitHub',
        'invalid_language' => 'Unsupported programming language',
        'content_too_large' => 'Code content is too large',
        'title_required' => 'Title is required',
        'content_required' => 'Code content is required',
        'github_not_connected' => 'Please connect your GitHub account first',
        'rate_limit' => 'Too many requests, please try again later',
    ],

    // Hints
    'hints' => [
        'create_first' => 'Create your first Gist',
        'no_gists' => 'No Gists yet',
        'no_results' => 'No Gists found matching your criteria',
        'empty_search' => 'No search results',
        'login_required' => 'Please sign in to create Gists',
        'github_sync_info' => 'Connect GitHub to sync your Gists',
        'public_visible' => 'Public Gists are visible to everyone',
        'private_visible' => 'Private Gists are only visible to you',
        'tags_help' => 'Add tags to better organize your code',
        'language_auto_detect' => 'Programming language will be auto-detected',
        'filename_optional' => 'Filename is optional and will be auto-generated based on language',
    ],

    // GitHub integration
    'github' => [
        'sync_to_github' => 'Sync to GitHub',
        'import_from_github' => 'Import from GitHub',
        'github_gist_id' => 'GitHub Gist ID',
        'view_on_github' => 'View on GitHub',
        'sync_status' => 'Sync Status',
        'last_synced' => 'Last Synced',
        'auto_sync' => 'Auto Sync',
        'manual_sync' => 'Manual Sync',
        'sync_all' => 'Sync All',
        'import_all' => 'Import All',
        'github_url' => 'GitHub URL',
    ],

    // Code related
    'code' => [
        'syntax_highlighting' => 'Syntax Highlighting',
        'line_numbers' => 'Line Numbers',
        'word_wrap' => 'Word Wrap',
        'theme' => 'Theme',
        'font_size' => 'Font Size',
        'copy_code' => 'Copy Code',
        'select_all' => 'Select All',
        'raw_content' => 'Raw Content',
        'formatted' => 'Formatted',
        'minified' => 'Minified',
        'beautify' => 'Beautify',
        'validate' => 'Validate',
        'run_online' => 'Run Online',
    ],

    // Comments
    'comments' => [
        'add_comment' => 'Add Comment',
        'edit_comment' => 'Edit Comment',
        'delete_comment' => 'Delete Comment',
        'reply' => 'Reply',
        'no_comments' => 'No comments yet',
        'comment_placeholder' => 'Write your comment...',
        'comment_posted' => 'Comment posted successfully',
        'comment_updated' => 'Comment updated successfully',
        'comment_deleted' => 'Comment deleted successfully',
        'load_more_comments' => 'Load more comments',
    ],

    // Sharing
    'sharing' => [
        'share_gist' => 'Share Gist',
        'copy_link' => 'Copy Link',
        'embed_code' => 'Embed Code',
        'social_share' => 'Social Share',
        'email_share' => 'Email Share',
        'qr_code' => 'QR Code',
        'short_url' => 'Short URL',
        'share_settings' => 'Share Settings',
    ],
];
