# Cloudflare Production Deployment Guide

This guide details how to deploy the **Convora Digital Commerce Storefront** to Cloudflare.

---

## Architecture Overview

This application is built with:
- **Next.js 14 App Router** (React 18, TypeScript, Tailwind CSS)
- **Prisma ORM** (Compatible with PostgreSQL and SQLite)
- **Razorpay Payments & Webhook Verification**
- **Meta Pixel & Conversions API (CAPI)**
- **AES-256-GCM Encrypted Vault** for digital credentials

---

## Deployment Option 1: Cloudflare Pages (Serverless Edge)

### Step 1: Connect GitHub Repository
1. Log in to the [Cloudflare Dashboard](https://dash.cloudflare.com/).
2. Navigate to **Workers & Pages** → **Create application** → **Pages** → **Connect to Git**.
3. Select this repository: `mith268/capcut-store`.

### Step 2: Build & Output Settings
- **Framework Preset:** `Next.js`
- **Build Command:** `npx prisma generate && npm run build`
- **Build Output Directory:** `.next`
- **Root Directory:** `/`

### Step 3: Set Environment Variables
In the Cloudflare Pages settings under **Settings** → **Environment variables**, configure:

| Variable | Description | Example |
| :--- | :--- | :--- |
| `NODE_ENV` | Production environment | `production` |
| `DATABASE_URL` | PostgreSQL connection string | `postgresql://user:pass@host:5432/db?sslmode=require` |
| `JWT_SECRET` | 32+ character random string | `your-secure-jwt-secret-key-32-chars-min` |
| `ENCRYPTION_KEY` | 32-byte (64 hex characters) AES key | Generate via `openssl rand -hex 32` |
| `RAZORPAY_KEY_ID` | Razorpay Live/Test Key ID | `rzp_live_...` or `rzp_test_...` |
| `RAZORPAY_KEY_SECRET`| Razorpay Key Secret | `your_razorpay_secret` |
| `RAZORPAY_WEBHOOK_SECRET`| Webhook verification secret | `your_webhook_secret` |
| `APP_URL` | Your production custom domain | `https://yourdomain.com` |

> **Database Note:** Cloudflare Workers run on an edge runtime without a persistent local hard disk. Use a managed cloud PostgreSQL database such as [Supabase](https://supabase.com) (Free) or [Neon](https://neon.tech) (Free).

---

## Deployment Option 2: Cloudflare + Vercel / Railway / VPS (Zero-Friction)

If you prefer a 100% standard Node.js server with Cloudflare's global CDN and security:
1. Deploy the repository to **Vercel** or **Railway** by importing `mith268/capcut-store`.
2. Add your custom domain to **Cloudflare DNS**.
3. Enable the **Cloudflare Proxy (Orange Cloud ☁️)** on your DNS records.
4. Set SSL/TLS encryption mode to **Full (Strict)**.

You get the full performance of Cloudflare's global edge caching, DDoS mitigation, and firewall protection while running native Node.js and Razorpay verification.

---

## Deployment Option 3: Self-Hosted Docker with Cloudflare Tunnel

1. Build and run the included `Dockerfile`:
   ```bash
   docker build -t capcut-store .
   docker run -p 3000:3000 --env-file .env capcut-store
   ```
2. In Cloudflare Zero Trust dashboard, create a **Cloudflare Tunnel** pointing to `http://localhost:3000`.
3. No open firewall ports or static IP needed!
