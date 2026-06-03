#!/bin/sh
set -e

php artisan optimize

echo "Laravel setup is complete. Starting PHP-FPM..."
exec "$@"