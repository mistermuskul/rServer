#!/bin/bash



# Настройка прав доступа
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Очистка кэша
php artisan config:clear
php artisan cache:clear

# Генерация ключа приложения
php artisan key:generate --force

# Создание символической ссылки для storage
php artisan storage:link

# Запуск миграций и сидеров
php artisan migrate --force --seed

# Запуск веб-сервера
php artisan serve --host=0.0.0.0 --port=$PORT
