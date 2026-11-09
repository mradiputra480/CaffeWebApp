#!/bin/sh
set -e

echo "Starting CafeSync Application Server..."

# Ensure storage and cache directories exist and permissions are correct
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/database

# Check if using SQLite
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        echo "Creating fresh SQLite database file..."
        touch /var/www/html/database/database.sqlite
    fi
fi

# Generate app key if missing
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY is missing, generating key..."
    php artisan key:generate --force
fi

# Run migrations and seeders
echo "Running database migrations and seeders..."
php artisan migrate --force --seed

# Optimize caches in production
if [ "$APP_ENV" = "production" ]; then
    echo "Caching configuration and routes..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Determine port (Render and Fly.io pass $PORT, default to 8080 or 8000)
PORT="${PORT:-8080}"
echo "Serving CafeSync on port ${PORT}..."

exec php -S 0.0.0.0:"${PORT}" -t public
