# 🍽️ DineX – Scan. Order. Play. Enjoy.

DineX is a complete, secure, production-ready QR-based restaurant ordering, kitchen, billing, gamification, coupon, subscription, and analytics platform.

It is built with **PHP 8+, MySQL/MariaDB, HTML5, CSS3, Vanilla JavaScript, and Fetch API**, with no Node.js, Laravel, Python, or other backend frameworks.

DineX is designed to work on **XAMPP, shared hosting, and cPanel environments**.

---

## ✨ Features

### 🔍 Core QR Ordering

- QR-code-based table identification using secure random tokens.
- Anonymous customer table sessions.
- Mobile-first digital restaurant menu.
- Live food search with debounce.
- Category, cuisine, food type, price, and sorting filters.
- Trending foods.
- Best sellers.
- Chef's specials.
- Signature dishes.
- Food variants.
- Food add-ons.
- Server-side cart validation.
- Server-side price validation to prevent client-side price manipulation.

---

### 🍳 Kitchen & Order Management

- Kitchen Display System (KDS).
- Real-time order status management.
- Complete order workflow:

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

Orders can also be cancelled when permitted.

Features include:

Order management.
Kitchen order queue.
Table status management.
Customer order tracking.
Order status history.
Server-side order validation.
🎮 Gamification

DineX includes six mini games:

Spin the Wheel
Instant Lottery
Slot Machine
Catch & Win
Snakes & Ladders
Tap Speed

Gamification features include:

Minimum-order eligibility rules.
Daily play limits.
One-game-per-order rules.
Server-side game result generation.
Server-side reward validation.
Coupon rewards.
Discount rewards.
Free-item rewards.
Prevention of client-side game-result manipulation.
🎟️ Coupons & Promotions
Unique coupon codes.
Single-use coupons.
Time-limited coupons.
Percentage discounts.
Fixed-value discounts.
Minimum bill requirements.
Maximum discount limits.
Coupon validation.
Coupon redemption tracking.
Double-use prevention.
Campaign management.
Game-generated coupons.
💳 Billing & Payments
Automatic bill generation.
Tax calculation.
Discount calculation.
Coupon discount handling.
Bill number generation.
Pending/paid/cancelled bill states.
Cashier billing panel.
Payment recording.
Transaction ID tracking.
Payment status tracking.
Completed bill history.
📊 Analytics

DineX provides restaurant and platform analytics including:

Daily sales.
Monthly sales.
Daily orders.
Monthly orders.
Popular foods.
Category sales.
Cuisine sales.
Food-type performance.
Peak-hour analysis.
Table revenue.
Game statistics.
Coupon statistics.
Payment statistics.
Revenue reports.
Chart-based visualisations.

Charts are powered by Chart.js.

👥 Administration System

DineX uses a two-level administration architecture.

Level 1 – Founder / Platform Admin

The Founder controls the complete DineX platform.

Features include:

Founder login.
Restaurant management.
Create restaurants.
Edit restaurants.
View restaurants.
Activate restaurants.
Suspend restaurants.
Cancel restaurants.
Subscription management.
Subscription plan management.
Monthly plans.
Yearly plans.
Feature management.
Restaurant feature overrides.
Subscription payments.
Platform analytics.
Audit logs.
Platform settings.
Level 2 – Restaurant Admin

Each restaurant has its own administration panel.

Owner

The Owner has full restaurant management access.

Manager

The Manager receives configurable permissions according to the restaurant's permission system.

Cashier

The Cashier focuses on:

Orders.
Billing.
Payments.
Coupons.

Restaurant administration includes:

Restaurant profile.
Tables.
QR codes.
Categories.
Cuisines.
Foods.
Food variants.
Food add-ons.
Orders.
Kitchen.
Games.
Rewards.
Coupons.
Campaigns.
Reviews.
Analytics.
Staff.
Permissions.
Billing.
Payments.
Settings.
🔒 Security

DineX is designed with security as a core requirement.

Security features include:

PDO prepared statements.
SQL injection protection.
CSRF protection.
XSS protection.
IDOR protection.
Restaurant ownership checks.
Server-side authorization.
Role-based permissions.
Subscription-based feature access.
Secure sessions.
HttpOnly cookies.
SameSite cookie protection.
Session regeneration.
Login rate limiting.
File upload validation.
MIME-type validation.
Restricted image formats.
Audit logging.
Server-side price validation.
Server-side game-result validation.
Server-side coupon validation.

