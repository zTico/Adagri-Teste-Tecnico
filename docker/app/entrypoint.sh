#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

mkdir -p \
    bootstrap/cache \
    storage/app \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs

chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwX storage bootstrap/cache || true

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

until pg_isready -h "${DB_HOST:-db}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-postgres}" >/dev/null 2>&1; do
    echo "Waiting for PostgreSQL..."
    sleep 2
done

if ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

php artisan migrate --force
php artisan storage:link --force

USER_COUNT=$(PGPASSWORD="${DB_PASSWORD:-postgres}" psql \
    -h "${DB_HOST:-db}" \
    -p "${DB_PORT:-5432}" \
    -U "${DB_USERNAME:-postgres}" \
    -d "${DB_DATABASE:-agro_management}" \
    -tAc "SELECT COUNT(*) FROM users" 2>/dev/null || echo "0")

if [ "${USER_COUNT}" = "0" ]; then
    php artisan db:seed --force
fi

exec "$@"
