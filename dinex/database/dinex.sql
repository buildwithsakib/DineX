-- ============================================================
-- DineX Platform Database
-- ============================================================

CREATE DATABASE IF NOT EXISTS `dinex`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `dinex`;

-- ============================================================
-- DineX Platform Database Schema
-- Version: 1.0 (with Part 2 seed data)
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ------------------------------------------------------------
-- PLATFORM USERS
-- ------------------------------------------------------------
CREATE TABLE `platform_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(180) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `status` enum('ACTIVE','INACTIVE','SUSPENDED') NOT NULL DEFAULT 'ACTIVE',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `platform_users` (`id`, `name`, `email`, `password_hash`, `status`) VALUES
(1, 'DineX Founder', 'founder@dinex.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ACTIVE');
-- Default founder password: password

-- ------------------------------------------------------------
-- RESTAURANTS
-- ------------------------------------------------------------
CREATE TABLE `restaurants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(180) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `owner_name` varchar(120) NOT NULL,
  `email` varchar(180) NOT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(30) DEFAULT NULL,
  `country` varchar(80) DEFAULT 'India',
  `description` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `status` enum('PENDING','ACTIVE','SUSPENDED','CANCELLED') NOT NULL DEFAULT 'PENDING',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `restaurants` (`id`, `name`, `slug`, `owner_name`, `email`, `phone`, `address`, `city`, `state`, `postal_code`, `country`, `description`, `status`) VALUES
(1, 'Spice Garden', 'spice-garden', 'Raj Sharma', 'raj@spicegarden.local', '9876543210', '12 MG Road', 'Bengaluru', 'Karnataka', '560001', 'India', 'A fine dining Indian restaurant.', 'ACTIVE');

-- ------------------------------------------------------------
-- RESTAURANT STAFF USERS
-- ------------------------------------------------------------
CREATE TABLE `restaurant_staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(180) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('OWNER','MANAGER','CASHIER') NOT NULL DEFAULT 'CASHIER',
  `status` enum('ACTIVE','INACTIVE','SUSPENDED') NOT NULL DEFAULT 'ACTIVE',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `restaurant_id` (`restaurant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `restaurant_staff` (`id`, `restaurant_id`, `name`, `email`, `password_hash`, `role`) VALUES
