# Deployment Checklist

## Before launch

- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Set the real `APP_URL`
- Use strong database credentials
- Configure `MAIL_MAILER` for real email delivery
- Configure `QUEUE_CONNECTION` and run a queue worker
- Run `php artisan storage:link`
- Run `php artisan migrate --force`
- Run `php artisan config:cache`
- Run `php artisan route:cache`
- Run `php artisan view:cache`

## Recommended production services

- MySQL for database
- Redis for cache/queue/session if available
- Supervisor or Windows service for queue workers
- Real SMTP provider or transactional mail service

## Demo vs production

### Demo
- Keep demo card payment enabled
- `MAIL_MAILER=log` is acceptable
- `QUEUE_CONNECTION=database` is acceptable
- Use the seeded demo accounts documented in `README.md`

### Production
- Replace demo card emphasis with a real gateway
- Move notifications to queue workers
- Use real SMTP credentials
- Review coupon rules and stock thresholds

## Operational checks

- Verify admin login, customer login, and store login are isolated
- Verify approved stores can log in and pending stores cannot
- Verify products are visible only when active and store is approved
- Verify checkout works for COD, bank transfer, and demo card
- Verify order status updates send notifications
- Verify low-stock products appear in admin/store dashboards
- Verify exports download correctly

## Database discipline

- Do not make manual phpMyAdmin schema changes without adding matching migrations
- Keep the live database and migration history aligned
- Back up the database before structural changes

## Final handoff suggestion

Before presenting or deploying, test this flow end to end:

1. Register a customer
2. Register a store
3. Approve the store in admin
4. Create a product as store
5. Browse product publicly
6. Add to cart and checkout with demo card
7. Review order in customer, store, and admin panels
8. Update order status and verify reflected changes
