# 🍽️ DineX – Scan. Order. Play. Enjoy.

DineX is a complete, secure, production-ready QR-based restaurant ordering, kitchen, billing, gamification, coupon, subscription, and analytics platform.

It is built using PHP, MySQL, HTML, CSS, and Vanilla JavaScript, with no Node.js, Laravel, Python, or frontend frameworks.

DineX is designed to run on XAMPP, shared hosting, and cPanel environments.

---

# 🚀 Overview

DineX provides a complete digital restaurant experience where customers can scan a table QR code, browse the digital menu, add food to their cart, place orders, track order status, play games, receive rewards, and complete billing without creating an account.

The platform provides two administration levels.

## Founder / Platform Admin

The Founder manages the complete DineX platform, including:

* Restaurant accounts
* Restaurant approval and suspension
* Subscription plans
* Restaurant subscriptions
* Feature access
* Feature overrides
* Payments
* Platform analytics
* Audit logs
* Platform settings

## Restaurant Admin

Restaurant administrators manage daily restaurant operations using role-based access:

* Owner
* Manager
* Cashier

---

# 🔐 Privacy First

DineX is designed around anonymous customer interactions.

Customers do not need:

* Registration
* Login
* Customer account
* Customer profile
* Loyalty account
* Personal information

Customer sessions are associated with restaurant tables using secure session tokens.

---

# ✨ Features

## 🔍 QR-Based Ordering

* Secure QR code generation for restaurant tables
* Unique random QR tokens
* Table identification using QR tokens
* Anonymous customer sessions
* Mobile-first digital menu
* Live food search
* Debounced search
* Category filtering
* Cuisine filtering
* Veg / Non-Veg / Egg / Vegan filtering
* Price filtering
* Sorting options
* Trending foods
* Best sellers
* Chef's specials
* Signature dishes
* Food variants
* Food add-ons
* Server-side cart validation
* Server-side price validation

---

## 🍳 Kitchen & Order Management

DineX includes a Kitchen Display System for restaurant staff.

### Order Workflow

```text
PLACED
   ↓
ACCEPTED
   ↓
PREPARING
   ↓
READY
   ↓
SERVED
   ↓
COMPLETED
```

Orders can also be cancelled when required.

Features include:

* Kitchen Display System
* Real-time order status updates
* Customer order tracking
* Table status management
* Order history

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

Features include:

* Minimum order eligibility
* Daily play limits
* One game per order rules
* Server-side reward calculation
* Automatic coupon generation
* Protection against client-side tampering

---

## 🎟️ Coupons & Billing

Features include:

* Unique coupon codes
* Single-use coupons
* Time-limited coupons
* Percentage discounts
* Fixed discounts
* Coupon validation
* Double-use prevention
* Bill generation
* Tax calculation
* Discount calculation
* Cashier payment panel
* Completed bill history

---

## 📊 Analytics

Restaurant analytics include:

* Daily sales
* Monthly sales
* Order reports
* Popular foods
* Category sales
* Cuisine sales
* Food type performance
* Peak hours
* Table revenue
* Game analytics
* Coupon analytics
* Chart.js visualisations

---

## 👥 Role-Based Access

### Owner

* Full restaurant management
* Staff management
* Settings
* Analytics
* Billing

### Manager

* Operational management
* Menu management
* Orders
* Kitchen
* Games
* Coupons

### Cashier

* Orders
* Billing
* Payments
* Coupons

Permissions are enforced server-side.

---

# 🔒 Security

DineX implements multiple layers of security.

* PDO prepared statements
* SQL injection prevention
* CSRF tokens
* XSS escaping
* IDOR protection
* Restaurant ownership checks
* Secure sessions
* HttpOnly cookies
* SameSite cookies
* Session regeneration
* File upload validation
* MIME type validation
* Rate limiting
* Audit logging
* Server-side price validation
* Server-side coupon validation
* Server-side subscription validation
* Server-side feature access checks

---

# 🛠️ Technology Stack

| Layer           | Technology                      |
| --------------- | ------------------------------- |
| Backend         | PHP 8+                          |
| Database        | MySQL 8+ / MariaDB              |
| Frontend        | HTML5, CSS3, Vanilla JavaScript |
| Database Access | PDO                             |
| AJAX            | Fetch API                       |
| CSS             | Tailwind CSS (CDN)              |
| Icons           | Font Awesome (CDN)              |
| Charts          | Chart.js (CDN)                  |
| Alerts          | SweetAlert2 (CDN)               |
| QR Codes        | QRCode.js (CDN)                 |

