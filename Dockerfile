FROM serversideup/php:8.4-frankenphp-alpine AS base

USER root

ENV LOG_CHANNEL=stderr \
    SSL_MODE=on \
    PHP_OPCACHE_ENABLE=1 \
    PHP_OPCACHE_JIT=on \
    COMPOSER_ALLOW_SUPERUSER=false

RUN apk add --no-cache \
    bash curl ca-certificates \
    libpng-dev libzip-dev libxml2-dev \
    zip unzip

RUN install-php-extensions intl exif ldap bcmath gd

USER www-data

WORKDIR /var/www/html

FROM base AS build

USER root
COPY --chown=www-data:www-data composer.* ./
USER www-data

RUN composer install \
    --no-dev --no-interaction --prefer-dist \
    --optimize-autoloader --no-scripts

COPY --chown=www-data:www-data . .

RUN composer dump-autoload --optimize --classmap-authoritative

FROM node:20-alpine AS assets

WORKDIR /var/www/html

COPY --from=build /var/www/html /var/www/html

RUN npm ci

RUN rm -rf public/build && npm cache clear --force

RUN npm run build

FROM base

COPY --from=build --chown=www-data:www-data /var/www/html /var/www/html
COPY --from=assets /var/www/html/public/build /var/www/html/public/build
COPY --chown=root:root Caddyfile /etc/frankenphp/Caddyfile
COPY --chown=root:root docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

USER root

RUN mkdir -p storage/framework/{cache,sessions,views} \
    bootstrap/cache \
    storage/app/{public,private/livewire-tmp} && \
    chown -R www-data:www-data storage bootstrap/cache

RUN php artisan filament:assets && \
    php artisan storage:link && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan event:cache && \
    php artisan filament:optimize

USER www-data

HEALTHCHECK CMD wget --no-verbose --tries=1 --spider \
  http://localhost:8080/up || exit 1
