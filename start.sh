#!/bin/bash

echo "Waiting for database connection..."
sleep 10

chmod -R 775 storage
chmod -R 775 bootstrap/cache

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

php artisan storage:link

php artisan migrate --force

php artisan db:seed --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan serve --host=0.0.0.0 --port=$PORT
