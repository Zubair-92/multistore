# Multistore Marketplace

A Laravel-based multistore marketplace with separate customer, store, and admin experiences.

## What is included

- Customer registration, login, profile, wishlist, reviews, cart, checkout, and order history
- Store registration, approval flow, dashboard, product management, inventory adjustments, and store order handling
- Admin dashboard, subadmin permissions, store management, product management, coupons, reporting, exports, and order operations
- Public marketplace with product pages, store pages, filters, featured deals, recently viewed products, and demo payment flow

## Demo payment

This project includes a sandbox demo card flow for presentation use.

- Successful demo card: `4242 4242 4242 4242`
- Declined demo card: `4000 0000 0000 0002`
- Demo payments do not charge real money

## Demo accounts

Use these seeded accounts for local testing and demos:

- Admin: `admin@multistore.com` / `admin@admin`
- Subadmin: `subadmin@multistore.com` / `subadmin@admin`
- Customer: `subair@gmail.com` / `customer@admin`
- Store record: `rahath@rahath.com`
  Store owner login: `admin@rahath.com` / `admin@rahath`
- Store record: `benice@benice.com`
  Store owner login: `admin@benice.com` / `admin@benice`

If the accounts are missing, run:

```bash
php artisan db:seed --class=DemoAccountSeeder --force
```

## Local setup

1. Copy `.env.example` to `.env`
2. Update database credentials
3. Run:

```bash
composer install
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

If you use frontend assets in your environment, also run:

```bash
npm install
npm run build
```

## Recommended local environment

- PHP 8.2+
- MySQL 8+
- Composer
- Node.js 18+
- XAMPP/Laragon is fine for demo use

## Important notes

- Manual phpMyAdmin schema edits should be avoided from now on. Use migrations for every database change.
- Storefront product visibility depends on active products belonging to approved stores.
- Queue-backed notifications and real mail delivery should be configured before production launch.
- The app includes a health endpoint at `/up`.

## Key modules

- Multi-guard auth isolation: customer, store, admin
- Store approval and store-only dashboard access
- Orders, payment status, stock sync, and cancellation recovery
- Coupons and sandbox demo card checkout
- Inventory movement tracking
- Reviews, wishlist, storefront discovery, and public store pages
- Admin permissions, reports, and CSV exports

## Testing

Run the test suite with:

```bash
php artisan test
```

## Launch readiness

See [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) for a practical deployment and handoff checklist.
