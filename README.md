# Inventory & Orders — Laravel demo

A small but genuinely working Laravel application, built to show PHP/Laravel work
rather than describe it. Nothing on the pages is hard-coded: every figure is a
query against the database.

**Live: https://daydreamatelier8.alwaysdata.net**

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

The data is sample data. The dealers, prices and order references are invented
for the demo; the behaviour is not. The database is re-seeded nightly, so the
"advance order" button really writes and the demo is clean again by morning.

## Stack

Laravel 13 · PHP 8.4 · SQLite · Blade · plain CSS (no build step)

## Running it locally

```
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

## Deployment

It runs on shared PHP hosting with the document root pointed at `public/`.
`DEPLOY.md` has the exact steps used for the live instance.

A `Dockerfile` and `docker/start.sh` are also in the repository for running the
whole thing as a container. That path is not what the live site uses, so treat
it as a starting point rather than something proven in production.

Built by Zohaib Ahmad — [studioyume.pages.dev](https://studioyume.pages.dev)
