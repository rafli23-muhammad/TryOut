#!/bin/sh
set -e

# If .env does not exist, copy from .env.example
if [ ! -f /app/.env ]; then
    cp /app/.env.example /app/.env
fi

# Ensure APP_KEY exists
if ! grep -q "^APP_KEY=base64:" /app/.env && [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Ensure SQLite file exists
if [ ! -f /app/database/database.sqlite ]; then
    touch /app/database/database.sqlite
fi

# Create symlinks and ensure permissions
php artisan storage:link --force 2>/dev/null || true
chmod -R 777 /app/storage /app/bootstrap/cache /app/database 2>/dev/null || true

# Run migrations if database is accessible
php artisan migrate --force 2>/dev/null || true

# Export PORT for Caddy
export PORT="${PORT:-80}"

# Run FrankenPHP with custom Caddyfile
exec frankenphp run --config /app/Caddyfile
