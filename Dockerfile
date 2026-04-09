FROM php:8.3-fpm-alpine

# 安装 PDO MySQL 扩展（解决 could not find driver）
RUN apk add --no-cache $PHPIZE_DEPS \
    && docker-php-ext-install pdo pdo_mysql \
    && apk del $PHPIZE_DEPS

# 复制你的代码
COPY . /var/www/html
WORKDIR /var/www/html

EXPOSE 8080

# 使用 PHP 内置服务器（Railway 推荐方式）
CMD ["php", "-S", "0.0.0.0:8080", "-t", "/var/www/html"]
