#!/bin/sh
set -e

echo "Starting Laravel container..."

# Ensure .env exists
if [ ! -f .env ]; then
  echo "Creating .env file..."
  cp .env.prod .env
fi

# Generate APP_KEY if missing
if ! grep -q "APP_KEY=base64" .env; then
  echo "Generating APP_KEY..."
  php artisan key:generate --force
fi

# Fix storage permissions
echo "Fixing permissions..."
mkdir -p storage/logs \
  storage/framework/cache \
  storage/framework/sessions \
  storage/framework/views \
  bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Clear caches (important for fresh environments)
echo "Clearing caches..."
php artisan optimize:clear || true

# Run migrations (safe in dev/test context)
echo "Running migrations..."
php artisan migrate --force || true

echo "Container ready"

# Start PHP-FPM
exec php-fpm
