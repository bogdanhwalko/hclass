#!/usr/bin/env bash
set -e

cd /var/www

# Install PHP deps if missing
if [ ! -d vendor ]; then
  echo "==> Installing composer dependencies..."
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Ensure .env
if [ ! -f .env ]; then
  cp .env.example .env
fi

# App key
if ! grep -q "^APP_KEY=base64" .env; then
  php artisan key:generate --force
fi

# Build frontend assets if not built
if [ ! -d public/build ]; then
  echo "==> Building frontend assets..."
  npm install
  npm run build
fi

# Wait for DB, then migrate + seed
echo "==> Running migrations & seeders..."
php artisan migrate --force --seed || php artisan migrate --force

# Permissions
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

php artisan config:clear || true

exec "$@"
