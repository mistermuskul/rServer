#!/bin/bash
# Exit immediately if a command exits with a non-zero status.
set -e

# --- 1. Wait for Database ---
echo "Waiting for database connection..."
max_attempts=30
attempt_num=1
# This loop will use the DATABASE_URL variable to try and connect.
while ! php artisan tinker --execute="DB::connection()->getPdo()" >/dev/null 2>&1; do
    if [ ${attempt_num} -eq ${max_attempts} ]; then
        echo "Database connection failed after ${max_attempts} attempts. Exiting."
        exit 1
    fi
    echo "Attempt ${attempt_num} of ${max_attempts}: Database not ready. Waiting 5 seconds..."
    sleep 5
    attempt_num=$((attempt_num+1))
done
echo "Database connection successful!"

# Add a small buffer just in case the database needs a moment to stabilize
echo "Giving the database a moment to stabilize..."
sleep 5

# --- 2. Run Migrations & Seeders ---
echo "Running database migrations and seeding..."
php artisan migrate --force --seed

# --- 3. Cache Configuration ---
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# --- 4. Start Server ---
echo "Starting Laravel application..."
php artisan serve --host 0.0.0.0 --port 8080 