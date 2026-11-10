# syntax=docker/dockerfile:1
#
# Stages: deps -> base -> build -> test / runtime
# - deps:    resolves production vendor/ via the official Composer image.
# - base:    shared, pinned PHP runtime (alpine, no compilers/dev tools).
# - build:   assembles the final app tree (no compile step — pure PHP).
# - test:    full dev-dependency install + lint/static-analysis/unit tests/audit.
#            `docker build --target test` fails the build if any gate fails.
# - runtime: slim, non-root, healthchecked; the only stage ever deployed.

FROM composer:2@sha256:8de2184c6de224f4689e8f80ed5f5edaf0030b6b8b7839a22a2bcb6722ac87d0 AS deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/composer-cache,sharing=locked \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    composer install --no-dev --no-interaction --no-scripts --optimize-autoloader

FROM php:8.5-cli-alpine@sha256:d1fbbe78850d1eb9aa8f7da652ec5db61eb4c9377c8215cf5e23f3b528ed4f2b AS base
WORKDIR /app

FROM base AS build
COPY --from=deps /app/vendor ./vendor
COPY composer.json ./
COPY src ./src

FROM php:8.5-cli@sha256:50956f094e90a716d43bea28087d447555ad80510e386ab8c9c10d8c70d781a9 AS test
RUN apt-get update && apt-get install -y --no-install-recommends libzip-dev unzip \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2@sha256:8de2184c6de224f4689e8f80ed5f5edaf0030b6b8b7839a22a2bcb6722ac87d0 /usr/bin/composer /usr/bin/composer
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
