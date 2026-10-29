import Link from "next/link";
import { Shield, Lock, Zap, Clock } from "lucide-react";

export function StoreFooter({ storeName = "Convora Store" }: { storeName?: string }) {
  return (
    <footer className="mt-auto border-t border-[#E2E8F0] bg-white pt-12 pb-8 px-4 sm:px-6 lg:px-8 text-xs text-[#64748B]">
      <div className="max-w-7xl mx-auto space-y-8">
        
        {/* Trust Badges Strip */}
        <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 p-5 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] text-center shadow-soft-sm">
          <div className="flex items-center justify-center space-x-2.5">
            <div className="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-brand-blue">
              <Lock className="w-4 h-4" />
            </div>
            <div className="text-left">
              <div className="font-bold text-[#0F172A]">256-Bit SSL</div>
              <div className="text-[10px] text-[#64748B]">Bank-Grade Security</div>
            </div>
          </div>

          <div className="flex items-center justify-center space-x-2.5">
            <div className="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-brand-blue">
              <Zap className="w-4 h-4" />
            </div>
            <div className="text-left">
              <div className="font-bold text-[#0F172A]">Instant Delivery</div>
              <div className="text-[10px] text-[#64748B]">Auto-Fulfilled in Seconds</div>
            </div>
          </div>

          <div className="flex items-center justify-center space-x-2.5">
            <div className="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-brand-blue">
              <Shield className="w-4 h-4" />
            </div>
            <div className="text-left">
              <div className="font-bold text-[#0F172A]">Warranty Protected</div>
              <div className="text-[10px] text-[#64748B]">Replacement Guarantee</div>
            </div>
          </div>

          <div className="flex items-center justify-center space-x-2.5">
            <div className="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-brand-blue">
              <Clock className="w-4 h-4" />
            </div>
            <div className="text-left">
              <div className="font-bold text-[#0F172A]">24/7 Support</div>
              <div className="text-[10px] text-[#64748B]">Direct WhatsApp Helpdesk</div>
            </div>
          </div>
        </div>

        {/* Links Grid */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-8 pt-4">
          <div className="space-y-3">
            <div className="text-sm font-black text-[#0F172A]">{storeName}</div>
            <p className="text-[#64748B] text-[11px] leading-relaxed">
              Your trusted platform for verified digital subscriptions, creator tools, software licenses, and educational resources with immediate fulfillment.
            </p>
          </div>

          <div className="space-y-2">
            <div className="font-bold text-[#0F172A] uppercase tracking-wider text-[11px]">Popular Products</div>
            <ul className="space-y-1.5">
              <li><Link href="/products/capcut-pro" className="hover:text-brand-blue transition-colors">CapCut Pro</Link></li>
              <li><Link href="/products/canva-pro" className="hover:text-brand-blue transition-colors">Canva Pro</Link></li>
              <li><Link href="/products/gemini-ai" className="hover:text-brand-blue transition-colors">Google Gemini AI</Link></li>
              <li><Link href="/products" className="hover:text-brand-blue transition-colors">All Products</Link></li>
            </ul>
          </div>

          <div className="space-y-2">
            <div className="font-bold text-[#0F172A] uppercase tracking-wider text-[11px]">Support & Orders</div>
            <ul className="space-y-1.5">
              <li><Link href="/order-access" className="hover:text-brand-blue transition-colors">Order Lookup Portal</Link></li>
              <li><Link href="/faq" className="hover:text-brand-blue transition-colors">Frequently Asked Questions</Link></li>
              <li><Link href="/contact" className="hover:text-brand-blue transition-colors">Contact Support</Link></li>
              <li><Link href="/admin/login" className="hover:text-brand-blue transition-colors">Staff Login</Link></li>
            </ul>
          </div>

          <div className="space-y-2">
            <div className="font-bold text-[#0F172A] uppercase tracking-wider text-[11px]">Legal & Policies</div>
            <ul className="space-y-1.5">
              <li><Link href="/privacy-policy" className="hover:text-brand-blue transition-colors">Privacy Policy</Link></li>
              <li><Link href="/terms-and-conditions" className="hover:text-brand-blue transition-colors">Terms of Service</Link></li>
              <li><Link href="/refund-policy" className="hover:text-brand-blue transition-colors">Refund & Replacement Policy</Link></li>
              <li><Link href="/delivery-policy" className="hover:text-brand-blue transition-colors">Digital Delivery Policy</Link></li>
            </ul>
          </div>
        </div>

        {/* Disclaimer & Copyright */}
        <div className="border-t border-[#E2E8F0] pt-6 text-center space-y-2 text-[11px] text-[#94A3B8]">
          <p>
            Disclaimer: All product names, logos, and brands are property of their respective owners. All company, product, and service names used on this website are for identification purposes only.
          </p>
          <p>
            © {new Date().getFullYear()} {storeName}. All rights reserved. Powered by Convora Commerce.
          </p>
        </div>

      </div>
    </footer>
  );
}
