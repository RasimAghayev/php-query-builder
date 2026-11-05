# syntax=docker/dockerfile:1
#
# Stages: deps -> base -> build -> test / runtime
# - deps:    resolves production vendor/ via the official Composer image.
# - base:    shared, pinned PHP runtime (alpine, no compilers/dev tools).
# - build:   assembles the final app tree (no compile step — pure PHP).
# - test:    full dev-dependency install + lint/static-analysis/unit tests/audit.
#            `docker build --target test` fails the build if any gate fails.
# - runtime: slim, non-root, healthchecked; the only stage ever deployed.

FROM composer:2@sha256:5190d9b445add2fa26090108273cb1ef1e1af98175201c737b85b3c4f90fbf7c AS deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/composer-cache,sharing=locked \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    composer install --no-dev --no-interaction --no-scripts --optimize-autoloader

FROM php:8.5-cli-alpine@sha256:e899a75c775804e5466dbd9c96b31f8b6c6ae2ddb053a8466223a0c32992ff8e AS base
WORKDIR /app

FROM base AS build
COPY --from=deps /app/vendor ./vendor
COPY composer.json ./
COPY src ./src

FROM php:8.5-cli@sha256:9236fd373d3bcda0659cf94838a744bd79791419292958963f8c6b34a0071554 AS test
RUN apt-get update && apt-get install -y --no-install-recommends libzip-dev unzip \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2@sha256:5190d9b445add2fa26090108273cb1ef1e1af98175201c737b85b3c4f90fbf7c /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/composer-cache,sharing=locked \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    composer install --no-interaction --no-scripts
COPY . .
CMD ["sh", "-c", "composer lint && composer analyse && composer test && composer audit"]

FROM base AS runtime
RUN addgroup -S app && adduser -S app -G app

COPY --from=build /app/vendor ./vendor
COPY --from=build /app/composer.json ./
COPY --from=build /app/src ./src

USER app

EXPOSE 3000
STOPSIGNAL SIGTERM

HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD wget -q -O- http://127.0.0.1:3000/ >/dev/null 2>&1 || exit 1

# Document root is src/ so src/index.php is served at "/"; the working
# directory stays /app so its require 'vendor/autoload.php' resolves.
CMD ["php", "-S", "0.0.0.0:3000", "-t", "src"]
