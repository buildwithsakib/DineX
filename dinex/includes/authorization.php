<?php
// Authorization middleware

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/permissions.php';
require_once __DIR__ . '/subscription.php';
require_once __DIR__ . '/feature-access.php';

function require_founder_access(): array
{
    return require_founder_auth();
}

function require_restaurant_access(): array
{
    return require_restaurant_auth();
}

function require_restaurant_owner(): array
{
    return require_role([ROLE_OWNER]);
}

function require_restaurant_owner_or_manager(): array
{
    return require_role([ROLE_OWNER, ROLE_MANAGER]);
}

function require_restaurant_permission(string $permissionKey): array
{
    return require_permission($permissionKey);
}

function require_active_subscription_middleware(): array
{
    $staff = require_restaurant_auth();
    $subscription = get_current_subscription($staff['restaurant_id']);
    if (!$subscription || $subscription['status'] !== SUBSCRIPTION_STATUS_ACTIVE || strtotime($subscription['end_date']) < time()) {
        header('HTTP/1.1 403 Forbidden');
        exit('Forbidden: No active subscription.');
    }
    return $staff;
}

function require_feature_access_middleware(string $featureKey): array
{
    $staff = require_restaurant_auth();
    if (!restaurant_has_feature($staff['restaurant_id'], $featureKey)) {
        header('HTTP/1.1 403 Forbidden');
        exit('Forbidden: This feature is not available in your current subscription.');
    }
    return $staff;
}

function require_restaurant_ownership(int $restaurantId, int $resourceRestaurantId): void
{
    if ((int)$restaurantId !== (int)$resourceRestaurantId) {
        header('HTTP/1.1 403 Forbidden');
        exit('Forbidden: You cannot access another restaurant\'s data.');
    }
}