# Production Deployment Checklist

Use this interactive checklist to verify and track your deployment to cPanel or shared hosting.

---

### Phase 1: Pre-Upload & Database Setup
- [ ] **Create MySQL Database** in cPanel / hosting control panel.
- [ ] **Create Database User** and generate a strong password.
- [ ] **Assign User Permissions** to the database with `ALL PRIVILEGES`.
- [ ] **Import Database Schema** via phpMyAdmin using `database.sql` (or `database/schema.sql`).
- [ ] Confirm in phpMyAdmin that all 18 tables were created successfully.

---

### Phase 2: Application File Upload
- [ ] **Upload Files** to web root (`public_html/`) via cPanel File Manager or FTP.
- [ ] **Verify Hidden Files:** Confirm `.htaccess` in root and `.htaccess` in `public/` are present.
- [ ] **Set Permissions:** Ensure `storage/` and `bootstrap/cache/` are writable (`chmod 775`).
- [ ] **Configure `.env`:** Copy `.env.example` to `.env` and fill in DB credentials and `APP_URL`.
- [ ] Verify `APP_DEBUG=false` in `.env`.
- [ ] Verify `RAZORPAY_KEY_ID=` and `RAZORPAY_KEY_SECRET=` are empty in `.env`.

---

### Phase 3: Storefront & Admin Initial Configuration
- [ ] **Open Homepage:** Visit `https://yourdomain.com` in your browser. Verify dark neon UI and YouTube video.
- [ ] **Login to Admin:** Visit `https://yourdomain.com/admin/login` (`admin@capcutstore.local` / `Admin@CapCut2026!`).
- [ ] **Configure Store Branding:**
  - [ ] Set Store Name in Admin > Settings.
  - [ ] Upload Store Logo (PNG / SVG / WebP).
  - [ ] Configure WhatsApp Support Number (e.g. `919876543210`).
  - [ ] Click **Save All Settings** and verify changes reflect on storefront navbar and footer.
- [ ] **Configure Product:**
  - [ ] Check price (e.g. ₹299.00) in Admin > Products.
  - [ ] Confirm Product Title: `CapCut Pro — 1 Month (1200 AI Credits) + Canva Pro 1 Year FREE`.

---

### Phase 4: Razorpay Test Mode Verification
- [ ] Open **Admin → Store Configuration → Razorpay Gateway** tab.
- [ ] Enter **Razorpay Test Key ID** (`rzp_test_...`).
- [ ] Enter **Razorpay Test Key Secret**.
- [ ] Select **Test Mode** (amber badge: `TEST MODE ACTIVE`).
- [ ] Click **[ Test Connection ]** and verify green success response:
  - `✓ Razorpay Test Mode Connected Successfully`
- [ ] Click **Save Settings**.
- [ ] Verify Key Secret displays masked indicator: `•••••••••••••••• (Encrypted in database)`.

---

### Phase 5: End-to-End Test Transactions
- [ ] Open incognito browser window on storefront.
- [ ] Click **⚡ Get CapCut Pro Now**.
- [ ] **Lead Capture Test:** Enter only a phone number into the modal, then close modal.
  - [ ] In Admin > Orders, verify lead is saved under **Drafts** / **Abandoned**.
- [ ] **Complete Test Payment:**
  - [ ] Reopen checkout, enter complete name, email, and WhatsApp number.
  - [ ] Proceed to Razorpay Test Checkout.
  - [ ] Simulate successful payment.
  - [ ] Confirm redirect to Order Confirmation page with access token.
  - [ ] In Admin > Orders, verify order is marked **PAID** with gateway transaction ID.
- [ ] **Canva Bonus Test:** On order confirmation page, enter Canva bonus email and verify status changes to `requested` in Admin.

---

### Phase 6: Responsive & Device QA Pass
- [ ] **Mobile (360px–430px):** Verify hamburger menu, centered 2x2 login tutorial cards, YouTube video aspect ratio, floating WhatsApp button, and sticky CTA.
- [ ] **Tablet (768px–1024px):** Verify grid layouts, pricing cards, and admin dashboard tables.
- [ ] **Desktop (1280px–1920px):** Verify hero split-column video embed, navbar actions, and full layout symmetry.

---

### Phase 7: Live Mode Activation
- [ ] Enter **Live Razorpay Key ID** (`rzp_live_...`) in Admin Settings.
- [ ] Enter **Live Razorpay Key Secret**.
- [ ] Click **Live Mode** and confirm the safety prompt: `[ Confirm Live Mode ]`.
- [ ] Click **[ Test Connection ]** to verify live credentials against Razorpay API.
- [ ] Click **Save Settings**.
- [ ] Verify green status badge: **LIVE MODE ACTIVE**.
- [ ] (Optional) Configure Razorpay Webhook URL: `https://yourdomain.com/webhooks/razorpay`.
- [ ] Perform 1 real live micro-payment (e.g. ₹1) to confirm final bank gateway settlement.
- [ ] Store is officially LIVE and production-ready!
