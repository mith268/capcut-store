# Production Launch Checklist

Before making your store live to the public, verify each item on this checklist:

---

### Security & Environment
- [ ] `APP_DEBUG=false` in `.env` (Never expose stack traces or DB credentials in production).
- [ ] `APP_ENV=production` configured.
- [ ] `APP_URL` matches your live HTTPS production URL (e.g. `https://yourstore.com`).
- [ ] Valid 256-bit SSL Certificate is active (Force HTTPS on server).
- [ ] `APP_KEY` has been generated via `php artisan key:generate`.
- [ ] Database credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) verified.
- [ ] Default administrator password changed from `Admin@CapCut2026!` to a strong unique password.
- [ ] `.env` file permissions set to `600` or `640` and inaccessible via web browser.

---

### Payment & Webhooks
- [ ] Razorpay live `RAZORPAY_KEY_ID` configured in `.env` or Admin Settings.
- [ ] Razorpay live `RAZORPAY_KEY_SECRET` configured in `.env` or Admin Settings.
- [ ] Razorpay Webhook configured in Razorpay Dashboard:
  - **Webhook URL:** `https://yourstore.com/webhooks/razorpay`
  - **Active Events:** `payment.captured`, `order.paid`, `payment.failed`, `refund.processed`
  - **Secret:** Matches `RAZORPAY_WEBHOOK_SECRET`
- [ ] Test payment executed and cryptographic signature verified server-side.
- [ ] Test failed/dismissed checkout handled gracefully.
- [ ] Webhook duplicate event idempotency verified.

---

### Marketing & Tracking
- [ ] WhatsApp support number configured (digits with country code, e.g. `919876543210`).
- [ ] WhatsApp Checkout click event tracking tested.
- [ ] Meta Pixel ID configured in Admin Settings.
- [ ] Meta Conversions API (CAPI) Access Token configured in Admin Settings.
- [ ] Meta Purchase event fires with matching `event_id` for deduplication.
- [ ] UTM attribution parameters (`utm_source`, `utm_campaign`, `fbclid`) captured on checkout.

---

### Inventory & Fulfillment
- [ ] Real CapCut Pro Team account credentials uploaded into Inventory Vault.
- [ ] Password encryption at rest verified (Stored as AES-256 encrypted payload).
- [ ] Out of stock handling verified (Customer sees prompt, admin alerted).
- [ ] Credential reveal security verified (Customer access requires UUID + 64-char token).
- [ ] Credential copy buttons function with visual clipboard feedback.

---

### Legal & Ethics
- [ ] No false claims of official CapCut or ByteDance affiliation.
- [ ] Transparent disclaimer visible in footer and terms.
- [ ] 25-Day Digital Warranty policy published and visible before checkout.
- [ ] Device restrictions (1 mobile + 1 laptop) explicitly highlighted.
- [ ] Refund & Replacement policy published.
- [ ] No fabricated customer reviews or fake scarcity countdowns used.

---

### Performance & Mobile Verification
- [ ] Test layout at 360px, 375px, 390px, 414px, and 768px+ (Zero horizontal scrolling).
- [ ] Sticky bottom purchase bar works smoothly on mobile.
- [ ] Floating WhatsApp button does not overlap CTA on mobile.
- [ ] Compiled assets (`public/build/` and `public/assets/`) loading with fast Core Web Vitals.
