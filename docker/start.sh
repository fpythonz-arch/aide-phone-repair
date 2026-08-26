#!/bin/bash
set -e

echo "==> Caching config..."
php artisan config:cache

echo "==> Caching routes..."
php artisan route:cache

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Seeding demo users..."
php artisan db:seed --class=UserSeeder --force

echo "==> Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
