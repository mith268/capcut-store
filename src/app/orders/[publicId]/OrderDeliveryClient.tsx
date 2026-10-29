"use client";

import { useState } from "react";
import Link from "next/link";
import { formatINR } from "@/lib/format";
import {
  ShieldCheck,
  CheckCircle2,
  Clock,
  AlertTriangle,
  Copy,
  Check,
  Eye,
  EyeOff,
  ExternalLink,
  Gift,
  HelpCircle,
  Download,
  Lock,
  ArrowRight,
  Loader2,
  RefreshCw,
} from "lucide-react";
import { WhatsAppIcon } from "@/components/ui/WhatsAppIcon";

interface OrderDeliveryClientProps {
  order: {
    publicId: string;
    orderNumber: string;
    customerName: string;
    customerEmail: string;
    customerPhone: string | null;
    productTitle: string;
    productSlug: string;
    planName: string;
    duration: string;
    unitPrice: number;
    discountAmount: number;
    totalAmount: number;
    currency: string;
    paymentStatus: string;
    fulfillmentStatus: string;
    bonusStatus: string;
    bonusCustomerInput: string | null;
    paidAt: string | null;
    deliveryMethod: string;
    deliveryInstructions: string | null;
  };
  credentials: {
    emailOrUsername?: string;
    password?: string;
    activationCode?: string;
    activationLink?: string;
    downloadUrl?: string;
    notes?: string;
  } | null;
  isAuthorized: boolean;
  initialToken?: string;
}

