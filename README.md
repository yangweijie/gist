# 🚀 GitHub Gist Manager

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel" alt="Laravel 11">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Filament-3.2-F59E0B?style=for-the-badge&logo=filament" alt="Filament 3.2">
  <img src="https://img.shields.io/badge/HTMX-1.9-3366CC?style=for-the-badge&logo=htmx" alt="HTMX">
  <img src="https://img.shields.io/badge/Tests-147%20Passed-00D26A?style=for-the-badge&logo=github-actions" alt="Tests">
</p>

<p align="center">
  <strong>一个功能完整的 GitHub Gist 管理网站，支持在线 PHP 代码执行、多语言国际化、社交功能和企业级管理后台。</strong>
</p>

## ✨ 项目特色

这是一个基于 **Laravel 11** 构建的现代化 GitHub Gist 管理平台，集成了多项前沿技术：

### 🎯 **核心功能**
- **🔐 GitHub OAuth 认证** - 安全的用户身份验证
- **📝 Gist 完整管理** - 创建、编辑、删除、分享 Gists
- **⚡ 在线 PHP 执行** - 基于 PHP WASM 的安全代码运行环境
- **🎨 代码高亮** - 支持多种编程语言的语法高亮
- **🌐 多语言支持** - 中文/英文国际化
- **👥 社交功能** - 点赞、收藏、评论、关注系统

### 🛠️ **技术栈**
- **后端**: Laravel 11 + PHP 8.2+
- **前端**: HTMX + Alpine.js + Tailwind CSS
- **管理后台**: Filament 3.2
- **数据库**: MySQL/PostgreSQL
- **缓存**: Redis
- **队列**: Redis/Database
- **测试**: Pest + Laravel Dusk

## 🚀 快速开始

### 📋 环境要求

- **PHP**: 8.2 或更高版本
- **Composer**: 2.0+
- **Node.js**: 18.0+
- **数据库**: MySQL 8.0+ / PostgreSQL 13+
- **Redis**: 6.0+ (可选，用于缓存和队列)

### ⚡ 安装步骤

1. **克隆项目**
   ```bash
   git clone <repository-url>
   cd gist-manager
   ```

2. **安装依赖**
   ```bash
   composer install
   npm install
   ```

3. **环境配置**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **配置数据库**
   ```bash
   # 编辑 .env 文件，设置数据库连接
   php artisan migrate --seed
   ```

5. **构建前端资源**
   ```bash
   npm run build
   ```

6. **启动开发服务器**
   ```bash
   php artisan serve
   ```

## 🔧 配置说明

### GitHub OAuth 设置

