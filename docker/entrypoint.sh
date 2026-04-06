#!/bin/sh
set -e

# Update nginx port to match Render's PORT env var (defaults to 8000)
sed -i "s/listen 8000;/listen ${PORT:-8000};/" /etc/nginx/nginx.conf

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
while ! php -r "new PDO('mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD');" 2>/dev/null; do
    sleep 2
done
echo "MySQL is ready!"

# Run migrations
php artisan migrate --force

# Seed languages and test users (only if not already seeded)
php artisan db:seed --class=LanguageSeeder --force
php artisan db:seed --class=TestUsersSeeder --force

# Cache config and routes
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chown -R www-data:www-data /app/storage /app/bootstrap/cache

exec "$@"
