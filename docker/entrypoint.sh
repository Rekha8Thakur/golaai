#!/bin/sh
set -e

# Echo commands
set -x

echo "Starting Laravel preparation..."

# Optimizing configuration loading
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "Running migrations..."
php artisan migrate --force

echo "Starting Supervisor..."
exec supervisord -c /etc/supervisord.conf
