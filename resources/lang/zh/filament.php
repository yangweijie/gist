<?php

return [
    // 品牌和标题
    'brand_name' => 'Gist 管理后台',
    'dashboard' => '仪表板',
    'welcome' => '欢迎使用 Gist 管理系统',

    // 消息
    'messages' => [
        'saved' => '保存成功',
        'created' => '创建成功',
        'updated' => '更新成功',
        'deleted' => '删除成功',
        'error' => '操作失败',
    ],

    // 导航组
    'navigation_groups' => [
        'content' => '内容管理',
        'users' => '用户管理',
        'system' => '系统管理',
    ],

    // 资源标题
    'resources' => [
        'gist' => [
            'label' => 'Gist',
            'plural_label' => 'Gist 列表',
            'navigation_label' => 'Gist 管理',
            'breadcrumb' => 'Gist',
        ],
        'user' => [
            'label' => '用户',
            'plural_label' => '用户列表',
            'navigation_label' => '用户管理',
            'breadcrumb' => '用户',
        ],
        'tag' => [
            'label' => '标签',
            'plural_label' => '标签列表',
            'navigation_label' => '标签管理',
            'breadcrumb' => '标签',
        ],
        'comment' => [
            'label' => '评论',
            'plural_label' => '评论列表',
            'navigation_label' => '评论管理',
            'breadcrumb' => '评论',
        ],
    ],

    // 页面标题
    'pages' => [
        'dashboard' => [
            'title' => '仪表板',
            'navigation_label' => '仪表板',
        ],
        'settings' => [
            'title' => '系统设置',
            'navigation_label' => '系统设置',
        ],
    ],

    // 表单字段标签
    'fields' => [
        // 通用字段
        'id' => 'ID',
        'created_at' => '创建时间',
        'updated_at' => '更新时间',
        'actions' => '操作',

        // Gist 字段
        'gist' => [
            'title' => '标题',
            'description' => '描述',
            'content' => '代码内容',
            'language' => '编程语言',
            'filename' => '文件名',
            'is_public' => '公开',
            'user' => '作者',
            'views_count' => '浏览次数',
            'likes_count' => '点赞数',
            'comments_count' => '评论数',
            'favorites_count' => '收藏数',
            'github_gist_id' => 'GitHub Gist ID',
            'github_url' => 'GitHub 链接',
            'tags' => '标签',
        ],

        // 用户字段
        'user' => [
            'name' => '姓名',
            'email' => '邮箱',
            'avatar_url' => '头像',
            'github_id' => 'GitHub ID',
            'github_username' => 'GitHub 用户名',
            'github_token' => 'GitHub Token',
            'is_active' => '激活状态',
            'email_verified_at' => '邮箱验证时间',
            'gists_count' => 'Gist 数量',
            'last_login_at' => '最后登录时间',
        ],

        // 标签字段
        'tag' => [
            'name' => '标签名称',
            'slug' => '标签别名',
            'description' => '标签描述',
            'color' => '标签颜色',
            'usage_count' => '使用次数',
            'is_featured' => '推荐标签',
        ],

        // 评论字段
        'comment' => [
            'content' => '评论内容',
            'user' => '评论者',
            'gist' => '所属 Gist',
            'parent' => '父评论',
            'is_approved' => '已审核',
            'replies_count' => '回复数',
        ],

        // 设置字段
        'settings' => [
            'site_name' => '网站名称',
            'site_description' => '网站描述',
            'items_per_page' => '每页显示数量',
            'enable_registration' => '允许用户注册',
            'enable_comments' => '启用评论功能',
            'auto_approve_comments' => '自动审核评论',
            'github_sync_enabled' => '启用 GitHub 同步',
        ],
    ],

    // 表单部分标题
    'sections' => [
        'basic_info' => '基本信息',
        'content' => '内容',
        'code_content' => '代码内容',
        'metadata' => '元数据',
        'settings' => '设置',
        'github_info' => 'GitHub 信息',
        'statistics' => '统计信息',
        'permissions' => '权限设置',
        'website_settings' => '网站设置',
        'feature_settings' => '功能设置',
    ],

    // 操作按钮
    'actions' => [
        'create' => '创建',
        'edit' => '编辑',
        'delete' => '删除',
        'view' => '查看',
        'save' => '保存',
        'cancel' => '取消',
        'reset' => '重置',
        'search' => '搜索',
        'filter' => '筛选',
        'export' => '导出',
        'import' => '导入',
        'bulk_delete' => '批量删除',
        'approve' => '审核通过',
        'reject' => '拒绝',
        'activate' => '激活',
        'deactivate' => '停用',
        'sync' => '同步',
        'refresh' => '刷新',
    ],

    // 状态和标签
    'status' => [
        'active' => '激活',
        'inactive' => '未激活',
        'approved' => '已审核',
        'pending' => '待审核',
        'rejected' => '已拒绝',
        'public' => '公开',
        'private' => '私有',
        'featured' => '推荐',
        'draft' => '草稿',
        'published' => '已发布',
    ],

    // 消息提示
    'messages' => [
        'created' => '创建成功',
        'updated' => '更新成功',
        'deleted' => '删除成功',
        'saved' => '保存成功',
        'error' => '操作失败',
        'no_records' => '暂无数据',
        'confirm_delete' => '确定要删除吗？',
        'bulk_delete_confirm' => '确定要删除选中的 :count 项吗？',
        'operation_success' => '操作成功',
        'operation_failed' => '操作失败',
    ],

    // 筛选器
    'filters' => [
        'all' => '全部',
        'active' => '激活',
        'inactive' => '未激活',
        'public' => '公开',
        'private' => '私有',
        'featured' => '推荐',
        'approved' => '已审核',
        'pending' => '待审核',
        'this_week' => '本周',
        'this_month' => '本月',
        'this_year' => '今年',
    ],

    // 表格列标题
    'table' => [
        'columns' => [
            'id' => 'ID',
            'title' => '标题',
            'name' => '名称',
            'email' => '邮箱',
            'status' => '状态',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'actions' => '操作',
            'user' => '用户',
            'language' => '语言',
            'views' => '浏览',
            'likes' => '点赞',
            'comments' => '评论',
            'tags' => '标签',
        ],
        'empty_state' => [
            'heading' => '暂无数据',
            'description' => '还没有任何记录。',
        ],
    ],

    // 小部件
    'widgets' => [
        'stats_overview' => [
            'total_gists' => '总 Gist 数',
            'total_users' => '总用户数',
            'total_views' => '总浏览量',
            'total_likes' => '总点赞数',
            'public_gists' => '公开 Gist',
            'private_gists' => '私有 Gist',
            'active_users' => '活跃用户',
            'new_users_this_month' => '本月新用户',
        ],
        'latest_gists' => [
            'title' => '最新 Gist',
            'view_all' => '查看全部',
        ],
        'user_activity' => [
            'title' => '用户活动',
            'recent_activities' => '最近活动',
        ],
    ],

    // 验证消息
    'validation' => [
        'required' => ':attribute 字段是必填的',
        'email' => ':attribute 必须是有效的邮箱地址',
        'unique' => ':attribute 已经存在',
        'min' => ':attribute 至少需要 :min 个字符',
        'max' => ':attribute 不能超过 :max 个字符',
        'numeric' => ':attribute 必须是数字',
        'url' => ':attribute 必须是有效的 URL',
    ],
];
