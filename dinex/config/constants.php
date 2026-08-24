<?php
// DineX constants

// Order statuses
define('ORDER_STATUS_PLACED', 'PLACED');
define('ORDER_STATUS_ACCEPTED', 'ACCEPTED');
define('ORDER_STATUS_PREPARING', 'PREPARING');
define('ORDER_STATUS_READY', 'READY');
define('ORDER_STATUS_SERVED', 'SERVED');
define('ORDER_STATUS_COMPLETED', 'COMPLETED');
define('ORDER_STATUS_CANCELLED', 'CANCELLED');

// Restaurant statuses
define('RESTAURANT_STATUS_PENDING', 'PENDING');
define('RESTAURANT_STATUS_ACTIVE', 'ACTIVE');
define('RESTAURANT_STATUS_SUSPENDED', 'SUSPENDED');
define('RESTAURANT_STATUS_CANCELLED', 'CANCELLED');

// Subscription statuses
define('SUBSCRIPTION_STATUS_PENDING', 'PENDING');
define('SUBSCRIPTION_STATUS_ACTIVE', 'ACTIVE');
define('SUBSCRIPTION_STATUS_EXPIRED', 'EXPIRED');
define('SUBSCRIPTION_STATUS_SUSPENDED', 'SUSPENDED');
define('SUBSCRIPTION_STATUS_CANCELLED', 'CANCELLED');

// Subscription payment statuses
define('SUBSCRIPTION_PAYMENT_PENDING', 'PENDING');
define('SUBSCRIPTION_PAYMENT_PAID', 'PAID');
define('SUBSCRIPTION_PAYMENT_FAILED', 'FAILED');
define('SUBSCRIPTION_PAYMENT_REFUNDED', 'REFUNDED');

// Roles
define('ROLE_OWNER', 'OWNER');
define('ROLE_MANAGER', 'MANAGER');
define('ROLE_CASHIER', 'CASHIER');

// Feature keys
define('FEATURE_QR_ORDERING', 'qr_ordering');
define('FEATURE_DIGITAL_MENU', 'digital_menu');
define('FEATURE_KITCHEN', 'kitchen');
define('FEATURE_BILLING', 'billing');
define('FEATURE_GAMES', 'games');
define('FEATURE_COUPONS', 'coupons');
define('FEATURE_REVIEWS', 'reviews');
define('FEATURE_CAMPAIGNS', 'campaigns');
define('FEATURE_ANALYTICS', 'analytics');
define('FEATURE_ADVANCED_ANALYTICS', 'advanced_analytics');

// Table session statuses
define('TABLE_SESSION_ACTIVE', 'ACTIVE');
define('TABLE_SESSION_CLOSED', 'CLOSED');
define('TABLE_SESSION_EXPIRED', 'EXPIRED');

// General
define('RESTAURANT_OWNER_DEFAULT_ROLE', ROLE_OWNER);
