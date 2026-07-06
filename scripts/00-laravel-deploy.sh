#!/bin/sh

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Create storage symlink
echo "Creating storage symlink..."
php artisan storage:link --force

# Optimize config and route loading
echo "Caching configuration, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations in production
echo "Running database migrations..."
php artisan migrate --force
