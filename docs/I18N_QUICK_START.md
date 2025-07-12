# 国际化快速入门指南

## 🚀 5分钟快速上手

### 1. 基本使用

```blade
<!-- 在 Blade 模板中 -->
{{ __('common.navigation.home') }}
{{ __('gist.actions.create') }}
{{ __('auth.messages.welcome', ['name' => $user->name]) }}
```

```php
// 在控制器中
return redirect()->with('success', __('gist.success.created'));

// 在验证中
$request->validate([
    'title' => 'required',
], [
    'title.required' => __('validation.required'),
]);
```

### 2. 语言切换

```blade
<!-- 使用语言切换组件 -->
<x-language-switcher />

<!-- 手动链接 -->
<a href="{{ route('locale.switch', 'en') }}">English</a>
<a href="{{ route('locale.switch', 'zh') }}">中文</a>
```

### 3. 添加新翻译

1. 在 `resources/lang/zh/common.php` 中添加：
```php
'new_feature' => [
    'title' => '新功能',
    'description' => '这是一个新功能的描述',
],
```

2. 在 `resources/lang/en/common.php` 中添加对应翻译：
```php
'new_feature' => [
    'title' => 'New Feature',
    'description' => 'This is a description of the new feature',
],
```

3. 在代码中使用：
```blade
<h1>{{ __('common.new_feature.title') }}</h1>
<p>{{ __('common.new_feature.description') }}</p>
```

### 4. 检查翻译完整性

```bash
# 检查所有翻译
php artisan translation:check-integrity

# 自动修复缺失的翻译
php artisan translation:check-integrity --fix

# 查看翻译统计
php artisan translation:manage stats
```

## 📁 文件结构速查

```
resources/lang/
├── zh/                    # 中文翻译
│   ├── common.php        # 通用文本
│   ├── auth.php          # 认证相关
│   ├── gist.php          # Gist 功能
│   ├── tag.php           # 标签功能
│   ├── php-runner.php    # PHP 运行器
│   └── filament.php      # 后台管理
└── en/                   # 英文翻译
    └── [对应文件]
```

## 🎯 常用翻译键

### 通用操作
```php
__('common.actions.create')     // 创建
__('common.actions.edit')       // 编辑
__('common.actions.delete')     // 删除
__('common.actions.save')       // 保存
__('common.actions.cancel')     // 取消
__('common.actions.confirm')    // 确认
```

### 导航菜单
```php
__('common.navigation.home')    // 首页
__('common.navigation.gists')   // Gist
__('common.navigation.tags')    // 标签
__('common.navigation.login')   // 登录
__('common.navigation.logout')  // 退出
```

### 消息提示
```php
__('common.messages.success')   // 操作成功
__('common.messages.error')     // 操作失败
__('common.messages.loading')   // 加载中
__('common.messages.no_data')   // 暂无数据
```

### 认证相关
```php
__('auth.titles.login')         // 登录
__('auth.titles.register')      // 注册
__('auth.fields.email')         // 邮箱
__('auth.fields.password')      // 密码
__('auth.success.login')        // 登录成功
__('auth.errors.failed')        // 登录失败
```

### Gist 相关
```php
__('gist.titles.index')         // Gist 列表
__('gist.titles.create')        // 创建 Gist
__('gist.fields.title')         // 标题
__('gist.fields.content')       // 内容
__('gist.success.created')      // 创建成功
__('gist.errors.not_found')     // 未找到
```

## 🛠️ 实用命令

```bash
# 翻译管理
php artisan translation:manage check          # 检查翻译
php artisan translation:manage stats          # 显示统计
php artisan translation:manage sync           # 同步翻译
php artisan translation:manage export         # 导出翻译

# 完整性检查
php artisan translation:check-integrity       # 检查完整性
php artisan translation:check-integrity --fix # 自动修复

# 语言检测测试
php artisan locale:test                        # 测试检测功能

# SEO 相关
php artisan sitemap:generate                  # 生成 sitemap
```

## 🎨 自定义 Blade 指令

```blade
@locale                          <!-- 当前语言代码 -->
@localeFlag                      <!-- 当前语言国旗 -->
@localeName                      <!-- 当前语言名称 -->
@formatDate($date)               <!-- 格式化日期 -->
@formatCurrency($amount)         <!-- 格式化货币 -->
@isRtl                          <!-- 是否从右到左 -->
```

## 🔧 配置选项

```env
# 基本配置
APP_LOCALE=zh                    # 默认语言
APP_FALLBACK_LOCALE=en          # 回退语言

# 检测配置
LOCALE_BROWSER_DETECTION=true   # 浏览器检测
LOCALE_IP_DETECTION=false       # IP 检测
LOCALE_REMEMBER_GUEST=true      # 记住访客选择

# SEO 配置
LOCALE_GENERATE_HREFLANG=true   # 生成 hreflang
LOCALE_INCLUDE_IN_SITEMAP=true  # 包含在 sitemap
```

## 🚨 常见错误

### 1. 翻译键不存在
```php
// 错误：返回键名本身
{{ __('non.existent.key') }}  // 输出：non.existent.key

// 解决：检查键是否存在
php artisan translation:manage check
```

### 2. 语言切换不生效
```php
// 检查中间件是否正确注册
// 检查路由是否正确
// 清除缓存
php artisan cache:clear
```

### 3. Filament 后台语言问题
```bash
# 重新发布 Filament 翻译
php artisan vendor:publish --tag=filament-translations --force
```

## 💡 最佳实践

1. **使用描述性的键名**
   ```php
   // ✅ 好的
   'user.profile.edit_button'
   
   // ❌ 不好的
   'btn1'
   ```

2. **保持结构一致**
   ```php
   // 所有语言文件都应该有相同的结构
   'module.section.item'
   ```

3. **使用参数化翻译**
   ```php
   // 翻译文件
   'welcome' => '欢迎, :name!'
   
   // 使用
   __('common.welcome', ['name' => $user->name])
   ```

4. **定期检查翻译完整性**
   ```bash
   # 在 CI/CD 中添加检查
   php artisan translation:check-integrity --strict
   ```

## 📞 获取帮助

- 查看完整文档：`docs/INTERNATIONALIZATION.md`
- 运行检查命令：`php artisan translation:check-integrity`
- 查看翻译统计：`php artisan translation:manage stats`
- 测试语言检测：`php artisan locale:test`

---

**快速开始，立即体验多语言功能！** 🌍✨
