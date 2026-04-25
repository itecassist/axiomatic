# Builder stage - handles both Composer and npm
FROM php:8.3-alpine AS builder
WORKDIR /app

# Install system dependencies
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    nodejs npm

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    bcmath \
    intl \
    zip \
    gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy dependency files
COPY package*.json composer.json composer.lock ./

# Install dependencies (skip scripts to avoid Laravel bootstrap during build)
RUN npm ci && composer install --no-dev --optimize-autoloader --no-scripts

# Copy application code
COPY . .

# Build assets
RUN npm run build

# PHP FPM stage
FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    mysql-client

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    bcmath \
    intl \
    zip \
    gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy from builder
COPY --from=builder /app .
COPY --chown=www-data:www-data --from=builder /app/vendor ./vendor
COPY --chown=www-data:www-data --from=builder /app/public ./public

# Ensure storage and cache directories exist with proper permissions
RUN mkdir -p /app/storage /app/bootstrap/cache && \
    chown -R www-data:www-data /app/storage /app/bootstrap/cache && \
    chmod -R 755 /app/storage /app/bootstrap/cache

# Expose port
EXPOSE 9000

CMD ["php-fpm"]
