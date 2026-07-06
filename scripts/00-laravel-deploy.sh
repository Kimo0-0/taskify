#!/bin/sh
# Don't exit container if a command fails
set +e

echo "======================================="
echo " Laravel Deploy Script Starting..."
echo "======================================="

# Guard: skip everything if APP_KEY is missing
if [ -z "$APP_KEY" ]; then
    echo "WARNING: APP_KEY is not set. Skipping artisan commands."
    echo "Please set APP_KEY in Railway Dashboard → Variables."
    exit 0
fi

# Create storage symlink
echo "[1/5] Creating storage symlink..."
php artisan storage:link --force

# Cache config, routes, views
echo "[2/5] Caching configuration..."
php artisan config:cache

echo "[3/5] Caching routes..."
php artisan route:cache

echo "[4/5] Caching views..."
php artisan view:cache

# Run migrations only if DB is configured
if [ -n "$DB_HOST" ]; then
    echo "[5/5] Running database migrations..."
    php artisan migrate --force
else
    echo "[5/5] Skipping migrations: DB_HOST not set."
fi

echo "======================================="
echo " Deploy script completed successfully!"
echo "======================================="
