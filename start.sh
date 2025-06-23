#!/bin/bash

# Установка зависимостей
composer install --no-dev --optimize-autoloader

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

# Проверка подключения к базе данных
echo "Проверка подключения к базе данных..."
php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'База данных подключена успешно'; } catch (Exception \$e) { echo 'Ошибка подключения к БД: ' . \$e->getMessage(); exit(1); }"

# Запуск миграций и сидеров
php artisan migrate --force --seed

# Запуск веб-сервера
php artisan serve --host=0.0.0.0 --port=$PORT
