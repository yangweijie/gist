# 翻译贡献指南

## 🌍 欢迎参与翻译

感谢您对本项目国际化的贡献！本指南将帮助您了解如何参与翻译工作。

## 📋 翻译流程

### 1. 准备工作

```bash
# 1. Fork 项目到您的 GitHub 账户
# 2. 克隆项目到本地
git clone https://github.com/your-username/gist-management.git
cd gist-management

# 3. 安装依赖
composer install
npm install

# 4. 检查当前翻译状态
php artisan translation:manage stats
```

### 2. 添加新语言

```bash
# 创建新语言目录（以日语为例）
mkdir -p resources/lang/ja

# 复制中文文件作为模板
cp resources/lang/zh/*.php resources/lang/ja/

# 更新配置文件
```

在 `config/localization.php` 中添加新语言：

```php
'supported_locales' => [
    'zh' => [
        'code' => 'zh',
        'name' => 'Chinese',
        'native' => '中文',
        'flag' => '🇨🇳',
        'direction' => 'ltr',
        'enabled' => true,
    ],
    'en' => [
        'code' => 'en',
        'name' => 'English',
        'native' => 'English',
        'flag' => '🇺🇸',
        'direction' => 'ltr',
        'enabled' => true,
    ],
    'ja' => [
        'code' => 'ja',
        'name' => 'Japanese',
        'native' => '日本語',
        'flag' => '🇯🇵',
        'direction' => 'ltr',
        'enabled' => true,
    ],
],
```

### 3. 翻译文件

#### 翻译优先级

1. **高优先级** - 用户界面核心文本
   - `common.php` - 导航、按钮、通用消息
   - `auth.php` - 登录、注册相关

2. **中优先级** - 功能模块文本
   - `gist.php` - Gist 管理功能
   - `tag.php` - 标签管理功能

3. **低优先级** - 高级功能文本
   - `php-runner.php` - PHP 在线运行器
   - `filament.php` - 后台管理界面

#### 翻译示例

**原文 (zh/common.php):**
```php
'navigation' => [
    'home' => '首页',
    'gists' => 'Gist',
    'tags' => '标签',
    'php_runner' => 'PHP 运行器',
    'login' => '登录',
    'logout' => '退出',
],
```

**日语翻译 (ja/common.php):**
```php
'navigation' => [
    'home' => 'ホーム',
    'gists' => 'Gist',
    'tags' => 'タグ',
    'php_runner' => 'PHP実行環境',
    'login' => 'ログイン',
    'logout' => 'ログアウト',
],
```

### 4. 翻译规范

#### 命名约定
- 保持键名不变，只翻译值
- 使用一致的术语
- 考虑上下文语境

#### 格式规范
```php
// ✅ 正确格式
'user' => [
    'profile' => [
        'title' => 'ユーザープロフィール',
        'edit' => '編集',
    ],
],

// ❌ 错误格式
'user.profile.title' => 'ユーザープロフィール',  // 不要改变结构
```

#### 参数处理
```php
// 保持参数名不变
'welcome_message' => 'こんにちは、:name さん！',
'items_count' => '{0} アイテムなし|{1} 1個のアイテム|[2,*] :count個のアイテム',
```

### 5. 质量检查

```bash
# 检查翻译完整性
php artisan translation:check-integrity

# 检查特定语言
php artisan translation:manage check --locale=ja

# 自动修复缺失的键
php artisan translation:check-integrity --fix
```

### 6. 测试翻译

```bash
# 切换到新语言测试
php artisan locale:test --browser-lang="ja-JP,ja;q=0.9"

# 在浏览器中测试
# 访问 /?lang=ja 查看效果
```

## 📝 翻译文件详解

### common.php - 通用文本
包含导航、按钮、消息等通用界面元素。

**重要部分：**
- `navigation` - 导航菜单
- `actions` - 操作按钮
- `messages` - 系统消息
- `language` - 语言相关文本

### auth.php - 认证相关
包含登录、注册、密码重置等认证功能文本。

**重要部分：**
- `titles` - 页面标题
- `fields` - 表单字段
- `buttons` - 操作按钮
- `messages` - 提示消息

