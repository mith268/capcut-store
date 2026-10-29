import { StoreHeader } from "@/components/store/StoreHeader";
import { StoreFooter } from "@/components/store/StoreFooter";
import { RefreshCw } from "lucide-react";

export const metadata = {
  title: "Refund & Replacement Policy | Convora Store",
  description: "Replacement guarantee and refund guidelines for digital orders.",
};

export default function RefundPolicyPage() {
  return (
    <div className="min-h-screen bg-[#F8FAFC] text-[#0F172A] flex flex-col font-sans">
      <StoreHeader />
      <main className="flex-1 max-w-4xl w-full mx-auto px-4 py-12 md:py-16">
        <div className="flex items-center gap-3 mb-6">
          <div className="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-brand-blue shadow-soft-sm">
            <RefreshCw className="w-5 h-5" />
          </div>
          <div>
            <h1 className="text-3xl font-extrabold text-[#0F172A]">Refund & Replacement Policy</h1>
            <p className="text-xs text-[#64748B]">100% Replacement Guarantee on all valid plans</p>
          </div>
        </div>

        <div className="bg-white border border-[#E2E8F0] rounded-3xl p-6 md:p-10 shadow-soft-md space-y-6 text-sm text-[#475569] leading-relaxed">
          <section className="space-y-2">
            <h2 className="text-lg font-bold text-[#0F172A]">1. Replacement Guarantee First</h2>
            <p>
              Due to the nature of digital goods and immediate access provisioning, all sales are protected under our Full Duration Replacement Guarantee. If your account credentials or license key stop functioning during your active plan period, we replace them free of charge.
            </p>
          </section>

          <section className="space-y-2">
            <h2 className="text-lg font-bold text-[#0F172A]">2. Eligible Circumstances for Refunds</h2>
            <p>
              A monetary refund back to your original payment method (via Razorpay) is granted under the following circumstances:
            </p>
            <ul className="list-disc pl-5 space-y-1 text-[#64748B]">
              <li>We are unable to deliver your working credentials within 24 hours of purchase.</li>
              <li>An item was marked out of stock after your payment was captured and you choose not to wait for restocking.</li>
              <li>A technical incompatibility prevents activation that our technical support team cannot resolve.</li>
            </ul>
          </section>

          <section className="space-y-2">
            <h2 className="text-lg font-bold text-[#0F172A]">3. Non-Refundable Scenarios</h2>
            <p>
              Refunds will not be issued for:
            </p>
            <ul className="list-disc pl-5 space-y-1 text-[#64748B]">
              <li>Change of mind after successful credential decryption and usage.</li>
              <li>Violation of plan guidelines (e.g., sharing credentials beyond allowed devices or changing recovery passwords).</li>
              <li>Failure to report an issue within the active plan duration.</li>
            </ul>
          </section>

          <section className="space-y-2">
            <h2 className="text-lg font-bold text-[#0F172A]">4. How to Request Support or Replacement</h2>
            <p>
              To request a credential refresh or refund review, simply contact our WhatsApp support with your Order Number (e.g. CS-10023). Our agents respond within 30 minutes during business hours.
            </p>
          </section>
        </div>
      </main>
      <StoreFooter />
    </div>
  );
}
