#!/bin/bash

# Установка зависимостей
composer install --no-dev --optimize-autoloader

# Настройка прав доступа
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Очистка кэша
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Генерация ключа приложения (только если не установлен)
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Создание символической ссылки для storage
php artisan storage:link

# Ожидание подключения к базе данных
echo "Waiting for database connection..."
while ! php artisan tinker --execute="DB::connection()->getPdo();" 2>/dev/null; do
    echo "Database not ready. Waiting..."
    sleep 2
done
echo "Database connection established!"

# Запуск миграций и сидеров
php artisan migrate --force
php artisan db:seed --force

# Оптимизация для production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Запуск веб-сервера
php artisan serve --host=0.0.0.0 --port=$PORT
