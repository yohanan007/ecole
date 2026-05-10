#!/bin/bash
set -e

echo "=== Starting Symfony Entrypoint ==="

# Default values
DB_HOST=${DB_HOST:-database}
DB_PORT=${DB_PORT:-3306}
APP_ENV=${APP_ENV:-dev}

echo "=== Symfony Entrypoint ==="
echo "Environment: $APP_ENV"
echo "Database: $DB_HOST:$DB_PORT"

# Wait for database to be ready using wait-for-it script
echo ""
echo "Waiting for database at $DB_HOST:$DB_PORT to be ready..."
bash /wait-for-it.sh "$DB_HOST:$DB_PORT" -t 30 || {
    echo "✗ Database failed to start after 30 attempts"
    exit 1
}
echo "✓ Database is ready!"

# Run composer scripts now that DB is available
if [ "$APP_ENV" != "prod" ]; then
    echo ""
    echo "Running composer install..."
    composer install \
        --no-interaction \
        --optimize-autoloader \
        2>&1 || true
fi

# Clear cache
echo ""
echo "Clearing cache..."
php bin/console cache:clear --no-interaction || true

# Run the appropriate server
echo ""
if [ "$APP_ENV" = "prod" ]; then
    echo "Starting PHP-FPM (production)..."
    exec php-fpm
else
    echo "Starting PHP built-in server (development)..."
    cd /app
    exec php -S 0.0.0.0:8000 -t public/
fi