All state-changing operations must be protected against CSRF attacks.

🔐 Customer Privacy

DineX follows a privacy-first customer architecture.

Customers do not need to create an account.

DineX does not provide:

Customer registration.
Customer login.
Customer profiles.
Customer passwords.
Customer loyalty accounts.
Customer personal profiles.

Customer interactions are based on anonymous table sessions.

Temporary customer session data can be periodically cleaned up.

Billing records are retained for accounting and restaurant operations.

💳 Subscription System

DineX supports subscription-based restaurant management.

Supported billing cycles:

Monthly.
Yearly.

Demo plans included in the database:

Plan	Billing Cycle	Price	Duration
Monthly Basic	Monthly	₹999	30 days
Monthly Standard	Monthly	₹1,999	30 days
Monthly Premium	Monthly	₹3,499	30 days
Yearly Basic	Yearly	₹9,990	365 days
Yearly Standard	Yearly	₹19,990	365 days
Yearly Premium	Yearly	₹34,990	365 days

Subscription features include:

Plan creation.
Plan editing.
Plan activation/deactivation.
Monthly subscriptions.
Yearly subscriptions.
Subscription expiry.
Payment tracking.
Feature entitlements.
Restaurant-specific feature overrides.
Server-side feature gating.

Feature access is enforced on the server and is not based only on hiding UI elements.

🛠️ Technology Stack
Layer	Technology
Backend	PHP 8+
Database	MySQL 8+ / MariaDB
Database Access	PDO
Frontend	HTML5
Styling	CSS3 / Tailwind CSS CDN
JavaScript	Vanilla JavaScript
API Communication	Fetch API
Icons	Font Awesome CDN
Charts	Chart.js CDN
Alerts	SweetAlert2 CDN
QR Generation	QRCode.js CDN
Server	Apache / XAMPP / cPanel
Authentication	PHP Sessions
No Node.js
No Laravel
No Python
No React
No Vue
No backend framework

DineX is designed to run directly on PHP-compatible hosting.

📦 Installation
1. Upload the Project

Place the project inside your web server directory.

For XAMPP:

C:\xampp\htdocs\dinex\

For example:

http://localhost/dinex/
2. Create the Database

Create a MySQL/MariaDB database named:

dinex

The included SQL file is:

database/dinex.sql

Import this file through phpMyAdmin.

The SQL file creates the required database tables, relationships, indexes, demo records, subscription plans, permissions, restaurant data, menu data, QR tokens, games, and other required seed data.

If the SQL file already contains:

CREATE DATABASE IF NOT EXISTS `dinex`;

USE `dinex`;

you can import it directly.

3. Configure Database Credentials

Open:

config/config.php

Configure the database connection:

define('DB_HOST', 'localhost');
define('DB_NAME', 'dinex');
define('DB_USER', 'root');
define('DB_PASS', '');

For XAMPP, the default MySQL configuration is commonly:

Host: localhost
Database: dinex
Username: root
Password: empty

For production hosting, use the credentials supplied by your hosting provider.

4. Configure the Base URL

Open:

config/config.php

For XAMPP:

define('BASE_URL', '/dinex');

If DineX is installed directly at the web root:

define('BASE_URL', '');

Example local installation:

http://localhost/dinex
5. Required Writable Directories

Make sure these directories exist and are writable:

logs/
assets/uploads/

They are used for:

Application logs.
Uploaded food images.
📱 QR Codes on Mobile Devices

When testing DineX QR codes on a mobile phone, do not use localhost or 127.0.0.1 in the QR URL.

This will not work from the phone:

http://localhost/dinex/customer/menu.php?token=...

Because localhost refers to the device itself.

Instead, use the LAN IP address of the computer running XAMPP.

Example:

http://192.168.1.100/dinex/customer/menu.php?token=...

Replace:

192.168.1.100

with the actual IPv4 address of your computer.

Find your Windows IPv4 address using:

ipconfig

Look for:

IPv4 Address

Example:

192.168.1.105

