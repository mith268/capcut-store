# Deployment Guide — Shared Hosting & cPanel Deployment

This guide explains how to deploy this complete **Laravel + PHP 8.2+ + MySQL** digital commerce application to standard shared hosting environments (such as Hostinger, Bluehost, Namecheap, GoDaddy, cPanel, or Plesk) **without requiring Docker, Redis, permanent Node.js runtimes, or VPS root access**.

---

## Prerequisites
* **PHP:** Version 8.2 or higher
* **PHP Extensions:** `BCMath`, `Ctype`, `cURL`, `DOM`, `Fileinfo`, `JSON`, `Mbstring`, `OpenSSL`, `PCRE`, `PDO`, `PDO_MySQL`, `Tokenizer`, `XML`
* **Database:** MySQL 5.7+ or MariaDB 10.3+
* **cPanel Access** (File Manager, phpMyAdmin, MySQL Databases, Cron Jobs)

---

## Strategy A: Standard cPanel (When Document Root Can Be Set)

If your cPanel hosting allows you to set the Document Root of your primary domain or addon domain to `/public` (e.g. `/home/username/capcut_store/public`):

1. **Upload Project:**
   - Compress the project root directory (excluding `node_modules` and `.git`). Note: pre-compiled production assets are already included in `public/build/` and `public/assets/`!
   - Upload and extract the archive into `/home/username/capcut_store`.
2. **Point Document Root:**
   - In cPanel > **Domains** > Set Document Root to: `/home/username/capcut_store/public`.
3. **Database Configuration:**
   - Create a MySQL database and user in cPanel > **MySQL Databases**.
   - Open **phpMyAdmin**, select the new database, and import `database.sql`.
4. **Environment File:**
   - Copy `.env.example` to `.env`.
   - Update `APP_URL`, database credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`), and gateway keys.
   - Run `php artisan key:generate` (or set a 32-character base64 key generated locally).
5. **Permissions:**
   - Ensure `storage/` and `bootstrap/cache/` have `775` permissions.

---

## Strategy B: Traditional Shared Hosting (Document Root Fixed to `public_html`)

If your shared hosting forces your primary domain to point directly to `public_html`, use this secure layout to prevent sensitive source code and `.env` files from being exposed to the web:

### Directory Layout
```
/home/username/
├── capcut_app/            <-- Entire Laravel project folder (outside public_html)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── storage/
│   ├── vendor/
│   └── .env               <-- SAFELY OUTSIDE WEB ROOT
└── public_html/           <-- Only the contents of Laravel's /public folder
    ├── assets/
    ├── build/
    ├── favicon.ico
    ├── robots.txt
    ├── sitemap.xml
    ├── .htaccess
    └── index.php          <-- Edited bootstrap paths
```

### Steps:
1. Create a folder named `capcut_app` in your home directory (`/home/username/capcut_app`).
2. Upload all Laravel directories and files **except** the `public/` directory into `capcut_app`.
3. Copy all files from inside Laravel's `public/` folder directly into your hosting's `public_html/`.
4. Open `public_html/index.php` and update the bootstrap paths to point to `capcut_app`:
   ```php
   // Change:
   require __DIR__.'/../vendor/autoload.php';
   $app = require_once __DIR__.'/../bootstrap/app.php';

   // To:
   require __DIR__.'/../capcut_app/vendor/autoload.php';
   $app = require_once __DIR__.'/../capcut_app/bootstrap/app.php';
   ```
5. Ensure `public_html/.htaccess` contains Apache rewrite rules:
   ```apache
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteCond %{REQUEST_FILENAME} !-d
       RewriteCond %{REQUEST_FILENAME} !-f
       RewriteRule ^ index.php [L]
   </IfModule>
   ```

---

## Database Import via phpMyAdmin

1. Log into your cPanel account.
2. Click **phpMyAdmin**.
3. Select your newly created MySQL database from the left sidebar.
4. Click the **Import** tab on the top menu.
5. Click **Choose File** and select `database.sql` from your project root.
6. Click **Import** (or **Go**).
7. All 18 relational tables, indexes, constraints, default settings, FAQs, policies, and the initial administrator account will be imported immediately.

---

## Cron Jobs Configuration (cPanel)

To allow Laravel's scheduler to process delayed notifications, queue jobs, and clean temporary states:

1. Open cPanel > **Cron Jobs**.
2. Select Interval: **Once Per Minute** (`* * * * *`).
3. Set the command:
   ```bash
   /usr/local/bin/php /home/username/capcut_app/artisan schedule:run >> /dev/null 2>&1
   ```
   *(Replace `/usr/local/bin/php` with your host's PHP 8.2 binary path, e.g. `/usr/bin/php82` or `/opt/cpanel/ea-php82/root/usr/bin/php`)*

---

## Queue Worker on Shared Hosting

If you use background mail dispatch:
- In `.env`, you can set `QUEUE_CONNECTION=sync` for immediate synchronous execution (zero daemon required).
- Or keep `QUEUE_CONNECTION=database` and add a cron job every 5 minutes:
  ```bash
  /usr/local/bin/php /home/username/capcut_app/artisan queue:work --stop-when-empty >> /dev/null 2>&1
  ```

---

## File Permissions Checklist

Set the following folder permissions in cPanel File Manager:
- `storage/`: `775` (or `755` depending on host PHP handler like suPHP / lsphp)
- `storage/framework/`: `775`
- `storage/logs/`: `775`
- `bootstrap/cache/`: `775`
- `.env`: `600` or `640` (restrict public read permissions)

---

## Production Optimization Commands (SSH or Terminal)

If SSH terminal access is available in cPanel:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
To clear caches after editing `.env`:
```bash
php artisan optimize:clear
```
