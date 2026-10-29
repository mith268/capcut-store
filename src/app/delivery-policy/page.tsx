import { StoreHeader } from "@/components/store/StoreHeader";
import { StoreFooter } from "@/components/store/StoreFooter";
import { Zap } from "lucide-react";

export const metadata = {
  title: "Digital Delivery Policy | Convora Store",
  description: "Information regarding instant automated delivery and manual activations.",
};

export default function DeliveryPolicyPage() {
  return (
    <div className="min-h-screen bg-[#F8FAFC] text-[#0F172A] flex flex-col font-sans">
      <StoreHeader />
      <main className="flex-1 max-w-4xl w-full mx-auto px-4 py-12 md:py-16">
        <div className="flex items-center gap-3 mb-6">
          <div className="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-brand-blue shadow-soft-sm">
            <Zap className="w-5 h-5" />
          </div>
          <div>
            <h1 className="text-3xl font-extrabold text-[#0F172A]">Digital Delivery Policy</h1>
            <p className="text-xs text-[#64748B]">Instant Automated Provisioning System</p>
          </div>
        </div>

        <div className="bg-white border border-[#E2E8F0] rounded-3xl p-6 md:p-10 shadow-soft-md space-y-6 text-sm text-[#475569] leading-relaxed">
          <section className="space-y-2">
            <h2 className="text-lg font-bold text-[#0F172A]">1. Instant On-Screen Delivery</h2>
            <p>
              95% of our products feature automated instant delivery. As soon as Razorpay confirms your payment, you are redirected to your encrypted order page where your logins, license keys, and setup instructions are revealed immediately.
            </p>
          </section>

          <section className="space-y-2">
            <h2 className="text-lg font-bold text-[#0F172A]">2. Email Confirmation</h2>
            <p>
              Simultaneously, a purchase confirmation receipt containing your unique Order Number and private retrieval link is dispatched to your provided email address and phone.
            </p>
          </section>

          <section className="space-y-2">
            <h2 className="text-lg font-bold text-[#0F172A]">3. Manual Invites & Restocking Delivery Times</h2>
            <p>
              For products requiring manual workspace invites (such as customized Canva teams or personal email upgrades), our technical team processes invites within 2 to 4 hours. You will receive WhatsApp and email alerts once the upgrade is live.
            </p>
          </section>

          <section className="space-y-2">
            <h2 className="text-lg font-bold text-[#0F172A]">4. Order Retrieval at Any Time</h2>
            <p>
              You can retrieve your active licenses anytime using our Order Lookup Portal by submitting your Order Number and customer email.
            </p>
          </section>
        </div>
      </main>
      <StoreFooter />
    </div>
  );
}
