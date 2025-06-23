#!/bin/bash

# Exit on any error
set -e

# Check if Composer dependencies are installed
if [ ! -f "vendor/autoload.php" ]; then
    composer install --no-progress --no-interaction
fi

# Generate Laravel application key if it doesn't exist
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

if [ -z "$APP_KEY" ]; then
    php artisan key:generate
fi

# Clear cache to ensure fresh environment variables are used
php artisan config:clear
php artisan route:clear

# Debug: Show database connection info
echo "Database connection debug info:"
echo "DB_CONNECTION: $DB_CONNECTION"
echo "DB_HOST: $DB_HOST"
echo "DB_PORT: $DB_PORT"
echo "DB_DATABASE: $DB_DATABASE"
echo "DB_USERNAME: $DB_USERNAME"
echo "MYSQLHOST: $MYSQLHOST"
echo "MYSQLPORT: $MYSQLPORT"
echo "MYSQLDATABASE: $MYSQLDATABASE"
echo "MYSQLUSER: $MYSQLUSER"

# Wait for the database to be ready
# Use a loop to attempt connection until it succeeds
echo "Waiting for database connection..."
MAX_ATTEMPTS=30
ATTEMPTS=0
while ! php artisan tinker --execute="DB::connection()->getPdo()" > /dev/null 2>&1; do
    ATTEMPTS=$((ATTEMPTS+1))
    if [ $ATTEMPTS -ge $MAX_ATTEMPTS ]; then
        echo "Database connection failed after $MAX_ATTEMPTS attempts."
        exit 1
    fi
    echo "Waiting for database... (attempt $ATTEMPTS of $MAX_ATTEMPTS)"
    sleep 2
done
echo "Database is ready."

# Run database migrations with retry
echo "Running database migrations..."
MAX_MIGRATE_ATTEMPTS=5
MIGRATE_ATTEMPTS=0
while ! php artisan migrate --force; do
    MIGRATE_ATTEMPTS=$((MIGRATE_ATTEMPTS+1))
    if [ $MIGRATE_ATTEMPTS -ge $MAX_MIGRATE_ATTEMPTS ]; then
        echo "Migration failed after $MAX_MIGRATE_ATTEMPTS attempts."
        exit 1
    fi
    echo "Migration failed, retrying... (attempt $MIGRATE_ATTEMPTS of $MAX_MIGRATE_ATTEMPTS)"
    sleep 5
done
echo "Migrations completed successfully."

# Clear permission cache before seeding
echo "Clearing permission cache..."
php artisan permission:cache-reset

# Run database seeding with retry
echo "Running database seeding..."
MAX_SEED_ATTEMPTS=5
SEED_ATTEMPTS=0
while ! php artisan db:seed --force; do
    SEED_ATTEMPTS=$((SEED_ATTEMPTS+1))
    if [ $SEED_ATTEMPTS -ge $MAX_SEED_ATTEMPTS ]; then
        echo "Seeding failed after $MAX_SEED_ATTEMPTS attempts."
        # Don't exit, just log it. The app might still be able to run.
        echo "Seeding failed after multiple attempts."
        break
    fi
    echo "Seeding failed, retrying... (attempt $SEED_ATTEMPTS of $MAX_SEED_ATTEMPTS)"
    sleep 5
done
echo "Seeding process finished."


# Clear and cache configuration and routes for performance
php artisan config:cache
php artisan route:cache

# Start the Laravel server
echo "Starting Laravel application on port $PORT..."
php artisan serve --host 0.0.0.0 --port $PORT 