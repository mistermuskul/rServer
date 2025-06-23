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

# Запуск миграций
php artisan migrate --force

# Запуск сервера
echo "Starting Laravel application on port $PORT..."
php -S 0.0.0.0:$PORT -t public/ 