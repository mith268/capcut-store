"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import {
  CreditCard,
  Activity,
  Store,
  MessageCircle,
  Save,
  CheckCircle2,
  AlertTriangle,
  Loader2,
  ShieldCheck,
  Eye,
  EyeOff,
  Radio,
} from "lucide-react";

interface AdminSettingsClientProps {
  initialSettings: Record<string, string>;
}

export function AdminSettingsClient({ initialSettings }: AdminSettingsClientProps) {
  const router = useRouter();

  // Razorpay
  const [razorpayKeyId, setRazorpayKeyId] = useState(initialSettings["razorpay_key_id"] || "");
  const [razorpayKeySecret, setRazorpayKeySecret] = useState(initialSettings["razorpay_key_secret"] || "");
  const [razorpayWebhookSecret, setRazorpayWebhookSecret] = useState(initialSettings["razorpay_webhook_secret"] || "");
  const [razorpayMode, setRazorpayMode] = useState(initialSettings["razorpay_mode"] || "test");
  const [showRazorpaySecret, setShowRazorpaySecret] = useState(false);

  // Meta Tracking
  const [metaPixelId, setMetaPixelId] = useState(initialSettings["meta_pixel_id"] || "");
  const [metaAccessToken, setMetaAccessToken] = useState(initialSettings["meta_access_token"] || "");
  const [metaTestEventCode, setMetaTestEventCode] = useState(initialSettings["meta_test_event_code"] || "");
  const [metaTrackingEnabled, setMetaTrackingEnabled] = useState(
    initialSettings["meta_tracking_enabled"] !== "false"
  );
  const [showMetaToken, setShowMetaToken] = useState(false);

  // Store Identity
  const [storeName, setStoreName] = useState(initialSettings["store_name"] || "Convora Store");
  const [storeTagline, setStoreTagline] = useState(
    initialSettings["store_tagline"] || "Instant Digital Creator Software & Subscriptions"
  );
  const [supportEmail, setSupportEmail] = useState(initialSettings["support_email"] || "support@capcutstore.local");

  // WhatsApp
  const [whatsappNumber, setWhatsappNumber] = useState(initialSettings["whatsapp_number"] || "+919999999999");
  const [whatsappMessage, setWhatsappMessage] = useState(
    initialSettings["whatsapp_default_message"] || "Hi! I need help with my digital license order."
  );

  // Status
  const [saving, setSaving] = useState(false);
  const [successMsg, setSuccessMsg] = useState<string | null>(null);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);

  async function handleSave(e: React.FormEvent) {
    e.preventDefault();
    setSaving(true);
    setSuccessMsg(null);
    setErrorMsg(null);

    try {
      const res = await fetch("/api/admin/settings", {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          settings: {
            razorpay_key_id: razorpayKeyId.trim(),
            razorpay_key_secret: razorpayKeySecret.trim(),
            razorpay_webhook_secret: razorpayWebhookSecret.trim(),
            razorpay_mode: razorpayMode,
            meta_pixel_id: metaPixelId.trim(),
            meta_access_token: metaAccessToken.trim(),
            meta_test_event_code: metaTestEventCode.trim(),
            meta_tracking_enabled: String(metaTrackingEnabled),
            store_name: storeName.trim(),
            store_tagline: storeTagline.trim(),
            support_email: supportEmail.trim(),
            whatsapp_number: whatsappNumber.trim(),
            whatsapp_default_message: whatsappMessage.trim(),
          },
        }),
      });

      const data = await res.json();
      if (!res.ok) {
        setErrorMsg(data.error || "Failed to update settings.");
      } else {
        setSuccessMsg("Settings saved successfully! Payments and tracking updated instantly.");
        setTimeout(() => setSuccessMsg(null), 3500);
        router.refresh();
      }
    } catch {
      setErrorMsg("An unexpected error occurred while saving.");
    } finally {
      setSaving(false);
    }
  }

  return (
    <form onSubmit={handleSave} className="space-y-8 max-w-4xl mx-auto">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl sm:text-3xl font-extrabold text-white">Store & Payment Settings</h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Configure payment gateways, Meta ads tracking, WhatsApp support, and branding without editing code.
          </p>
        </div>
        <button
          type="submit"
          disabled={saving}
          className="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#00C8F4] to-[#1677FF] hover:from-[#38d4f7] hover:to-[#2582ff] text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-cyan-500/20 transition disabled:opacity-50 self-start sm:self-auto"
        >
          {saving ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
          Save All Settings
        </button>
      </div>

      {successMsg && (
        <div className="p-4 rounded-xl bg-emerald-950/70 border border-emerald-500/50 text-emerald-300 text-xs flex items-center gap-2">
          <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
          <span>{successMsg}</span>
        </div>
      )}
      {errorMsg && (
        <div className="p-4 rounded-xl bg-red-950/70 border border-red-500/50 text-red-300 text-xs flex items-center gap-2">
          <AlertTriangle className="w-4 h-4 text-red-400 shrink-0" />
          <span>{errorMsg}</span>
        </div>
      )}

      {/* 1. Razorpay Gateway Settings */}
      <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl p-6 sm:p-8 space-y-6 shadow-xl">
        <div className="flex items-center gap-3 border-b border-slate-800 pb-4">
          <div className="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-[#00C8F4]">
            <CreditCard className="w-5 h-5" />
          </div>
          <div>
            <h2 className="text-lg font-bold text-white">Razorpay Payment Gateway</h2>
            <p className="text-xs text-slate-400">Controls online payments via UPI, Cards, Netbanking, and Wallets.</p>
          </div>
        </div>

        {/* Mode Selector */}
        <div>
          <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
            Gateway Environment Mode
          </label>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <label
              className={`p-4 rounded-xl border flex items-center gap-3 cursor-pointer transition ${
                razorpayMode === "test"
                  ? "bg-cyan-950/40 border-cyan-500/50 text-white"
                  : "bg-[#05090F] border-slate-800 text-slate-400 hover:text-white"
              }`}
            >
              <input
                type="radio"
                name="razorpayMode"
                value="test"
                checked={razorpayMode === "test"}
                onChange={(e) => setRazorpayMode(e.target.value)}
                className="accent-cyan-500"
              />
              <div>
                <span className="font-bold text-xs block text-white">Test / Sandbox Mode</span>
                <span className="text-[11px] text-slate-400">Allows simulating test payments safely with test cards/UPI</span>
              </div>
            </label>

            <label
              className={`p-4 rounded-xl border flex items-center gap-3 cursor-pointer transition ${
                razorpayMode === "live"
                  ? "bg-emerald-950/40 border-emerald-500/50 text-white"
                  : "bg-[#05090F] border-slate-800 text-slate-400 hover:text-white"
              }`}
            >
              <input
                type="radio"
                name="razorpayMode"
                value="live"
                checked={razorpayMode === "live"}
                onChange={(e) => setRazorpayMode(e.target.value)}
                className="accent-emerald-500"
              />
              <div>
                <span className="font-bold text-xs block text-emerald-300">Live Production Mode</span>
                <span className="text-[11px] text-slate-400">Accepts real Indian & international customer transactions</span>
              </div>
            </label>
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
              Razorpay Key ID
            </label>
            <input
              type="text"
              value={razorpayKeyId}
              onChange={(e) => setRazorpayKeyId(e.target.value)}
              placeholder="rzp_test_... or rzp_live_..."
              className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-xs text-white font-mono outline-none"
            />
          </div>

          <div>
            <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
              Razorpay Key Secret
            </label>
            <div className="relative">
              <input
                type={showRazorpaySecret ? "text" : "password"}
                value={razorpayKeySecret}
                onChange={(e) => setRazorpayKeySecret(e.target.value)}
                placeholder="••••••••••••••••••••••••"
                className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 pr-10 text-xs text-white font-mono outline-none"
              />
              <button
                type="button"
                onClick={() => setShowRazorpaySecret(!showRazorpaySecret)}
                className="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-white"
              >
                {showRazorpaySecret ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
              </button>
            </div>
          </div>
        </div>

        <div>
          <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
            Razorpay Webhook Secret (Optional)
          </label>
          <input
            type="text"
            value={razorpayWebhookSecret}
            onChange={(e) => setRazorpayWebhookSecret(e.target.value)}
            placeholder="Webhook secret configured in Razorpay dashboard"
            className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-xs text-white font-mono outline-none"
          />
          <p className="text-[11px] text-slate-500 mt-1">
            Endpoint URL: <span className="font-mono text-cyan-300">/api/webhooks/razorpay</span>
          </p>
        </div>
      </div>

      {/* 2. Meta Ads Tracking & Conversions API */}
      <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl p-6 sm:p-8 space-y-6 shadow-xl">
        <div className="flex items-center justify-between border-b border-slate-800 pb-4">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/30 flex items-center justify-center text-blue-400">
              <Activity className="w-5 h-5" />
            </div>
            <div>
              <h2 className="text-lg font-bold text-white">Meta Pixel & Conversions API (CAPI)</h2>
              <p className="text-xs text-slate-400">Dual-channel client and server-side conversion attribution.</p>
            </div>
          </div>

          <label className="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              checked={metaTrackingEnabled}
              onChange={(e) => setMetaTrackingEnabled(e.target.checked)}
              className="w-4 h-4 rounded accent-cyan-500"
            />
            <span className="text-xs font-semibold text-white">Tracking Enabled</span>
          </label>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
              Meta Pixel ID (Dataset ID)
            </label>
            <input
              type="text"
              value={metaPixelId}
              onChange={(e) => setMetaPixelId(e.target.value)}
              placeholder="e.g. 1234567890123456"
              className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-xs text-white font-mono outline-none"
            />
          </div>

          <div>
            <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
              Test Event Code (Optional)
            </label>
            <input
              type="text"
              value={metaTestEventCode}
              onChange={(e) => setMetaTestEventCode(e.target.value)}
              placeholder="e.g. TEST12345"
              className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-xs text-white font-mono outline-none"
            />
            <p className="text-[11px] text-slate-500 mt-1">
              Use code from Meta Events Manager &quot;Test events&quot; tab to verify server events.
            </p>
          </div>
        </div>

        <div>
          <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
            Meta Conversions API (CAPI) Access Token
          </label>
          <div className="relative">
            <input
              type={showMetaToken ? "text" : "password"}
              value={metaAccessToken}
              onChange={(e) => setMetaAccessToken(e.target.value)}
              placeholder="EAAG..."
              className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 pr-10 text-xs text-white font-mono outline-none"
            />
            <button
              type="button"
              onClick={() => setShowMetaToken(!showMetaToken)}
              className="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-white"
            >
              {showMetaToken ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
            </button>
          </div>
        </div>
      </div>

      {/* 3. WhatsApp Support & Branding */}
      <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl p-6 sm:p-8 space-y-6 shadow-xl">
        <div className="flex items-center gap-3 border-b border-slate-800 pb-4">
          <div className="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
            <MessageCircle className="w-5 h-5" />
          </div>
          <div>
            <h2 className="text-lg font-bold text-white">Store Identity & WhatsApp Support</h2>
            <p className="text-xs text-slate-400">Control store branding and live WhatsApp click-to-chat settings.</p>
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
              Store Brand Name
            </label>
            <input
              type="text"
              value={storeName}
              onChange={(e) => setStoreName(e.target.value)}
              className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-xs text-white outline-none"
            />
          </div>
          <div>
            <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
              Official Support Email
            </label>
            <input
              type="email"
              value={supportEmail}
              onChange={(e) => setSupportEmail(e.target.value)}
              className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-xs text-white outline-none"
            />
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
              WhatsApp Support Number
            </label>
            <input
              type="text"
              value={whatsappNumber}
              onChange={(e) => setWhatsappNumber(e.target.value)}
              placeholder="+91 9999999999"
              className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-xs text-white outline-none"
            />
            <p className="text-[11px] text-slate-500 mt-1">Include country code without spaces (e.g. 919999999999).</p>
          </div>
          <div>
            <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
              WhatsApp Default Message
            </label>
            <input
              type="text"
              value={whatsappMessage}
              onChange={(e) => setWhatsappMessage(e.target.value)}
              placeholder="Hi! I need help with my digital license order."
              className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-xs text-white outline-none"
            />
          </div>
        </div>
      </div>

      <div className="flex items-center justify-end">
        <button
          type="submit"
          disabled={saving}
          className="px-6 py-3 rounded-xl bg-gradient-to-r from-[#00C8F4] to-[#1677FF] text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-cyan-500/20 disabled:opacity-50"
        >
          {saving ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
          Save All Settings
        </button>
      </div>
    </form>
  );
}
