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

Orders can also be:

```text
CANCELLED

Features include:

Kitchen order dashboard
Real-time order status updates
Order history
Customer order tracking
Table status management
Order status history
Restaurant-specific order isolation
