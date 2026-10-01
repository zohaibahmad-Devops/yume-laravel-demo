# Deploying this demo

The live instance runs on an alwaysdata free account — ordinary shared PHP
hosting, no card required. Any host with PHP 8.3+, SSH and Composer will do.

## What the host needs

PHP 8.3 or newer with `pdo_sqlite`, Composer, and Git. No database server:
the whole thing lives in one SQLite file.

## Steps

```
git clone https://github.com/zohaibahmad-Devops/yume-laravel-demo.git
cd yume-laravel-demo
composer install --no-dev --no-interaction --prefer-dist
cp .env.example .env
php artisan key:generate --force
: > database/database.sqlite
chmod -R 775 storage bootstrap/cache database
php artisan migrate --force --seed
```

Then set `APP_URL` in `.env` to the real address and build the caches:

```
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Web server

Point the site's document root at `yume-laravel-demo/public` — not at the
project root, or the `.env` file and the SQLite database become downloadable.

`public/.htaccess` carries the front-controller rewrite. Without it Apache
serves `/` and 404s every other route, because nothing sends the request to
`index.php`.

Pin the PHP version rather than leaving it on the host's "default". A host that
moves its default to the next major release will otherwise break the app with
no change on your side.

## Nightly reset

A scheduled task runs at 03:00 in the project directory:

```
php artisan migrate:fresh --force --seed
```

This is what makes the "advance order" button safe to leave in a public demo —
visitors really do write to the database, and it is clean again by morning.

## Container alternative

The `Dockerfile` builds the same app with `php artisan serve` behind it. It was
written for a host that turned out to want a card, so it has never actually been
built. Treat it as a starting point.
