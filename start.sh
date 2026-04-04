#!/bin/bash
set -e

touch /var/data/database.sqlite

php artisan migrate --force
php artisan storage:link --force 2>/dev/null || true
php artisan optimize

exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
