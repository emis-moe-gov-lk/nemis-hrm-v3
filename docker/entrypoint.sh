#!/bin/sh
set -e

echo "Setting storage permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Generate an application key if none is provided or persisted
if [ -z "$APP_KEY" ]; then
    if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
        echo "Generating application key..."
        echo "APP_KEY=$(php artisan key:generate --show)" > .env
    fi
    export APP_KEY="$(sed -n 's/^APP_KEY=//p' .env)"
fi

echo "Running migrations..."
php artisan migrate --force

if [ ! -L public/storage ]; then
    echo "Linking public storage..."
    php artisan storage:link
fi

echo "Starting PHP-FPM..."
exec php-fpm
