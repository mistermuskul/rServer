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

# Проверка подключения к базе данных
echo "Checking database connection..."
php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connection OK';" || {
    echo "Database connection failed!"
    exit 1
}

# Запуск миграций
echo "Running migrations..."
php artisan migrate --force

# Запуск сидов
echo "Running seeders..."
php artisan db:seed --force

# Очистка кэша после миграций
php artisan config:cache
php artisan route:cache

# Запуск сервера
echo "Starting Laravel application on port $PORT..."
php -S 0.0.0.0:$PORT -t public/ 