No Node.js.

No Laravel.

No Python.

---

# 📁 Project Structure

```text
dinex/
├── admin/
│   ├── founder/
│   ├── restaurant/
│   └── templates/
├── api/
│   ├── customer/
│   ├── founder/
│   ├── restaurant/
│   └── ...
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── uploads/
├── config/
├── customer/
├── database/
│   └── dinex.sql
├── games/
│   ├── spin-wheel/
│   ├── lottery/
│   ├── slot-machine/
│   ├── catch-win/
│   ├── snakes-ladders/
│   └── tap-speed/
├── includes/
├── logs/
├── documentation/
├── .htaccess
├── index.php
├── register.php
└── README.md
```

---

# 🗄️ Database

The complete DineX database schema is stored at:

`database/dinex.sql`

The database includes tables for:

* Platform users
* Restaurants
* Restaurant staff
* Roles
* Permissions
* Role permissions
* Subscription plans
* Subscription plan features
* Restaurant subscriptions
* Restaurant feature overrides
* Subscription payments
* Restaurant tables
* QR codes
* Table sessions
* Categories
* Cuisines
* Foods
* Food variants
* Food add-ons
* Orders
* Order items
* Order status history
* Games
* Game rewards
* Game sessions
* Coupons
* Coupon redemptions
* Bills
* Payments
* Reviews
* Campaigns
* Notifications
* Settings
* Audit logs
* Rate limiting

---

# 📦 Installation

## 1. Upload the Project

Copy the complete DineX project into your web server directory.

### XAMPP

```text
C:\xampp\htdocs\dinex
```

### Shared Hosting

```text
public_html/dinex
```

---

## 2. Create the Database

Open phpMyAdmin and create a database.

Example:

`dinex`

Then import:

`database/dinex.sql`

The SQL file contains the complete database schema, relationships, indexes, foreign keys, and demo seed data.

---

## 3. Configure Database Credentials

Open:

`config/config.php`

Configure your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'dinex');
define('DB_USER', 'root');
define('DB_PASS', '');
```

For production hosting, replace these values with the credentials provided by your hosting provider.

---

## 4. Configure Base URL

If the project is running locally at:

`http://localhost/dinex`

set:

```php
define('BASE_URL', '/dinex');
```

If DineX is installed at the domain root, set:

```php
define('BASE_URL', '');
```

---

## 5. Configure Writable Directories

The following directories must be writable by the web server:

```text
logs/
assets/uploads/
```

The application uses these directories for logging and uploaded food images.

---

# 📱 Mobile QR Code Testing

QR codes containing:

`localhost`

or:

`127.0.0.1`

will not work when scanned from a mobile phone.

This happens because `localhost` on the phone refers to the phone itself.

For local network testing, use your computer's LAN IP address.

Example:

`http://192.168.1.100/dinex/customer/menu.php?token=YOUR_TOKEN`

Replace `192.168.1.100` with your computer's actual local IP address.

Your computer and mobile phone must be connected to the same Wi-Fi or LAN network.

Make sure Apache is running and Windows Firewall allows Apache/network access.

---

# 🔑 Demo Accounts

The database includes demo accounts.

Default demo password:

`password`

| Role    | Email                       |
| ------- | --------------------------- |
| Founder | `founder@dinex.local`       |
| Owner   | `owner@spicegarden.local`   |
| Manager | `manager@spicegarden.local` |
| Cashier | `cashier@spicegarden.local` |

**Change all demo passwords before production deployment.**

---

# 🍽️ Demo Restaurant

The database contains a demo restaurant.

| Field      | Value        |
| ---------- | ------------ |
| Restaurant | Spice Garden |
| Owner      | Raj Sharma   |
| City       | Bengaluru    |
| State      | Karnataka    |
| Country    | India        |

---

# 🔳 Demo QR Tokens

The database includes three demo table QR tokens.

| Table    | Token                                    |
| -------- | ---------------------------------------- |
| Table 01 | `dinex_spice_garden_t1_7f9a41b2c3d4e5f6` |
| Table 02 | `dinex_spice_garden_t2_8a0b1c2d3e4f5a6b` |
| Table 03 | `dinex_spice_garden_t3_9b1c2d3e4f5a6b7c` |

