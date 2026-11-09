# ==========================================
# 1. Frontend Asset Build Stage (Node.js)
# ==========================================
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ==========================================
# 2. Composer Dependencies Stage
# ==========================================
FROM composer:2 AS composer-builder
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# ==========================================
# 3. Final Production Runtime Stage
# ==========================================
FROM php:8.3-cli-alpine

LABEL maintainer="CafeSync Core Team"
LABEL description="Specialty Coffee Multi-Role POS & ERP Platform"

# Install system dependencies & PHP extensions
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    sqlite-dev \
    sqlite \
    oniguruma-dev \
    icu-dev \
    git \
    bash \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        pdo_sqlite \
        mbstring \
        bcmath \
        zip \
        gd \
        intl \
        opcache

WORKDIR /var/www/html

# Copy project files
COPY . .

# Copy production vendors and built assets
COPY --from=composer-builder /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

# Set permissions
RUN chmod +x docker/entrypoint.sh \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Default environment variables
ENV APP_ENV=production \
    APP_DEBUG=false \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/var/www/html/database/database.sqlite \
    SESSION_DRIVER=database \
    CACHE_STORE=database \
    QUEUE_CONNECTION=database \
    PORT=8080

EXPOSE 8080

ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
