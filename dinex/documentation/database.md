# Database Schema

The database `dinex` contains all platform and operational tables.

## Platform Tables

- `platform_users` – Founder/admin accounts
- `restaurants` – Restaurant profiles
- `restaurant_staff` – Restaurant staff users
- `roles`, `permissions`, `role_permissions` – RBAC
- `subscription_plans` – Plan definitions
- `subscription_plan_features` – Feature entitlements per plan
- `restaurant_subscriptions` – Assigned subscriptions
- `restaurant_features` – Feature overrides per restaurant
- `subscription_payments` – Subscription payments

## Operational Tables

- `tables`, `qr_codes`, `table_sessions` – Table and session management
- `categories`, `cuisines`, `foods`, `food_variants`, `food_addons` – Menu
- `orders`, `order_items`, `order_status_history` – Ordering
- `games`, `game_rewards`, `game_sessions` – Gamification
- `coupons`, `coupon_redemptions` – Coupons
- `bills`, `payments` – Billing
- `reviews` – Anonymous feedback
- `campaigns` – Promotions
- `notifications` – Notifications
- `settings` – Key-value settings
- `audit_logs` – Audit trail
- `rate_limit` – Login rate limiting

No customer tables exist. Customer data is anonymous and temporary via `table_sessions`.
