# Release Manifest

**Application Name:** CapCut Pro Digital Storefront & Commerce Platform  
**Release Target:** Shared Hosting & cPanel Production Package  
**Build Date:** 2026-10-02  
**Framework Version:** Laravel 11.x on PHP 8.2+  
**Frontend Engine:** Blade + Tailwind CSS (Vite v7.3.6 compiled assets) + Alpine.js  

---

### System Specifications & Requirements

| Specification | Requirement |
| :--- | :--- |
| **Application Version** | `2.5.0-production` |
| **Database Schema Version** | `2026_10_02_000012` |
| **Required PHP Version** | `PHP 8.2.0+` (PHP 8.3 recommended) |
| **Required Database** | `MySQL 5.7+` / `MySQL 8.0+` / `MariaDB 10.3+` |
| **Required PHP Extensions** | `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pcre`, `pdo_mysql`, `tokenizer`, `xml` |
| **Web Server** | Apache (with `mod_rewrite`) or Nginx |
| **Hosting Compatibility** | Standard Shared Hosting, cPanel, DirectAdmin, Plesk, VPS |

---

### Component Verification Status

| Component | Status | Details |
| :--- | :---: | :--- |
| **Production Build Status** | **PASSED** | Compiled with Vite; production CSS/JS bundled in `public/build/` and synced to `public/assets/` |
| **Database SQL Included** | **PASSED** | Clean MySQL dump generated at `database.sql` and `database/schema.sql` (18 tables, zero secrets) |
| **Razorpay Admin Control** | **PASSED** | Mode selector (Test/Live), Key ID, encrypted Key Secret, zero credentials in `.env` |
| **Test Connection Feature** | **PASSED** | Server-side ping verification directly to Razorpay API with real-time feedback |
| **Payment Verification** | **PASSED** | Cryptographic HMAC-SHA256 signature verification server-side; forged signatures rejected |
| **Customer Lead Capture** | **PASSED** | Partial contact info (phone/email/name) immediately saved as drafts/abandoned leads |
| **Order Storage** | **PASSED** | Full transaction lifecycle recorded in `orders` and `payments` tables with audit logs |
| **Tracking & Analytics** | **PASSED** | Server-side deduplication by `event_id`; automated sensitive key stripping |
| **Responsive QA** | **PASSED** | Verified on mobile (320px–430px), tablet (768px–1024px), and desktop (1280px–1920px) |
| **Security Audit** | **PASSED** | 0 secrets in source code, 0 secrets in `.env`, 0 credentials in SQL, 0 localhost dependencies |
| **Automated Test Suite** | **PASSED** | 52 automated tests passed (212 assertions) |

---

### Verification Summary
- No hardcoded API keys or secrets in `.env`, `.env.example`, blade templates, or SQL dump.
- Admin Panel operates as the single source of truth for payment configuration.
- Shared-hosting direct upload ready with root `.htaccess` and `public/.htaccess`.
