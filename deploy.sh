#!/usr/bin/env bash
# ---------------------------------------------------------------------------
# HClass — single-file deploy for shared hosting (cPanel) over SSH.
#
# First run on a fresh clone:   bash deploy.sh --seed
# Every later update:           bash deploy.sh
#
# Frontend assets (public/build) ship via git — the host needs NO Node.
# Requires on the host: php (8.1+), composer, git, a MySQL database.
# ---------------------------------------------------------------------------
set -e

cd "$(dirname "$0")"

SEED=""
[ "$1" = "--seed" ] && SEED="--seed"

# --- 1. Pull latest code (no-op on a fresh clone) --------------------------
echo "==> Pulling latest code..."
git pull origin main || echo "    (skipped git pull)"

# --- 2. Ensure .env --------------------------------------------------------
if [ ! -f .env ]; then
  echo "==> No .env found — creating from .env.production.example"
  cp .env.production.example .env

  if [ -t 0 ]; then
    # Interactive: ask for the values that differ per host.
    read -rp "    APP_URL (e.g. https://your-domain.com): " APP_URL
    read -rp "    DB_DATABASE: " DB_DATABASE
    read -rp "    DB_USERNAME: " DB_USERNAME
    read -rsp "    DB_PASSWORD: " DB_PASSWORD; echo
    sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|"          .env
    sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${DB_DATABASE}|" .env
    sed -i "s|^DB_USERNAME=.*|DB_USERNAME=${DB_USERNAME}|" .env
    sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env
    echo "    .env created. (Edit it later for MAIL_* SMTP settings.)"
  else
    echo "    Not a TTY — edit .env with DB credentials, then run again."
    exit 1
  fi
fi

# --- 3. PHP dependencies ---------------------------------------------------
echo "==> Installing PHP dependencies (no dev)..."
composer install --no-dev --optimize-autoloader

# --- 4. App key ------------------------------------------------------------
if ! grep -q "^APP_KEY=base64" .env; then
  echo "==> Generating APP_KEY..."
  php artisan key:generate --force
fi

# --- 5. Database -----------------------------------------------------------
echo "==> Running migrations ${SEED}..."
php artisan migrate --force ${SEED}

# --- 6. Storage symlink (for uploaded board images) ------------------------
echo "==> Linking storage..."
php artisan storage:link || true

# --- 7. Production caches ---------------------------------------------------
echo "==> Rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# --- 8. Permissions --------------------------------------------------------
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "✓ Deployed"
