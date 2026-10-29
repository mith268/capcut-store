"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { StoreHeader } from "@/components/store/StoreHeader";
import { StoreFooter } from "@/components/store/StoreFooter";
import { Search, ShieldCheck, Key, HelpCircle, ArrowRight, Loader2 } from "lucide-react";
import { WhatsAppIcon } from "@/components/ui/WhatsAppIcon";

export default function OrderAccessPage() {
  const router = useRouter();
  const [orderIdentifier, setOrderIdentifier] = useState("");
  const [contact, setContact] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function handleLookup(e: React.FormEvent) {
    e.preventDefault();
    setError(null);

    if (!orderIdentifier.trim() || !contact.trim()) {
      setError("Please enter both your Order Number and your Email or Phone.");
      return;
    }

    try {
      setLoading(true);
      const res = await fetch("/api/orders/lookup", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          orderIdentifier: orderIdentifier.trim(),
          contact: contact.trim(),
        }),
      });

      const data = await res.json();
      if (!res.ok) {
        setError(data.error || "Order not found. Please check your details.");
      } else {
        router.push(`/orders/${data.publicId}?email=${encodeURIComponent(contact.trim().toLowerCase())}`);
      }
    } catch {
      setError("Failed to look up order. Please check your internet connection.");
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="min-h-screen bg-[#F8FAFC] text-[#0F172A] flex flex-col font-sans">
      <StoreHeader />

      <main className="flex-1 max-w-3xl w-full mx-auto px-4 py-16 flex flex-col items-center justify-center">
        {/* Header */}
        <div className="text-center mb-8">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 border border-blue-200 text-xs font-semibold text-brand-blue mb-3 shadow-soft-sm">
            <Key className="w-3.5 h-3.5" /> Instant Digital Order Access
          </div>
          <h1 className="text-3xl md:text-4xl font-extrabold text-[#0F172A] tracking-tight">
            Retrieve Your Digital Credentials
          </h1>
          <p className="text-sm text-[#64748B] mt-2 max-w-md mx-auto">
            Enter your Order Number and the Email or Phone used during checkout to view your licenses, logins, and downloads.
          </p>
        </div>

        {/* Card */}
        <div className="w-full bg-white border border-[#E2E8F0] rounded-3xl p-6 md:p-8 shadow-soft-md relative overflow-hidden">
          <form onSubmit={handleLookup} className="space-y-5">
            {error && (
              <div className="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2 shadow-soft-sm">
                <span className="font-bold">Error:</span> {error}
              </div>
            )}

            <div>
              <label className="block text-xs font-semibold uppercase tracking-wider text-[#475569] mb-2">
                Order Number
              </label>
              <div className="relative">
                <input
                  type="text"
                  placeholder="e.g. CS-10001 or ord_..."
                  value={orderIdentifier}
                  onChange={(e) => setOrderIdentifier(e.target.value)}
                  className="w-full bg-[#F8FAFC] border border-[#E2E8F0] focus:border-brand-blue focus:bg-white rounded-xl px-4 py-3 text-sm text-[#0F172A] placeholder-[#94A3B8] outline-none transition shadow-soft-sm"
                  required
                />
              </div>
              <p className="text-[11px] text-[#64748B] mt-1.5">
                You can find this on your checkout confirmation or payment receipt.
              </p>
            </div>

            <div>
              <label className="block text-xs font-semibold uppercase tracking-wider text-[#475569] mb-2">
                Customer Email or WhatsApp Phone
              </label>
              <div className="relative">
                <input
                  type="text"
                  placeholder="name@example.com or +91 9876543210"
                  value={contact}
                  onChange={(e) => setContact(e.target.value)}
                  className="w-full bg-[#F8FAFC] border border-[#E2E8F0] focus:border-brand-blue focus:bg-white rounded-xl px-4 py-3 text-sm text-[#0F172A] placeholder-[#94A3B8] outline-none transition shadow-soft-sm"
                  required
                />
              </div>
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full py-3.5 px-6 rounded-xl font-bold text-sm btn-primary-blue text-white shadow-soft-md flex items-center justify-center gap-2 transition disabled:opacity-50"
            >
              {loading ? (
                <>
                  <Loader2 className="w-4 h-4 animate-spin" />
                  Verifying Order...
                </>
              ) : (
                <>
                  <Search className="w-4 h-4" />
                  Access Order & Credentials
                  <ArrowRight className="w-4 h-4" />
                </>
              )}
            </button>
          </form>

          {/* Trust points */}
          <div className="mt-8 pt-6 border-t border-[#E2E8F0] grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-[#64748B]">
            <div className="flex items-center gap-2">
              <ShieldCheck className="w-4 h-4 text-brand-blue" />
              <span>AES-256 Encrypted Credential Vault</span>
            </div>
            <div className="flex items-center gap-2">
              <HelpCircle className="w-4 h-4 text-emerald-600" />
              <span>24/7 Human WhatsApp Activation Support</span>
            </div>
          </div>
        </div>

        {/* WhatsApp Help CTA */}
        <div className="mt-8 text-center text-xs text-[#64748B]">
          Need quick assistance with your order?{" "}
          <a
            href="https://wa.me/919990798996?text=Hi!%20I%20need%20help%20accessing%20my%20digital%20order."
            target="_blank"
            rel="noopener noreferrer"
            className="text-emerald-600 hover:text-emerald-700 font-semibold inline-flex items-center gap-1.5 ml-1"
          >
            <WhatsAppIcon className="w-4 h-4 text-emerald-600" /> Chat on WhatsApp
          </a>
        </div>
      </main>

      <StoreFooter />
    </div>
  );
}
