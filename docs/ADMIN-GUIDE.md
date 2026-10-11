# Store Administrator Operations Guide

This manual covers the store management features available in the Admin Portal (`/admin`).

---

### 1. Store Identity & Branding Settings
Located at: **Admin → Store Configuration → General & WhatsApp**

- **Store Name:** Sets the primary brand name across the navigation bar, hero title, page titles, footer, customer emails, and order receipts.
- **Store Badge:** Sets the small neon pill badge next to the store name (e.g. `PRO`, `OFFICIAL`, `PREMIUM`). Leave blank to hide.
- **Store Tagline:** Displayed beneath the main header and in footer metadata.
- **Store Logo:**
  - Upload a custom PNG, JPG, WebP, or SVG logo (max 3MB).
  - The uploaded logo immediately replaces the default logo across navbar, mobile menu, and invoices.
  - Check *Reset to Default Logo* to revert at any time.
- **Customer Support WhatsApp Number:**
  - Controls the floating WhatsApp button, header contact link, and direct WhatsApp purchase CTAs.
  - Format with international country code without spaces or symbols (e.g. `919876543210` for India).

---

### 2. Product & Pricing Configuration
Located at: **Admin → Products Management**

- **Effective Price:** The actual price charged at checkout. When adjusted, all storefront elements (hero badge, pricing card, checkout summaries, and schema structured data) automatically update.
- **Regular Price & Discount:** Controls the strike-through original price comparison.
- **Duration:** Typically `1 Month`.
- **AI Credits:** `1200 AI Credits`.
- **Canva Bonus:** `Canva Pro 1 Year FREE`.
- **Feature Points:** Manage the 6 bullet/feature items displayed in the product overview card.

---

### 3. Payment Gateway (Razorpay)
Located at: **Admin → Store Configuration → Razorpay Gateway**

- **Gateway Mode Toggle:** Switch seamlessly between `Test Mode` (amber indicator) and `Live Mode` (emerald indicator).
- **Live Safety Confirmation:** Switching to live mode triggers a modal confirmation to prevent accidental charges during testing.
- **Key ID & Key Secret:** Enter your credentials. The Key Secret is stored using server-side AES-256 encryption.
- **Test Connection:** Live ping verification to Razorpay API to confirm API authorization before launching.

---

### 4. Orders & Customer Lead Management
Located at: **Admin → Orders Management**

#### Status Filter Tabs:
- **All:** Full overview of every customer interaction.
- **Paid:** Completed, verified transactions.
- **Drafts:** Prospective customers who opened the checkout modal and entered at least one contact field (phone, name, or email).
- **Abandoned:** Incomplete checkout sessions where the customer typed contact info but closed the checkout modal without paying.
- **Pending Pay:** Orders where checkout was initiated with Razorpay but payment has not yet been confirmed.
- **Failed:** Transactions where payment was declined or signature verification failed.
- **Refunded:** Orders where a refund was issued.

#### Customer Contact Information:
- If a customer provides only their WhatsApp number, the record is immediately saved and displayed with:
  - `Customer: Not provided`
  - `Email: Not provided`
  - `WhatsApp: +91 98765 43210`
  - Status badge: `LEAD / INCOMPLETE`
- This ensures zero lost customer leads for follow-up!

---

### 5. Inventory & Credential Vault
Located at: **Admin → Inventory Management**

- Manage pre-stocked digital accounts (email + password combinations).
- All passwords are encrypted in the database at rest (`password_encrypted`).
- Admins can securely reveal passwords with full action auditing.
- Automated allocation: When a customer completes payment, the system automatically marks the next available credential as `sold` and attaches it to the order.

---

### 6. Canva Pro Bonus Management
Located at: **Admin → Orders Management → [Order Details]**

- After payment, customers are prompted to enter their email to request their Canva Pro 1-year bonus.
- In the Order details view, administrators can track:
  - `not_requested`: Customer hasn't submitted their Canva email yet.
  - `requested`: Customer provided an email, awaiting activation.
  - `activated`: Administrator sent the Canva team invite/license.
- Admins can update this status with a single click.

---

### 7. Tracking & Meta Ads (Pixel & CAPI)
Located at: **Admin → Store Configuration → Meta Pixel & CAPI**

- **Meta Pixel ID:** Client-side event tracking.
- **Meta Conversions API (CAPI) Access Token:** Server-side event synchronization.
- **Meta Test Event Code:** For validating events in Meta Events Manager.
- Built-in event deduplication prevents duplicate conversions on page reload.
