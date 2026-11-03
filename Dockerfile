# syntax=docker/dockerfile:1

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --optimize-autoloader

FROM php:8.5-cli-alpine AS runtime
WORKDIR /app

RUN addgroup -S app && adduser -S app -G app

COPY --from=vendor /app/vendor ./vendor
COPY composer.json ./
COPY src ./src

USER app

EXPOSE 3000
STOPSIGNAL SIGTERM

# Document root is src/ so src/index.php is served at "/"; the working
# directory stays /app so its require 'vendor/autoload.php' resolves.
CMD ["php", "-S", "0.0.0.0:3000", "-t", "src"]

FROM php:8.5-cli AS test
RUN apt-get update && apt-get install -y --no-install-recommends libzip-dev unzip \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts
COPY . .
CMD ["sh", "-c", "composer lint && composer analyse && composer test && composer audit"]
