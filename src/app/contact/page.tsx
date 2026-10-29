import { StoreHeader } from "@/components/store/StoreHeader";
import { StoreFooter } from "@/components/store/StoreFooter";
import { Mail, Clock, MapPin } from "lucide-react";
import { WhatsAppIcon } from "@/components/ui/WhatsAppIcon";

export const metadata = {
  title: "Contact Us | Convora Store Support",
  description: "Get in touch with customer support for activation assistance and inquiries.",
};

export default function ContactPage() {
  return (
    <div className="min-h-screen bg-[#F8FAFC] text-[#0F172A] flex flex-col font-sans">
      <StoreHeader />
      <main className="flex-1 max-w-4xl w-full mx-auto px-4 py-12 md:py-16">
        <div className="text-center max-w-lg mx-auto mb-10">
          <h1 className="text-3xl md:text-4xl font-extrabold text-[#0F172A]">Contact Customer Support</h1>
          <p className="text-sm text-[#64748B] mt-2">
            Have questions about a plan or need instant help with your order activation? We are here 24/7.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {/* WhatsApp Card */}
          <div className="bg-white border border-emerald-200 rounded-3xl p-6 md:p-8 flex flex-col justify-between shadow-soft-md">
            <div>
              <div className="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-brand-whatsapp mb-4">
                <WhatsAppIcon className="w-6 h-6 text-brand-whatsapp" />
              </div>
              <h3 className="text-xl font-bold text-[#0F172A] mb-2">WhatsApp Live Support</h3>
              <p className="text-xs text-[#475569] leading-relaxed mb-6">
                Connect directly with our activation specialists on WhatsApp for instantaneous assistance, credential replacements, or custom inquiries.
              </p>
            </div>
            <a
              href="https://wa.me/919990798996?text=Hi!%20I%20have%20an%20inquiry%20regarding%20Convora%20Store."
              target="_blank"
              rel="noopener noreferrer"
              className="w-full py-3 px-4 rounded-xl btn-whatsapp text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-soft-sm"
            >
              <WhatsAppIcon className="w-4 h-4 text-white" /> Chat on WhatsApp Now
            </a>
          </div>

          {/* Email Support Card */}
          <div className="bg-white border border-blue-200 rounded-3xl p-6 md:p-8 flex flex-col justify-between shadow-soft-md">
            <div>
              <div className="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-200 flex items-center justify-center text-brand-blue mb-4">
                <Mail className="w-6 h-6 text-brand-blue" />
              </div>
              <h3 className="text-xl font-bold text-[#0F172A] mb-2">Email Support</h3>
              <p className="text-xs text-[#475569] leading-relaxed mb-6">
                Prefer email communication or need formal corporate receipts? Send our support desk an email and we will respond within 4 hours.
              </p>
            </div>
            <a
              href="mailto:support@convora.store"
              className="w-full py-3 px-4 rounded-xl bg-[#F8FAFC] hover:bg-white text-brand-blue font-bold text-xs flex items-center justify-center gap-2 transition border border-[#E2E8F0] shadow-soft-sm"
            >
              <Mail className="w-4 h-4" /> support@convora.store
            </a>
          </div>
        </div>
      </main>
      <StoreFooter />
    </div>
  );
}
