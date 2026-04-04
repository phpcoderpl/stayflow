#!/bin/bash
set -e

echo "=== StayFlow start.sh ==="
echo "Checking /var/data..."
ls -la /var/data/ 2>/dev/null || echo "/var/data not found!"

touch /var/data/database.sqlite
echo "Database file size: $(wc -c < /var/data/database.sqlite) bytes"

echo "Running migrate:fresh + seed..."
php artisan migrate:fresh --seed --force

echo "Storage link..."
php artisan storage:link --force 2>/dev/null || true

echo "Optimize..."
php artisan optimize

echo "Starting server on port ${PORT:-8080}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
