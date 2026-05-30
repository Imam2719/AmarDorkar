# 🛍️ AmarDorkar — Full-Stack E-Commerce Platform

<p align="center">
  <img src="https://amardorkar.store/amardorkar.png" alt="AmarDorkar Logo" width="200"/>
</p>

<p align="center">
  <strong>বাংলাদেশের নিজস্ব ই-কমার্স প্ল্যাটফর্ম</strong><br>
  A bilingual (Bengali + English), feature-rich e-commerce web application built with Laravel 12.
</p>

<p align="center">
  <a href="https://amardorkar.store" target="_blank">🌐 Live Site: amardorkar.store</a>
</p>

---

## 📋 Table of Contents

- [About the Project](#about-the-project)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [Features Overview](#features-overview)
  - [Customer-Facing](#customer-facing-features)
  - [Admin Panel](#admin-panel-features)
- [Application Flow](#application-flow)
- [Notification System](#notification-system)
- [Security](#security)
- [Installation & Setup](#installation--setup)
- [Environment Variables](#environment-variables)
- [Deployment](#deployment)

---

## About the Project

**AmarDorkar** (আমার দোকার — "My Shop") is a production-grade, bilingual e-commerce platform designed for the Bangladeshi market. It supports both **Bengali (বাংলা)** and **English** interfaces, with automatic account creation at checkout, WhatsApp/Email order notifications, PDF invoice generation, and a comprehensive admin panel with role-based permissions.

**Live URL:** [https://amardorkar.store](https://amardorkar.store)

---

## Tech Stack

| Layer | Technology |
|---|---|
| **Backend Framework** | Laravel 12 (PHP 8.2+) |
| **Frontend** | Blade Templates + Tailwind CSS + Vite |
| **Database** | MySQL (Production) / SQLite (Development) |
| **PDF Generation** | barryvdh/laravel-dompdf |
| **Authentication** | Laravel Breeze (extended) |
| **Notifications** | Email (SMTP/Gmail) + WhatsApp (Meta Cloud API) |
| **Queue** | Laravel Queue (sync/database) |
| **Asset Bundling** | Vite + PostCSS |

---

## Project Structure

```
amardorkar-laravel/
├── app/
│   ├── Console/Commands/        # Artisan commands
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/           # All admin panel controllers
│   │   │   ├── Auth/            # Authentication controllers
│   │   │   ├── CartController.php
│   │   │   ├── CheckoutController.php
│   │   │   ├── HomeController.php
│   │   │   ├── ImageController.php    # DB-served image controller
│   │   │   ├── LanguageController.php
│   │   │   ├── OrderController.php
│   │   │   ├── ProductController.php
│   │   │   ├── ProductReviewController.php
│   │   │   ├── ProfileController.php
│   │   │   ├── ReturnRequestController.php
│   │   │   └── SearchController.php
│   │   ├── Middleware/
│   │   │   ├── AdminMiddleware.php
│   │   │   ├── AdminPermissionMiddleware.php
│   │   │   ├── LanguageMiddleware.php
│   │   │   └── SetPhpLimits.php
│   │   └── Requests/
│   ├── Jobs/                    # Queued notification jobs
│   │   ├── SendAdminOrderNotification.php
│   │   ├── SendCouponEmail.php
│   │   ├── SendCouponWhatsApp.php
│   │   ├── SendOrderConfirmationEmail.php
│   │   ├── SendOrderConfirmationWhatsApp.php
│   │   ├── SendOrderStatusUpdateEmail.php
│   │   └── SendOrderStatusWhatsApp.php
│   ├── Models/                  # Eloquent models (16 total)
│   ├── Services/
│   │   └── WhatsAppService.php  # Meta Cloud API integration
│   └── Providers/
├── config/
├── database/
│   ├── migrations/              # 40+ migration files
│   └── database.sqlite          # Dev SQLite database
├── lang/
│   ├── bn/messages.php          # Bengali translations
│   └── en/messages.php          # English translations
├── public/
│   ├── Logo_eng.png
│   ├── Logo_bng.png
│   └── amardorkar.png
├── resources/
│   └── views/
│       ├── admin/               # Admin panel views
│       ├── auth/                # Auth pages
│       ├── checkout/
│       ├── home/
│       ├── layouts/
│       ├── orders/
│       ├── product/
│       ├── profile/
│       ├── returns/
│       └── search/
├── routes/
│   ├── web.php                  # All application routes
│   ├── auth.php                 # Auth routes
│   └── console.php
├── .env
├── composer.json
├── package.json
├── tailwind.config.js
└── vite.config.js
```

---

## Database Schema

The application contains **18 database models** spanning the following tables:

| Table | Description |
|---|---|
| `users` | Customer accounts (name, email, mobile, password) |
| `admins` | Admin accounts with role + JSON permissions |
| `categories` | Product categories with status toggle |
| `products` | Products with bilingual names, SKU, stock, type, size |
| `product_images` | Multi-image support (DB-stored LONGTEXT base64) |
| `discount_products` | Time-based product discounts (start/end date) |
| `flash_sales` | Flash sale events with time window |
| `flash_sale_products` | Products linked to flash sale with flash price |
| `orders` | Full order info: customer, shipping, payment, status, coupon |
| `order_items` | Line items per order with selected size |
| `cart_items` | Session-based cart (guest + auth supported) |
| `coupons` | Discount coupons with usage limits and expiry |
| `coupon_usages` | Per-user/per-order coupon usage tracking |
| `banners` | Homepage/promo banners (DB-stored images) |
| `product_reviews` | Customer reviews with moderation (approve/reject) |
| `return_requests` | Product return/refund requests |
| `return_request_images` | Images uploaded with return requests |
| `sales_history` | Aggregated sales data for reporting |
| `user_messages` | Admin-sent messages to customers |
| `sessions` | Database-backed sessions |

---

## Features Overview

### Customer-Facing Features

#### 🏠 Homepage & Discovery
- Hero banner carousel (DB-managed, toggleable)
- Featured products section
- New arrivals section
- Offers / flash sale section
- Real-time **autocomplete search** with AJAX
- Full-text product search
- Category-based browsing

#### 🛍️ Product Pages
- Bilingual product name and description (EN/BN)
- Multiple product images with gallery
- Live pricing: regular → discount → flash sale price (priority chain)
- Product type and size selector
- Customer reviews with star ratings (moderated)
- Leave a review (auth required)

#### 🛒 Cart & Checkout
- Guest cart (session-based) + authenticated cart (DB-based)
- Cart persists across login
- Add / update / remove cart items via AJAX
- Checkout with:
  - Customer name, email, mobile, address
  - Shipping location selection (Dhaka / Outside Dhaka charge)
  - Coupon code apply (validates limits, expiry, usage per user)
  - Payment method selection
- **Automatic account creation** at checkout (no separate registration needed)
- Order success page with order number

#### 📦 Order Management (Customer)
- My Orders list with status tracking
- Order detail view
- **Download PDF Memo** (invoice PDF via DomPDF)
- Cancel pending order
- 7-day return window after delivery

#### 🔄 Returns & Refunds
- File a return request with description and images
- View return request status (pending/approved/rejected)
- 7-day window from `delivered_at` timestamp

#### 👤 Profile
- Update name, email, mobile
- Change password
- Delete account

#### 🌐 Bilingual Support
- Full Bengali (বাংলা) and English (English) UI
- Language switcher (POST `/language`)
- All models serve translated name/description via locale-aware accessors
- Bilingual logos (Logo_eng.png / Logo_bng.png)

#### 🌙 Dark Mode
- Full light/dark theme toggle stored in session
- Applied site-wide via Tailwind dark variant

---

### Admin Panel Features

Access at `/admin/login` — separate auth guard.

#### 🔐 Authentication
- Admin login with rate limiting (5 attempts/min)
- OTP-based password reset (email)
- Role-based access: `super_admin` vs `admin`
- Granular permission system (12 permission keys, JSON-stored)

#### 📊 Dashboard
- Stats cards: total products, orders, customers, revenue, pending orders, today's orders, active discounts
- Recent 10 orders table
- Monthly sales chart (last 6 months — orders count + revenue)

#### 📦 Order Management
- Full order listing with filters and bulk status update
- Order detail: customer info, items, payment, shipping
- Status workflow: `pending → confirmed → processing → shipped → delivered → cancelled`
- Mark order as sold (records in sales history)
- Bulk status update across multiple orders

#### 🛒 Product Management
- Create/edit/delete products with bilingual fields
- Multi-image upload (stored as base64 LONGTEXT in DB)
- Set primary image, reorder images
- Toggle featured / new arrival / active status
- SKU, stock quantity, product type and size
- Notify customers about a product via WhatsApp/Email

#### 🏷️ Category Management
- Create/rename/delete categories
- Toggle active/inactive status (AJAX)

#### 💸 Discounts
- Assign time-based discounts to individual products
- Toggle discount active/inactive
- View all discounted products

#### ⚡ Flash Sales
- Create flash sale events with start/end time
- Add/remove products to flash sales with individual flash prices
- Toggle flash sale active/inactive

#### 🏷️ Coupons
- Create coupons: fixed or percentage discount
- Set max total uses and max uses per user
- Set expiry date
- Send coupons to users via Email + WhatsApp
- Bulk delete expired coupons
- Toggle active/inactive

#### 🖼️ Banners
- Upload/manage homepage banners (DB-stored)
- Toggle banner visibility
- Update banner image or link

#### 📈 Sales Reports
- Filterable sales history view
- Export to **CSV**
- Export to **PDF** (DomPDF)
- Send sales report via **Email**
- Share via **WhatsApp**

#### 👥 Customer Management
- View all registered customers
- Per-customer order history

#### ⭐ Review Moderation
- Approve or reject pending reviews
- Delete any review

#### 🔄 Return Management
- View all return requests
- Approve/reject/resolve returns with notes
- View submitted images

#### 📣 User Messaging
- Send direct messages/notifications to specific customers

#### 🛡️ Admin Management *(Super Admin only)*
- Create/edit/delete admin accounts
- Assign role: `admin` or `super_admin`
- Set granular module-level permissions for regular admins

---

## Application Flow

### Order Placement Flow

```
1. Customer browses products
2. Adds to cart (guest session or auth DB)
3. Goes to /checkout
   ├── Fills customer info
   ├── Selects shipping location (price varies: Dhaka / outside)
   ├── Applies coupon (optional)
   └── Selects payment method
4. Order saved → auto-creates user account if email is new
5. Queue jobs dispatched:
   ├── SendOrderConfirmationEmail (to customer)
   ├── SendOrderConfirmationWhatsApp (to customer)
   └── SendAdminOrderNotification (to admin)
6. Cart cleared
7. Redirected to /order-success/{order}
```

### Admin Status Update Flow

```
Admin updates order status
→ SendOrderStatusUpdateEmail dispatched
→ SendOrderStatusWhatsApp dispatched
→ If status = 'delivered': delivered_at timestamp saved
→ Customer can file return within 7 days
```

### Image Storage Flow

```
All images (product, banner, review, return) stored as base64 LONGTEXT in DB.
Served via dedicated image routes:
  /img/product/{id}
  /img/banner/{id}
  /img/review/{id}/{index}
  /img/return/{id}
```

---

## Notification System

The app dispatches queued jobs for all notification events:

| Event | Email | WhatsApp |
|---|---|---|
| New order placed | ✅ Customer | ✅ Customer |
| Admin order alert | ✅ Admin | — |
| Order status update | ✅ Customer | ✅ Customer |
| Coupon sent | ✅ Customer | ✅ Customer |

**WhatsApp** uses Meta Cloud API (Facebook Graph API v25.0). Numbers are auto-formatted to Bangladeshi international format (880xxxxxxxxxx).

**Email** uses Gmail SMTP.

---

## Security

- All sessions encrypted (`SESSION_ENCRYPT=true`)
- Admin rate limiting on login (5 req/min) and OTP endpoints
- Separate admin auth guard (`auth('admin')`)
- Super admin / regular admin role separation with JSON permissions
- CSRF protection on all forms
- OTP-based password reset for both users and admins
- Input validation on all request handlers

---

## Installation & Setup

### Prerequisites

- PHP 8.2+
- Composer
- Node.js + npm
- MySQL (or SQLite for dev)

### Quick Setup

```bash
# 1. Clone the repository
git clone https://github.com/Imam2719/AmarDorkar.git
cd AmarDorkar

# 2. Run the setup script (installs all deps + migrates DB)
composer run setup

# 3. Configure environment
cp .env.example .env
# Edit .env — see Environment Variables section below

# 4. Start development server
composer run dev
```

The `composer run setup` script will:
1. Install PHP dependencies (`composer install`)
2. Copy `.env.example` → `.env` if not present
3. Generate app key (`php artisan key:generate`)
4. Run migrations (`php artisan migrate`)
5. Install Node dependencies (`npm install`)
6. Build assets (`npm run build`)

### Manual Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan serve
```

---

## Environment Variables

Key variables to configure in `.env`:

```env
APP_NAME="amardorkar"
APP_URL=https://amardorkar.store

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=amardorkar_db
DB_USERNAME=root
DB_PASSWORD=your_password

# Mail (Gmail SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your@gmail.com
MAIL_FROM_NAME="AmardoKar"

# WhatsApp (Meta Cloud API)
WHATSAPP_TOKEN=your_meta_token
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_API_VERSION=v25.0

# Queue (use 'database' for production)
QUEUE_CONNECTION=sync

# Session
SESSION_DRIVER=database
SESSION_ENCRYPT=true
```

---

## Deployment

The live site runs at [https://amardorkar.store](https://amardorkar.store).

**Recommended production checklist:**

```bash
# Set production environment
APP_ENV=production
APP_DEBUG=false

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run queue worker (for email/WhatsApp notifications)
php artisan queue:work --tries=3 --timeout=60
```

---

## Admin Panel Access

URL: `https://amardorkar.store/admin/login`

Admin accounts are managed by the super admin from within the panel at `/admin/admins`. Each admin can be assigned specific module permissions (dashboard, orders, products, categories, discounts, flash sales, coupons, banners, sales, customers, reviews, returns).

---

## Contributing

This is a private project. For inquiries, contact the repository owner via GitHub: [@Imam2719](https://github.com/Imam2719)

---

## License

This project is proprietary and not open-source. All rights reserved © 2026 AmarDorkar.

---

<p align="center">Made with ❤️ in Bangladesh 🇧🇩</p>
