#!/usr/bin/env bash
# Deploy HClass on shared hosting (cPanel) over SSH.
# Frontend assets (public/build) ship via git — the host has no Node.
set -e

cd "$(dirname "$0")"

echo "==> Pulling latest code..."
git pull origin main

echo "==> Installing PHP dependencies (no dev)..."
composer install --no-dev --optimize-autoloader

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Ensuring storage symlink..."
php artisan storage:link || true

echo "==> Rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✓ Deployed"
