# Production Installation Guide
## CapCut Pro Storefront & Commerce Platform

This guide outlines the standard deployment process for cPanel, Apache, and standard PHP/MySQL shared hosting environments.

---

### Prerequisites
- **Web Server:** Apache with `mod_rewrite` enabled
- **PHP Version:** 8.2 or 8.3+
- **PHP Extensions:** `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pcre`, `pdo_mysql`, `tokenizer`, `xml`
- **Database:** MySQL 5.7+ / 8.0+ or MariaDB 10.3+

---

### Step-by-Step Deployment Procedure

#### STEP 1: Create MySQL Database
1. Log into your hosting control panel (e.g. cPanel, DirectAdmin, or Plesk).
2. Navigate to **MySQL Databases** (or **MySQL Database Wizard**).
3. Create a new database (e.g., `user_capcutstore`).

#### STEP 2: Create Database User
1. Under **Add New User**, enter a username (e.g., `user_dbadmin`).
2. Generate a secure password and save it securely.
3. Click **Create User**.

#### STEP 3: Assign Permissions
1. Under **Add User to Database**, select the user and database you just created.
2. Select **ALL PRIVILEGES** and click **Make Changes**.

#### STEP 4: Import Database Schema & Initial Data
1. In your hosting panel, open **phpMyAdmin**.
2. Select your newly created database on the left panel.
3. Click the **Import** tab on the top menu.
4. Choose the `database.sql` (or `database/schema.sql`) file included with the package.
5. Ensure character set is set to `utf8mb4` and click **Import**.
   - *Result: All 18 tables, initial products, default settings, and admin accounts will be created.*

#### STEP 5: Upload Application Files
1. Open **File Manager** (or connect via FTP / SFTP).
2. Upload the files into your web root directory (usually `public_html/`).
3. Ensure the root `.htaccess` and `public/.htaccess` files are uploaded (in cPanel File Manager, ensure "Show Hidden Files" is checked).
4. Set folder permissions:
   - `storage/` and all subfolders: `775` (or writable by web server)
   - `bootstrap/cache/`: `775`

#### STEP 6: Configure Environment Settings (.env)
1. In the root directory, open or create `.env` using `.env.example` as a template.
2. Update the environment and database connection values:
   ```dotenv
   APP_NAME="CapCut Pro Store"
   APP_ENV=production
   APP_KEY=base64:755Z9p9Uq6pX3r39qIeR8gV7g2O4zB2uM4kL6jH1dE0=
   APP_DEBUG=false
   APP_URL=https://yourdomain.com

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_cpanel_database
   DB_USERNAME=your_cpanel_username
   DB_PASSWORD="your_database_password"

   # Leave Razorpay credentials blank — configured securely via Admin Panel!
   RAZORPAY_KEY_ID=
   RAZORPAY_KEY_SECRET=
   ```
3. Save the `.env` file. Notice that Razorpay keys do not need to be written here.

#### STEP 7: Open the Public Website
1. Navigate to your domain in the web browser: `https://yourdomain.com`.
2. Confirm the homepage loads with high-performance dark theme, YouTube tutorial, 2x2 login tutorial cards, and live pricing.

#### STEP 8: Open the Admin Panel
1. Navigate to `https://yourdomain.com/admin/login`.
2. Enter the default administrator credentials:
   - **Email:** `admin@capcutstore.local`
   - **Password:** `Admin@CapCut2026!`
3. Log in to access the administrator dashboard. *(Recommended: Update your admin password from the settings/profile screen upon first login).*

#### STEP 9: Configure Store Name & Logo
1. In the sidebar, click **Store Configuration** (`/admin/settings`).
2. Under the **General & WhatsApp** tab:
   - Set **Store Name** (e.g., `Pro Video Desk`).
   - Set **Optional Badge / Label** (e.g., `OFFICIAL`).
   - Upload your custom **Store Logo** (PNG/SVG/WebP) or use the default.
   - Enter your customer support **WhatsApp Number** (with country code, e.g., `919876543210`).
3. Click **Save All Settings**. Changes immediately reflect on the public storefront.

#### STEP 10: Configure Product & Pricing
1. Navigate to **Products Management** (`/admin/products`).
2. Edit the main product:
   - **Name:** CapCut Pro — 1 Month
   - **Effective Price:** Set your selling price (e.g., ₹299.00).
   - **AI Credits:** 1200
   - **Bonus:** Canva Pro 1 Year FREE
3. Save product details.

#### STEP 11: Open Payment Settings
1. Go back to **Store Configuration** > Click **Razorpay Gateway** tab.

#### STEP 12: Enter Razorpay Test Key ID
- Paste your test Key ID (starts with `rzp_test_...`).

#### STEP 13: Enter Razorpay Test Secret
- Paste your test Key Secret. It will be encrypted in the database using AES-256 via `APP_KEY` and will never be revealed to browsers.

#### STEP 14: Select Test Mode
- Ensure the **Test Mode** pill button is selected (indicated by the amber badge: `TEST MODE ACTIVE`).

#### STEP 15: Click [ Test Connection ]
1. Click the **Test Connection** button.
2. The server makes an authentic server-side verification request to Razorpay's API.
3. Verify that the banner displays:
   `✓ Razorpay Test Mode Connected Successfully`
4. Click **Save Settings**.

#### STEP 16: Run a Complete Test Payment
1. Open the homepage in an incognito window: `https://yourdomain.com`.
2. Click **⚡ Get CapCut Pro Now**.
3. Enter test details:
   - Name: `Test Customer`
   - WhatsApp: `9876543210`
   - Email: `test@example.com`
4. The Razorpay checkout modal opens.
5. In Razorpay Test Mode, select **UPI** or **Netbanking**, choose **Success**, and complete the checkout.
6. The browser redirects to the secure Order Confirmation page (`/orders/CAP-XXXXXX-XXX?token=...`).

#### STEP 17: Verify Customer & Order Data in Admin
1. Go back to Admin > **Orders Management** (`/admin/orders`).
2. Verify that the new order appears with:
   - Status: `Paid`
   - Razorpay Order ID & Payment ID recorded
   - Customer Name, Email, and WhatsApp number recorded
3. Test lead capture: Open the checkout modal again, enter only a phone number, and close the window. Refresh Admin orders and observe the **LEAD / INCOMPLETE** record saved under the **Drafts** / **Abandoned** filter.

#### STEP 18: Switch to Live Mode When Ready
1. When you are ready to accept real payments, open Admin > **Store Configuration** > **Razorpay Gateway**.
2. Enter your live credentials (`rzp_live_...` and live secret).
3. Click **Live Mode**.
4. Confirm the safety modal: `Switch to Live Mode?`
5. Click **Test Connection** to verify live API authorization.
6. Click **Save Settings**. Your store is now fully operational in production!
