# 国际化 (i18n) 完整指南

## 📖 目录

1. [概述](#概述)
2. [架构设计](#架构设计)
3. [开发指南](#开发指南)
4. [翻译指南](#翻译指南)
5. [部署指南](#部署指南)
6. [管理工具](#管理工具)
7. [最佳实践](#最佳实践)
8. [故障排除](#故障排除)

## 🌍 概述

本项目实现了完整的多语言国际化支持，包括：

- **前台多语言化**: 所有用户界面文本支持多语言
- **后台 Filament 多语言化**: 管理后台完全本地化
- **智能语言检测**: 自动检测用户偏好语言
- **SEO 优化**: 多语言 SEO 支持，包括 hreflang 标签
- **翻译管理工具**: 完整的翻译管理和检查工具

### 支持的语言

- 🇨🇳 中文 (zh)
- 🇺🇸 英文 (en)
- 🔄 可扩展支持更多语言

## 🏗️ 架构设计

### 语言文件结构

```
resources/lang/
├── zh/                           # 中文语言包
│   ├── common.php               # 通用文本 (150+ 项)
│   ├── auth.php                 # 认证相关 (100+ 项)
│   ├── gist.php                 # Gist 相关 (150+ 项)
│   ├── tag.php                  # 标签相关 (80+ 项)
│   ├── php-runner.php           # PHP 运行器 (120+ 项)
│   └── filament.php             # Filament 后台 (200+ 项)
├── en/                          # 英文语言包
│   └── [对应的英文翻译文件]
└── vendor/                      # Filament 官方翻译
    ├── filament/zh/             # Filament 核心翻译
    ├── filament-actions/zh/     # 操作翻译
    ├── filament-forms/zh/       # 表单翻译
    ├── filament-tables/zh/      # 表格翻译
    └── filament-notifications/zh/ # 通知翻译
```

### 核心服务

1. **LocalizationService**: 本地化核心服务
2. **GeoLocationService**: 地理位置检测服务
3. **SeoLocalizationService**: SEO 多语言服务
4. **SetLocale Middleware**: 语言检测中间件

### 语言检测优先级

```
1. URL 参数 (?lang=en)
2. Session 存储
3. 用户数据库偏好
4. Cookie 记忆
5. 浏览器 Accept-Language 头
6. IP 地理位置检测（可选）
7. 默认语言回退
```

## 👨‍💻 开发指南

### 在 Blade 模板中使用翻译

```blade
<!-- 基本用法 -->
{{ __('common.navigation.home') }}

<!-- 带参数 -->
{{ __('common.messages.welcome', ['name' => $user->name]) }}

<!-- 使用 Blade 指令 -->
@lang('common.actions.save')

<!-- 复数形式 -->
{{ trans_choice('common.items_count', $count) }}
```

### 在控制器中使用翻译

```php
// 基本用法
return redirect()->with('success', __('gist.success.created'));

// 验证消息
$request->validate([
    'title' => 'required',
], [
    'title.required' => __('validation.required', ['attribute' => __('gist.fields.title')]),
]);

// 通知
Notification::make()
    ->title(__('common.messages.success'))
    ->success()
    ->send();
```

### 在 JavaScript 中使用翻译

```javascript
// 在 Blade 模板中传递翻译
<script>
window.translations = @json([
    'confirm_delete' => __('common.messages.confirm_delete'),
    'success' => __('common.messages.success'),
]);
</script>

// 在 JavaScript 中使用
function confirmDelete() {
    return confirm(window.translations.confirm_delete);
}
```

### 自定义 Blade 指令

```blade
<!-- 获取当前语言 -->
@locale

<!-- 格式化日期 -->
@formatDate($date, 'date')

<!-- 格式化货币 -->
@formatCurrency($amount)

<!-- 获取当前语言国旗 -->
@localeFlag

<!-- 获取当前语言名称 -->
@localeName
```

### Filament 资源多语言化

```php
class GistResource extends Resource
{
    public static function getNavigationLabel(): string
    {
        return __('filament.resources.gist.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.gist.label');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')
                ->label(__('filament.fields.gist.title'))
                ->required()
                ->validationMessages([
                    'required' => __('filament.validation.required', [
                        'attribute' => __('filament.fields.gist.title')
                    ]),
                ]),
        ]);
    }
}
```

## 🌐 翻译指南

### 添加新的翻译键

1. **确定翻译键的位置**
   ```php
   // 通用文本 -> common.php
   'navigation.home' => '首页'
   
   // 功能特定 -> gist.php
   'actions.create' => '创建 Gist'
   
   // 后台专用 -> filament.php
   'resources.gist.label' => 'Gist'
   ```

2. **使用嵌套结构**
   ```php
   'gist' => [
       'actions' => [
           'create' => '创建',
           'edit' => '编辑',
           'delete' => '删除',
       ],
       'fields' => [
           'title' => '标题',
           'content' => '内容',
       ],
   ],
   ```

3. **保持一致性**
   - 使用统一的命名约定
   - 保持所有语言版本的键结构一致
   - 使用描述性的键名

### 翻译最佳实践

1. **避免硬编码文本**
   ```php
   // ❌ 错误
   echo "用户创建成功";
   
   // ✅ 正确
   echo __('user.messages.created');
   ```

2. **使用参数化翻译**
   ```php
   // 翻译文件
   'welcome_message' => '欢迎, :name！您有 :count 条新消息。'
   
   // 使用
   __('common.welcome_message', ['name' => $user->name, 'count' => $messageCount])
   ```

3. **处理复数形式**
   ```php
   // 翻译文件
   'items_count' => '{0} 没有项目|{1} 1 个项目|[2,*] :count 个项目'
   
   // 使用
   trans_choice('common.items_count', $count, ['count' => $count])
   ```

## 🚀 部署指南

### 环境配置

```env
# .env 文件配置
APP_LOCALE=zh
APP_FALLBACK_LOCALE=en

# 语言检测配置
LOCALE_BROWSER_DETECTION=true
LOCALE_IP_DETECTION=false
LOCALE_REMEMBER_GUEST=true
LOCALE_COOKIE_LIFETIME=365

# SEO 配置
LOCALE_GENERATE_HREFLANG=true
LOCALE_INCLUDE_IN_SITEMAP=true
```

### 发布 Filament 翻译

```bash
# 发布 Filament 翻译文件
php artisan vendor:publish --tag=filament-translations
php artisan vendor:publish --tag=filament-actions-translations
php artisan vendor:publish --tag=filament-forms-translations
php artisan vendor:publish --tag=filament-tables-translations
php artisan vendor:publish --tag=filament-notifications-translations

# 复制中文翻译
cp -r resources/lang/vendor/filament/zh_CN resources/lang/vendor/filament/zh
cp -r resources/lang/vendor/filament-actions/zh_CN resources/lang/vendor/filament-actions/zh
# ... 其他包
```

### 缓存配置

```bash
# 清除配置缓存
php artisan config:clear

# 重新缓存配置
php artisan config:cache

# 清除翻译缓存
php artisan cache:clear
```

### 生成 Sitemap

```bash
# 生成基础 sitemap
php artisan sitemap:generate

# 包含 Gist 页面
php artisan sitemap:generate --include-gists

# 包含用户页面
php artisan sitemap:generate --include-users
```

## 🛠️ 管理工具

### 翻译管理命令

```bash
# 检查翻译完整性
php artisan translation:manage check

# 显示翻译统计
php artisan translation:manage stats

# 同步翻译（添加缺失的键）
php artisan translation:manage sync

# 导出翻译为 JSON
php artisan translation:manage export --format=json --output=translations.json

# 导出翻译为 CSV
php artisan translation:manage export --format=csv --output=translations.csv
```

### 翻译完整性检查

```bash
# 基本检查
php artisan translation:check-integrity

# 自动修复问题
php artisan translation:check-integrity --fix

# 生成 JSON 报告
php artisan translation:check-integrity --report=json --output=report.json

# 严格模式（有问题时失败）
php artisan translation:check-integrity --strict
```

### 语言检测测试

```bash
# 测试语言检测功能
php artisan locale:test

# 测试特定 IP
php artisan locale:test 8.8.8.8

# 测试浏览器语言
php artisan locale:test --browser-lang="en-US,en;q=0.9"
```

## 📋 最佳实践

### 1. 翻译键命名规范

```php
// 使用点号分隔的层级结构
'module.section.item'

// 示例
'gist.actions.create'        // Gist 模块的创建操作
'auth.messages.login_failed' // 认证模块的登录失败消息
'common.navigation.home'     // 通用导航的首页
```

### 2. 翻译内容规范

- 保持翻译简洁明了
- 使用一致的术语
- 考虑上下文语境
- 避免直译，注重本地化

### 3. 性能优化

```php
// 使用翻译缓存
config(['localization.cache.enabled' => true]);

// 预加载常用翻译
$commonTranslations = __('common');
```

### 4. SEO 优化

```blade
<!-- 在布局文件中使用 SEO 组件 -->
<x-seo-meta 
    :title="__('common.seo.home_title')"
    :description="__('common.seo.site_description')"
    :keywords="__('common.seo.keywords')"
/>
```

## 🔧 故障排除

### 常见问题

1. **翻译不显示**
   ```bash
   # 检查语言文件是否存在
   ls resources/lang/zh/
   
   # 检查翻译键是否正确
   php artisan translation:manage check
   
   # 清除缓存
   php artisan cache:clear
   ```

2. **语言切换不生效**
   ```bash
   # 检查中间件是否注册
   php artisan route:list --middleware=SetLocale
   
   # 检查配置
   php artisan config:show localization
   ```

3. **Filament 后台语言问题**
   ```bash
   # 检查 Filament 翻译文件
   ls resources/lang/vendor/filament/zh/
   
   # 重新发布翻译
   php artisan vendor:publish --tag=filament-translations --force
   ```

### 调试技巧

```php
// 在代码中调试当前语言
dd(app()->getLocale());

// 检查翻译是否存在
dd(__('common.navigation.home'));

// 检查语言文件加载
dd(trans()->getLoader()->load(app()->getLocale(), 'common'));
```

### 日志检查

```bash
# 查看语言检测日志
tail -f storage/logs/laravel.log | grep "locale"

# 查看翻译相关错误
tail -f storage/logs/laravel.log | grep "translation"
```

## 📚 参考资源

- [Laravel 本地化文档](https://laravel.com/docs/localization)
- [Filament 多语言文档](https://filamentphp.com/docs/panels/configuration#locale)
- [PHP Intl 扩展](https://www.php.net/manual/en/book.intl.php)
- [Unicode CLDR](http://cldr.unicode.org/)

---

## 🤝 贡献指南

如需添加新语言或改进翻译，请：

1. Fork 项目
2. 创建新的语言文件
3. 运行翻译完整性检查
4. 提交 Pull Request

感谢您对国际化功能的贡献！🌍