Customer menu URL pattern:

`/customer/menu.php?token=<token>`

Example:

`http://localhost/dinex/customer/menu.php?token=dinex_spice_garden_t1_7f9a41b2c3d4e5f6`

---

# 🧪 Testing

Testing documentation is available at:

`documentation/testing.md`

The testing process covers:

* Founder authentication
* Restaurant authentication
* Role isolation
* Permission management
* QR token validation
* Anonymous table sessions
* Menu browsing
* Search
* Filtering
* Food variants
* Food add-ons
* Cart validation
* Order placement
* Kitchen workflow
* Order tracking
* Game eligibility
* Game reward validation
* Coupon generation
* Coupon redemption
* Billing
* Payments
* Subscription lifecycle
* Feature gating
* SQL injection protection
* XSS protection
* CSRF protection
* IDOR protection
* File upload validation
* Rate limiting

---

# 🔐 Security Architecture

DineX follows a server-side security model.

Important business operations are never trusted from the browser.

Typical request flow:

```text
Client Request
      ↓
Authentication
      ↓
Authorization
      ↓
CSRF Validation
      ↓
Input Validation
      ↓
Database Validation
      ↓
Business Logic
      ↓
Database Transaction
      ↓
Response
```

Prices, permissions, coupons, game rewards, subscriptions, and feature access are validated server-side.

---

# 💡 Subscription System

DineX supports Monthly and Yearly subscription plans.

## Monthly Plans

* Monthly Basic
* Monthly Standard
* Monthly Premium

## Yearly Plans

* Yearly Basic
* Yearly Standard
* Yearly Premium

Subscription features can include:

* QR ordering
* Digital menu
* Kitchen
* Billing
* Games
* Coupons
* Reviews
* Campaigns
* Analytics
* Advanced analytics

Feature access is enforced server-side.

Restaurant-specific feature overrides are also supported.

---

# 📈 Multi-Restaurant Architecture

DineX is designed as a multi-restaurant platform.

Most operational tables contain a `restaurant_id` field.

This provides restaurant-level data isolation and allows multiple restaurants to use the same DineX installation and database.

Founder-level administrators manage the platform, while restaurant users can access only their authorized restaurant data.

---

# 🚀 Production Deployment

Before deploying DineX to production:

1. Change all demo passwords.
2. Configure production database credentials.
3. Configure the correct `BASE_URL`.
4. Disable unnecessary PHP error display.
5. Enable HTTPS.
6. Configure proper file permissions.
7. Protect configuration files.
8. Configure regular database backups.
9. Configure log cleanup.
10. Configure session cleanup.
11. Configure cron jobs if required.
12. Verify CSRF protection.
13. Verify authentication and authorization.
14. Test IDOR protection.
15. Test file upload restrictions.
16. Test rate limiting.
17. Remove demo/test data if not required.

Production deployment documentation:

`documentation/deployment.md`

---

# 📋 Development Principles

DineX follows these development principles:

* Server-side validation
* PDO prepared statements
* Secure authentication
* Role-based authorization
* Restaurant-level data isolation
* CSRF protection
* XSS prevention
* IDOR protection
* Secure sessions
* Secure file uploads
* Audit logging
* Rate limiting
* Transaction-safe database operations
* Anonymous customer sessions
* Minimal customer data collection

---

# 🤝 Contributing

Contributions are welcome.

For major changes, create an issue first to discuss the proposed change.

When submitting code:

* Follow the existing project structure.
* Maintain existing security protections.
* Do not bypass authorization checks.
* Do not trust client-side prices or permissions.
* Use PDO prepared statements.
* Validate all user input.
* Add CSRF protection to state-changing requests.
* Keep restaurant data isolated.

---

# 📄 License

MIT License.

You are free to use, modify, and distribute this software according to the terms of the MIT License.

---

# 🍽️ DineX

**SCAN. ORDER. PLAY. ENJOY.**

DineX brings QR ordering, digital menus, kitchen operations, billing, payments, gamification, coupons, subscriptions, and analytics together into one restaurant management platform.

Built with:

* PHP 8+
* MySQL / MariaDB
* HTML5
* CSS3
* Vanilla JavaScript
* PDO
* Fetch API

No Node.js.

No Laravel.

No Python.

Designed for restaurants. Built for simplicity. Secured for real-world use.
