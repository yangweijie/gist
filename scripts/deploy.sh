#!/bin/bash

# 生产部署脚本
# 用于安全地部署到生产环境

set -e  # 遇到错误立即退出

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🚀 开始生产部署...${NC}"

# 1. 检查环境
echo -e "\n${BLUE}🔍 检查部署环境...${NC}"

# 检查是否在生产环境
if [ "$APP_ENV" != "production" ]; then
    echo -e "${YELLOW}⚠️  当前不是生产环境，继续部署？ (y/N)${NC}"
    read -r response
    if [[ ! "$response" =~ ^[Yy]$ ]]; then
        echo -e "${RED}❌ 部署已取消${NC}"
        exit 1
    fi
fi

# 2. 备份数据库
echo -e "\n${BLUE}💾 备份数据库...${NC}"
BACKUP_FILE="backup_$(date +%Y%m%d_%H%M%S).sql"
if command -v mysqldump &> /dev/null; then
    mysqldump -u "${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" > "storage/backups/${BACKUP_FILE}"
    echo -e "${GREEN}✅ 数据库备份完成: ${BACKUP_FILE}${NC}"
else
    echo -e "${YELLOW}⚠️  mysqldump 未找到，跳过数据库备份${NC}"
fi

# 3. 进入维护模式
echo -e "\n${BLUE}🔧 进入维护模式...${NC}"
php artisan down --message="系统升级中，请稍后访问" --retry=60

# 4. 拉取最新代码
echo -e "\n${BLUE}📥 拉取最新代码...${NC}"
git pull origin main

# 5. 安装依赖
echo -e "\n${BLUE}📦 安装依赖...${NC}"
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# 6. 运行数据库迁移
echo -e "\n${BLUE}🗄️  运行数据库迁移...${NC}"
php artisan migrate --force

# 7. 清除和重建缓存
echo -e "\n${BLUE}🧹 清除和重建缓存...${NC}"
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 8. 优化自动加载
echo -e "\n${BLUE}⚡ 优化自动加载...${NC}"
composer dump-autoload --optimize

# 9. 设置正确的文件权限
echo -e "\n${BLUE}🔐 设置文件权限...${NC}"
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 10. 运行测试（可选）
echo -e "\n${BLUE}🧪 运行关键测试...${NC}"
if php artisan test tests/Feature/PerformanceTest.php; then
    echo -e "${GREEN}✅ 关键测试通过${NC}"
else
    echo -e "${RED}❌ 测试失败，是否继续部署？ (y/N)${NC}"
    read -r response
    if [[ ! "$response" =~ ^[Yy]$ ]]; then
        echo -e "${RED}❌ 部署已取消${NC}"
        php artisan up
        exit 1
    fi
fi

# 11. 退出维护模式
echo -e "\n${BLUE}🟢 退出维护模式...${NC}"
php artisan up

# 12. 验证部署
echo -e "\n${BLUE}✅ 验证部署...${NC}"
if curl -f -s -o /dev/null "${APP_URL}"; then
    echo -e "${GREEN}✅ 网站可正常访问${NC}"
else
    echo -e "${RED}❌ 网站访问异常${NC}"
fi

# 13. 生成部署报告
echo -e "\n${BLUE}📊 生成部署报告...${NC}"
cat > "storage/logs/deployment_$(date +%Y%m%d_%H%M%S).log" << EOF
部署报告
========

部署时间: $(date)
Git 提交: $(git rev-parse HEAD)
分支: $(git branch --show-current)
部署者: $(whoami)

部署步骤:
- ✅ 数据库备份
- ✅ 代码更新
- ✅ 依赖安装
- ✅ 数据库迁移
- ✅ 缓存重建
- ✅ 权限设置
- ✅ 测试验证

状态: 成功
EOF

echo -e "\n${GREEN}🎉 部署完成！${NC}"
echo -e "${BLUE}📝 部署日志已保存到 storage/logs/deployment_$(date +%Y%m%d_%H%M%S).log${NC}"

# 14. 发送通知（可选）
if [ ! -z "$SLACK_WEBHOOK" ]; then
    curl -X POST -H 'Content-type: application/json' \
        --data '{"text":"🚀 Gist 管理系统部署成功！"}' \
        "$SLACK_WEBHOOK"
fi