1. **创建 GitHub OAuth App**
   - 访问 [GitHub Developer Settings](https://github.com/settings/developers)
   - 创建新的 OAuth App
   - 设置回调 URL: `http://your-domain.com/auth/github/callback`

2. **配置环境变量**
   ```env
   GITHUB_CLIENT_ID=your_github_client_id
   GITHUB_CLIENT_SECRET=your_github_client_secret
   GITHUB_REDIRECT_URI=http://your-domain.com/auth/github/callback
   ```

### 多语言配置

项目支持中文和英文，可以通过以下方式配置：

```env
# 默认语言
APP_LOCALE=zh
APP_FALLBACK_LOCALE=en

# 支持的语言
LOCALIZATION_SUPPORTED_LOCALES=zh,en
```

### PHP WASM 配置

在线 PHP 执行功能基于 PHP WASM，需要配置：

```env
# PHP WASM 设置
PHP_WASM_ENABLED=true
PHP_WASM_MEMORY_LIMIT=128M
PHP_WASM_TIME_LIMIT=30
```

## � 项目结构

```
gist-manager/
├── app/
│   ├── Filament/           # Filament 管理后台
│   ├── Http/
│   │   ├── Controllers/    # 控制器
│   │   └── Middleware/     # 中间件
│   ├── Models/             # Eloquent 模型
│   ├── Services/           # 业务逻辑服务
│   └── Livewire/          # Livewire 组件
├── resources/
│   ├── views/             # Blade 模板
│   ├── js/                # JavaScript 文件
│   └── css/               # 样式文件
├── tests/
│   ├── Feature/           # 功能测试
│   ├── Unit/              # 单元测试
│   └── Browser/           # 浏览器测试
├── config/                # 配置文件
├── database/
│   ├── migrations/        # 数据库迁移
│   └── seeders/           # 数据填充
└── scripts/               # 部署和维护脚本
```

## �📚 功能模块

### 🎯 **用户认证与管理**
- GitHub OAuth 登录/注册
- 用户资料管理
- 权限控制系统

### 📝 **Gist 管理**
- 创建/编辑/删除 Gists
- 多文件 Gist 支持
- 版本历史管理
- 公开/私有设置

### ⚡ **在线代码执行**
- PHP WASM 安全执行环境
- 实时代码运行结果
- 错误处理和输出捕获
- 执行时间和内存限制

### 🎨 **代码高亮与编辑**
- 多语言语法高亮
- 代码编辑器集成
- 主题切换支持

### 👥 **社交功能**
- 点赞/收藏系统
- 评论功能
- 用户关注
- 活动时间线

### 🌐 **国际化支持**
- 中文/英文界面
- 智能语言检测
- 用户偏好记忆

### 🛡️ **管理后台 (Filament)**
- 用户管理
- Gist 管理
- 系统监控
- 数据统计

## 🎮 使用示例

### 创建和运行 PHP Gist

1. **登录系统**
   ```
   访问首页 → 点击"GitHub 登录" → 授权应用
   ```

2. **创建 Gist**
   ```php
   <?php
   // 示例 PHP 代码
   echo "Hello, Gist Manager!";

   $numbers = [1, 2, 3, 4, 5];
   $sum = array_sum($numbers);
   echo "\nSum: " . $sum;
   ```

3. **在线执行**
   - 点击"运行代码"按钮
   - 查看实时输出结果
   - 支持错误处理和调试

### API 使用

项目提供 RESTful API 接口：

```bash
# 获取用户的 Gists
GET /api/gists?user_id=123

# 创建新 Gist
POST /api/gists
Content-Type: application/json
{
  "title": "My Gist",
  "description": "A sample gist",
  "files": [
    {
      "filename": "example.php",
      "content": "<?php echo 'Hello World';"
    }
  ],
  "public": true
}

# 执行 PHP 代码
POST /api/php/execute
Content-Type: application/json
{
  "code": "<?php echo 'Hello from API';"
}
```

## 🧪 测试

项目包含完整的测试套件：

```bash
# 运行所有测试
php artisan test

# 运行特定测试
php artisan test --filter=GistTest

# 运行并行测试
php artisan test --parallel

# 生成测试覆盖率报告
php artisan test --coverage
```

### 测试统计
- **单元测试**: 85+ 个
- **功能测试**: 60+ 个
- **浏览器测试**: 12+ 个
- **总覆盖率**: 90%+

## 🚀 部署

### 生产环境部署

1. **服务器要求**
   - PHP 8.2+ with extensions: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
   - Nginx/Apache
   - MySQL/PostgreSQL
   - Redis (推荐)

2. **部署脚本**
   ```bash
   # 使用提供的部署脚本
   ./scripts/deploy.sh

   # 或手动部署
   composer install --optimize-autoloader --no-dev
   npm run build
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **性能优化**
   ```bash
   # 启用 OPcache
   # 配置 Redis 缓存
   # 设置队列处理器
   php artisan queue:work --daemon
   ```

## ❓ 常见问题

### Q: GitHub OAuth 登录失败？
**A**: 检查以下配置：
- GitHub OAuth App 的回调 URL 是否正确
- `.env` 文件中的 `GITHUB_CLIENT_ID` 和 `GITHUB_CLIENT_SECRET` 是否正确
- 确保应用已在 GitHub 中激活

### Q: PHP 代码执行失败？
**A**: 可能的原因：
- 检查 PHP WASM 是否正确加载
- 确认代码语法正确
- 检查是否超出内存或时间限制

### Q: 多语言切换不生效？
**A**: 解决方案：
- 清除浏览器缓存
- 运行 `php artisan config:cache`
- 检查语言文件是否存在

### Q: 测试失败？
**A**: 常见解决方法：
- 运行 `php artisan migrate:fresh --seed` 重置测试数据库
- 确保 Redis 服务正在运行
- 检查 `.env.testing` 配置

## 🔧 故障排除

### 性能问题
```bash
# 清除所有缓存
php artisan optimize:clear

# 重新生成优化缓存
php artisan optimize

# 检查队列状态
php artisan queue:monitor
```

### 数据库问题
```bash
# 重置数据库
php artisan migrate:fresh --seed

# 检查数据库连接
php artisan tinker
>>> DB::connection()->getPdo();
```

### 前端资源问题
```bash
# 重新构建前端资源
npm run build

# 开发模式热重载
npm run dev
```

## 📊 性能监控

项目内置健康检查和性能监控：

- **健康检查**: `/health`
- **系统状态**: `/health/status`
- **性能指标**: 仪表板响应时间 < 2s，API 响应时间 < 1s

## 🤝 贡献指南

欢迎贡献代码！请遵循以下步骤：

1. Fork 项目
2. 创建功能分支 (`git checkout -b feature/amazing-feature`)
3. 提交更改 (`git commit -m 'Add amazing feature'`)
4. 推送到分支 (`git push origin feature/amazing-feature`)
5. 创建 Pull Request

### 代码规范
- 遵循 PSR-12 编码标准
- 编写测试用例
- 更新相关文档

## � 更新日志

### v1.2.0 (最新)
- ✅ **语言切换器UI优化** - 修复覆层不关闭和loading状态问题
- ✅ **性能监控增强** - 新增健康检查和性能指标
- ✅ **测试覆盖率提升** - 达到 90%+ 测试覆盖率
- ✅ **错误处理改进** - 更好的异常处理和用户反馈

### v1.1.0
- ✅ **PHP WASM 集成** - 在线 PHP 代码执行功能
- ✅ **多语言支持** - 中文/英文国际化
- ✅ **社交功能** - 点赞、收藏、评论系统
- ✅ **管理后台** - Filament 3.2 集成

### v1.0.0
- ✅ **基础功能** - GitHub OAuth、Gist 管理
- ✅ **代码高亮** - 多语言语法支持
- ✅ **响应式设计** - 移动端适配
- ✅ **基础测试** - 核心功能测试覆盖

## 🗺️ 路线图

### 即将推出
- 🔄 **实时协作** - 多人同时编辑 Gist
- 🔄 **代码片段模板** - 常用代码模板库
- 🔄 **API 文档生成** - 自动生成 API 文档
- 🔄 **Docker 支持** - 容器化部署方案

### 计划中
- 📋 **更多语言支持** - JavaScript、Python 在线执行
- 📋 **插件系统** - 第三方插件支持
- 📋 **移动端 App** - 原生移动应用
- 📋 **企业版功能** - 团队协作、权限管理

## �📄 许可证

本项目基于 [MIT License](LICENSE) 开源协议。

## 🙏 致谢

感谢以下开源项目：
- [Laravel](https://laravel.com) - 优雅的 PHP 框架
- [Filament](https://filamentphp.com) - 现代化管理面板
- [HTMX](https://htmx.org) - 高性能前端交互
- [PHP WASM](https://github.com/php/web-php) - 浏览器中的 PHP 执行

---

<p align="center">
  <strong>🌟 如果这个项目对您有帮助，请给个 Star！</strong>
</p>
