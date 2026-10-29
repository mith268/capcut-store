# Convora Commerce — Digital Products & Subscriptions Platform

A production-ready digital product commerce platform with high-converting SaaS landing pages, Razorpay checkout, encrypted credential vault, admin management dashboard, Meta Pixel & Conversions API attribution, and automated delivery.

---

## Features

- **Modern SaaS Landing Pages:** Light theme with subtle grid pattern, 2-column desktop hero, plan switcher, dynamic pricing cards, and official WhatsApp CTA.
- **Dedicated Product Slugs:** Individual landing pages for Meta Ads (`/products/capcut-pro`, `/products/canva-pro`, `/products/gemini-ai`, etc.).
- **Multi-Product Store Catalog:** Homepage storefront (`/`), categorized catalog (`/products`), and policy pages.
- **Encrypted Credential Delivery:** AES-256-GCM vault with guest authorization and instant on-screen access (`/orders/[publicId]`).
- **Free Bonus Claiming:** Integrated Canva Pro 1-year bonus registration.
- **Admin Management Portal:** Manage products, plans, inventory licenses, orders, coupons, settings, and Meta tracking at `/admin`.
- **Razorpay Integration:** Standard modal checkout, test & live mode support, and server signature verification.
- **Cloudflare Ready:** Pre-configured with `wrangler.toml`, `Dockerfile`, and `DEPLOY-CLOUDFLARE.md`.

---

## Local Development

```bash
# Install dependencies
npm install

# Generate Prisma Client & seed database
npx prisma generate
npm run dev
```

Visit [http://localhost:3000](http://localhost:3000) to view the storefront.

---

## Production Deployment

Refer to [`DEPLOY-CLOUDFLARE.md`](./DEPLOY-CLOUDFLARE.md) for full instructions on deploying to **Cloudflare Pages**, **Docker**, or **Vercel / VPS behind Cloudflare**.
