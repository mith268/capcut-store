import { StoreHeader } from "@/components/store/StoreHeader";
import { StoreFooter } from "@/components/store/StoreFooter";
import { FileText } from "lucide-react";

export const metadata = {
  title: "Terms and Conditions | Convora Store",
  description: "Terms of service and digital purchase agreements.",
};

export default function TermsPage() {
  return (
    <div className="min-h-screen bg-[#F8FAFC] text-[#0F172A] flex flex-col font-sans">
      <StoreHeader />
      <main className="flex-1 max-w-4xl w-full mx-auto px-4 py-12 md:py-16">
        <div className="flex items-center gap-3 mb-6">
          <div className="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-brand-blue shadow-soft-sm">
            <FileText className="w-5 h-5" />
          </div>
          <div>
            <h1 className="text-3xl font-extrabold text-[#0F172A]">Terms & Conditions</h1>
            <p className="text-xs text-[#64748B]">Effective Date: 2026</p>
          </div>
        </div>

        <div className="bg-white border border-[#E2E8F0] rounded-3xl p-6 md:p-10 shadow-soft-md space-y-6 text-sm text-[#475569] leading-relaxed">
          <section className="space-y-2">
            <h2 className="text-lg font-bold text-[#0F172A]">1. Agreement to Terms</h2>
            <p>
              By accessing our website and purchasing digital goods or subscriptions, you agree to be bound by these Terms and Conditions. If you do not agree with any part of these terms, please do not use our services.
            </p>
          </section>

          <section className="space-y-2">
            <h2 className="text-lg font-bold text-[#0F172A]">2. Nature of Digital Products</h2>
            <p>
              We provide access credentials, activation keys, digital licenses, and premium workspace invitations for creative and productivity software. Deliveries are made digitally and instantaneously upon verified payment completion.
            </p>
          </section>

          <section className="space-y-2">
            <h2 className="text-lg font-bold text-[#0F172A]">3. Device & Account Usage Guidelines</h2>
            <p>
              Purchased accounts and access credentials must be used according to the plan terms (e.g., maximum device limit, non-modification of master account email or passwords where prohibited). Modifying unauthorized master profile settings may result in termination of service warranty.
            </p>
          </section>

          <section className="space-y-2">
            <h2 className="text-lg font-bold text-[#0F172A]">4. Pricing and Currency</h2>
            <p>
              All prices displayed are in Indian Rupees (INR) unless otherwise noted. We reserve the right to modify prices or discontinue plans at any time without prior notice.
            </p>
          </section>

          <section className="space-y-2">
            <h2 className="text-lg font-bold text-[#0F172A]">5. Warranty and Replacements</h2>
            <p>
              We provide full warranty for the duration specified in your plan (e.g. 1 Month, 1 Year). In the rare event of credential disruption or invalidation, our support team provides free replacement credentials within 24 hours.
            </p>
          </section>
        </div>
      </main>
      <StoreFooter />
    </div>
  );
}
