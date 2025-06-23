#!/bin/bash

# Установка зависимостей если их нет
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader
fi

# Генерация ключа приложения если его нет
if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate
fi

# Очистка кэша
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# -- Robust Database Connection Check with Retry --
echo "Waiting for database to be ready..."
max_attempts=15
attempt_num=1
# Hide output of tinker, we just need the exit code
while ! php artisan tinker --execute="DB::connection()->getPdo()" >/dev/null 2>&1; do
    if [ ${attempt_num} -eq ${max_attempts} ]; then
        echo "Database connection failed after ${max_attempts} attempts. Exiting."
        exit 1
    fi
    echo "Attempt ${attempt_num} of ${max_attempts}: Database not ready. Waiting 2 seconds..."
    attempt_num=$((attempt_num+1))
    sleep 2
done
echo "Database connection successful!"
# -- End of Connection Check --

# Запуск миграций с повторными попытками
echo "Running migrations with retries..."
php artisan migrate --force || (sleep 5 && php artisan migrate --force) || (sleep 10 && php artisan migrate --force)

# Запуск сидов с повторными попытками
echo "Running seeders with retries..."
php artisan db:seed --force || (sleep 5 && php artisan db:seed --force) || (sleep 10 && php artisan db:seed --force)

# Очистка кэша после миграций
php artisan config:cache
php artisan route:cache

# Запуск сервера
echo "Starting Laravel application on port $PORT..."
php -S 0.0.0.0:$PORT -t public/ 