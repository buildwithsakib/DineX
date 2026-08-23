# 🍽️ DineX – Scan. Order. Play. Enjoy.

DineX is a complete, secure, production-ready QR-based restaurant ordering, kitchen, billing, gamification, coupon, subscription, and analytics platform.

It is built using PHP, MySQL, HTML, CSS, and Vanilla JavaScript, with no Node.js, Laravel, Python, or frontend frameworks.

DineX is designed to run on XAMPP, shared hosting, and cPanel environments.

---

## 🚀 Overview

DineX provides a complete digital restaurant experience where customers can scan a table QR code, browse the digital menu, add food to their cart, place orders, track order status, play games, receive rewards, and complete billing without creating an account.

The platform provides two administration levels:

### Founder / Platform Admin

The Founder manages the complete DineX platform, including:

- Restaurant accounts
- Restaurant approval and suspension
- Subscription plans
- Restaurant subscriptions
- Feature access
- Feature overrides
- Payments
- Platform analytics
- Audit logs
- Platform settings

### Restaurant Admin

Restaurant administrators manage daily restaurant operations using role-based access:

- Owner
- Manager
- Cashier

---

## 🔐 Privacy First

DineX is designed around anonymous customer interactions.

Customers do not need:

- Registration
- Login
- Customer account
- Customer profile
- Loyalty account
- Personal information

Customer sessions are associated with restaurant tables using secure session tokens.

---

# ✨ Features

## 🔍 QR-Based Ordering

- Secure QR code generation for restaurant tables
- Unique random QR tokens
- Table identification using QR tokens
- Anonymous customer sessions
- Mobile-first digital menu
- Live food search
- Debounced search
- Category filtering
- Cuisine filtering
- Veg / Non-Veg / Egg / Vegan filtering
- Price filtering
- Sorting options
- Trending foods
- Best sellers
- Chef's specials
- Signature dishes
- Food variants
- Food add-ons
- Server-side cart validation
- Server-side price validation

---

## 🍳 Kitchen & Order Management

DineX includes a Kitchen Display System for restaurant staff.

### Order Workflow

PLACED → ACCEPTED → PREPARING → READY → SERVED → COMPLETED

Orders can also be cancelled when required.

Features include:

- Kitchen order dashboard
- Real-time order status updates
- Order history
- Customer order tracking
- Table status management
- Order status history
- Restaurant-specific order isolation

---

## 🎮 Gamification

DineX includes six mini-games that can be used to increase customer engagement.

### Available Games

1. Spin the Wheel
2. Instant Lottery
3. Slot Machine
4. Catch & Win
5. Snakes & Ladders
6. Tap Speed

### Game Features

- Game eligibility rules
- Minimum order requirements
- Daily game limits
- One-game-per-order rules
- Server-side reward determination
- Game session tracking
- Automatic coupon generation
- Reward validation
- Game analytics

Game results and rewards are determined server-side to prevent client-side manipulation.

---

## 🎟️ Coupons & Rewards

DineX supports secure restaurant coupons and game-based rewards.

Features include:

- Unique coupon codes
- Percentage discounts
- Fixed discounts
- Minimum bill requirements
- Maximum discount limits
- Start and expiry dates
- Single-use coupons
- Coupon redemption tracking
- Double-use prevention
- Game-generated coupons
- Coupon analytics

---

## 💳 Billing & Payments

DineX provides complete restaurant billing functionality.

Features include:

- Automatic bill generation
- Subtotal calculation
- Tax calculation
- Discount calculation
- Final total calculation
- Unique bill numbers
- Payment records
- Payment methods
- Transaction IDs
- Payment status tracking
- Completed bill history
- Cashier billing panel

Supported payment statuses include:

- PENDING
- SUCCESS
- FAILED
- REFUNDED

---

## 📊 Analytics

DineX provides restaurant-level and platform-level analytics.

### Restaurant Analytics

- Daily sales
- Monthly sales
- Daily orders
- Monthly orders
- Popular foods
- Category performance
- Cuisine performance
- Food type performance
- Peak hours
- Table revenue
- Game performance
- Coupon usage
- Payment analytics

### Platform Analytics

Founder-level analytics include:

- Total restaurants
- Active restaurants
- Suspended restaurants
- Subscription statistics
- Revenue
- Payment statistics
- Feature usage
- Audit activity

Charts are displayed using Chart.js.

---

# 👥 Two-Level Administration

DineX uses a two-level administration architecture.

## Level 1 – Founder / Platform Admin

The Founder has platform-wide access.

Features include:

- Founder login
- Founder dashboard
- Restaurant management
- Restaurant approval
- Restaurant suspension
- Restaurant activation
- Restaurant details
- Subscription management
- Subscription plans
- Plan creation
- Plan editing
- Feature management
- Restaurant feature overrides
- Subscription payments
- Platform analytics
- Audit logs
- Platform settings

---

## Level 2 – Restaurant Admin

Restaurant administrators have role-based access.

### Owner

The Owner has full restaurant administration access.

### Manager

The Manager receives configurable operational permissions.

### Cashier

The Cashier focuses on:

- Orders
- Billing
- Payments
- Coupons

Permissions are controlled using roles and permissions stored in the database.

---

# 🔒 Security

