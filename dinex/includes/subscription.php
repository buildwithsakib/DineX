<?php
// Subscription helpers

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/functions.php';

function get_current_subscription(int $restaurantId): ?array
{
    $pdo = db();
    $stmt = $pdo->prepare('
        SELECT rs.*, p.name AS plan_name, p.billing_cycle, p.price, p.duration_days, p.max_tables, p.max_staff
        FROM restaurant_subscriptions rs
        JOIN subscription_plans p ON p.id = rs.plan_id
        WHERE rs.restaurant_id = :restaurant_id
        ORDER BY rs.id DESC
        LIMIT 1
    ');
    $stmt->execute([':restaurant_id' => $restaurantId]);
    $sub = $stmt->fetch();
    return $sub ?: null;
}

function get_subscription_history(int $restaurantId): array
{
    $pdo = db();
    $stmt = $pdo->prepare('
        SELECT rs.*, p.name AS plan_name, p.billing_cycle, p.price
        FROM restaurant_subscriptions rs
        JOIN subscription_plans p ON p.id = rs.plan_id
        WHERE rs.restaurant_id = :restaurant_id
        ORDER BY rs.id DESC
    ');
    $stmt->execute([':restaurant_id' => $restaurantId]);
    return $stmt->fetchAll();
}

function create_subscription(int $restaurantId, int $planId, string $status = SUBSCRIPTION_STATUS_PENDING, string $paymentStatus = SUBSCRIPTION_PAYMENT_PENDING): int
{
    $pdo = db();

    $planStmt = $pdo->prepare('SELECT * FROM subscription_plans WHERE id = :id');
    $planStmt->execute([':id' => $planId]);
    $plan = $planStmt->fetch();
    if (!$plan) {
        throw new InvalidArgumentException('Plan not found.');
    }

    $startDate = date('Y-m-d');
    $endDate = date('Y-m-d', strtotime('+' . $plan['duration_days'] . ' days'));

    $stmt = $pdo->prepare('
        INSERT INTO restaurant_subscriptions (restaurant_id, plan_id, start_date, end_date, status, payment_status)
        VALUES (:restaurant_id, :plan_id, :start_date, :end_date, :status, :payment_status)
    ');
    $stmt->execute([
        ':restaurant_id' => $restaurantId,
        ':plan_id' => $planId,
        ':start_date' => $startDate,
        ':end_date' => $endDate,
        ':status' => $status,
        ':payment_status' => $paymentStatus,
    ]);

    return (int)$pdo->lastInsertId();
}

function activate_subscription(int $subscriptionId): bool
{
    $pdo = db();
    $stmt = $pdo->prepare('
        UPDATE restaurant_subscriptions
        SET status = :active, payment_status = :paid
        WHERE id = :id
    ');
    return $stmt->execute([
        ':active' => SUBSCRIPTION_STATUS_ACTIVE,
        ':paid' => SUBSCRIPTION_PAYMENT_PAID,
        ':id' => $subscriptionId,
    ]);
}

function expire_subscription(int $subscriptionId): bool
{
    $pdo = db();
    $stmt = $pdo->prepare('UPDATE restaurant_subscriptions SET status = :expired WHERE id = :id');
    return $stmt->execute([':expired' => SUBSCRIPTION_STATUS_EXPIRED, ':id' => $subscriptionId]);
}

function suspend_subscription(int $subscriptionId): bool
{
    $pdo = db();
    $stmt = $pdo->prepare('UPDATE restaurant_subscriptions SET status = :suspended WHERE id = :id');
    return $stmt->execute([':suspended' => SUBSCRIPTION_STATUS_SUSPENDED, ':id' => $subscriptionId]);
}

function cancel_subscription(int $subscriptionId): bool
{
    $pdo = db();
    $stmt = $pdo->prepare('UPDATE restaurant_subscriptions SET status = :cancelled WHERE id = :id');
    return $stmt->execute([':cancelled' => SUBSCRIPTION_STATUS_CANCELLED, ':id' => $subscriptionId]);
}

function is_subscription_active(?array $subscription): bool
{
    return $subscription
        && $subscription['status'] === SUBSCRIPTION_STATUS_ACTIVE
        && strtotime($subscription['end_date']) >= strtotime(today());
}