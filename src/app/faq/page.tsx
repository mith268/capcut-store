import { StoreHeader } from "@/components/store/StoreHeader";
import { StoreFooter } from "@/components/store/StoreFooter";
import { HelpCircle, ChevronDown } from "lucide-react";

export const metadata = {
  title: "Frequently Asked Questions | Convora Store",
  description: "Find answers to commonly asked questions about digital licenses, subscriptions, and delivery.",
};

const FAQS = [
  {
    q: "How will I receive my account credentials?",
    a: "Immediately upon successful payment via Razorpay, you will be redirected to your secure order delivery portal where your username, password, and login instructions will be displayed. You can also view them anytime using the Order Lookup page.",
  },
  {
    q: "Is CapCut Pro compatible with Mac, Windows, iOS, and Android?",
    a: "Yes! Our CapCut Pro accounts work seamlessly across PC (Windows & macOS) as well as the mobile app. All Pro features, cloud templates, AI effects, and transitions are unlocked.",
  },
  {
    q: "How do I claim the free Canva Pro bonus?",
    a: "If you purchased a plan that includes the Canva bonus, simply visit your order page after checkout, enter your Canva email address in the bonus box, and click 'Claim Bonus'. Our team will invite you to the Canva Pro workspace within 2-4 hours.",
  },
  {
    q: "What if the credentials stop working before my plan ends?",
    a: "All purchases come with a 100% active warranty. If you ever experience issues, simply message our WhatsApp support with your Order Number, and we will issue fresh replacement credentials right away.",
  },
  {
    q: "Which payment methods are accepted?",
    a: "We accept all major Indian and international payment methods via Razorpay: UPI (Google Pay, PhonePe, Paytm, CRED), Credit/Debit Cards, Net Banking, and Wallets.",
  },
  {
    q: "Can I use the account on multiple devices?",
    a: "Check the specific plan details for the device limit. Most accounts are configured for 1 active device at a time to ensure uninterrupted uptime.",
  },
];

export default function FAQPage() {
  return (
    <div className="min-h-screen bg-[#F8FAFC] text-[#0F172A] flex flex-col font-sans">
      <StoreHeader />
      <main className="flex-1 max-w-4xl w-full mx-auto px-4 py-12 md:py-16 space-y-8">
        <div className="flex items-center gap-3 mb-6">
          <div className="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-brand-blue shadow-soft-sm">
            <HelpCircle className="w-5 h-5" />
          </div>
          <div>
            <h1 className="text-3xl font-extrabold text-[#0F172A]">Frequently Asked Questions</h1>
            <p className="text-xs text-[#64748B]">Everything you need to know about our digital licenses and service.</p>
          </div>
        </div>

        <div className="space-y-4">
          {FAQS.map((faq, index) => (
            <div
              key={index}
              className="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-soft-sm hover:shadow-soft-md transition"
            >
              <h3 className="text-base font-bold text-[#0F172A] mb-2">
                {faq.q}
              </h3>
              <p className="text-xs sm:text-sm text-[#475569] leading-relaxed">
                {faq.a}
              </p>
            </div>
          ))}
        </div>
      </main>
      <StoreFooter />
    </div>
  );
}
