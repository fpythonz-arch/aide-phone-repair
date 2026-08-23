#!/bin/sh
set -e

echo "==> Caching config..."
php artisan config:cache

echo "==> Caching routes..."
php artisan route:cache

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
