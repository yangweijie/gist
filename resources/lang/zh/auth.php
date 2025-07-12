<?php

return [
    // 页面标题
    'titles' => [
        'login' => '登录到您的账户',
        'register' => '创建新账户',
        'forgot_password' => '忘记密码',
        'reset_password' => '重置密码',
        'verify_email' => '验证邮箱',
        'two_factor' => '双重验证',
    ],

    // 表单字段
    'fields' => [
        'name' => '姓名',
        'email' => '邮箱地址',
        'password' => '密码',
        'password_confirmation' => '确认密码',
        'current_password' => '当前密码',
        'new_password' => '新密码',
        'remember_me' => '记住我',
        'verification_code' => '验证码',
    ],

    // 占位符
    'placeholders' => [
        'name' => '请输入您的姓名',
        'email' => '请输入邮箱地址',
        'password' => '请输入密码',
        'password_confirmation' => '请再次输入密码',
        'current_password' => '请输入当前密码',
        'new_password' => '请输入新密码',
        'verification_code' => '请输入验证码',
    ],

    // 按钮文本
    'buttons' => [
        'login' => '登录',
        'register' => '注册',
        'logout' => '登出',
        'forgot_password' => '忘记密码？',
        'reset_password' => '重置密码',
        'send_reset_link' => '发送重置链接',
        'verify_email' => '验证邮箱',
        'resend_verification' => '重新发送验证邮件',
        'github_login' => '使用 GitHub 登录',
        'github_register' => '使用 GitHub 注册',
        'back_to_login' => '返回登录',
        'back_to_register' => '返回注册',
    ],

    // 链接文本
    'links' => [
        'already_registered' => '已有账户？',
        'not_registered' => '还没有账户？',
        'login_here' => '点击登录',
        'register_here' => '点击注册',
        'forgot_password' => '忘记密码？',
        'remember_password' => '想起密码了？',
    ],

    // 成功消息
    'success' => [
        'login' => '登录成功！',
        'register' => '注册成功！',
        'logout' => '已安全退出',
        'password_reset' => '密码重置成功！',
        'password_reset_sent' => '密码重置链接已发送到您的邮箱',
        'email_verified' => '邮箱验证成功！',
        'verification_sent' => '验证邮件已发送',
    ],

    // 错误消息
    'errors' => [
        'failed' => '登录信息不正确',
        'password' => '密码错误',
        'throttle' => '登录尝试次数过多，请在 :seconds 秒后重试',
        'email_not_verified' => '请先验证您的邮箱地址',
        'account_disabled' => '您的账户已被禁用',
        'invalid_token' => '重置令牌无效或已过期',
        'email_not_found' => '未找到该邮箱地址',
        'weak_password' => '密码强度不够',
        'password_mismatch' => '密码确认不匹配',
        'email_taken' => '该邮箱地址已被使用',
        'github_error' => 'GitHub 授权失败',
        'github_email_taken' => '该 GitHub 邮箱已被其他账户使用',
    ],

    // 提示信息
    'hints' => [
        'password_requirements' => '密码至少需要 8 个字符，包含字母和数字',
        'email_verification' => '我们已向您的邮箱发送了验证链接',
        'password_reset_info' => '输入您的邮箱地址，我们将发送重置链接',
        'github_benefits' => '使用 GitHub 登录可以同步您的 Gist',
        'secure_login' => '我们使用安全加密保护您的账户信息',
    ],

    // GitHub 相关
    'github' => [
        'connect' => '连接 GitHub',
        'disconnect' => '断开 GitHub',
        'connected' => '已连接 GitHub',
        'not_connected' => '未连接 GitHub',
        'sync_gists' => '同步 GitHub Gist',
        'import_gists' => '导入 GitHub Gist',
        'permissions' => '需要访问您的 GitHub Gist 权限',
        'username' => 'GitHub 用户名',
        'profile' => 'GitHub 个人资料',
    ],

    // 验证相关
    'verification' => [
        'email_sent' => '验证邮件已发送到 :email',
        'email_verified' => '邮箱已验证',
        'email_not_verified' => '邮箱未验证',
        'resend_email' => '重新发送验证邮件',
        'check_email' => '请检查您的邮箱并点击验证链接',
        'expired' => '验证链接已过期',
        'invalid' => '验证链接无效',
    ],

    // 安全相关
    'security' => [
        'two_factor' => '双重验证',
        'enable_2fa' => '启用双重验证',
        'disable_2fa' => '禁用双重验证',
        'backup_codes' => '备份代码',
        'recovery_codes' => '恢复代码',
        'authenticator_app' => '验证器应用',
        'scan_qr_code' => '扫描二维码',
        'enter_code' => '输入验证码',
        'invalid_code' => '验证码无效',
        'codes_generated' => '备份代码已生成',
        'save_codes' => '请保存这些备份代码',
    ],

    // 会话相关
    'session' => [
        'expired' => '会话已过期，请重新登录',
        'invalid' => '会话无效',
        'timeout' => '会话即将过期',
        'extend' => '延长会话',
        'active_sessions' => '活跃会话',
        'logout_other_devices' => '在其他设备上登出',
        'current_device' => '当前设备',
        'last_activity' => '最后活动时间',
    ],
];