### gist.php - Gist 功能
包含 Gist 创建、编辑、管理等功能文本。

**重要部分：**
- `titles` - 页面标题
- `fields` - 表单字段
- `actions` - 操作按钮
- `status` - 状态文本

### filament.php - 后台管理
包含 Filament 后台管理界面的所有文本。

**重要部分：**
- `navigation_groups` - 导航分组
- `resources` - 资源标签
- `fields` - 表单字段
- `actions` - 操作按钮

## 🎯 翻译技巧

### 1. 术语一致性

建立术语表，确保关键词翻译一致：

| 中文 | 英文 | 日语 | 法语 |
|------|------|------|------|
| Gist | Gist | Gist | Gist |
| 代码片段 | Code Snippet | コードスニペット | Extrait de code |
| 标签 | Tag | タグ | Étiquette |
| 用户 | User | ユーザー | Utilisateur |

### 2. 上下文考虑

```php
// 考虑不同上下文的翻译
'delete' => '删除',           // 按钮文本
'delete_confirm' => '确认删除', // 确认对话框
'delete_success' => '删除成功', // 成功消息
```

### 3. 文化适应

- 考虑目标语言的文化背景
- 使用当地用户熟悉的表达方式
- 注意日期、时间、数字格式

### 4. 长度考虑

- 某些语言翻译后可能更长
- 确保界面布局能容纳翻译文本
- 必要时使用缩写或简化表达

## 🔍 质量保证

### 自动检查

```bash
# 运行所有检查
php artisan translation:check-integrity --strict

# 生成详细报告
php artisan translation:check-integrity --report=json --output=translation-report.json
```

### 手动检查清单

- [ ] 所有键都有对应翻译
- [ ] 翻译文本符合目标语言习惯
- [ ] 参数格式正确
- [ ] 复数形式处理正确
- [ ] 术语使用一致
- [ ] 界面显示正常

### 同行评审

1. 提交 Pull Request
2. 请其他母语使用者评审
3. 根据反馈修改
4. 最终合并

## 📊 翻译进度跟踪

### 查看统计信息

```bash
# 查看整体统计
php artisan translation:manage stats

# 查看特定语言
php artisan translation:manage check --locale=ja
```

### 进度报告

| 文件 | 中文 | 英文 | 日语 | 法语 |
|------|------|------|------|------|
| common.php | ✅ 100% | ✅ 100% | 🔄 80% | ❌ 0% |
| auth.php | ✅ 100% | ✅ 100% | 🔄 90% | ❌ 0% |
| gist.php | ✅ 100% | ✅ 100% | 🔄 70% | ❌ 0% |

## 🚀 提交贡献

### 1. 提交前检查

```bash
# 运行完整性检查
php artisan translation:check-integrity --strict

# 确保没有语法错误
php artisan config:cache
```

### 2. 提交信息格式

```
feat(i18n): add Japanese translation for auth module

- Add complete Japanese translation for auth.php
- Update localization config to include Japanese
- Test language switching functionality

Closes #123
```

### 3. Pull Request 模板

```markdown
## 翻译贡献

### 添加的语言
- [ ] 日语 (ja)

### 翻译的文件
- [x] common.php (100%)
- [x] auth.php (100%)
- [ ] gist.php (进行中)

### 检查清单
- [x] 运行翻译完整性检查
- [x] 测试语言切换功能
- [x] 检查界面显示效果
- [x] 术语使用一致

### 截图
[添加界面截图展示翻译效果]
```

## 🎉 感谢贡献者

我们感谢所有为项目国际化做出贡献的译者：

- 🇨🇳 中文：项目团队
- 🇺🇸 英文：项目团队
- 🇯🇵 日语：[待添加]
- 🇫🇷 法语：[待添加]
- 🇩🇪 德语：[待添加]

## 📞 获取帮助

如果您在翻译过程中遇到问题：

1. 查看 [国际化文档](INTERNATIONALIZATION.md)
2. 运行 `php artisan translation:manage check`
3. 在 GitHub 上创建 Issue
4. 联系项目维护者

---

**让我们一起让这个项目服务全球用户！** 🌍✨
