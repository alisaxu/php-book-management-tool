# 使用 PHP-FPM 8.0 镜像作为基础
FROM php:8.0-fpm

# 安装必要的 PHP 扩展
RUN docker-php-ext-install pdo pdo_mysql

# 复制应用的配置文件和脚本
COPY ./src /var/www/html

# 设置工作目录
WORKDIR /var/www/html

# 修改目录权限（可选，如果需要）
#RUN chown -R www-data:www-data /var/www/html
