#!/bin/sh
set -e

cd /app

# The demo carries no .env; build one from the example on first boot.
if [ ! -f .env ]; then
  cp .env.example .env
fi

if ! grep -q '^APP_KEY=base64' .env; then
  php artisan key:generate --force
fi

# Fresh SQLite file and fresh demo data on every boot, so anyone clicking
# "Move to <next stage>" can never leave the demo in a confusing state.
mkdir -p database
: > database/database.sqlite
php artisan migrate:fresh --force --seed

php artisan config:clear
php artisan view:clear

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
