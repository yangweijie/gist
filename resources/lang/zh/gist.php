<?php

return [
    // 页面标题
    'titles' => [
        'index' => '代码片段',
        'create' => '创建新 Gist',
        'edit' => '编辑 Gist',
        'show' => 'Gist 详情',
        'my_gists' => '我的 Gist',
        'public_gists' => '公开 Gist',
        'private_gists' => '私有 Gist',
        'favorites' => '我的收藏',
        'liked' => '我点赞的',
    ],

    // 表单字段
    'fields' => [
        'title' => '标题',
        'description' => '描述',
        'content' => '代码内容',
        'language' => '编程语言',
        'filename' => '文件名',
        'visibility' => '可见性',
        'tags' => '标签',
        'is_public' => '公开',
        'is_private' => '私有',
        'sync_github' => '同步到 GitHub',
    ],

    // 占位符
    'placeholders' => [
        'title' => '请输入 Gist 标题...',
        'description' => '请输入 Gist 描述（可选）...',
        'content' => '请输入您的代码...',
        'filename' => '例如：script.php',
        'search' => '搜索 Gist...',
        'tags' => '选择或输入标签...',
    ],

    // 按钮和操作
    'actions' => [
        'create' => '创建 Gist',
        'update' => '更新 Gist',
        'delete' => '删除 Gist',
        'edit' => '编辑',
        'view' => '查看',
        'copy' => '复制代码',
        'download' => '下载',
        'share' => '分享',
        'like' => '点赞',
        'unlike' => '取消点赞',
        'favorite' => '收藏',
        'unfavorite' => '取消收藏',
        'comment' => '评论',
        'run_code' => '运行代码',
        'fork' => '复制',
        'embed' => '嵌入',
        'raw' => '原始代码',
        'preview' => '预览',
        'save_draft' => '保存草稿',
        'publish' => '发布',
        'sync' => '同步',
        'import' => '导入',
        'export' => '导出',
    ],

    // 状态
    'status' => [
        'public' => '公开',
        'private' => '私有',
        'draft' => '草稿',
        'synced' => '已同步',
        'not_synced' => '未同步',
        'syncing' => '同步中',
        'sync_failed' => '同步失败',
        'published' => '已发布',
        'unpublished' => '未发布',
    ],

    // 统计信息
    'stats' => [
        'views' => '浏览次数',
        'likes' => '点赞数',
        'comments' => '评论数',
        'favorites' => '收藏数',
        'forks' => '复制数',
        'downloads' => '下载次数',
        'created_at' => '创建时间',
        'updated_at' => '更新时间',
        'author' => '作者',
        'language' => '语言',
        'file_size' => '文件大小',
        'lines' => '行数',
    ],

    // 筛选和排序
    'filters' => [
        'all_languages' => '所有语言',
        'all_tags' => '所有标签',
        'all_users' => '所有用户',
        'public_only' => '仅公开',
        'private_only' => '仅私有',
        'with_tags' => '有标签',
        'without_tags' => '无标签',
        'recently_updated' => '最近更新',
        'most_popular' => '最受欢迎',
        'most_viewed' => '浏览最多',
        'newest_first' => '最新优先',
        'oldest_first' => '最旧优先',
    ],

    // 排序选项
    'sorting' => [
        'created_at' => '最新创建',
        'updated' => '最近更新',
        'popular' => '最受欢迎',
        'views' => '浏览最多',
        'likes' => '点赞最多',
        'comments' => '评论最多',
        'title' => '标题',
        'language' => '语言',
    ],

    // 成功消息
    'success' => [
        'created' => 'Gist 创建成功！',
        'updated' => 'Gist 更新成功！',
        'deleted' => 'Gist 删除成功！',
        'copied' => '代码已复制到剪贴板',
        'liked' => '点赞成功！',
        'unliked' => '取消点赞成功！',
        'favorited' => '收藏成功！',
        'unfavorited' => '取消收藏成功！',
        'synced' => '同步到 GitHub 成功！',
        'imported' => '从 GitHub 导入成功！',
        'forked' => 'Gist 复制成功！',
        'shared' => '分享链接已复制',
    ],

    // 错误消息
    'errors' => [
        'not_found' => 'Gist 不存在',
        'no_permission' => '您没有权限访问此 Gist',
        'create_failed' => '创建 Gist 失败',
        'update_failed' => '更新 Gist 失败',
        'delete_failed' => '删除 Gist 失败',
        'copy_failed' => '复制失败，请手动复制',
        'sync_failed' => '同步到 GitHub 失败',
        'import_failed' => '从 GitHub 导入失败',
        'invalid_language' => '不支持的编程语言',
        'content_too_large' => '代码内容过大',
        'title_required' => '标题不能为空',
        'content_required' => '代码内容不能为空',
        'github_not_connected' => '请先连接 GitHub 账户',
        'rate_limit' => '操作过于频繁，请稍后重试',
    ],

    // 提示信息
    'hints' => [
        'create_first' => '创建您的第一个 Gist',
        'no_gists' => '还没有 Gist',
        'no_results' => '没有找到符合条件的 Gist',
        'empty_search' => '搜索结果为空',
        'login_required' => '请登录后创建 Gist',
        'github_sync_info' => '连接 GitHub 后可以同步您的 Gist',
        'public_visible' => '公开的 Gist 对所有人可见',
        'private_visible' => '私有的 Gist 只有您可以查看',
        'tags_help' => '添加标签可以更好地组织您的代码',
        'language_auto_detect' => '系统会自动检测编程语言',
        'filename_optional' => '文件名是可选的，会根据语言自动生成',
    ],

    // GitHub 集成
    'github' => [
        'sync_to_github' => '同步到 GitHub',
        'import_from_github' => '从 GitHub 导入',
        'github_gist_id' => 'GitHub Gist ID',
        'view_on_github' => '在 GitHub 上查看',
        'sync_status' => '同步状态',
        'last_synced' => '最后同步时间',
        'auto_sync' => '自动同步',
        'manual_sync' => '手动同步',
        'sync_all' => '同步所有',
        'import_all' => '导入所有',
        'github_url' => 'GitHub 链接',
    ],

    // 代码相关
    'code' => [
        'syntax_highlighting' => '语法高亮',
        'line_numbers' => '行号',
        'word_wrap' => '自动换行',
        'theme' => '主题',
        'font_size' => '字体大小',
        'copy_code' => '复制代码',
        'select_all' => '全选',
        'raw_content' => '原始内容',
        'formatted' => '格式化',
        'minified' => '压缩',
        'beautify' => '美化',
        'validate' => '验证',
        'run_online' => '在线运行',
    ],

    // 评论相关
    'comments' => [
        'add_comment' => '添加评论',
        'edit_comment' => '编辑评论',
        'delete_comment' => '删除评论',
        'reply' => '回复',
        'no_comments' => '暂无评论',
        'comment_placeholder' => '写下您的评论...',
        'comment_posted' => '评论发表成功',
        'comment_updated' => '评论更新成功',
        'comment_deleted' => '评论删除成功',
        'load_more_comments' => '加载更多评论',
    ],

    // 分享相关
    'sharing' => [
        'share_gist' => '分享 Gist',
        'copy_link' => '复制链接',
        'embed_code' => '嵌入代码',
        'social_share' => '社交分享',
        'email_share' => '邮件分享',
        'qr_code' => '二维码',
        'short_url' => '短链接',
        'share_settings' => '分享设置',
    ],
];
