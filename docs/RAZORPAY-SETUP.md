# Razorpay Gateway Setup & Operations Guide

This guide explains how to acquire, configure, test, and manage Razorpay payments on your store using the Admin Panel as the configuration source of truth.

---

### 1. Where to Obtain Razorpay Credentials
1. Log in to your [Razorpay Dashboard](https://dashboard.razorpay.com).
2. Look at the top navigation bar or sidebar to check your mode:
   - **Test Mode:** For testing card, UPI, and netbanking flows without real money.
   - **Live Mode:** For accepting authentic transactions from real customers.
3. In the sidebar, navigate to:
   **Account & Settings** → **API Keys** (under *Website and app settings*).
4. Click **Generate Test Key** (or **Generate Live Key**):
   - You will see two values:
     - **Key ID:** (e.g. `rzp_test_...` or `rzp_live_...`)
     - **Key Secret:** (A confidential string shown only once upon generation)
5. Copy both values. Keep the Key Secret secure.

---

### 2. Configuring Razorpay in the Admin Panel
1. Log in to your Admin Panel (`https://yourdomain.com/admin/login`).
2. Navigate to **Store Configuration** (`/admin/settings`).
3. Click the **Razorpay Gateway** tab.
4. Fill in the fields:
   - **Gateway Mode:** Toggle **Test Mode** (or **Live Mode**).
   - **Razorpay Key ID:** Paste your Key ID.
   - **Razorpay Key Secret:** Paste your Key Secret.
5. Notice that once saved:
   - Key Secret is stored with AES-256 server-side encryption via your application key.
   - The Admin UI never reveals the raw secret again (it displays `•••••••••••••••• (Encrypted in database)`).
   - The browser never receives or stores the secret.

---

### 3. Testing the Connection Server-Side
Before accepting any payments, verify that your credentials are valid:
1. Click the **[ Test Connection ]** button in the Razorpay Gateway tab.
2. The server dispatches an authenticated ping to Razorpay's API (`/v1/orders`).
3. Feedback states:
   - **Success:** `✓ Razorpay Test Mode Connected Successfully`
   - **Failure:** `✕ Razorpay connection failed: Invalid credentials (HTTP 401 Unauthorized)` or descriptive API error message.
4. Click **Save Settings** to persist your configuration.

---

### 4. Running a Test Payment
1. Open your storefront in an incognito window: `https://yourdomain.com`.
2. Click any of the primary checkout CTAs: **⚡ Get CapCut Pro Now**.
3. Fill in the contact details:
   - Customer Name
   - WhatsApp Number
   - Email Address
4. Click **Proceed to Payment**.
5. Razorpay Standard Checkout opens in a modal window.
6. In Razorpay Test Mode:
   - Select **UPI** → Enter `success@razorpay` (or click *Simulate Success*).
   - Or select **Card** → Use any 16-digit test card number (e.g. `4111 1111 1111 1111`, CVV `123`, any future expiry date). Enter OTP `123456`.
7. Once paid, the server cryptographically verifies the HMAC-SHA256 signature:
   $$\text{Signature} = \text{HMAC-SHA256}(\text{order\_id} \parallel "|" \parallel \text{payment\_id}, \text{KEY\_SECRET})$$
8. If authentic, the order is marked `paid`, inventory is automatically assigned (if credentials are stocked), and the user is redirected to the secure order view.

---

### 5. Inspecting Orders & Incomplete Checkouts in Admin
1. Open Admin > **Orders Management** (`/admin/orders`).
2. Your test order will appear with:
   - Order Number (e.g., `CAP-789ABC-123`)
   - Customer Name, Email, and WhatsApp number
   - Amount (₹299.00)
   - Status badge: `PAID`
   - Delivery status & Canva bonus request status
3. Click on the order number to view full audit logs, gateway order IDs, and payment references.

---

### 6. Switching to Live Mode
1. In the Razorpay Dashboard, switch the environment toggle from **Test** to **Live**.
2. Generate your **Live API Key** (starts with `rzp_live_...`).
3. In your store Admin Panel (`/admin/settings` > *Razorpay Gateway*):
   - Click **Live Mode**.
   - A modal prompt will appear:
     > *"Switching to Live Mode will process real customer payments and charge actual bank accounts/cards. Ensure you have entered valid Live Razorpay Credentials."*
   - Click **[ Confirm Live Mode ]**.
   - Paste your **Live Key ID** (`rzp_live_...`).
   - Paste your **Live Key Secret**.
   - Click **[ Test Connection ]** to confirm authorization.
   - Click **[ Save Settings ]**.
4. The status badge will change to a green **LIVE MODE ACTIVE** indicator.

---

### 7. Webhook Configuration (Optional but Recommended)
For automatic payment status synchronization during asynchronous UPI timeouts:
1. In Razorpay Dashboard, go to **Settings** → **Webhooks** → **Add New Webhook**.
2. **Webhook URL:** `https://yourdomain.com/webhooks/razorpay`
3. **Secret:** Set a random secret and paste it in Admin settings (*Razorpay Webhook Secret*).
4. **Active Events:**
   - `payment.captured`
   - `payment.failed`
   - `order.paid`
5. Save the webhook in Razorpay.
