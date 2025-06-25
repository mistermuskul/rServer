#!/bin/bash

# Ждем подключения к базе данных
echo "Waiting for database connection..."
sleep 10

# Настройка прав доступа
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Создание SQLite базы данных если её нет
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    chmod 664 database/database.sqlite
fi

# Установка переменных окружения для SQLite
export DB_CONNECTION=sqlite
export DB_DATABASE=/app/database/database.sqlite

# Очистка кэша
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Генерация ключа приложения (если не установлен)
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Создание символической ссылки для storage
php artisan storage:link

# Запуск миграций
php artisan migrate --force

# Запуск сидеров (только если база пустая)
php artisan db:seed --force

# Оптимизация для продакшена
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Запуск веб-сервера
php artisan serve --host=0.0.0.0 --port=$PORT
