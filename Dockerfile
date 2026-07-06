# Stage 1: Build the assets (Vite/Tailwind)
FROM node:20-alpine AS assets-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: PHP 8.3 + Nginx via serversideup (designed for Laravel)
FROM serversideup/php:8.3-fpm-nginx

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Set working directory
WORKDIR /var/www/html

# Switch to root for installation
USER root

# Copy application files with correct ownership
COPY --chown=www-data:www-data . .

# Copy compiled assets from builder stage
COPY --from=assets-builder --chown=www-data:www-data /app/public/build ./public/build

# Install PHP production dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Fix storage & cache permissions
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Copy deploy script to entrypoint directory (serversideup convention)
COPY scripts/00-laravel-deploy.sh /etc/entrypoint.d/00-laravel-deploy.sh
RUN chmod +x /etc/entrypoint.d/00-laravel-deploy.sh

EXPOSE 8080
