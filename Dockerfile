# Stage 1: Install Composer dependencies
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# Stage 2: Build frontend assets
FROM node:22-bookworm-slim AS build

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

# Flux CSS and framework view paths are referenced from app.css
COPY --from=vendor /app/vendor ./vendor

RUN npm run build

# Stage 3: Application runtime
FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libxml2-dev \
    libonig-dev \
    git \
    curl \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip xml \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Application code
COPY . .

# Composer dependencies
COPY --from=vendor /app/vendor ./vendor

# Built frontend assets
COPY --from=build /app/public/build ./public/build

# Pass environment variables through to PHP workers
COPY docker/php-fpm.d/zz-clear-env.conf /usr/local/etc/php-fpm.d/zz-clear-env.conf

# Fix permissions for Laravel storage/cache
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
