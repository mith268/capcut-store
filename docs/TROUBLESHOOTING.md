# Production Troubleshooting Guide

This guide addresses common deployment, hosting, and operational issues.

---

### 1. Database Connection Failure (`SQLSTATE[HY000] [2002]` or `Access Denied`)
**Symptoms:** Error establishing a database connection or 500 error on first page load.
**Causes & Solutions:**
- Check `.env` configuration:
  ```dotenv
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1      # On shared hosting, localhost or 127.0.0.1 is standard
  DB_PORT=3306
  DB_DATABASE=yourcpanel_dbname
  DB_USERNAME=yourcpanel_dbuser
  DB_PASSWORD="your_password"
  ```
- Verify in cPanel **MySQL Databases** that the user has been added to the database with **ALL PRIVILEGES**.
- If your host uses a specific socket or remote database host, substitute `DB_HOST` with the host IP/hostname provided in your cPanel dashboard.

---

### 2. Blank Page or HTTP 500 Internal Server Error
**Symptoms:** White screen or generic 500 error when opening the website.
**Causes & Solutions:**
- **Permissions:** Shared hosts require web write access to `storage/` and `bootstrap/cache/`:
  ```bash
  chmod -R 775 storage bootstrap/cache
  ```
  In cPanel File Manager: Right-click `storage` → Change Permissions → Set to `775`.
- **Missing App Key:** Verify that `APP_KEY` in `.env` is populated (e.g. `APP_KEY=base64:...`).
- **PHP Version:** Ensure your hosting PHP version is set to PHP 8.2 or 8.3 in cPanel **Select PHP Version**.
- **Inspect Server Logs:** Check `storage/logs/laravel.log` or the cPanel **Errors** log for detailed diagnostics.

---

### 3. Razorpay Connection Failure (`[ Test Connection ]` Errors)
**Symptoms:** Click on "Test Connection" returns `✕ Razorpay connection failed: Invalid credentials`.
**Causes & Solutions:**
- **Credential Mismatch:** Double check that you did not mix up Test and Live keys. Test keys start with `rzp_test_`, while Live keys start with `rzp_live_`.
- **Extra Whitespace:** Ensure no leading or trailing spaces were accidentally copied when pasting the Key ID or Key Secret.
- **Outdated Credentials:** In Razorpay Dashboard, generate a new API key pair if the previous secret was revoked or regenerated.
- **cURL SSL Verification:** If your shared host has outdated root certificates, ensure PHP's OpenSSL and cURL extensions are enabled with standard CA certificates.

---

### 4. Razorpay Checkout Modal Not Opening
**Symptoms:** Customer clicks "Proceed to Payment" but the modal fails to launch.
**Causes & Solutions:**
- **Empty Key ID:** Ensure `razorpay_key_id` is set in Admin Settings > Razorpay Gateway.
- **Adblocker / Browser Extension:** Certain aggressive tracking blockers block `checkout.razorpay.com/v1/checkout.js`. Check the browser Console.
- **Order Amount:** Razorpay requires an order amount of at least 100 paise (₹1.00). Ensure the effective price in Admin is greater than 0.

---

### 5. Signature Verification Failed on Checkout
**Symptoms:** Modal closes, but screen alerts *"Payment signature verification failed"*.
**Causes & Solutions:**
- **Secret Key Mismatch:** The Key Secret in your Admin settings does not match the Key ID that created the order in Razorpay.
- Verify that both Key ID and Key Secret belong to the exact same API Key generation from your Razorpay Dashboard.
- If you regenerated your key in Razorpay, remember to update both the Key ID and Key Secret in Admin Settings and click **Save Settings**.

---

### 6. Admin Branding Changes Not Updating on Storefront
**Symptoms:** You changed the Store Name or Logo in Admin, but the storefront still shows the old brand.
**Causes & Solutions:**
- **Cache Flushing:** The system automatically flushes the settings cache upon save. If your shared hosting uses an external caching reverse proxy (such as Cloudflare, LiteSpeed Cache, or Nginx microcache), purge your cache in your hosting panel or Cloudflare dashboard.
- **Browser Cache:** Perform a hard refresh in your browser (`Ctrl+F5` on Windows/Linux or `Cmd+Shift+R` on Mac).

---

### 7. URL Rewriting / Apache `.htaccess` Routing Issues
**Symptoms:** Navigating to `/admin` or `/orders` results in a 404 from Apache.
**Causes & Solutions:**
- Ensure `mod_rewrite` is enabled on Apache.
- Ensure the root `.htaccess` file was uploaded to your web root (`public_html/`).
- In cPanel File Manager, click **Settings** (top right) and ensure **Show Hidden Files (dotfiles)** is enabled.
- Verify that `public/.htaccess` is also present inside the `public/` directory.
