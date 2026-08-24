# Subscription System

## Plans
Founder creates plans (Monthly/Yearly) with price, duration, max tables, max staff.

## Features
Each plan has feature entitlements stored in `subscription_plan_features`.  
Founder can override features per restaurant via `restaurant_features`.

## Effective Feature Access
The function `restaurant_has_feature($restaurantId, $featureKey)` determines access by considering:
1. Restaurant status (must be ACTIVE)
2. Active subscription (status ACTIVE and not expired)
3. Plan feature entitlement
4. Founder override

## Subscription Lifecycle
- `PENDING` → initial state after registration
- `ACTIVE` → after founder activation or payment
- `EXPIRED` → when `end_date` passes (enforced by date check)
- `SUSPENDED` → founder can suspend
- `CANCELLED` → founder can cancel

## Enforcement
All protected endpoints must call `require_feature_access_middleware($featureKey)` or `restaurant_has_feature()` before proceeding. Frontend hiding is not security.