# syntax=docker/dockerfile:1

ARG PHP_VERSION=8.5
ARG RR_VERSION=2025.1.15

FROM ghcr.io/roadrunner-server/roadrunner:${RR_VERSION} AS roadrunner

FROM composer:2 AS composer

# ------------------------------------------------------------------------------
# Base: PHP + extensões + RoadRunner + Composer
# ------------------------------------------------------------------------------
FROM php:${PHP_VERSION}-cli-alpine AS base

WORKDIR /var/www/html

COPY --from=ghcr.io/mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
        bcmath \
        intl \
        opcache \
        pcntl \
        pdo_pgsql \
        redis \
        sockets \
        zip

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr

# Fora do WORKDIR para não ser sobrescrito pelo volume em desenvolvimento
COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]

EXPOSE 8000

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD wget -qO- http://127.0.0.1:8000/up > /dev/null || exit 1

# ------------------------------------------------------------------------------
# Development: código montado via volume, roda com o UID/GID do host
# ------------------------------------------------------------------------------
FROM base AS development

ARG UID=1000
ARG GID=1000

# Node + chokidar habilitam o hot reload do Octane (--watch)
ENV NODE_PATH=/usr/local/lib/node_modules

RUN apk add --no-cache nodejs npm \
    && npm install -g --prefix /usr/local chokidar \
    && mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
    && addgroup -g "${GID}" app \
    && adduser -D -u "${UID}" -G app app

USER app

CMD ["php", "artisan", "octane:start", "--server=roadrunner", "--host=0.0.0.0", "--port=8000", "--watch"]

# ------------------------------------------------------------------------------
# Production: imagem final, imutável
# ------------------------------------------------------------------------------
FROM base AS production

ENV APP_ENV=production \
    APP_DEBUG=false

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf "opcache.enable_cli=1\nopcache.validate_timestamps=0\n" > "$PHP_INI_DIR/conf.d/zz-opcache.ini"

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

COPY . .

RUN composer dump-autoload --optimize --classmap-authoritative --no-dev --no-interaction \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && touch .rr.yaml \
    && chown -R www-data:www-data storage bootstrap/cache .rr.yaml

USER www-data

CMD ["php", "artisan", "octane:start", "--server=roadrunner", "--host=0.0.0.0", "--port=8000"]
