# Inventory & Orders — Laravel demo

A small but genuinely working Laravel application, built to show PHP/Laravel work
rather than describe it. Nothing on the pages is hard-coded: every figure is a
query against the database.

Live demo: _(deployed URL goes here)_

## What it does

A pipes-and-fittings distributor's back office:

- **Dashboard** — stock value at cost, items below reorder level, open orders,
  outstanding dealer balance, and the order pipeline by stage.
- **Stock** — every SKU with cost, selling price, margin %, quantity on hand and
  a reorder flag. Filterable by category, or to the reorder list only.
- **Orders** — dealer orders through a five-stage pipeline
  (New → Confirmed → Packed → Dispatched → Delivered), filterable by stage.
- **Order detail** — the order lines with unit prices and line totals, the
  dealer's credit limit against their current outstanding balance, and a button
  that advances the order one stage. The transition is enforced server-side; no
  stage can be skipped.

## Stack

Laravel 11 · PHP 8.2+ · SQLite · Blade · plain CSS (no build step)

## Running it locally

```
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

## Deploying

The repository carries a `Dockerfile` and a `render.yaml`, so it deploys as a
Docker web service with no extra configuration. The database is a file inside the
container and is re-seeded on each boot, which keeps the demo honest — the
"advance order" button really writes, and a restart puts the sample data back.

## Notes

The data is sample data. The dealers, prices and order references are invented
for the demo; the behaviour is not.

Built by Zohaib Ahmad — [studioyume.pages.dev](https://studioyume.pages.dev)