(1, 1, 'Raj Sharma', 'owner@spicegarden.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'OWNER'),
(2, 1, 'Anita Verma', 'manager@spicegarden.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'MANAGER'),
(3, 1, 'Ravi Kumar', 'cashier@spicegarden.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CASHIER');

-- ------------------------------------------------------------
-- ROLES / PERMISSIONS
-- ------------------------------------------------------------
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `key` varchar(60) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `roles` (`id`, `name`, `key`, `description`) VALUES
(1, 'Owner', 'OWNER', 'Full restaurant administration'),
(2, 'Manager', 'MANAGER', 'Restaurant operations manager'),
(3, 'Cashier', 'CASHIER', 'Orders, billing, payments and coupons');

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `key` varchar(100) NOT NULL,
  `group` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `permissions` (`id`, `name`, `key`, `group`) VALUES
(1, 'Dashboard', 'dashboard', 'core'),
(2, 'Restaurant Profile', 'restaurant', 'core'),
(3, 'Tables', 'tables', 'core'),
(4, 'QR Codes', 'qr', 'core'),
(5, 'Categories', 'categories', 'menu'),
(6, 'Cuisines', 'cuisines', 'menu'),
(7, 'Foods', 'foods', 'menu'),
(8, 'Orders', 'orders', 'operations'),
(9, 'Kitchen', 'kitchen', 'operations'),
(10, 'Games', 'games', 'gamification'),
(11, 'Rewards', 'rewards', 'gamification'),
(12, 'Coupons', 'coupons', 'promotions'),
(13, 'Campaigns', 'campaigns', 'promotions'),
(14, 'Reviews', 'reviews', 'feedback'),
(15, 'Analytics', 'analytics', 'reports'),
(16, 'Staff', 'staff', 'administration'),
(17, 'Permissions', 'permissions', 'administration'),
(18, 'Billing', 'billing', 'billing'),
(19, 'Payments', 'payments', 'billing'),
(20, 'Settings', 'settings', 'administration');

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9),
(1, 10), (1, 11), (1, 12), (1, 13), (1, 14), (1, 15), (1, 16), (1, 17), (1, 18), (1, 19), (1, 20),
(2, 1), (2, 2), (2, 3), (2, 4), (2, 5), (2, 6), (2, 7), (2, 8), (2, 9),
(2, 10), (2, 11), (2, 12), (2, 14), (2, 15),
(3, 1), (3, 8), (3, 12), (3, 18), (3, 19);

-- ------------------------------------------------------------
-- SUBSCRIPTION PLANS
-- ------------------------------------------------------------
CREATE TABLE `subscription_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `billing_cycle` enum('MONTHLY','YEARLY') NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `duration_days` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
  `max_tables` int(11) NOT NULL DEFAULT 20,
  `max_staff` int(11) NOT NULL DEFAULT 3,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `subscription_plans` (`id`, `name`, `slug`, `billing_cycle`, `price`, `duration_days`, `description`, `max_tables`, `max_staff`) VALUES
(1, 'Monthly Basic', 'monthly-basic', 'MONTHLY', 999.00, 30, 'QR ordering, menu, orders, kitchen and basic billing', 10, 3),
(2, 'Monthly Standard', 'monthly-standard', 'MONTHLY', 1999.00, 30, 'Everything in Basic plus games, coupons, reviews and basic analytics', 25, 8),
(3, 'Monthly Premium', 'monthly-premium', 'MONTHLY', 3499.00, 30, 'Everything in Standard plus advanced analytics, campaigns and reports', 100, 20),
(4, 'Yearly Basic', 'yearly-basic', 'YEARLY', 9990.00, 365, 'Yearly QR ordering, menu, orders, kitchen and basic billing', 10, 3),
(5, 'Yearly Standard', 'yearly-standard', 'YEARLY', 19990.00, 365, 'Yearly Standard plan with games, coupons and analytics', 25, 8),
(6, 'Yearly Premium', 'yearly-premium', 'YEARLY', 34990.00, 365, 'Yearly Premium plan with all features', 100, 20);

-- ------------------------------------------------------------
-- SUBSCRIPTION PLAN FEATURES  <-- Fixed: id is AUTO_INCREMENT
-- ------------------------------------------------------------
CREATE TABLE `subscription_plan_features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL,
  `feature_key` varchar(100) NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `limit_value` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `plan_feature` (`plan_id`,`feature_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `subscription_plan_features` (`plan_id`, `feature_key`, `is_enabled`, `limit_value`) VALUES
(1, 'qr_ordering', 1, NULL),
(1, 'digital_menu', 1, NULL),
(1, 'kitchen', 1, NULL),
(1, 'billing', 1, NULL),
(1, 'games', 0, NULL),
(1, 'coupons', 0, NULL),
(1, 'reviews', 1, NULL),
(1, 'campaigns', 0, NULL),
(1, 'analytics', 1, NULL),
(1, 'advanced_analytics', 0, NULL),
(2, 'qr_ordering', 1, NULL),
(2, 'digital_menu', 1, NULL),
(2, 'kitchen', 1, NULL),
(2, 'billing', 1, NULL),
(2, 'games', 1, NULL),
(2, 'coupons', 1, NULL),
(2, 'reviews', 1, NULL),
(2, 'campaigns', 0, NULL),
(2, 'analytics', 1, NULL),
(2, 'advanced_analytics', 0, NULL),
(3, 'qr_ordering', 1, NULL),
(3, 'digital_menu', 1, NULL),
(3, 'kitchen', 1, NULL),
(3, 'billing', 1, NULL),
(3, 'games', 1, NULL),
(3, 'coupons', 1, NULL),
(3, 'reviews', 1, NULL),
(3, 'campaigns', 1, NULL),
(3, 'analytics', 1, NULL),
(3, 'advanced_analytics', 1, NULL),
(4, 'qr_ordering', 1, NULL),
(4, 'digital_menu', 1, NULL),
(4, 'kitchen', 1, NULL),
(4, 'billing', 1, NULL),
(4, 'games', 0, NULL),
(4, 'coupons', 0, NULL),
(4, 'reviews', 1, NULL),
(4, 'campaigns', 0, NULL),
(4, 'analytics', 1, NULL),
(4, 'advanced_analytics', 0, NULL),
(5, 'qr_ordering', 1, NULL),
(5, 'digital_menu', 1, NULL),
(5, 'kitchen', 1, NULL),
(5, 'billing', 1, NULL),
(5, 'games', 1, NULL),
(5, 'coupons', 1, NULL),
(5, 'reviews', 1, NULL),
(5, 'campaigns', 0, NULL),
(5, 'analytics', 1, NULL),
(5, 'advanced_analytics', 0, NULL),
(6, 'qr_ordering', 1, NULL),
(6, 'digital_menu', 1, NULL),
(6, 'kitchen', 1, NULL),
(6, 'billing', 1, NULL),
(6, 'games', 1, NULL),
(6, 'coupons', 1, NULL),
(6, 'reviews', 1, NULL),
(6, 'campaigns', 1, NULL),
(6, 'analytics', 1, NULL),
(6, 'advanced_analytics', 1, NULL);

-- ------------------------------------------------------------
-- RESTAURANT SUBSCRIPTIONS
-- ------------------------------------------------------------
CREATE TABLE `restaurant_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('PENDING','ACTIVE','EXPIRED','SUSPENDED','CANCELLED') NOT NULL DEFAULT 'PENDING',
  `payment_status` enum('PENDING','PAID','FAILED','REFUNDED') NOT NULL DEFAULT 'PENDING',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `plan_id` (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `restaurant_subscriptions` (`id`, `restaurant_id`, `plan_id`, `start_date`, `end_date`, `status`, `payment_status`) VALUES
(1, 1, 3, '2026-08-01', '2026-08-31', 'ACTIVE', 'PAID');

-- ------------------------------------------------------------
-- RESTAURANT FEATURE OVERRIDES  <-- Fixed: id is AUTO_INCREMENT
-- ------------------------------------------------------------
CREATE TABLE `restaurant_features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `feature_key` varchar(100) NOT NULL,
  `override_enabled` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurant_feature` (`restaurant_id`,`feature_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `restaurant_features` (`restaurant_id`, `feature_key`, `override_enabled`) VALUES
(1, 'campaigns', 0),
(1, 'games', 1);

-- ------------------------------------------------------------
-- SUBSCRIPTION PAYMENTS
-- ------------------------------------------------------------
CREATE TABLE `subscription_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_subscription_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(80) DEFAULT NULL,
  `transaction_id` varchar(180) DEFAULT NULL,
  `status` enum('PENDING','SUCCESS','FAILED','REFUNDED') NOT NULL DEFAULT 'PENDING',
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `restaurant_subscription_id` (`restaurant_subscription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `subscription_payments` (`id`, `restaurant_subscription_id`, `amount`, `payment_method`, `transaction_id`, `status`, `paid_at`) VALUES
(1, 1, 3499.00, 'UPI', 'DEMO-TXN-001', 'SUCCESS', '2026-08-01 10:00:00');

-- ------------------------------------------------------------
-- TABLES
-- ------------------------------------------------------------
CREATE TABLE `tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `table_number` varchar(30) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 2,
  `status` enum('AVAILABLE','OCCUPIED','RESERVED','INACTIVE') NOT NULL DEFAULT 'AVAILABLE',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurant_table` (`restaurant_id`,`table_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tables` (`id`, `restaurant_id`, `table_number`, `capacity`) VALUES
(1, 1, 'T1', 2),
(2, 1, 'T2', 4),
(3, 1, 'T3', 6);

-- ------------------------------------------------------------
-- QR CODES
-- ------------------------------------------------------------
CREATE TABLE `qr_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `token` varchar(180) NOT NULL,
  `qr_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  UNIQUE KEY `table_id` (`table_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `qr_codes` (`id`, `restaurant_id`, `table_id`, `token`) VALUES
(1, 1, 1, 'dinex_spice_garden_t1_7f9a41b2c3d4e5f6'),
(2, 1, 2, 'dinex_spice_garden_t2_8a0b1c2d3e4f5a6b'),
(3, 1, 3, 'dinex_spice_garden_t3_9b1c2d3e4f5a6b7c');

-- ------------------------------------------------------------
-- TABLE SESSIONS
-- ------------------------------------------------------------
CREATE TABLE `table_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `session_token` varchar(180) NOT NULL,
  `status` enum('ACTIVE','CLOSED','EXPIRED') NOT NULL DEFAULT 'ACTIVE',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `closed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_token` (`session_token`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `table_id` (`table_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- CATEGORIES
-- ------------------------------------------------------------
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurant_category` (`restaurant_id`,`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`id`, `restaurant_id`, `name`, `slug`) VALUES
(1, 1, 'Starters', 'starters'),
(2, 1, 'Main Course', 'main-course'),
(3, 1, 'Breads', 'breads'),
(4, 1, 'Desserts', 'desserts'),
(5, 1, 'Beverages', 'beverages');

-- ------------------------------------------------------------
-- CUISINES
-- ------------------------------------------------------------
CREATE TABLE `cuisines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurant_cuisine` (`restaurant_id`,`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `cuisines` (`id`, `restaurant_id`, `name`, `slug`) VALUES
(1, 1, 'North Indian', 'north-indian'),
(2, 1, 'Chinese', 'chinese'),
(3, 1, 'South Indian', 'south-indian'),
(4, 1, 'Continental', 'continental');

-- ------------------------------------------------------------
-- FOODS
-- ------------------------------------------------------------
CREATE TABLE `foods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `cuisine_id` int(11) DEFAULT NULL,
  `name` varchar(180) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `description` text DEFAULT NULL,
  `food_type` enum('VEG','NON_VEG','EGG','VEGAN') NOT NULL DEFAULT 'VEG',
  `price` decimal(10,2) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 5.00,
  `image` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `is_chef_special` tinyint(1) NOT NULL DEFAULT 0,
  `is_signature` tinyint(1) NOT NULL DEFAULT 0,
  `is_best_seller` tinyint(1) NOT NULL DEFAULT 0,
  `is_trending` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurant_food_slug` (`restaurant_id`,`slug`),
  KEY `category_id` (`category_id`),
  KEY `cuisine_id` (`cuisine_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `foods` (`id`, `restaurant_id`, `category_id`, `cuisine_id`, `name`, `slug`, `description`, `food_type`, `price`, `tax_rate`, `is_chef_special`, `is_signature`, `is_best_seller`, `is_trending`) VALUES
(1, 1, 1, 1, 'Paneer Tikka', 'paneer-tikka', 'Char-grilled cottage cheese with spices', 'VEG', 249.00, 5.00, 1, 1, 1, 1),
(2, 1, 1, 2, 'Chicken Spring Roll', 'chicken-spring-roll', 'Crispy rolls stuffed with chicken', 'NON_VEG', 199.00, 5.00, 0, 0, 0, 1),
(3, 1, 2, 1, 'Butter Chicken', 'butter-chicken', 'Creamy tomato gravy with tender chicken', 'NON_VEG', 349.00, 5.00, 1, 1, 1, 1),
(4, 1, 2, 1, 'Dal Makhani', 'dal-makhani', 'Slow-cooked black lentils with butter', 'VEG', 279.00, 5.00, 0, 0, 1, 1),
(5, 1, 3, 1, 'Garlic Naan', 'garlic-naan', 'Soft naan topped with garlic', 'VEG', 69.00, 5.00, 0, 0, 1, 0),
(6, 1, 4, 1, 'Gulab Jamun', 'gulab-jamun', 'Warm milk-solid dumplings in sugar syrup', 'VEG', 129.00, 5.00, 0, 0, 0, 1),
(7, 1, 5, 1, 'Masala Chaas', 'masala-chaas', 'Spiced buttermilk', 'VEG', 79.00, 5.00, 0, 0, 0, 0),
(8, 1, 2, 3, 'Masala Dosa', 'masala-dosa', 'Crispy rice crepe with spiced potato', 'VEG', 169.00, 5.00, 0, 1, 0, 1);

-- ------------------------------------------------------------
-- FOOD VARIANTS
-- ------------------------------------------------------------
CREATE TABLE `food_variants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `food_id` int(11) NOT NULL,
  `variant_name` varchar(100) NOT NULL,
  `additional_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `food_id` (`food_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `food_variants` (`id`, `food_id`, `variant_name`, `additional_price`) VALUES
(1, 1, 'Regular', 0.00),
(2, 1, 'Spicy', 20.00),
(3, 3, 'Half', 0.00),
(4, 3, 'Full', 100.00);

-- ------------------------------------------------------------
-- FOOD ADDONS
-- ------------------------------------------------------------
CREATE TABLE `food_addons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `food_id` int(11) NOT NULL,
  `addon_name` varchar(120) NOT NULL,
  `additional_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `food_id` (`food_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `food_addons` (`id`, `food_id`, `addon_name`, `additional_price`) VALUES
(1, 1, 'Extra Cheese', 35.00),
(2, 3, 'Butter Naan', 45.00),
(3, 4, 'Extra Cream', 25.00);

-- ------------------------------------------------------------
-- ORDERS
-- ------------------------------------------------------------
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `order_number` varchar(50) NOT NULL,
  `status` enum('PLACED','ACCEPTED','PREPARING','READY','SERVED','COMPLETED','CANCELLED') NOT NULL DEFAULT 'PLACED',
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `special_instructions` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `table_id` (`table_id`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- ORDER ITEMS
-- ------------------------------------------------------------
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `food_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `addons` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `food_id` (`food_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- ORDER STATUS HISTORY
-- ------------------------------------------------------------
CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `status` varchar(40) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- GAMES
-- ------------------------------------------------------------
CREATE TABLE `games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `game_key` varchar(80) NOT NULL,
  `name` varchar(120) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `config` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurant_game` (`restaurant_id`,`game_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `games` (`id`, `restaurant_id`, `game_key`, `name`) VALUES
(1, 1, 'spin-wheel', 'Spin the Wheel'),
(2, 1, 'instant-lottery', 'Instant Lottery'),
(3, 1, 'slot-machine', 'Slot Machine'),
(4, 1, 'catch-win', 'Catch & Win'),
(5, 1, 'snakes-ladders', 'Snakes & Ladders'),
(6, 1, 'tap-speed', 'Tap Speed');

-- ------------------------------------------------------------
-- GAME REWARDS  <-- Fixed: id is AUTO_INCREMENT
-- ------------------------------------------------------------
CREATE TABLE `game_rewards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(11) NOT NULL,
  `reward_type` enum('COUPON','DISCOUNT','FREE_ITEM','NONE') NOT NULL DEFAULT 'NONE',
  `value` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- GAME SESSIONS
-- ------------------------------------------------------------
CREATE TABLE `game_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `table_session_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `result_payload` text DEFAULT NULL,
  `reward_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `table_session_id` (`table_session_id`),
  KEY `game_id` (`game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- COUPONS
-- ------------------------------------------------------------
CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `table_session_id` int(11) DEFAULT NULL,
  `code` varchar(80) NOT NULL,
  `discount_type` enum('PERCENT','FIXED') NOT NULL DEFAULT 'PERCENT',
  `discount_value` decimal(10,2) NOT NULL,
  `min_bill_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `valid_from` datetime NOT NULL,
  `valid_until` datetime NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `table_session_id` (`table_session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- COUPON REDEMPTIONS
-- ------------------------------------------------------------
CREATE TABLE `coupon_redemptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `bill_id` int(11) DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `coupon_id` (`coupon_id`),
  KEY `order_id` (`order_id`),
  KEY `bill_id` (`bill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- BILLS
-- ------------------------------------------------------------
CREATE TABLE `bills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `table_session_id` int(11) DEFAULT NULL,
  `bill_number` varchar(80) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('PENDING','PAID','CANCELLED') NOT NULL DEFAULT 'PENDING',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `bill_number` (`bill_number`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `order_id` (`order_id`),
  KEY `table_session_id` (`table_session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- PAYMENTS
-- ------------------------------------------------------------
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(80) DEFAULT NULL,
  `transaction_id` varchar(180) DEFAULT NULL,
  `status` enum('PENDING','SUCCESS','FAILED','REFUNDED') NOT NULL DEFAULT 'PENDING',
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `bill_id` (`bill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- REVIEWS
-- ------------------------------------------------------------
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `table_session_id` int(11) DEFAULT NULL,
  `rating` tinyint(1) NOT NULL DEFAULT 5,
  `feedback` text DEFAULT NULL,
  `status` enum('PENDING','APPROVED','REJECTED') NOT NULL DEFAULT 'PENDING',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `table_session_id` (`table_session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- CAMPAIGNS
-- ------------------------------------------------------------
CREATE TABLE `campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `name` varchar(180) NOT NULL,
  `type` varchar(80) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- NOTIFICATIONS
-- ------------------------------------------------------------
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) DEFAULT NULL,
  `title` varchar(180) NOT NULL,
  `message` text DEFAULT NULL,
  `type` varchar(60) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- SETTINGS
-- ------------------------------------------------------------
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(120) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES
(1, 'platform_name', 'DineX'),
(2, 'tagline', 'SCAN. ORDER. PLAY. ENJOY.'),
(3, 'session_retention_hours', '24'),
(4, 'default_tax_rate', '5.00');

-- ------------------------------------------------------------
-- AUDIT LOGS
-- ------------------------------------------------------------
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actor_type` enum('FOUNDER','RESTAURANT','CUSTOMER','SYSTEM') NOT NULL,
  `actor_id` int(11) DEFAULT NULL,
  `restaurant_id` int(11) DEFAULT NULL,
  `action` varchar(180) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(60) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `actor_type` (`actor_type`),
  KEY `restaurant_id` (`restaurant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- RATE LIMIT
-- ------------------------------------------------------------
CREATE TABLE `rate_limit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(180) NOT NULL,
  `action` varchar(100) NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `window_start` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `identifier_action` (`identifier`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- FOREIGN KEYS
-- ============================================================
ALTER TABLE `restaurant_staff`
  ADD CONSTRAINT `staff_restaurant_fk` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `role_permissions`
  ADD CONSTRAINT `rp_role_fk` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rp_permission_fk` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `subscription_plan_features`
  ADD CONSTRAINT `spf_plan_fk` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `restaurant_subscriptions`
  ADD CONSTRAINT `rs_restaurant_fk` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rs_plan_fk` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `restaurant_features`
  ADD CONSTRAINT `rf_restaurant_fk` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `subscription_payments`
  ADD CONSTRAINT `sp_sub_fk` FOREIGN KEY (`restaurant_subscription_id`) REFERENCES `restaurant_subscriptions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `tables`
  ADD CONSTRAINT `tables_restaurant_fk` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `qr_codes`
  ADD CONSTRAINT `qr_restaurant_fk` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `qr_table_fk` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `table_sessions`
  ADD CONSTRAINT `ts_restaurant_fk` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ts_table_fk` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `categories`
  ADD CONSTRAINT `cat_restaurant_fk` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cuisines`
  ADD CONSTRAINT `cuisine_restaurant_fk` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `foods`
  ADD CONSTRAINT `food_restaurant_fk` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `food_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `food_cuisine_fk` FOREIGN KEY (`cuisine_id`) REFERENCES `cuisines` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `food_variants`
  ADD CONSTRAINT `fv_food_fk` FOREIGN KEY (`food_id`) REFERENCES `foods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `food_addons`
  ADD CONSTRAINT `fa_food_fk` FOREIGN KEY (`food_id`) REFERENCES `foods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `orders`
  ADD CONSTRAINT `orders_restaurant_fk` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_table_fk` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_session_fk` FOREIGN KEY (`session_id`) REFERENCES `table_sessions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `order_items`
  ADD CONSTRAINT `oi_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `oi_food_fk` FOREIGN KEY (`food_id`) REFERENCES `foods` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `order_status_history`
  ADD CONSTRAINT `osh_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `games`
  ADD CONSTRAINT `games_restaurant_fk` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `game_rewards`
  ADD CONSTRAINT `gr_game_fk` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `game_sessions`
  ADD CONSTRAINT `gs_restaurant_fk` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `gs_table_session_fk` FOREIGN KEY (`table_session_id`) REFERENCES `table_sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `gs_game_fk` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `coupons`
  ADD CONSTRAINT `coupon_restaurant_fk` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `coupon_table_session_fk` FOREIGN KEY (`table_session_id`) REFERENCES `table_sessions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `coupon_redemptions`
  ADD CONSTRAINT `cr_coupon_fk` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cr_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `cr_bill_fk` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `bills`
  ADD CONSTRAINT `bills_restaurant_fk` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bills_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `bills_table_session_fk` FOREIGN KEY (`table_session_id`) REFERENCES `table_sessions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `payments`
  ADD CONSTRAINT `payments_restaurant_fk` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `payments_bill_fk` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_restaurant_fk` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reviews_table_session_fk` FOREIGN KEY (`table_session_id`) REFERENCES `table_sessions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `campaigns`
  ADD CONSTRAINT `campaigns_restaurant_fk` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_restaurant_fk` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_restaurant_fk` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ============================================================
-- Part 2 Seed Data: Game Rewards
-- ============================================================
INSERT INTO `game_rewards` (`game_id`, `reward_type`, `value`, `is_active`) VALUES
(1, 'COUPON', '10', 1),
(1, 'DISCOUNT', '5', 1),
(2, 'COUPON', '15', 1),
(2, 'FREE_ITEM', NULL, 1),
(3, 'DISCOUNT', '20', 1),
(3, 'COUPON', '25', 1),
(4, 'COUPON', '10', 1),
(4, 'DISCOUNT', '15', 1),
(5, 'COUPON', '20', 1),
(5, 'FREE_ITEM', NULL, 1),
(6, 'DISCOUNT', '5', 1),
(6, 'COUPON', '10', 1);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
