FROM serversideup/php:8.4-frankenphp-alpine AS base

ARG CI_PROJECT_URL
ARG CI_COMMIT_SHA
ARG CI_COMMIT_TAG

LABEL org.opencontainers.image.source="$CI_PROJECT_URL"
LABEL org.opencontainers.image.revision="$CI_COMMIT_SHA"
LABEL org.opencontainers.image.version="$CI_COMMIT_TAG"

USER root

ENV LOG_CHANNEL=stderr \
    PHP_OPCACHE_ENABLE=1 \
    SSL_MODE=on \
    COMPOSER_ALLOW_SUPERUSER=false

#
# install-php-extensions already pulls the runtime libraries each extension
# needs and removes its build dependencies, so no apk packages are required.
#
RUN install-php-extensions \
    intl \
    exif \
    ldap \
    bcmath \
    gd

# Utilizado para gerar PDF de relatórios
RUN apk add --no-cache \
    poppler-utils

USER www-data

WORKDIR /var/www/html

# ------------------------------------------------------------
# PHP dependencies
# ------------------------------------------------------------

FROM base AS build

COPY --chown=www-data:www-data composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

COPY --chown=www-data:www-data . .

# Don't ship local/dev-generated Laravel discovery caches.
RUN rm -rf bootstrap/cache/* storage/framework/views/*

RUN composer dump-autoload \
    --optimize \
    --classmap-authoritative \
    --no-scripts \
    && php artisan package:discover --ansi

# ------------------------------------------------------------
# Frontend
# ------------------------------------------------------------

FROM node:24-alpine AS assets

ENV HUSKY=0

WORKDIR /var/www/html

COPY package.json package-lock.json ./

RUN npm ci

COPY --from=build /var/www/html /var/www/html

RUN npm run build

# ------------------------------------------------------------
# Production
# ------------------------------------------------------------

FROM base

#
# Config, route, view and event caches depend on runtime values (APP_KEY,
# APP_URL, ...), so they are generated on container start by the image's
# Laravel automations instead of at build time. Migrations stay a deploy step.
#
ENV AUTORUN_ENABLED=true \
    AUTORUN_LARAVEL_MIGRATION=false \
    AUTORUN_LARAVEL_STORAGE_LINK=false

COPY --from=build --chown=www-data:www-data /var/www/html /var/www/html
COPY --from=assets /var/www/html/public/build /var/www/html/public/build
COPY --chown=root:root Caddyfile /etc/frankenphp/Caddyfile
COPY --chown=root:root docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

USER root

RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public \
    storage/app/private/livewire-tmp \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && php artisan storage:link

USER www-data

HEALTHCHECK \
    --interval=10s \
    --timeout=3s \
    --start-period=30s \
    --retries=3 \
    CMD wget --no-verbose --tries=1 --spider http://localhost:8080/up || exit 1