Then your QR URL becomes:

http://192.168.1.105/dinex/customer/menu.php?token=...
Important

For mobile testing:

Computer and phone must be connected to the same Wi-Fi/network.
Apache must be running.
MySQL must be running.
Windows Firewall must allow Apache/network access when required.
The QR code must contain the computer's LAN IP, not localhost.
🔑 Demo Accounts

All demo accounts use the following password:

password
Role	Email
Founder	founder@dinex.local
Owner	owner@spicegarden.local
Manager	manager@spicegarden.local
Cashier	cashier@spicegarden.local
⚠️ Production Warning

These are development/demo credentials.

Change all demo passwords before deploying DineX to production.

📱 Demo Restaurant

The included database contains a demo restaurant:

Restaurant: Spice Garden
Owner: Raj Sharma
City: Bengaluru
State: Karnataka

The demo restaurant contains:

Tables.
QR codes.
Categories.
Cuisines.
Foods.
Food variants.
Food add-ons.
Games.
Subscription.
Feature configuration.
Demo payment records.
📲 Demo QR Tokens
Table	Token
Table 01	dinex_spice_garden_t1_7f9a41b2c3d4e5f6
Table 02	dinex_spice_garden_t2_8a0b1c2d3e4f5a6b
Table 03	dinex_spice_garden_t3_9b1c2d3e4f5a6b7c

Customer menu URL pattern:

/customer/menu.php?token=<token>

Example:

http://localhost/dinex/customer/menu.php?token=dinex_spice_garden_t1_7f9a41b2c3d4e5f6

For mobile testing, replace localhost with the computer's LAN IP.

📁 Folder Structure
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
│       ├── header.php
│       └── footer.php
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
🧪 Testing

Testing documentation is available at:

documentation/testing.md

The testing process should cover:

Authentication
Founder login.
Restaurant login.
Logout.
Session handling.
Invalid login attempts.
Login rate limiting.
Authorization
Founder access isolation.
Restaurant access isolation.
Owner permissions.
Manager permissions.
Cashier permissions.
IDOR protection.
Restaurant ownership checks.
QR Ordering
QR token validation.
Table identification.
Anonymous table session creation.
Invalid token handling.
Expired/closed session handling.
Menu
Food listing.
Search.
Category filtering.
Cuisine filtering.
Food-type filtering.
Price filtering.
Sorting.
Food variants.
Food add-ons.
Cart
Server-side price validation.
Quantity validation.
Food availability validation.
Variant validation.
Add-on validation.
Total calculation.
Orders
Order creation.
Order status workflow.
Order history.
Kitchen workflow.
Customer order tracking.
Gamification
Game eligibility.
Minimum-order requirements.
Daily limits.
One-per-order rules.
Server-side result validation.
Reward generation.
Coupon generation.
Coupons
Coupon validation.
Expiry validation.
Minimum bill validation.
Maximum discount validation.
Single-use validation.
Double-redemption prevention.
Billing
Bill creation.
Tax calculation.
Discount calculation.
Coupon calculation.
Payment recording.
Bill status.
Payment status.
Subscription
Plan management.
Subscription assignment.
Subscription expiry.
Feature access.
Feature overrides.
Suspended subscriptions.
Expired subscriptions.
Security

Test for:

SQL injection.
XSS.
CSRF.
IDOR.
Unauthorized access.
Session fixation.
Session hijacking protections.
File upload vulnerabilities.
Rate-limit bypass.
Client-side price manipulation.
Client-side game-result manipulation.
Coupon manipulation.
🔐 Production Security Checklist

Before deploying DineX publicly:

Change all demo passwords.
Change database credentials.
Disable unnecessary PHP error display.
Enable server-side error logging.
Configure HTTPS.
Use secure session cookies.
Verify CSRF protection on every state-changing request.
Verify authorization on every protected endpoint.
Verify restaurant ownership checks.
Verify file upload restrictions.
Verify database permissions.
Protect configuration files.
Protect logs from public access.
Configure regular database backups.
Configure session cleanup.
Configure production cron jobs.
Remove development/demo credentials.
Test all APIs independently.
Test all admin roles independently.
🚀 Deployment

Production deployment documentation:

documentation/deployment.md