DineX includes multiple security layers.

### Database Security

- PDO database connections
- Prepared statements
- SQL injection protection
- Parameterized queries

### Web Security

- CSRF protection
- XSS protection
- Input validation
- Input sanitization
- Output escaping
- IDOR protection
- Restaurant ownership checks

### Authentication Security

- Secure PHP sessions
- HttpOnly cookies
- SameSite cookies
- Session regeneration
- Password hashing
- Login rate limiting

### File Upload Security

Food image uploads are validated using:

- File extension validation
- MIME type validation
- Allowed image formats
- Upload size restrictions

Supported image formats:

- JPG
- PNG
- WEBP

### Audit & Monitoring

Administrative actions can be recorded through audit logs.

The platform also includes rate-limit tracking for sensitive actions such as authentication.

---

# 🛠️ Technology Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8+ |
| Database | MySQL 8+ / MariaDB |
| Database Access | PDO |
| Frontend | HTML5 |
| Styling | CSS3 / Tailwind CSS CDN |
| JavaScript | Vanilla JavaScript |
| API | REST-style PHP APIs |
| Requests | Fetch API |
| Icons | Font Awesome CDN |
| Charts | Chart.js CDN |
| Alerts | SweetAlert2 CDN |
| QR Generation | QRCode.js CDN |
| Server | Apache |
| Local Development | XAMPP |
| Hosting | Shared Hosting / cPanel |

### No Node.js Required

DineX does not require:

- Node.js
- npm
- Laravel
- React
- Vue
- Angular
- Python

The application can run directly on PHP and MySQL hosting.

---

# 📁 Project Structure

```text
dinex/
│
├── admin/
│   ├── founder/
│   │   ├── login.php
│   │   ├── logout.php
│   │   ├── dashboard.php
│   │   ├── restaurants.php
│   │   ├── restaurant-view.php
│   │   ├── restaurant-create.php
│   │   ├── restaurant-edit.php
│   │   ├── subscriptions.php
│   │   ├── plans.php
│   │   ├── plan-create.php
│   │   ├── plan-edit.php
│   │   ├── features.php
│   │   ├── restaurant-features.php
│   │   ├── payments.php
│   │   ├── analytics.php
│   │   ├── audit-logs.php
│   │   └── settings.php
│   │
│   ├── restaurant/
│   │   ├── login.php
│   │   ├── logout.php
│   │   ├── dashboard.php
│   │   ├── restaurant.php
│   │   ├── tables.php
│   │   ├── qr.php
│   │   ├── categories.php
│   │   ├── cuisines.php
│   │   ├── foods.php
│   │   ├── orders.php
│   │   ├── kitchen.php
│   │   ├── games.php
│   │   ├── rewards.php
│   │   ├── coupons.php
│   │   ├── campaigns.php
│   │   ├── reviews.php
│   │   ├── analytics.php
│   │   ├── staff.php
│   │   ├── permissions.php
│   │   ├── billing.php
│   │   ├── payments.php
│   │   └── settings.php
│   │
│   └── templates/
│
├── api/
│   ├── auth/
│   ├── founder/
│   ├── restaurant/
│   ├── customer/
│   ├── foods/
│   ├── categories/
│   ├── cuisines/
│   ├── orders/
│   ├── tables/
│   ├── games/
│   ├── coupons/
│   ├── billing/
│   ├── subscriptions/
│   └── analytics/
│
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── uploads/
│
├── config/
│   ├── config.php
│   ├── database.php
│   └── constants.php
│
├── customer/
│   ├── index.php
│   ├── menu.php
│   ├── search.php
│   ├── food.php
│   ├── cart.php
│   ├── checkout.php
│   ├── order.php
│   ├── order-status.php
│   ├── games.php
│   ├── coupon.php
│   ├── feedback.php
│   └── session.php
│
├── database/
│   └── dinex.sql
│
├── games/
│   ├── spin-wheel/
│   ├── lottery/
│   ├── slot-machine/
│   ├── catch-win/
│   ├── snakes-ladders/
│   └── tap-speed/
│
├── includes/
│   ├── auth.php
│   ├── session.php
│   ├── csrf.php
│   ├── permissions.php
│   ├── authorization.php
│   ├── subscription.php
│   ├── feature-access.php
│   ├── functions.php
│   ├── validation.php
│   ├── security.php
│   └── rate-limit.php
│
├── logs/
│
├── documentation/
│   ├── testing.md
│   └── deployment.md
│
├── .htaccess
├── index.php
├── register.php
└── README.md

---
## 🗄️ Database

The complete DineX database schema is stored at:

database/dinex.sql

The database includes tables for:

- Platform users
- Restaurants
- Restaurant staff
- Roles
- Permissions
- Role permissions
- Subscription plans
- Subscription plan features
- Restaurant subscriptions
- Restaurant feature overrides
- Subscription payments
- Restaurant tables
- QR codes
- Table sessions
- Categories
- Cuisines
- Foods
- Food variants
- Food add-ons
- Orders
- Order items
- Order status history
- Games
- Game rewards
- Game sessions
- Coupons
- Coupon redemptions
- Bills
- Payments
- Reviews
- Campaigns
- Notifications
- Settings
- Audit logs
- Rate limiting

