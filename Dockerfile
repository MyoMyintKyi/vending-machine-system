FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-scripts \
    --optimize-autoloader

FROM php:8.2-cli-alpine

WORKDIR /opt/render/project/src

RUN docker-php-ext-install pdo_mysql

COPY --from=vendor /app/vendor ./vendor
COPY . ./

ENV APP_ENV=production
EXPOSE 10000

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000} -t public public/index.php"]