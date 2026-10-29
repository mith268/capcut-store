import { StoreHeader } from "@/components/store/StoreHeader";
import { StoreFooter } from "@/components/store/StoreFooter";
import { Shield } from "lucide-react";

export const metadata = {
  title: "Privacy Policy | Convora Store",
  description: "Privacy policy and personal data protection information.",
};

export default function PrivacyPolicyPage() {
  return (
    <div className="min-h-screen bg-[#F8FAFC] text-[#0F172A] flex flex-col font-sans">
      <StoreHeader />
      <main className="flex-1 max-w-4xl w-full mx-auto px-4 py-12 md:py-16">
        <div className="flex items-center gap-3 mb-6">
          <div className="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-brand-blue shadow-soft-sm">
            <Shield className="w-5 h-5" />
          </div>
          <div>
            <h1 className="text-3xl font-extrabold text-[#0F172A]">Privacy Policy</h1>
            <p className="text-xs text-[#64748B]">Last updated: 2026</p>
          </div>
        </div>

        <div className="bg-white border border-[#E2E8F0] rounded-3xl p-6 md:p-10 shadow-soft-md space-y-6 text-sm text-[#475569] leading-relaxed">
          <section className="space-y-2">
            <h2 className="text-lg font-bold text-[#0F172A]">1. Information We Collect</h2>
            <p>
              When you purchase a digital subscription or license on our store, we collect necessary customer information to fulfill your digital delivery. This includes your name, email address, phone number (WhatsApp for activation notifications), and order transaction identifiers.
            </p>
          </section>

          <section className="space-y-2">
            <h2 className="text-lg font-bold text-[#0F172A]">2. Payment Security</h2>
            <p>
              We do not store or process your credit card, debit card, or UPI PIN on our servers. All transactions are securely processed through our certified payment partner Razorpay using 256-bit SSL encryption.
            </p>
          </section>

          <section className="space-y-2">
            <h2 className="text-lg font-bold text-[#0F172A]">3. Digital Credential Encryption</h2>
            <p>
              All customer access credentials, license keys, and account tokens are encrypted at rest using industry-standard AES-256-GCM authenticated encryption before being stored in our database. Only authorized order tokens can decrypt and access credentials.
            </p>
          </section>

          <section className="space-y-2">
            <h2 className="text-lg font-bold text-[#0F172A]">4. Cookies & Tracking</h2>
            <p>
              We use essential session cookies to remember checkout progress and secure guest access. Anonymous attribution data may be collected via Meta Pixel and Conversions API to measure the effectiveness of our advertising campaigns. You may opt out or clear cookies in your browser settings.
            </p>
          </section>

          <section className="space-y-2">
            <h2 className="text-lg font-bold text-[#0F172A]">5. Third-Party Disclosures</h2>
            <p>
              We never sell, rent, or trade your personal information to third parties. Data is only shared with essential payment processors and infrastructure providers necessary to operate the digital store.
            </p>
          </section>
        </div>
      </main>
      <StoreFooter />
    </div>
  );
}
