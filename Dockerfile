# Stage 1: Build the assets (Vite/Tailwind)
FROM node:20-alpine AS assets-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Production PHP/Nginx environment
FROM richarvey/nginx-php-fpm:3.1.6

# Configure Environment
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr
ENV COMPOSER_ALLOW_SUPERUSER 1

# Railway uses port 8080 by default
ENV LISTEN_PORT 8080
EXPOSE 8080

# Set the working directory
WORKDIR /var/www/html

# Copy all files
COPY . .

# Copy compiled assets from the builder stage
COPY --from=assets-builder /app/public/build ./public/build

# Install production dependencies
RUN composer install --no-dev --optimize-autoloader

# Fix storage & bootstrap/cache permissions
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R nginx:nginx storage bootstrap/cache
