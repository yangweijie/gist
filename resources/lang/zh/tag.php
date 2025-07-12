<?php

return [
    // 页面标题
    'titles' => [
        'index' => '标签管理',
        'create' => '创建标签',
        'edit' => '编辑标签',
        'show' => '标签详情',
        'popular' => '热门标签',
        'cloud' => '标签云',
    ],

    // 表单字段
    'fields' => [
        'name' => '标签名称',
        'slug' => '标签别名',
        'description' => '标签描述',
        'color' => '标签颜色',
        'usage_count' => '使用次数',
        'is_featured' => '推荐标签',
        'created_at' => '创建时间',
        'updated_at' => '更新时间',
    ],

    // 占位符
    'placeholders' => [
        'name' => '请输入标签名称...',
        'description' => '请输入标签描述（可选）...',
        'search' => '搜索标签...',
    ],

    // 按钮和操作
    'actions' => [
        'create' => '创建标签',
        'edit' => '编辑标签',
        'delete' => '删除标签',
        'view_gists' => '查看相关 Gist',
        'add_to_gist' => '添加到 Gist',
        'remove_from_gist' => '从 Gist 移除',
        'feature' => '设为推荐',
        'unfeature' => '取消推荐',
        'merge' => '合并标签',
        'split' => '拆分标签',
    ],

    // 颜色选项
    'colors' => [
        'blue' => '蓝色',
        'green' => '绿色',
        'yellow' => '黄色',
        'red' => '红色',
        'purple' => '紫色',
        'pink' => '粉色',
        'indigo' => '靛蓝',
        'gray' => '灰色',
        'orange' => '橙色',
        'teal' => '青色',
    ],

    // 统计信息
    'stats' => [
        'total_tags' => '标签总数',
        'featured_tags' => '推荐标签',
        'popular_tags' => '热门标签',
        'unused_tags' => '未使用标签',
        'gists_count' => 'Gist 数量',
        'usage_count' => '使用次数',
        'created_by' => '创建者',
    ],

    // 筛选选项
    'filters' => [
        'all_tags' => '所有标签',
        'featured_only' => '仅推荐标签',
        'popular_only' => '仅热门标签',
        'unused_only' => '仅未使用标签',
        'by_color' => '按颜色筛选',
        'by_usage' => '按使用次数筛选',
        'alphabetical' => '按字母排序',
        'by_popularity' => '按热度排序',
        'recently_created' => '最近创建',
        'recently_used' => '最近使用',
    ],

    // 成功消息
    'success' => [
        'created' => '标签创建成功！',
        'updated' => '标签更新成功！',
        'deleted' => '标签删除成功！',
        'featured' => '标签已设为推荐！',
        'unfeatured' => '标签已取消推荐！',
        'merged' => '标签合并成功！',
        'added_to_gist' => '标签已添加到 Gist！',
        'removed_from_gist' => '标签已从 Gist 移除！',
    ],

    // 错误消息
    'errors' => [
        'not_found' => '标签不存在',
        'name_required' => '标签名称不能为空',
        'name_exists' => '标签名称已存在',
        'create_failed' => '创建标签失败',
        'update_failed' => '更新标签失败',
        'delete_failed' => '删除标签失败',
        'in_use' => '无法删除该标签，因为还有 Gist 在使用它',
        'invalid_color' => '无效的颜色选择',
        'merge_failed' => '标签合并失败',
        'no_permission' => '您没有权限管理标签',
    ],

    // 提示信息
    'hints' => [
        'no_tags' => '还没有标签',
        'create_first' => '创建您的第一个标签',
        'no_results' => '没有找到符合条件的标签',
        'usage_info' => '标签使用次数会自动更新',
        'color_help' => '选择合适的颜色可以更好地区分标签',
        'featured_help' => '推荐标签会在首页显示',
        'delete_warning' => '删除标签前请确保没有 Gist 在使用',
        'merge_warning' => '合并标签会将所有相关 Gist 转移到目标标签',
        'slug_auto' => '标签别名会根据名称自动生成',
    ],

    // 标签云相关
    'cloud' => [
        'title' => '标签云',
        'size_by_usage' => '大小表示使用频率',
        'click_to_filter' => '点击标签查看相关 Gist',
        'no_tags_available' => '暂无可用标签',
        'loading' => '正在加载标签云...',
        'refresh' => '刷新标签云',
        'view_all' => '查看所有标签',
    ],

    // 建议相关
    'suggestions' => [
        'popular_suggestions' => '热门标签建议',
        'related_tags' => '相关标签',
        'auto_suggest' => '自动建议',
        'no_suggestions' => '暂无建议',
        'add_suggestion' => '添加建议',
        'common_tags' => '常用标签',
        'language_tags' => '编程语言标签',
        'framework_tags' => '框架标签',
        'tool_tags' => '工具标签',
    ],

    // 管理相关
    'management' => [
        'bulk_actions' => '批量操作',
        'select_all' => '全选',
        'deselect_all' => '取消全选',
        'bulk_delete' => '批量删除',
        'bulk_feature' => '批量推荐',
        'bulk_unfeature' => '批量取消推荐',
        'bulk_change_color' => '批量更改颜色',
        'export_tags' => '导出标签',
        'import_tags' => '导入标签',
        'cleanup_unused' => '清理未使用标签',
    ],
];
