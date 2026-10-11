# CapCut Pro Team Digital Commerce Platform

A complete, production-grade, conversion-optimized digital-product selling application built with **Laravel 11, PHP 8.2+, MySQL, Tailwind CSS, Alpine.js, and Razorpay**.

Engineered specifically for selling **CapCut Pro Team 1-Month subscriptions with 1200 Credits and a 25-Day Digital Warranty**, with automated server-side verification, encrypted credential delivery, Meta Ads CAPI tracking, WhatsApp checkout, and zero-headache shared hosting deployability.

---

## 🌟 Key Highlights

* **High-Conversion Futuristic Visual Identity:** Near-black `#05070B` palette, `#00D9FF` cyan and `#1677FF` blue gradients, glassmorphism cards, floating badges, pulsing neon accents, and sticky bottom purchase bar with mobile safe-area padding.
* **Server-Controlled Price Integrity:** The browser NEVER determines prices. Client price inputs are completely ignored. Product pricing, discounts, and coupon math are strictly calculated server-side from the database.
* **Concurrency-Safe Inventory Allocation:** Prevents double-selling using atomic database transactions and row-level locking (`lockForUpdate()`).
* **Encrypted-at-Rest Digital Vault:** Credentials (`email_encrypted`, `password_encrypted`) use Laravel's AES-256 encryption. Plaintext is decrypted ONLY on authorized customer delivery pages or by admin password re-verification.
* **Zero-Leak Authorization:** Orders are inaccessible via sequential IDs (`/order/123`). Instead, access is guarded via cryptographic UUIDs and 64-character tokens (`/order/{uuid}/{token}`).
* **Meta Pixel & Conversions API (CAPI):** Real-time server-side purchase tracking with SHA-256 user data hashing and shared `event_id` deduplication.
* **Shared-Hosting Compatible:** Runs on standard Apache/cPanel shared hosting without Redis, Node.js background daemons, or Docker.

---

## 📁 Architecture & File Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── AuditLogController.php
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── CouponController.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── FaqController.php
│   │   │   │   ├── InventoryController.php
│   │   │   │   ├── OrderController.php
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── SettingController.php
│   │   │   │   └── TestimonialController.php
│   │   │   ├── CheckoutController.php
│   │   │   ├── HomeController.php
│   │   │   ├── OrderController.php
│   │   │   ├── PageController.php
│   │   │   ├── PaymentController.php
│   │   │   ├── TrackingController.php
│   │   │   └── WebhookController.php
│   │   └── Middleware/
│   │       ├── AdminAuthenticate.php
│   │       ├── CaptureUtmParameters.php
│   │       └── SecurityHeaders.php
│   ├── Mail/
│   │   ├── OrderReceivedMail.php
│   │   ├── PaymentFailedMail.php
│   │   ├── ProductDeliveredMail.php
│   │   └── RefundProcessedMail.php
│   ├── Models/
│   │   ├── AuditLog.php
│   │   ├── Coupon.php
│   │   ├── CouponRedemption.php
│   │   ├── Faq.php
│   │   ├── InventoryCredential.php (Encrypted)
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── Payment.php
│   │   ├── PaymentEvent.php (Idempotency)
│   │   ├── Product.php
│   │   ├── ProductFeature.php
│   │   ├── RefundPolicy.php
│   │   ├── Setting.php
│   │   ├── SupportMessage.php
│   │   ├── Testimonial.php
│   │   ├── TrackingEvent.php
│   │   ├── User.php
│   │   ├── UtmAttribution.php
│   │   └── WarrantyPolicy.php
│   └── Services/
│       ├── Coupon/CouponService.php
│       ├── Delivery/DeliveryService.php
│       ├── Inventory/InventoryService.php
│       ├── Order/OrderService.php
│       ├── Payment/
│       │   ├── PaymentGatewayInterface.php
│       │   └── RazorpayPaymentService.php
│       ├── Tracking/
│       │   ├── MetaConversionService.php
│       │   └── TrackingService.php
│       └── WhatsApp/WhatsAppService.php
├── database/
│   ├── migrations/ (10 relational migration sets)
│   ├── seeders/    (6 production & testing seeders)
│   └── database.sqlite
├── public/
│   ├── assets/     (Pre-compiled standalone production CSS & JS)
│   ├── build/      (Vite manifest and bundles)
│   ├── robots.txt
│   ├── sitemap.xml
│   └── index.php
├── resources/
│   ├── css/app.css
│   ├── js/app.js
│   └── views/
│       ├── admin/
│       ├── checkout/
│       ├── emails/
│       ├── home/
│       ├── layouts/
│       ├── order/
│       └── pages/
├── routes/
│   ├── admin.php
│   ├── console.php
│   └── web.php
├── tests/Feature/  (25 passing feature test assertions)
├── database.sql    (phpMyAdmin 1-Click Import Dump)
├── DEPLOYMENT.md   (Dedicated Shared-Hosting Guide)
├── PRODUCTION_CHECKLIST.md
└── .env.example
```

---

## 🚀 Quick Setup & Installation

### Local Development Setup:
```bash
# 1. Clone repository
git clone https://github.com/your-username/capcut-store.git
cd capcut-store

# 2. Install PHP and NPM dependencies
composer install
npm install

# 3. Environment configuration
cp .env.example .env
php artisan key:generate

# 4. Migrate and Seed Database
php artisan migrate --seed

