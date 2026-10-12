#!/usr/bin/env bash
# Safe update of an installed site: backup, maintenance mode, new code, migrations, caches.
#   bash scripts/hostinger/update.sh
set -euo pipefail

cd "$(dirname "$0")/../.."

php artisan erp:backup || echo "WARNING: backup failed. Export the database in phpMyAdmin before continuing."

php artisan down --retry=60
trap 'php artisan up' EXIT

git pull --ff-only
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan erp:roles:sync
php artisan optimize:clear
php artisan optimize

echo "Updated. The site is back online."
