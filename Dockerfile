FROM serversideup/php:8.4-frankenphp-alpine AS base

ARG CI_PROJECT_URL
ARG CI_COMMIT_SHA
ARG CI_COMMIT_TAG

LABEL org.opencontainers.image.source="$CI_PROJECT_URL"
LABEL org.opencontainers.image.revision="$CI_COMMIT_SHA"
LABEL org.opencontainers.image.version="$CI_COMMIT_TAG"

USER root

ENV LOG_CHANNEL=stderr \
    SSL_MODE=on \
    PHP_OPCACHE_ENABLE=1 \
    PHP_OPCACHE_JIT=on \
    COMPOSER_ALLOW_SUPERUSER=false

RUN apk add --no-cache \
    bash curl ca-certificates \
    libpng-dev libzip-dev libxml2-dev \
    poppler-utils \
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

#
# Ensure we don't ship (or load) locally-generated discovery caches that may
# reference dev-only packages (e.g. laravel/boost) which are not installed in
# this image (composer install --no-dev).
#
RUN rm -rf bootstrap/cache/* storage/framework/views/*

RUN composer dump-autoload --optimize --classmap-authoritative --no-scripts && \
    php artisan package:discover --ansi

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

RUN CACHE_STORE=array php artisan optimize:clear && \
    php artisan storage:link && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan event:cache

USER www-data

HEALTHCHECK CMD wget --no-verbose --tries=1 --spider \
  http://localhost:8080/up || exit 1
