#!/bin/bash

set -euo pipefail

cd "$(dirname "$0")/.."

if ! command -v php >/dev/null 2>&1; then
    echo "Deployment failed: PHP is not available in the cPanel terminal."
    exit 1
fi

if command -v composer >/dev/null 2>&1; then
    COMPOSER_BIN="$(command -v composer)"
elif [ -x /opt/cpanel/composer/bin/composer ]; then
    COMPOSER_BIN=/opt/cpanel/composer/bin/composer
else
    echo "Deployment failed: Composer is not available in the cPanel terminal."
    exit 1
fi

"$COMPOSER_BIN" install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader

if [ ! -f .env ]; then
    echo "Deployment stopped: create the production .env file in $(pwd) and deploy again."
    exit 1
fi

php artisan migrate --force
php artisan storage:link || true
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Laravel deployment completed successfully."
