<?php
// Feature entitlement and access enforcement

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/subscription.php';

function restaurant_has_feature(int $restaurantId, string $featureKey): bool
{
    $pdo = db();

    // Restaurant must be active
    $restaurantStmt = $pdo->prepare('SELECT status FROM restaurants WHERE id = :id LIMIT 1');
    $restaurantStmt->execute([':id' => $restaurantId]);
    $restaurant = $restaurantStmt->fetch();
    if (!$restaurant || $restaurant['status'] !== RESTAURANT_STATUS_ACTIVE) {
        return false;
    }

    $subscription = get_current_subscription($restaurantId);
    if (!is_subscription_active($subscription)) {
        return false;
    }

    // Check founder override first
    $overrideStmt = $pdo->prepare('
        SELECT override_enabled
        FROM restaurant_features
        WHERE restaurant_id = :restaurant_id AND feature_key = :feature_key
        LIMIT 1
    ');
    $overrideStmt->execute([':restaurant_id' => $restaurantId, ':feature_key' => $featureKey]);
    $override = $overrideStmt->fetch();

    if ($override && $override['override_enabled'] !== null) {
        return (bool)$override['override_enabled'];
    }

    // Check plan entitlement
    $planFeatureStmt = $pdo->prepare('
        SELECT spf.is_enabled
        FROM subscription_plan_features spf
        WHERE spf.plan_id = :plan_id AND spf.feature_key = :feature_key
        LIMIT 1
    ');
    $planFeatureStmt->execute([
        ':plan_id' => $subscription['plan_id'],
        ':feature_key' => $featureKey,
    ]);
    $planFeature = $planFeatureStmt->fetch();

    return $planFeature && (bool)$planFeature['is_enabled'];
}

function get_effective_features(int $restaurantId): array
{
    $pdo = db();
    $subscription = get_current_subscription($restaurantId);

    if (!is_subscription_active($subscription)) {
        return [];
    }

    $planFeatures = [];
    $stmt = $pdo->prepare('SELECT feature_key, is_enabled FROM subscription_plan_features WHERE plan_id = :plan_id');
    $stmt->execute([':plan_id' => $subscription['plan_id']]);
    foreach ($stmt->fetchAll() as $row) {
        $planFeatures[$row['feature_key']] = (bool)$row['is_enabled'];
    }

    $stmt = $pdo->prepare('SELECT feature_key, override_enabled FROM restaurant_features WHERE restaurant_id = :restaurant_id');
    $stmt->execute([':restaurant_id' => $restaurantId]);
    foreach ($stmt->fetchAll() as $row) {
        if ($row['override_enabled'] !== null) {
            $planFeatures[$row['feature_key']] = (bool)$row['override_enabled'];
        }
    }

    return $planFeatures;
}

function set_restaurant_feature_override(int $restaurantId, string $featureKey, ?bool $enabled): void
{
    $pdo = db();
    $stmt = $pdo->prepare('
        INSERT INTO restaurant_features (restaurant_id, feature_key, override_enabled)
        VALUES (:restaurant_id, :feature_key, :enabled_insert)
        ON DUPLICATE KEY UPDATE override_enabled = :enabled_update, updated_at = NOW()
    ');
    $stmt->execute([
        ':restaurant_id' => $restaurantId,
        ':feature_key' => $featureKey,
        ':enabled_insert' => $enabled === null ? null : (int)$enabled,
        ':enabled_update' => $enabled === null ? null : (int)$enabled,
    ]);
}