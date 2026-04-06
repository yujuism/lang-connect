#!/bin/sh
set -e

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
while ! php -r "new PDO('mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD');" 2>/dev/null; do
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
