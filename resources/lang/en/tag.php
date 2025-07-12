<?php

return [
    // Page titles
    'titles' => [
        'index' => 'Tag Management',
        'create' => 'Create Tag',
        'edit' => 'Edit Tag',
        'show' => 'Tag Details',
        'popular' => 'Popular Tags',
        'cloud' => 'Tag Cloud',
    ],

    // Form fields
    'fields' => [
        'name' => 'Tag Name',
        'slug' => 'Slug',
        'description' => 'Description',
        'color' => 'Color',
        'usage_count' => 'Usage Count',
        'is_featured' => 'Featured',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],

    // Placeholders
    'placeholders' => [
        'name' => 'Enter tag name...',
        'description' => 'Enter tag description (optional)...',
        'search' => 'Search tags...',
    ],

    // Actions
    'actions' => [
        'create' => 'Create Tag',
        'edit' => 'Edit Tag',
        'delete' => 'Delete Tag',
        'view_gists' => 'View Related Gists',
        'add_to_gist' => 'Add to Gist',
        'remove_from_gist' => 'Remove from Gist',
        'feature' => 'Feature',
        'unfeature' => 'Unfeature',
        'merge' => 'Merge Tags',
        'split' => 'Split Tag',
    ],

    // Colors
    'colors' => [
        'blue' => 'Blue',
        'green' => 'Green',
        'yellow' => 'Yellow',
        'red' => 'Red',
        'purple' => 'Purple',
        'pink' => 'Pink',
        'indigo' => 'Indigo',
        'gray' => 'Gray',
        'orange' => 'Orange',
        'teal' => 'Teal',
    ],

    // Statistics
    'stats' => [
        'total_tags' => 'Total Tags',
        'featured_tags' => 'Featured Tags',
        'popular_tags' => 'Popular Tags',
        'unused_tags' => 'Unused Tags',
        'gists_count' => 'Gists Count',
        'usage_count' => 'Usage Count',
        'created_by' => 'Created By',
    ],

    // Filter options
    'filters' => [
        'all_tags' => 'All Tags',
        'featured_only' => 'Featured Only',
        'popular_only' => 'Popular Only',
        'unused_only' => 'Unused Only',
        'by_color' => 'Filter by Color',
        'by_usage' => 'Filter by Usage',
        'alphabetical' => 'Alphabetical',
        'by_popularity' => 'By Popularity',
        'recently_created' => 'Recently Created',
        'recently_used' => 'Recently Used',
    ],

    // Success messages
    'success' => [
        'created' => 'Tag created successfully!',
        'updated' => 'Tag updated successfully!',
        'deleted' => 'Tag deleted successfully!',
        'featured' => 'Tag featured successfully!',
        'unfeatured' => 'Tag unfeatured successfully!',
        'merged' => 'Tags merged successfully!',
        'added_to_gist' => 'Tag added to Gist!',
        'removed_from_gist' => 'Tag removed from Gist!',
    ],

    // Error messages
    'errors' => [
        'not_found' => 'Tag not found',
        'name_required' => 'Tag name is required',
        'name_exists' => 'Tag name already exists',
        'create_failed' => 'Failed to create tag',
        'update_failed' => 'Failed to update tag',
        'delete_failed' => 'Failed to delete tag',
        'in_use' => 'Cannot delete tag because it is still being used by Gists',
        'invalid_color' => 'Invalid color selection',
        'merge_failed' => 'Failed to merge tags',
        'no_permission' => 'You do not have permission to manage tags',
    ],

    // Hints
    'hints' => [
        'no_tags' => 'No tags yet',
        'create_first' => 'Create your first tag',
        'no_results' => 'No tags found matching your criteria',
        'usage_info' => 'Tag usage count is automatically updated',
        'color_help' => 'Choose appropriate colors to better distinguish tags',
        'featured_help' => 'Featured tags will be displayed on the homepage',
        'delete_warning' => 'Make sure no Gists are using this tag before deleting',
        'merge_warning' => 'Merging tags will transfer all related Gists to the target tag',
        'slug_auto' => 'Tag slug will be auto-generated from the name',
    ],

    // Tag cloud
    'cloud' => [
        'title' => 'Tag Cloud',
        'size_by_usage' => 'Size represents usage frequency',
        'click_to_filter' => 'Click tag to view related Gists',
        'no_tags_available' => 'No tags available',
        'loading' => 'Loading tag cloud...',
        'refresh' => 'Refresh tag cloud',
        'view_all' => 'View all tags',
    ],

    // Suggestions
    'suggestions' => [
        'popular_suggestions' => 'Popular tag suggestions',
        'related_tags' => 'Related tags',
        'auto_suggest' => 'Auto suggest',
        'no_suggestions' => 'No suggestions',
        'add_suggestion' => 'Add suggestion',
        'common_tags' => 'Common tags',
        'language_tags' => 'Programming language tags',
        'framework_tags' => 'Framework tags',
        'tool_tags' => 'Tool tags',
    ],

    // Management
    'management' => [
        'bulk_actions' => 'Bulk Actions',
        'select_all' => 'Select All',
        'deselect_all' => 'Deselect All',
        'bulk_delete' => 'Bulk Delete',
        'bulk_feature' => 'Bulk Feature',
        'bulk_unfeature' => 'Bulk Unfeature',
        'bulk_change_color' => 'Bulk Change Color',
        'export_tags' => 'Export Tags',
        'import_tags' => 'Import Tags',
        'cleanup_unused' => 'Cleanup Unused Tags',
    ],
];
