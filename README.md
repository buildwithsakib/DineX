# 🍽️ DineX – Scan. Order. Play. Enjoy.

**DineX** is a complete, secure, production-ready QR-based restaurant ordering, kitchen, billing, gamification, coupon, and analytics platform. It is built entirely with PHP, MySQL, and vanilla JavaScript, with no Node.js or frameworks. Designed for shared hosting and cPanel.

**Privacy-first:** DineX does **not** collect any customer personal information. There is no registration, login, customer profile, or loyalty program. Customers remain anonymous.

---

## ✨ Features

### 🔍 Core Ordering

* QR-code-based table identification using secure random tokens
* Anonymous table sessions
* Mobile-first digital menu
* Live search with debounce
* Category, cuisine, food type (Veg/Non-Veg/Egg), price, and sorting filters
* Trending, best sellers, chef's specials, and signature dishes
* Food variants and add-ons
* Server-side cart validation so prices are never trusted from the client

### 🍳 Kitchen & Order Management

* Kitchen Display System (KDS) with real-time order status
* Order workflow: Placed → Accepted → Preparing → Ready → Served → Completed / Cancelled
* Customer order tracking page
* Table status management

### 🎮 Gamification

**Six mini games:**

1. Spin the Wheel
2. Instant Lottery
3. Slot Machine
4. Catch & Win
5. Snakes & Ladders
6. Tap Speed

* Game eligibility rules including minimum order, daily limits, and one-per-order rules
* Server-side reward determination to prevent client-side tampering
* Automatic coupon generation on winning

### 🎟️ Coupons & Billing

* Unique, single-use, time-limited coupons
* Coupon validation and redemption with double-use prevention
* Bill generation with tax and discount
* Cashier panel for payments
* Completed bill history

### 📊 Analytics

* Daily and monthly sales and order reports
* Popular foods
* Category and cuisine sales
* Food type performance
* Peak hours analysis
* Table revenue
* Game and coupon analytics
* Chart.js visualisations

### 👥 Role-Based Admin

* **Super Admin** – Manage restaurants, owners, and platform settings
* **Restaurant Owner** – Full restaurant management
* **Manager** – Configurable permissions
* **Cashier** – Orders, billing, and payments

### 🔒 Security

* PDO prepared statements for SQL injection prevention
* CSRF tokens on all state-changing requests
* XSS escaping
* IDOR protection with restaurant ownership checks
* File upload validation
* Rate limiting on login
* Secure sessions with HttpOnly, SameSite, and session regeneration
* Audit logs for administrative actions
* No customer personal data collection

---

## 🛠️ Tech Stack

| Layer    | Technology                                 |
| -------- | ------------------------------------------ |
| Backend  | PHP 8+                                     |
| Database | MySQL 8+ / MariaDB                         |
| Frontend | HTML5, CSS3, Vanilla JavaScript, Fetch API |
| CSS      | Tailwind CSS (CDN)                         |
| Icons    | Font Awesome (CDN)                         |
| Charts   | Chart.js (CDN)                             |
| Alerts   | SweetAlert2 (CDN)                          |
| QR Codes | QRCode.js (CDN)                            |

**No Node.js, no Laravel, no Python.** DineX runs on shared hosting and cPanel.

---

## 📦 Installation

### 1. Upload the Project

Clone or upload the repository to your web server, for example:

```text
dinex/
```

### 2. Create the Database

Create a MySQL/MariaDB database and import:

```text
database/dinex.sql
```

### 3. Configure Database Credentials

Edit:

```text
config/database.php
```

Set your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'dinex');
define('DB_USER', 'your_user');
define('DB_PASS', 'your_password');
```

### 4. Ensure Required Directories Are Writable

The following directories must have write permission:

```text
logs/
assets/uploads/foods/
```

These directories are required for application logs and food image uploads.

### 5. Configure Base Paths

If your project is not installed at the web root, adjust the required base paths in:

```text
.htaccess
```

and the relevant PHP files.

### 6. Configure QR Codes for Mobile Devices

For QR codes to work when scanned from a phone, the QR code must contain a URL that is accessible from the phone.

Update the full URL in:

```text
admin/owner/qr.php
```

For example, when testing on a local network:

```text
http://192.168.1.100/dinex/...
```

Replace `192.168.1.100` with the local IP address of the computer running the DineX project.

**Important:** `localhost` or `127.0.0.1` inside a QR code will point to the phone itself, not to your computer. Therefore, use your computer's LAN IP address when testing QR codes on a phone.

---

## 🔑 Demo Accounts

All demo passwords are:

```text
password
```

**Change all passwords before deploying to production.**

| Role        | Email                   |
| ----------- | ----------------------- |
| Super Admin | `superadmin@dinex.test` |
| Owner       | `owner@dinex.test`      |
| Manager     | `manager@dinex.test`    |
| Cashier     | `cashier@dinex.test`    |

---

## 📱 Demo QR Tokens

| Table    | Token      | URL                                 |
| -------- | ---------- | ----------------------------------- |
| Table 01 | `8F7K29XQ` | `/customer/menu.php?token=8F7K29XQ` |
| Table 02 | `P3K9M4ZR` | `/customer/menu.php?token=P3K9M4ZR` |
| Table 03 | `J5N8Q2WT` | `/customer/menu.php?token=J5N8Q2WT` |
| Table 04 | `L2R6X7KP` | `/customer/menu.php?token=L2R6X7KP` |

---

## 📁 Folder Structure

```text
dinex/
├── admin/                 # Four separate admin panels
├── api/                   # REST-like JSON APIs
├── assets/                # CSS, JavaScript, uploads
│   └── uploads/
│       └── foods/         # Food image uploads
├── config/                # Configuration and database
├── customer/              # Customer-facing pages
├── database/              # Database SQL files
│   └── dinex.sql
├── games/                 # Six game modules
├── includes/              # Core PHP functions and security
├── logs/                  # Error and cleanup logs
└── documentation/         # Installation, deployment, testing docs
```

---

## 🧪 Testing

A comprehensive testing guide is available at:

```text
documentation/testing.md
```

It covers:

* Role isolation and permissions
* QR session creation
* Menu search, filtering, and sorting
* Cart validation and order placement
* Kitchen workflow
* Game eligibility and reward validation
* Coupon redemption
* Billing and payments
* Security testing including SQL injection, XSS, CSRF, and IDOR

---

## 🔐 Security Notes

* All database queries use **PDO prepared statements**.
* **CSRF tokens** are required for all state-changing POST requests.
* Prices, game results, and coupons are validated server-side.
* File uploads are restricted to JPG, PNG, and WEBP with MIME validation.
* Customer sessions are anonymous and do not store personal information.
* There are no customer accounts, branches, loyalty programs, or customer profiles.
* Billing records are retained for accounting purposes.
* Temporary customer session data is periodically purged.

---

## 🚀 Deployment

For production deployment, see:

```text
documentation/deployment.md
```

The deployment documentation covers:

* Production hardening
* Cron jobs
* File permissions
* Configuration
* Performance optimisation
* Security recommendations

---

## 📄 License

MIT License.

You are free to use, modify, and distribute this software in accordance with the terms of the MIT License.

---

## 🤝 Contributing

Pull requests are welcome.

For major changes, please open an issue first to discuss the proposed changes.

---

## 🍽️ DineX

**Scan. Order. Play. Enjoy.**

DineX provides a complete restaurant experience covering QR-based ordering, kitchen operations, billing, payments, gamification, coupons, and analytics while keeping customer interactions anonymous.