export function OrderDeliveryClient({
  order,
  credentials,
  isAuthorized: initialIsAuthorized,
  initialToken = "",
}: OrderDeliveryClientProps) {
  const [isAuthorized, setIsAuthorized] = useState(initialIsAuthorized);
  const [unlockEmail, setUnlockEmail] = useState("");
  const [unlockLoading, setUnlockLoading] = useState(false);
  const [unlockError, setUnlockError] = useState<string | null>(null);

  // Password visibility
  const [showPassword, setShowPassword] = useState(false);
  const [copiedKey, setCopiedKey] = useState<string | null>(null);

  // Bonus claim state
  const [bonusEmail, setBonusEmail] = useState("");
  const [bonusSubmitting, setBonusSubmitting] = useState(false);
  const [bonusSuccessMsg, setBonusSuccessMsg] = useState<string | null>(null);
  const [bonusErrorMsg, setBonusErrorMsg] = useState<string | null>(null);
  const [bonusStatus, setBonusStatus] = useState(order.bonusStatus);
  const [bonusInput, setBonusInput] = useState(order.bonusCustomerInput);

  function copyToClipboard(text: string, key: string) {
    if (!navigator.clipboard) return;
    navigator.clipboard.writeText(text);
    setCopiedKey(key);
    setTimeout(() => setCopiedKey(null), 2500);
  }

  async function handleUnlock(e: React.FormEvent) {
    e.preventDefault();
    setUnlockError(null);
    setUnlockLoading(true);

    try {
      const res = await fetch("/api/orders/lookup", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          orderNumber: order.orderNumber,
          email: unlockEmail.trim(),
        }),
      });

      const data = await res.json();
      if (!res.ok) {
        setUnlockError(data.error || "Email verification failed.");
      } else {
        setIsAuthorized(true);
        if (data.redirectUrl) {
          window.location.href = data.redirectUrl;
        }
      }
    } catch {
      setUnlockError("Network error. Please try again.");
    } finally {
      setUnlockLoading(false);
    }
  }

  async function handleClaimBonus(e: React.FormEvent) {
    e.preventDefault();
    setBonusErrorMsg(null);
    setBonusSuccessMsg(null);
    setBonusSubmitting(true);

    try {
      const res = await fetch(`/api/orders/${order.publicId}/claim-bonus`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          bonusEmail: bonusEmail.trim(),
        }),
      });

      const data = await res.json();
      if (!res.ok) {
        setBonusErrorMsg(data.error || "Failed to submit bonus request.");
      } else {
        setBonusSuccessMsg("Your Canva Pro bonus has been recorded! Invitation will arrive in 2-4 hours.");
        setBonusStatus("PROCESSING");
        setBonusInput(bonusEmail.trim());
      }
    } catch {
      setBonusErrorMsg("Error submitting bonus. Please contact WhatsApp support.");
    } finally {
      setBonusSubmitting(false);
    }
  }

  const isDelivered = order.fulfillmentStatus === "DELIVERED";
  const isPending = order.fulfillmentStatus === "PENDING" || order.fulfillmentStatus === "PROCESSING";

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8 bg-[#F8FAFC]">
      {/* Top Breadcrumb & Status */}
      <div className="flex flex-wrap items-center justify-between gap-4">
        <Link
          href={`/products/${order.productSlug}`}
          className="text-xs text-[#64748B] hover:text-[#0F172A] flex items-center gap-1 font-semibold transition"
        >
          ← Return to Storefront
        </Link>
        <div className="flex items-center gap-2">
          <span className="text-xs text-[#64748B]">Order Reference:</span>
          <span className="font-mono text-xs font-bold text-[#0F172A] bg-white border border-[#E2E8F0] px-2.5 py-1 rounded-md shadow-soft-sm">
            #{order.orderNumber}
          </span>
        </div>
      </div>

      {/* Hero Order Banner */}
      <div className="p-6 sm:p-8 rounded-3xl bg-white border border-[#E2E8F0] shadow-soft-md space-y-6">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E2E8F0] pb-6">
          <div className="space-y-1">
            <span className="text-[10px] font-mono uppercase tracking-wider text-brand-blue font-bold bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
              Verified Order Delivery
            </span>
            <h1 className="text-2xl sm:text-3xl font-black text-[#0F172A] tracking-tight">
              {order.productTitle}
            </h1>
            <p className="text-xs text-[#64748B]">
              Plan: <span className="font-bold text-[#0F172A]">{order.planName}</span> • Duration: {order.duration}
            </p>
          </div>

          <div className="flex items-center gap-2">
            {isDelivered ? (
              <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 border border-emerald-200 text-emerald-700 shadow-soft-sm">
                <ShieldCheck className="w-3.5 h-3.5 text-emerald-600" />
                Access Delivered
              </span>
            ) : isPending ? (
              <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-purple-50 border border-purple-200 text-purple-700 shadow-soft-sm">
                <RefreshCw className="w-3.5 h-3.5 animate-spin text-brand-purple" />
                Processing Delivery
              </span>
            ) : (
              <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-rose-50 border border-rose-200 text-rose-700 shadow-soft-sm">
                <AlertTriangle className="w-3.5 h-3.5 text-rose-600" />
                {order.fulfillmentStatus}
              </span>
            )}
          </div>
        </div>

        {/* Pricing & Customer Metadata */}
        <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-2 text-xs">
          <div>
            <span className="text-[#64748B] block mb-0.5 font-medium">Customer</span>
            <span className="text-[#0F172A] font-bold">{order.customerName}</span>
          </div>
          <div>
            <span className="text-[#64748B] block mb-0.5 font-medium">Delivery Email</span>
            <span className="text-[#0F172A] font-bold break-all">{order.customerEmail}</span>
          </div>
          <div>
            <span className="text-[#64748B] block mb-0.5 font-medium">Amount Paid</span>
            <span className="font-black text-sm text-brand-blue font-mono">
              {formatINR(order.totalAmount)}
            </span>
          </div>
          <div>
            <span className="text-[#64748B] block mb-0.5 font-medium">Delivered / Paid At</span>
            <span className="text-[#0F172A] font-bold">
              {order.paidAt ? new Date(order.paidAt).toLocaleDateString("en-IN", { day: "numeric", month: "short", year: "numeric" }) : "In progress"}
            </span>
          </div>
        </div>
      </div>

      {/* Main Delivery Box */}
      {!isAuthorized ? (
        /* Guest Authorization Gate */
        <div className="bg-white border border-amber-200 rounded-3xl p-6 md:p-8 shadow-soft-md">
          <div className="max-w-md mx-auto text-center">
            <div className="w-12 h-12 rounded-full bg-amber-50 border border-amber-200 flex items-center justify-center mx-auto mb-4 text-amber-600">
              <Lock className="w-6 h-6" />
            </div>
            <h2 className="text-xl font-bold text-[#0F172A] mb-2">Protected Digital Order</h2>
            <p className="text-xs text-[#64748B] mb-6">
              To reveal your digital licenses and login credentials, please confirm the email address used during purchase.
            </p>

            <form onSubmit={handleUnlock} className="space-y-4 text-left">
              {unlockError && (
                <div className="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                  {unlockError}
                </div>
              )}
              <div>
                <label className="block text-xs font-semibold text-[#475569] mb-1.5">
                  Confirm Purchase Email
                </label>
                <input
                  type="email"
                  placeholder="name@example.com"
                  value={unlockEmail}
                  onChange={(e) => setUnlockEmail(e.target.value)}
                  className="w-full bg-[#F8FAFC] border border-[#E2E8F0] focus:border-brand-blue rounded-xl px-4 py-2.5 text-sm text-[#0F172A] placeholder-[#94A3B8] outline-none"
                  required
                />
              </div>
              <button
                type="submit"
                disabled={unlockLoading}
                className="w-full py-3 px-4 rounded-xl font-bold text-xs btn-primary-blue text-white flex items-center justify-center gap-2 transition shadow-soft-sm"
              >
                {unlockLoading ? <Loader2 className="w-4 h-4 animate-spin" /> : <ShieldCheck className="w-4 h-4" />}
                Unlock Credentials
              </button>
            </form>
          </div>
        </div>
      ) : isDelivered && credentials ? (
        /* Unlocked Delivered Credentials */
        <div className="bg-white border border-blue-200 rounded-3xl p-6 md:p-8 shadow-soft-lg space-y-6">
          <div className="flex items-center justify-between border-b border-[#E2E8F0] pb-4">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-brand-blue">
                <ShieldCheck className="w-5 h-5" />
              </div>
              <div>
                <h2 className="text-lg font-bold text-[#0F172A]">Your Digital Access Credentials</h2>
                <p className="text-xs text-[#64748B]">Encrypted in vault and ready for immediate activation.</p>
              </div>
            </div>
            <div className="text-xs text-emerald-700 font-semibold flex items-center gap-1 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
              <Check className="w-3.5 h-3.5" /> Instant Delivery
            </div>
          </div>

          <div className="space-y-4">
            {/* Username / Email */}
            {credentials.emailOrUsername && (
              <div className="bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-soft-sm">
                <div>
                  <span className="text-[11px] font-mono uppercase text-[#64748B] block mb-0.5 font-semibold">
                    Account Login / Username
                  </span>
                  <span className="text-[#0F172A] font-mono font-bold text-sm select-all">
                    {credentials.emailOrUsername}
                  </span>
                </div>
                <button
                  type="button"
                  onClick={() => copyToClipboard(credentials.emailOrUsername!, "user")}
                  className="px-3 py-1.5 rounded-lg bg-white border border-[#E2E8F0] hover:bg-[#F1F5F9] text-[#0F172A] text-xs font-semibold flex items-center gap-1.5 transition self-start sm:self-auto shadow-soft-sm"
                >
                  {copiedKey === "user" ? <Check className="w-3.5 h-3.5 text-emerald-600" /> : <Copy className="w-3.5 h-3.5 text-[#64748B]" />}
                  {copiedKey === "user" ? "Copied!" : "Copy"}
                </button>
              </div>
            )}

            {/* Password */}
            {credentials.password && (
              <div className="bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-soft-sm">
                <div>
                  <span className="text-[11px] font-mono uppercase text-[#64748B] block mb-0.5 font-semibold">
                    Password / Master Key
                  </span>
                  <span className="text-[#0F172A] font-mono font-bold text-sm tracking-wider select-all">
                    {showPassword ? credentials.password : "••••••••••••••••"}
                  </span>
                </div>
                <div className="flex items-center gap-2 self-start sm:self-auto">
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="p-2 rounded-lg bg-white border border-[#E2E8F0] hover:bg-[#F1F5F9] text-[#64748B] text-xs transition shadow-soft-sm"
                    title={showPassword ? "Hide Password" : "Show Password"}
                  >
                    {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                  </button>
                  <button
                    type="button"
                    onClick={() => copyToClipboard(credentials.password!, "pass")}
                    className="px-3 py-1.5 rounded-lg bg-white border border-[#E2E8F0] hover:bg-[#F1F5F9] text-[#0F172A] text-xs font-semibold flex items-center gap-1.5 transition shadow-soft-sm"
                  >
                    {copiedKey === "pass" ? <Check className="w-3.5 h-3.5 text-emerald-600" /> : <Copy className="w-3.5 h-3.5 text-[#64748B]" />}
                    {copiedKey === "pass" ? "Copied!" : "Copy"}
                  </button>
                </div>
              </div>
            )}

            {/* Activation Code / Key */}
            {credentials.activationCode && (
              <div className="bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-soft-sm">
                <div>
                  <span className="text-[11px] font-mono uppercase text-[#64748B] block mb-0.5 font-semibold">
                    License / Activation Code
                  </span>
                  <span className="text-[#0F172A] font-mono font-bold text-sm select-all">
                    {credentials.activationCode}
                  </span>
                </div>
                <button
                  type="button"
                  onClick={() => copyToClipboard(credentials.activationCode!, "code")}
                  className="px-3 py-1.5 rounded-lg bg-white border border-[#E2E8F0] hover:bg-[#F1F5F9] text-[#0F172A] text-xs font-semibold flex items-center gap-1.5 transition self-start sm:self-auto shadow-soft-sm"
                >
                  {copiedKey === "code" ? <Check className="w-3.5 h-3.5 text-emerald-600" /> : <Copy className="w-3.5 h-3.5 text-[#64748B]" />}
                  {copiedKey === "code" ? "Copied!" : "Copy"}
                </button>
              </div>
            )}

            {/* Activation Link */}
            {credentials.activationLink && (
              <div className="bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-soft-sm">
                <div className="min-w-0 pr-2">
                  <span className="text-[11px] font-mono uppercase text-[#64748B] block mb-0.5 font-semibold">
                    Direct Activation URL
                  </span>
                  <span className="text-brand-blue font-mono text-xs truncate block font-medium">
                    {credentials.activationLink}
                  </span>
                </div>
                <a
                  href={credentials.activationLink}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="px-4 py-2 rounded-xl btn-primary-blue text-white font-bold text-xs flex items-center gap-1.5 transition whitespace-nowrap self-start sm:self-auto shadow-soft-sm"
                >
                  Open Link <ExternalLink className="w-3.5 h-3.5" />
                </a>
              </div>
            )}

            {/* Download URL */}
            {credentials.downloadUrl && (
              <div className="bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-soft-sm">
                <div>
                  <span className="text-[11px] font-mono uppercase text-[#64748B] block mb-0.5 font-semibold">
                    Digital Download File
                  </span>
                  <span className="text-[#475569] text-xs">Official software build / assets package</span>
                </div>
                <a
                  href={credentials.downloadUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                  download
                  className="px-4 py-2 rounded-xl btn-whatsapp text-white font-bold text-xs flex items-center gap-1.5 transition self-start sm:self-auto shadow-soft-sm"
                >
                  <Download className="w-3.5 h-3.5" /> Download Now
                </a>
              </div>
            )}

            {/* Additional Notes / Setup guide */}
            {(credentials.notes || order.deliveryInstructions) && (
              <div className="mt-4 p-4 rounded-xl bg-blue-50/70 border border-blue-100 text-xs text-[#1E40AF] space-y-1">
                <div className="font-bold text-[#0F172A] flex items-center gap-1.5 mb-1">
                  <HelpCircle className="w-3.5 h-3.5 text-brand-blue" /> Setup & Activation Instructions:
                </div>
                <p className="whitespace-pre-wrap text-[#475569] leading-relaxed">
                  {credentials.notes || order.deliveryInstructions}
                </p>
              </div>
            )}
          </div>
        </div>
      ) : (
        /* Manual Pending / Restocking Box */
        <div className="bg-white border border-amber-200 rounded-3xl p-6 md:p-8 shadow-soft-md">
          <div className="flex items-start gap-4">
            <div className="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600 shrink-0">
              <Clock className="w-6 h-6 animate-pulse" />
            </div>
            <div>
              <h2 className="text-lg font-bold text-[#0F172A] mb-1">
                Payment Received & Under Rapid Setup
              </h2>
              <p className="text-xs text-[#64748B] leading-relaxed">
                Thank you! Your payment has been securely confirmed. Our fulfillment specialist is configuring your private access credentials and will deliver them here shortly.
              </p>
              <div className="mt-4 flex flex-wrap gap-3">
                <button
                  onClick={() => window.location.reload()}
                  className="px-4 py-2 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] hover:bg-white text-[#0F172A] font-semibold text-xs flex items-center gap-2 transition shadow-soft-sm"
                >
                  <RefreshCw className="w-3.5 h-3.5" /> Refresh Status
                </button>
                <a
                  href={`https://wa.me/919990798996?text=${encodeURIComponent(
                    `Hi! I just purchased Order #${order.orderNumber} for ${order.productTitle}. Please help me with activation.`
                  )}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="px-4 py-2 rounded-xl btn-whatsapp text-white font-bold text-xs flex items-center gap-2 transition shadow-soft-sm"
                >
                  <WhatsAppIcon className="w-4 h-4 text-white" /> WhatsApp Priority Activation
                </a>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Bonus Claim Card (Canva Pro / Free Extras) */}
      {order.bonusStatus !== "NOT_APPLICABLE" && (
        <div className="bg-gradient-to-r from-purple-50 via-white to-purple-50 border border-purple-200 rounded-3xl p-6 md:p-8 shadow-soft-md">
          <div className="flex items-center gap-2 text-xs font-bold text-brand-purple uppercase tracking-wider mb-2">
            <Gift className="w-4 h-4 text-brand-purple" /> Included Free Bonus
          </div>
          <h3 className="text-xl font-bold text-[#581C87] mb-2">
            Claim Your Canva Pro 1-Year Free Bonus
          </h3>
          <p className="text-xs text-[#6B21A8] mb-6 max-w-xl">
            As part of your special order bundle, you get free Canva Pro access! Enter the email of your Canva account below to receive your activation invite.
          </p>

          {bonusStatus === "PROCESSING" || bonusStatus === "DELIVERED" ? (
            <div className="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-3">
              <CheckCircle2 className="w-5 h-5 text-emerald-600 shrink-0" />
              <div>
                <span className="font-bold block text-[#0F172A]">Canva Bonus Registered</span>
                Invite will be dispatched to: <span className="font-mono text-brand-blue font-bold">{bonusInput}</span>
              </div>
            </div>
          ) : (
            <form onSubmit={handleClaimBonus} className="max-w-md space-y-3">
              {bonusSuccessMsg && (
                <div className="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs">
                  {bonusSuccessMsg}
                </div>
              )}
              {bonusErrorMsg && (
                <div className="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                  {bonusErrorMsg}
                </div>
              )}
              <div className="flex flex-col sm:flex-row gap-2">
                <input
                  type="email"
                  placeholder="your-canva-email@gmail.com"
                  value={bonusEmail}
                  onChange={(e) => setBonusEmail(e.target.value)}
                  className="flex-1 bg-white border border-[#E2E8F0] focus:border-brand-purple rounded-xl px-4 py-2.5 text-sm text-[#0F172A] placeholder-[#94A3B8] outline-none shadow-soft-sm"
                  required
                />
                <button
                  type="submit"
                  disabled={bonusSubmitting}
                  className="py-2.5 px-5 rounded-xl font-bold text-xs btn-purple-bonus text-white flex items-center justify-center gap-1.5 transition whitespace-nowrap shadow-soft-sm"
                >
                  {bonusSubmitting ? <Loader2 className="w-4 h-4 animate-spin" /> : <Gift className="w-4 h-4" />}
                  Claim Bonus
                </button>
              </div>
              <p className="text-[11px] text-[#64748B]">
                Invitation link will be emailed to this address within 2-4 hours.
              </p>
            </form>
          )}
        </div>
      )}

      {/* Support & Warranty Bar */}
      <div className="bg-white border border-[#E2E8F0] rounded-2xl p-6 flex flex-col md:flex-row items-center justify-between gap-4 shadow-soft-sm">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center text-brand-whatsapp">
            <WhatsAppIcon className="w-5 h-5 text-brand-whatsapp" />
          </div>
          <div>
            <h4 className="text-sm font-bold text-[#0F172A]">Need Live Activation Assistance?</h4>
            <p className="text-xs text-[#64748B]">Our customer success team is available on WhatsApp.</p>
          </div>
        </div>

        <a
          href={`https://wa.me/919990798996?text=${encodeURIComponent(
            `Hi! I have a question regarding my order #${order.orderNumber}.`
          )}`}
          target="_blank"
          rel="noopener noreferrer"
          className="px-5 py-2.5 rounded-xl btn-whatsapp text-white font-bold text-xs flex items-center gap-2 transition shadow-soft-sm"
        >
          <WhatsAppIcon className="w-4 h-4 text-white" /> Message Support
        </a>
      </div>
    </div>
  );
}