The deployment documentation covers:

Production configuration.
Database setup.
PHP configuration.
HTTPS.
File permissions.
Session security.
Cron jobs.
Temporary session cleanup.
Log management.
Backup strategy.
Performance optimisation.
Security hardening.
cPanel deployment.
Shared hosting deployment.
⚙️ Cron / Cleanup

DineX may use scheduled cleanup tasks for temporary customer session data and application maintenance.

Configure the required cron jobs according to:

documentation/deployment.md
📄 Database

The main database schema is located at:

database/dinex.sql

The database contains the required tables for:

Platform Users
Restaurants
Restaurant Staff
Roles
Permissions
Role Permissions
Subscription Plans
Subscription Plan Features
Restaurant Subscriptions
Restaurant Feature Overrides
Subscription Payments
Tables
QR Codes
Table Sessions
Categories
Cuisines
Foods
Food Variants
Food Add-ons
Orders
Order Items
Order Status History
Games
Game Rewards
Game Sessions
Coupons
Coupon Redemptions
Bills
Payments
Reviews
Campaigns
Notifications
Settings
Audit Logs
Rate Limiting
🧩 API Architecture

DineX uses REST-like PHP APIs for dynamic operations.

API modules are organised by responsibility:

api/
├── auth/
├── founder/
├── restaurant/
├── customer/
├── foods/
├── categories/
├── cuisines/
├── orders/
├── tables/
├── games/
├── coupons/
├── billing/
├── subscriptions/
└── analytics/

APIs should:

Validate input.
Authenticate requests where required.
Authorize the requested action.
Validate restaurant ownership.
Validate subscription feature access.
Use PDO prepared statements.
Return appropriate JSON responses.
Never trust client-side prices or permissions.
Apply CSRF protection to applicable state-changing requests.
🖥️ Local Development

For XAMPP:

Start Apache

Open XAMPP Control Panel and start:

Apache
Start MySQL

Start:

MySQL
Open DineX
http://localhost/dinex/
phpMyAdmin
http://localhost/phpmyadmin/
📱 Mobile Testing

To test QR ordering from a phone:

1. Find the computer IP

Run:

ipconfig

Find:

IPv4 Address

Example:

192.168.1.105
2. Test from the phone browser

Open:

http://192.168.1.105/dinex/

If this opens successfully, QR testing can proceed.

3. Generate QR using the LAN URL

Example:

http://192.168.1.105/dinex/customer/menu.php?token=dinex_spice_garden_t1_7f9a41b2c3d4e5f6
4. Scan the QR

The phone should open the DineX customer menu.

📝 Development Rules

When modifying DineX:

Do not remove existing security checks without a valid reason.
Do not trust client-side prices.
Do not trust client-side game results.
Do not trust client-side permissions.
Do not expose database credentials.
Do not expose passwords.
Do not store unnecessary customer personal information.
Use PDO prepared statements for database queries.
Validate all user input.
Escape output appropriately.
Protect state-changing requests with CSRF.
Check authentication before protected operations.
Check authorization before protected operations.
Check restaurant ownership before restaurant-level operations.
Check subscription feature access where applicable.
Log important administrative actions.
Preserve existing functionality when adding new features.
🤝 Contributing

Contributions are welcome.

Before making major architectural changes:

Review the existing folder structure.
Review the database schema.
Review authentication and authorization.
Review subscription and feature access.
Review security requirements.
Ensure existing functionality is not broken.
Test the affected modules.

For major changes, open an issue first to discuss the proposed architecture or implementation.

📜 License

MIT License.

You are free to use, modify, and distribute this software in accordance with the terms of the MIT License.

🍽️ DineX
Scan. Order. Play. Enjoy.

DineX provides a complete restaurant technology platform covering:

QR Ordering
     ↓
Digital Menu
     ↓
Cart
     ↓
Order
     ↓
Kitchen
     ↓
Billing
     ↓
Payment
     ↓
Games
     ↓
Rewards
     ↓
Coupons
     ↓
Analytics

DineX combines restaurant operations, QR-based ordering, kitchen management, billing, payments, gamification, coupons, subscriptions, feature management, and analytics into a single PHP and MySQL platform while keeping customer interactions anonymous.
