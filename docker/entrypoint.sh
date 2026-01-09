#!/bin/sh
set -e

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
while ! mysqladmin ping -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" --skip-ssl --silent; do
    sleep 2
done
echo "MySQL is ready!"

# Run migrations
php artisan migrate --force

# Cache config and routes
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chown -R www-data:www-data /app/storage /app/bootstrap/cache

exec "$@"