# 5. Build Assets & Start Local Server
npm run build
php artisan serve
```

---

## 🔐 Admin Panel Setup

* **Admin Portal URL:** `http://your-domain.com/admin`
* **Default Admin Email:** `admin@capcutstore.local`
* **Default Admin Password:** `Admin@CapCut2026!`

*(Immediately change this password inside the Admin Panel after first login!)*

### Admin Capabilities:
1. **Live Dashboard:** Revenue, orders, available/sold credentials, conversion rate, 14-day settlement chart.
2. **Products & Features:** Edit prices, sale discounts, 1200 credit allotment, warranty duration, and live bullet points.
3. **Inventory Vault:** View masked emails (`j***n@domain.com`), upload single accounts, bulk import CSVs, toggle disable, and re-authenticate to decrypt.
4. **Orders & Fulfillment:** Full customer timeline, gateway transaction IDs, UTM attribution data, manual override with mandatory audit reasons, refund processor, and delivery resend.
5. **Coupons:** Flat or percentage discounts, minimum cart values, usage caps, per-customer limits, and auto-expiry.
6. **Settings:** Razorpay live keys, Webhook secret, WhatsApp support number, Meta Pixel ID, Meta CAPI token, and legal disclaimer text.
7. **Security Audit Trail:** Immutable log of all administrative actions, credential reveals, price changes, and refunds.

---

## 💳 Payment Gateway: Razorpay & Webhook Setup

### 1. Razorpay Key Configuration
In `.env` or **Admin > Settings > Razorpay Gateway**:
```env
RAZORPAY_KEY_ID=rzp_live_xxxxxxxxxxxx
RAZORPAY_KEY_SECRET=your_razorpay_secret_key
RAZORPAY_WEBHOOK_SECRET=your_webhook_secret
```

### 2. Webhook Configuration in Razorpay Dashboard
1. Log into [Razorpay Dashboard](https://dashboard.razorpay.com/) > **Settings** > **Webhooks**.
2. Click **Add New Webhook**.
3. Set **Webhook URL:** `https://yourdomain.com/webhooks/razorpay`
4. Set **Secret:** Enter a strong secret and match it in `RAZORPAY_WEBHOOK_SECRET`.
5. Select **Active Events:**
   * `payment.captured`
   * `order.paid`
   * `payment.failed`
   * `refund.processed`
6. Click **Save Webhook**.

### Webhook Verification & Idempotency:
* Verified cryptographically via HMAC-SHA256 (`hash_hmac('sha256', $payload, $secret)`).
* Stored in `payment_events` with unique compound index `[gateway, event_id]` to guarantee duplicate webhooks are rejected safely with HTTP 200.

---

## 📱 WhatsApp Direct Checkout & Floating Button

* Configurable phone number in **Admin > Settings** (e.g. `919876543210`).
* Generates compliant click-to-chat links:
  ```
  https://wa.me/919876543210?text=Hello%2C%20I%20want%20to%20purchase%20CapCut%20Pro%20Team%201%20Month...
  ```
* Dedicated floating button with pulsing green glow and automatic mobile safe-area offset so it never obscures the checkout bar.
* Tracks clicks as `WhatsAppCheckoutClick` conversion events.

---

## 📊 Meta Ads Tracking (Pixel + Conversions API)

* Client-side Pixel fires: `PageView`, `ViewContent`, `InitiateCheckout`, `WhatsAppCheckoutClick`, `CopyCredential`.
* **Purchase Event Deduplication:** Server-side Meta Conversions API (CAPI) sends `Purchase` with identical `event_id` (`order-{order_id}-purchase`), user email hashed with SHA-256, and phone hashed with SHA-256.
* Only fires after cryptographically verified payment.

---

## 📦 Bulk Inventory CSV Import Format

Under **Admin > Inventory > Import CSV**, upload a `.csv` or `.txt` file with this format:
```csv
email,password,notes
editor.alpha@workteam.com,StrongPass#2026!,Profile 1 PIN: 1234
editor.beta@workteam.com,StrongPass#2026!,Profile 2
editor.gamma@workteam.com,StrongPass#2026!,
```
The importer automatically:
1. Ignores header rows (`email,password`).
2. Validates email formats.
3. Checks for duplicates against active stock.
4. Encrypts all emails, passwords, and notes with AES-256 before saving.

---

## 🧪 Automated Testing

Run the full automated test suite:
```bash
php artisan test
```
All **25 tests and 74 assertions** cover:
* Homepage and dynamic price display
* Server-side price tampering protection
* Mandatory checkout validation
* Coupon calculation, percentage caps, and expiry
* Payment signature verification and tamper rejection
* Webhook signature and idempotency
* Concurrency locking (`lockForUpdate()`) and atomic allocation
* Password encryption at rest
* Token authorization for credential reveal
* Admin authentication and brute-force throttling

---

## 📜 Legal Disclaimers & Fair Branding

This software conforms to fair presentation guidelines:
* Transparent non-affiliation statement displayed prominently:
  > *"Independent digital service provider. Not affiliated with or endorsed by CapCut/ByteDance unless explicitly stated otherwise."*
* Permitted device restrictions (1 mobile + 1 laptop) explicitly highlighted prior to checkout.
* 25-Day Digital Warranty clearly delineates covered technical issues vs excluded modifications.
* Zero fake countdown timers, zero fake stock scarcity counters, and zero fabricated reviews.
