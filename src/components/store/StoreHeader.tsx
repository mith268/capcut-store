"use client";

import Link from "next/link";
import { useState } from "react";
import { Menu, X, Package } from "lucide-react";
import { WhatsAppIcon } from "@/components/ui/WhatsAppIcon";

interface StoreHeaderProps {
  storeName?: string;
  storeBadge?: string;
  whatsappNumber?: string;
}

export function StoreHeader({
  storeName = "Convora Store",
  storeBadge = "VERIFIED",
  whatsappNumber = "919990798996",
}: StoreHeaderProps) {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  const cleanWhatsapp = whatsappNumber.replace(/[^0-9]/g, "") || "919990798996";
  const whatsappUrl = `https://wa.me/${cleanWhatsapp}?text=${encodeURIComponent(
    `Hi ${storeName}, I have a question regarding products on your store.`
  )}`;

  return (
    <>
      {/* Top Announcement Bar */}
      <div className="bg-[#F1F5F9] border-b border-[#E2E8F0] text-[11px] sm:text-xs py-2 px-4 text-center font-medium tracking-wide text-[#475569]">
        <div className="max-w-7xl mx-auto flex items-center justify-center space-x-2">
          <span className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" />
          <span>⚡ Instant Digital Delivery • 100% Genuine Subscriptions & Guides • 24/7 WhatsApp Support</span>
        </div>
      </div>

      {/* Main Sticky Header */}
      <header className="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-[#E2E8F0] shadow-soft-sm">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between gap-4">
          
          {/* Brand Logo & Title */}
          <Link href="/" className="flex items-center space-x-3 group shrink-0">
            <div className="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-tr from-brand-blue to-blue-700 flex items-center justify-center shadow-soft-sm group-hover:scale-105 transition-transform text-white">
              <span className="font-black text-base sm:text-lg tracking-tighter">C</span>
            </div>
            <div>
              <div className="text-base sm:text-lg font-black text-[#0F172A] tracking-wider flex items-center gap-1.5">
                <span>{storeName}</span>
                {storeBadge && (
                  <span className="text-[9px] font-mono uppercase px-2 py-0.5 rounded-full bg-blue-50 text-brand-blue font-bold tracking-wider border border-blue-200">
                    {storeBadge}
                  </span>
                )}
              </div>
              <div className="text-[10px] font-mono text-[#64748B] font-semibold uppercase tracking-wider">
                Digital Commerce & Subscriptions
              </div>
            </div>
          </Link>

          {/* Desktop Navigation */}
          <nav className="hidden md:flex items-center space-x-6 text-xs font-semibold text-[#475569]">
            <Link href="/" className="hover:text-brand-blue transition-colors">
              Home
            </Link>
            <Link href="/products" className="hover:text-brand-blue transition-colors">
              All Products
            </Link>
            <Link href="/products/capcut-pro" className="hover:text-brand-blue transition-colors">
              CapCut Pro
            </Link>
            <Link href="/products/canva-pro" className="hover:text-brand-blue transition-colors">
              Canva Pro
            </Link>
            <Link href="/order-access" className="hover:text-brand-blue transition-colors">
              Order Lookup
            </Link>
            <Link href="/faq" className="hover:text-brand-blue transition-colors">
              FAQs
            </Link>
          </nav>

          {/* Right Actions: Order Lookup & WhatsApp CTA */}
          <div className="flex items-center space-x-3">
            <Link
              href="/order-access"
              className="hidden sm:flex items-center space-x-1.5 text-xs text-[#475569] hover:text-[#0F172A] font-medium px-3 py-2 rounded-xl border border-[#E2E8F0] bg-white hover:bg-[#F8FAFC] transition-colors shadow-soft-sm"
            >
              <Package className="w-3.5 h-3.5 text-brand-blue" />
              <span>Lookup Order</span>
            </Link>

            <a
              href={whatsappUrl}
              target="_blank"
              rel="noopener noreferrer"
              className="btn-whatsapp px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs flex items-center space-x-1.5 shadow-soft-sm text-white"
            >
              <WhatsAppIcon className="w-4 h-4 shrink-0 text-white" />
              <span>WhatsApp</span>
            </a>

            {/* Mobile Hamburger Button */}
            <button
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              className="md:hidden p-2 text-[#475569] hover:text-[#0F172A] rounded-lg border border-[#E2E8F0] bg-white"
              aria-label="Toggle menu"
            >
              {mobileMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
            </button>
          </div>
        </div>

        {/* Mobile Navigation Drawer */}
        {mobileMenuOpen && (
          <div className="md:hidden border-t border-[#E2E8F0] bg-white px-4 py-4 space-y-3 shadow-lg">
            <Link
              href="/"
              onClick={() => setMobileMenuOpen(false)}
              className="block text-sm font-semibold text-[#0F172A] py-1.5"
            >
              Home Storefront
            </Link>
            <Link
              href="/products"
              onClick={() => setMobileMenuOpen(false)}
              className="block text-sm font-semibold text-[#0F172A] py-1.5"
            >
              All Products Catalog
            </Link>
            <Link
              href="/products/capcut-pro"
              onClick={() => setMobileMenuOpen(false)}
              className="block text-sm font-semibold text-brand-blue py-1.5"
            >
              ⚡ CapCut Pro
            </Link>
            <Link
              href="/products/canva-pro"
              onClick={() => setMobileMenuOpen(false)}
              className="block text-sm font-semibold text-purple-600 py-1.5"
            >
              🎨 Canva Pro
            </Link>
            <Link
              href="/order-access"
              onClick={() => setMobileMenuOpen(false)}
              className="block text-sm font-semibold text-[#475569] py-1.5"
            >
              📦 Lookup Order
            </Link>
            <Link
              href="/faq"
              onClick={() => setMobileMenuOpen(false)}
              className="block text-sm font-semibold text-[#475569] py-1.5"
            >
              Frequently Asked Questions
            </Link>
            <div className="pt-2">
              <a
                href={whatsappUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="w-full btn-whatsapp py-2.5 rounded-xl text-xs flex items-center justify-center space-x-2 text-white"
              >
                <WhatsAppIcon className="w-4 h-4 shrink-0 text-white" />
                <span>Chat on WhatsApp</span>
              </a>
            </div>
          </div>
        )}
      </header>
    </>
  );
}
