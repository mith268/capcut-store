"use client";

import { useState } from "react";
import Link from "next/link";
import { formatINR, calculateSavingsPercentage, safeJsonParse } from "@/lib/format";
import {
  CheckCircle2,
  Play,
  Sparkles,
  ShieldCheck,
  Zap,
  ChevronDown,
  Lock,
  ArrowRight,
  Clock,
  Check,
} from "lucide-react";
import { WhatsAppIcon } from "@/components/ui/WhatsAppIcon";

interface Plan {
  id: string;
  name: string;
  sku: string;
  duration: string;
  price: number;
  compareAtPrice: number | null;
  benefits: string | null;
  restrictions: string | null;
  deviceLimit: number;
  isRecommended: boolean;
  recommendedLabel: string | null;
}

interface Section {
  id: string;
  sectionType: string;
  title: string | null;
  subtitle: string | null;
  badgeText: string | null;
  content: string;
}

interface ProductLandingClientProps {
  product: {
    id: string;
    title: string;
    slug: string;
    subtitle: string | null;
    shortDescription: string | null;
    longDescription: string | null;
    thumbnail: string | null;
    videoUrl: string | null;
    videoId: string | null;
    themePrimaryColor: string | null;
    themeAccentColor: string | null;
    bonusEnabled: boolean;
    bonusTitle: string | null;
    bonusBadge: string | null;
    bonusSubtitle: string | null;
    bonusExplanation: string | null;
    stickyBarEnabled: boolean;
    stickyBarText: string | null;
    stickyBarButtonLabel: string | null;
  };
  plans: Plan[];
  sections: Section[];
  whatsappNumber: string;
  storeName: string;
}

export function ProductLandingClient({
  product,
  plans,
  sections,
  whatsappNumber,
  storeName,
}: ProductLandingClientProps) {
  // Plan selection state (default to plan recommended or first)
  const defaultPlan = plans.find((p) => p.isRecommended) || plans[0];
  const [selectedPlan, setSelectedPlan] = useState<Plan>(defaultPlan || plans[0]);
  const [videoLoaded, setVideoLoaded] = useState(false);
  const [openFaqIndex, setOpenFaqIndex] = useState<number | null>(null);

  const cleanWhatsapp = whatsappNumber.replace(/[^0-9]/g, "") || "919990798996";
  const whatsappUrl = `https://wa.me/${cleanWhatsapp}?text=${encodeURIComponent(
    `Hi ${storeName}, I want to order ${product.title} (${selectedPlan?.name || "1 Month"}). Please provide payment details.`
  )}`;

  const currentPrice = selectedPlan?.price || 0;
  const comparePrice = selectedPlan?.compareAtPrice || 0;
  const savingsPct = calculateSavingsPercentage(comparePrice, currentPrice);

  // Extract sections by type
  const heroSection = sections.find((s) => s.sectionType === "HERO");
  const heroContent = safeJsonParse(heroSection?.content, {
    kicker: "⚡ VERIFIED DIGITAL ACCESS",
    headline: product.title,
    highlightedText: product.subtitle || null,
    bonusLine: product.bonusEnabled ? product.bonusTitle : null,
    primaryCta: "BUY NOW",
    whatsappCta: "WHATSAPP",
    videoId: product.videoId || "GeXVI3p-BqE",
  });

  const keyFactsSection = sections.find((s) => s.sectionType === "KEY_FACTS");
  const keyFacts = safeJsonParse<{ chips: { icon: string; text: string }[] }>(
    keyFactsSection?.content,
    { chips: [] }
  );

  const benefitsSection = sections.find((s) => s.sectionType === "BENEFITS");
  const benefits = safeJsonParse<{ items: { title: string; description: string; icon: string }[] }>(
    benefitsSection?.content,
    { items: [] }
  );

  const howItWorksSection = sections.find((s) => s.sectionType === "HOW_IT_WORKS");
  const howItWorks = safeJsonParse<{
    steps: { number: string; title: string; description: string; badge: string }[];
  }>(howItWorksSection?.content, { steps: [] });

  const bonusSection = sections.find((s) => s.sectionType === "BONUS");
  const bonusContent = safeJsonParse(bonusSection?.content, {
    explanation: product.bonusExplanation || "",
    buttonText: "Claim After Checkout",
  });

  const faqsSection = sections.find((s) => s.sectionType === "FAQS");
  const faqs = safeJsonParse<{ faqs: { q: string; a: string }[] }>(
    faqsSection?.content,
    { faqs: [] }
  );

  return (
    <div className={`space-y-16 sm:space-y-24 ${product.stickyBarEnabled ? "pb-28" : "pb-16"}`}>
      
      {/* ========================================================
          HERO SECTION: 2-Column High-Converting SaaS Layout
      ======================================================== */}
      <section className="pt-8 sm:pt-14 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
          
          {/* Left Column: Product Information & Purchase Controls (7 cols) */}
          <div className="lg:col-span-7 space-y-6">
            
            {/* Top Kicker Pill */}
            <div className="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-blue-50 border border-blue-200 text-brand-blue text-xs font-bold tracking-wider shadow-soft-sm">
              <span className="w-2 h-2 rounded-full bg-brand-blue animate-pulse" />
              <span>{heroContent.kicker || "⚡ VERIFIED DIGITAL ACCESS"}</span>
            </div>

            {/* Headline with Highlighting */}
            <div className="space-y-2">
              <h1 className="text-3xl sm:text-5xl lg:text-6xl font-black text-[#0F172A] tracking-tight leading-tight">
                {product.title}
              </h1>
              {heroContent.highlightedText && (
                <div className="text-2xl sm:text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-brand-blue via-indigo-600 to-blue-700">
                  {heroContent.highlightedText}
                </div>
              )}
            </div>

            {/* Bonus notice badge */}
            {product.bonusEnabled && (
              <div className="p-3.5 rounded-2xl bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 text-purple-900 text-xs sm:text-sm font-semibold flex items-center space-x-2.5 shadow-soft-sm">
                <div className="w-7 h-7 rounded-lg bg-purple-100 flex items-center justify-center text-brand-purple shrink-0">
                  <Sparkles className="w-4 h-4" />
                </div>
                <span>{product.bonusTitle || "Includes 1 Year Canva Pro FREE Bonus"}</span>
              </div>
            )}

            {/* Short Description */}
            <p className="text-sm sm:text-base text-[#475569] leading-relaxed max-w-2xl">
              {product.shortDescription || product.subtitle}
            </p>

            {/* Plan Selector Buttons */}
            {plans.length > 1 && (
              <div className="space-y-2 pt-2">
                <div className="text-xs font-bold uppercase tracking-wider text-[#64748B] font-mono flex items-center justify-between">
                  <span>Select Validity Duration:</span>
                  <span className="text-[11px] text-brand-blue font-semibold lowercase">instant activation</span>
                </div>
                <div className="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                  {plans.map((p) => {
                    const isSelected = selectedPlan.id === p.id;
                    return (
                      <button
                        key={p.id}
                        type="button"
                        onClick={() => setSelectedPlan(p)}
                        className={`p-3.5 rounded-xl border text-left transition-all relative ${
                          isSelected
                            ? "bg-blue-50 border-2 border-brand-blue ring-2 ring-blue-100 text-[#0F172A] shadow-soft-sm"
                            : "bg-white border-[#E2E8F0] text-[#64748B] hover:border-blue-300 hover:text-[#0F172A] shadow-soft-sm"
                        }`}
                      >
                        {isSelected && (
                          <div className="absolute top-2 right-2 w-4 h-4 rounded-full bg-brand-blue text-white flex items-center justify-center">
                            <Check className="w-2.5 h-2.5" />
                          </div>
                        )}
                        <div className="text-xs font-bold truncate pr-3">{p.name}</div>
                        <div className="text-sm font-black font-mono text-[#0F172A] mt-1">
                          {formatINR(p.price)}
                        </div>
                      </button>
                    );
                  })}
                </div>
              </div>
            )}

            {/* Live Price Display Row */}
            <div className="p-5 rounded-2xl bg-white border border-[#E2E8F0] shadow-soft-md flex flex-wrap items-center justify-between gap-4">
              <div>
                <div className="text-[11px] font-mono uppercase text-[#64748B] font-semibold">
                  Pay Once • No Auto-Renewal • 100% Genuine
                </div>
                <div className="flex items-baseline space-x-3 mt-1">
                  <span className="text-4xl sm:text-5xl font-black text-[#0F172A] font-mono">
                    {formatINR(currentPrice)}
                  </span>
                  {comparePrice > currentPrice && (
                    <span className="text-base text-[#94A3B8] line-through font-mono">
                      {formatINR(comparePrice)}
                    </span>
                  )}
                  {savingsPct > 0 && (
                    <span className="text-xs font-bold px-2.5 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">
                      Save {savingsPct}%
                    </span>
                  )}
                </div>
              </div>

              <div className="text-right">
                <span className="badge-green text-[10px]">
                  INSTANT DIGITAL DELIVERY
                </span>
                <span className="block text-[11px] text-[#64748B] mt-1 font-mono font-medium">
                  30-Day Guarantee
                </span>
              </div>
            </div>

            {/* Purchase CTA Buttons */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
              <Link
                href={`/checkout?product=${product.id}&plan=${selectedPlan?.id}`}
                className="w-full py-4 rounded-xl btn-primary-blue text-sm font-bold text-center flex items-center justify-center space-x-2 tracking-wide text-white min-h-[50px] shadow-soft-md"
              >
                <Zap className="w-4 h-4" />
                <span>{heroContent.primaryCta || "BUY NOW"}</span>
                <ArrowRight className="w-4 h-4" />
              </Link>

              <a
                href={whatsappUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="w-full py-4 rounded-xl btn-whatsapp text-sm font-bold text-center flex items-center justify-center space-x-2 tracking-wide text-white min-h-[50px] shadow-soft-md"
              >
                <WhatsAppIcon className="w-5 h-5 text-white shrink-0" />
                <span>{heroContent.whatsappCta || "WHATSAPP SUPPORT"}</span>
              </a>
            </div>

            {/* Micro Trust Indicators */}
            <div className="grid grid-cols-3 gap-2 text-center text-[10px] sm:text-[11px] text-[#64748B] pt-3 border-t border-[#E2E8F0]">
              <div className="flex items-center justify-center space-x-1.5">
                <Lock className="w-3.5 h-3.5 text-brand-blue" />
                <span className="font-medium">SSL Encrypted</span>
              </div>
              <div className="flex items-center justify-center space-x-1.5">
                <Zap className="w-3.5 h-3.5 text-brand-blue" />
                <span className="font-medium">Auto Fulfillment</span>
              </div>
              <div className="flex items-center justify-center space-x-1.5">
                <ShieldCheck className="w-3.5 h-3.5 text-brand-blue" />
                <span className="font-medium">Replacement Warranty</span>
              </div>
            </div>

          </div>

          {/* Right Column: YouTube Video Embed Container (5 cols) */}
          <div className="lg:col-span-5">
            <div className="p-2 sm:p-3 bg-white rounded-2xl border border-[#E2E8F0] shadow-soft-xl relative overflow-hidden group">
              <div className="relative aspect-video rounded-xl overflow-hidden bg-slate-900 flex items-center justify-center border border-[#E2E8F0]">
                {videoLoaded ? (
                  <iframe
                    className="w-full h-full"
                    src={`https://www.youtube.com/embed/${heroContent.videoId || "GeXVI3p-BqE"}?autoplay=1&rel=0&modestbranding=1`}
                    title="Product Tutorial & Demonstration"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowFullScreen
                  />
                ) : (
                  <div
                    onClick={() => setVideoLoaded(true)}
                    className="w-full h-full cursor-pointer relative flex items-center justify-center group/btn"
                  >
                    {/* YouTube thumbnail background */}
                    <img
                      src={`https://img.youtube.com/vi/${heroContent.videoId || "GeXVI3p-BqE"}/hqdefault.jpg`}
                      alt="Video Tutorial"
                      className="absolute inset-0 w-full h-full object-cover opacity-80 group-hover/btn:opacity-95 transition-opacity"
                    />

                    {/* Dark Vignette */}
                    <div className="absolute inset-0 bg-slate-950/30 group-hover/btn:bg-slate-950/20 transition-colors" />

                    {/* Play Button */}
                    <div className="relative z-10 w-16 h-16 rounded-full bg-white text-brand-blue flex items-center justify-center shadow-soft-xl group-hover/btn:scale-110 transition-transform border border-blue-100">
                      <Play className="w-7 h-7 fill-brand-blue ml-1" />
                    </div>

                    <div className="absolute bottom-3 inset-x-3 text-center text-xs font-bold text-[#0F172A] bg-white/95 py-2 px-3 rounded-lg backdrop-blur-sm shadow-soft-sm flex items-center justify-center space-x-2">
                      <Clock className="w-3.5 h-3.5 text-brand-blue" />
                      <span>▶ Watch Step-by-Step Setup Video</span>
                    </div>
                  </div>
                )}
              </div>
            </div>
          </div>

        </div>
      </section>

      {/* ========================================================
          KEY FACTS / CHIPS ROW
      ======================================================== */}
      {keyFacts.chips && keyFacts.chips.length > 0 && (
        <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            {keyFacts.chips.map((chip, idx) => (
              <div
                key={idx}
                className="p-3.5 rounded-xl bg-white border border-[#E2E8F0] flex items-center space-x-2.5 text-xs font-bold text-[#0F172A] shadow-soft-sm hover:border-blue-300 transition-colors"
              >
                <span className="w-2 h-2 rounded-full bg-brand-blue shrink-0" />
                <span className="truncate">{chip.text}</span>
              </div>
            ))}
          </div>
        </section>
      )}

      {/* ========================================================
          TUTORIAL STEPS: How It Works Section
      ======================================================== */}
      {howItWorks.steps && howItWorks.steps.length > 0 && (
        <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="p-8 sm:p-12 rounded-3xl bg-white border border-[#E2E8F0] shadow-soft-md space-y-8">
            <div className="text-center max-w-2xl mx-auto space-y-2">
              <span className="badge-blue">EASY SETUP</span>
              <h2 className="text-2xl sm:text-4xl font-black text-[#0F172A] tracking-tight">
                {howItWorksSection?.title || "Tutorial Steps to Login & Activate"}
              </h2>
              <p className="text-xs sm:text-sm text-[#64748B]">
                {howItWorksSection?.subtitle || "Follow these simple steps to connect, sign in, verify your access, and start editing."}
              </p>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {howItWorks.steps.map((step, idx) => (
                <div
                  key={idx}
                  className="p-6 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] space-y-3 relative group hover:border-blue-300 hover:shadow-soft-sm transition-all"
                >
                  <div className="flex items-center justify-between">
                    <span className="text-xs font-mono font-bold text-brand-blue bg-blue-50 px-2.5 py-1 rounded-md border border-blue-200">
                      {step.number}
                    </span>
                    {step.badge && (
                      <span className="text-[10px] font-bold text-[#64748B] font-mono">
                        {step.badge}
                      </span>
                    )}
                  </div>
                  <h3 className="text-base font-bold text-[#0F172A] pt-1">{step.title}</h3>
                  <p className="text-xs text-[#64748B] leading-relaxed">
                    {step.description}
                  </p>
                </div>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* ========================================================
          BENEFITS GRID SECTION
      ======================================================== */}
      {benefits.items && benefits.items.length > 0 && (
        <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
          <div className="text-center max-w-2xl mx-auto space-y-2">
            <span className="badge-blue">PRO ADVANTAGES</span>
            <h2 className="text-2xl sm:text-4xl font-black text-[#0F172A] tracking-tight">
              {benefitsSection?.title || "Key Features & Capabilities"}
            </h2>
            <p className="text-xs sm:text-sm text-[#64748B]">
              {benefitsSection?.subtitle || "Everything included with your verified license."}
            </p>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {benefits.items.map((item, idx) => (
              <div
                key={idx}
                className="p-6 rounded-2xl bg-white border border-[#E2E8F0] space-y-3 shadow-soft-sm hover:shadow-soft-md hover:border-blue-300 transition-all"
              >
                <div className="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 text-brand-blue flex items-center justify-center">
                  <CheckCircle2 className="w-5 h-5" />
                </div>
                <h3 className="text-base font-bold text-[#0F172A]">{item.title}</h3>
                <p className="text-xs text-[#64748B] leading-relaxed">
                  {item.description}
                </p>
              </div>
            ))}
          </div>
        </section>
      )}

      {/* ========================================================
          BONUS SECTION: Canva Pro / Extra Value Highlight
      ======================================================== */}
      {product.bonusEnabled && (
        <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="p-8 sm:p-12 rounded-3xl bg-gradient-to-br from-[#FAF5FF] via-[#F5F3FF] to-[#EDE9FE] border-2 border-[#DDD6FE] shadow-soft-lg space-y-6 relative overflow-hidden">
            <div className="max-w-3xl space-y-4">
              <span className="badge-purple">
                {product.bonusBadge || "VALUED AT ₹1,499"}
              </span>
              <h2 className="text-2xl sm:text-4xl font-black text-[#581C87] tracking-tight">
                {product.bonusTitle || "🎁 BONUS: CANVA PRO FREE (1 YEAR)"}
              </h2>
              <p className="text-sm sm:text-base text-[#6B21A8] font-medium leading-relaxed">
                {product.bonusSubtitle || "Complete your CapCut Pro purchase and claim your Canva Pro bonus directly on your order delivery page."}
              </p>
              {product.bonusExplanation && (
                <div className="p-4 rounded-xl bg-white/80 border border-[#DDD6FE] text-xs text-[#581C87] leading-relaxed font-mono backdrop-blur-sm">
                  {product.bonusExplanation}
                </div>
              )}
              <div className="pt-2">
                <Link
                  href={`/checkout?product=${product.id}&plan=${selectedPlan?.id}`}
                  className="inline-flex items-center space-x-2 px-6 py-3.5 rounded-xl btn-purple-bonus text-xs font-bold tracking-wider uppercase text-white shadow-soft-md"
                >
                  <Sparkles className="w-4 h-4" />
                  <span>Claim Bonus With Order</span>
                </Link>
              </div>
            </div>
          </div>
        </section>
      )}

      {/* ========================================================
          PRICING PLANS TABLE
      ======================================================== */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div className="text-center max-w-2xl mx-auto space-y-2">
          <span className="badge-blue">TRANSPARENT PRICING</span>
          <h2 className="text-2xl sm:text-4xl font-black text-[#0F172A] tracking-tight">
            Choose Your Subscription Plan
          </h2>
          <p className="text-xs sm:text-sm text-[#64748B]">
            One-time payment with zero hidden charges or unwanted auto-debits.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
          {plans.map((p) => {
            const isSelected = selectedPlan.id === p.id;
            const savings = calculateSavingsPercentage(p.compareAtPrice || 0, p.price);
            const planBenefits = safeJsonParse<string[]>(p.benefits, []);

            return (
              <div
                key={p.id}
                className={`p-8 rounded-2xl flex flex-col justify-between transition-all relative ${
                  isSelected
                    ? "bg-white border-2 border-brand-blue shadow-soft-lg ring-4 ring-blue-50"
                    : "bg-white border border-[#E2E8F0] shadow-soft-sm hover:border-blue-300"
                }`}
              >
                {p.isRecommended && (
                  <span className="absolute -top-3 right-6 badge-purple text-[10px] shadow-soft-sm">
                    {p.recommendedLabel || "RECOMMENDED"}
                  </span>
                )}

                <div className="space-y-4">
                  <div>
                    <h3 className="text-lg font-black text-[#0F172A]">{p.name}</h3>
                    <p className="text-xs text-[#64748B] font-mono mt-0.5">{p.duration} Full Access</p>
                  </div>

                  <div className="pt-2">
                    <div className="flex items-baseline space-x-2">
                      <span className="text-4xl font-black text-[#0F172A] font-mono">
                        {formatINR(p.price)}
                      </span>
                      {p.compareAtPrice && p.compareAtPrice > p.price && (
                        <span className="text-sm text-[#94A3B8] line-through font-mono">
                          {formatINR(p.compareAtPrice)}
                        </span>
                      )}
                    </div>
                    {savings > 0 && (
                      <span className="inline-block mt-1 text-[11px] font-bold text-emerald-600">
                        Instant Savings: {savings}% OFF
                      </span>
                    )}
                  </div>

                  <ul className="space-y-2.5 pt-4 border-t border-[#E2E8F0] text-xs text-[#475569]">
                    {planBenefits.map((b, idx) => (
                      <li key={idx} className="flex items-center space-x-2">
                        <CheckCircle2 className="w-4 h-4 text-brand-blue shrink-0" />
                        <span>{b}</span>
                      </li>
                    ))}
                  </ul>
                </div>

                <div className="pt-6 mt-6 border-t border-[#E2E8F0]">
                  <Link
                    href={`/checkout?product=${product.id}&plan=${p.id}`}
                    onClick={() => setSelectedPlan(p)}
                    className={`w-full py-3.5 rounded-xl text-xs font-bold text-center block ${
                      isSelected
                        ? "btn-primary-blue text-white shadow-soft-sm"
                        : "bg-[#F8FAFC] border border-[#E2E8F0] hover:bg-white hover:border-brand-blue text-[#0F172A] transition-colors"
                    }`}
                  >
                    Select {p.duration} Plan
                  </Link>
                </div>
              </div>
            );
          })}
        </div>
      </section>

      {/* ========================================================
          FREQUENTLY ASKED QUESTIONS
      ======================================================== */}
      {faqs.faqs && faqs.faqs.length > 0 && (
        <section className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
          <div className="text-center space-y-2">
            <span className="badge-blue">GOT QUESTIONS?</span>
            <h2 className="text-2xl sm:text-3xl font-black text-[#0F172A] tracking-tight">
              {faqsSection?.title || "Frequently Asked Questions"}
            </h2>
          </div>

          <div className="space-y-3">
            {faqs.faqs.map((faq, idx) => {
              const isOpen = openFaqIndex === idx;
              return (
                <div
                  key={idx}
                  className="rounded-2xl bg-white border border-[#E2E8F0] overflow-hidden transition-colors shadow-soft-sm"
                >
                  <button
                    type="button"
                    onClick={() => setOpenFaqIndex(isOpen ? null : idx)}
                    className="w-full p-5 text-left font-bold text-sm text-[#0F172A] flex items-center justify-between hover:text-brand-blue transition-colors"
                  >
                    <span>{faq.q}</span>
                    <ChevronDown
                      className={`w-4 h-4 text-brand-blue transition-transform ${
                        isOpen ? "rotate-180" : ""
                      }`}
                    />
                  </button>
                  {isOpen && (
                    <div className="px-5 pb-5 text-xs sm:text-sm text-[#475569] leading-relaxed border-t border-[#E2E8F0] pt-4 bg-[#F8FAFC]/50">
                      {faq.a}
                    </div>
                  )}
                </div>
              );
            })}
          </div>
        </section>
      )}

      {/* ========================================================
          MOBILE & DESKTOP STICKY PURCHASE BAR
      ======================================================== */}
      {product.stickyBarEnabled && (
        <div className="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-[#E2E8F0] py-3 px-4 shadow-soft-xl">
          <div className="max-w-7xl mx-auto flex items-center justify-between gap-4">
            
            <div className="flex items-center space-x-3 truncate">
              <div className="truncate">
                <span className="font-bold text-xs sm:text-sm text-[#0F172A] truncate block">
                  {product.stickyBarText || `${product.title} • ${selectedPlan.name}`}
                </span>
                <span className="text-[10px] text-emerald-600 font-mono font-bold flex items-center space-x-1">
                  <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block" />
                  <span>Instant Delivery • 30-Day Guarantee</span>
                </span>
              </div>
              <span className="hidden sm:inline font-mono font-black text-[#0F172A] text-lg pl-3 border-l border-[#E2E8F0]">
                {formatINR(currentPrice)}
              </span>
            </div>

            <div className="flex items-center space-x-2 shrink-0">
              <Link
                href={`/checkout?product=${product.id}&plan=${selectedPlan?.id}`}
                className="px-5 py-2.5 rounded-xl btn-primary-blue text-xs font-bold uppercase tracking-wider text-white shadow-soft-sm"
              >
                {product.stickyBarButtonLabel || "Buy Now"}
              </Link>
              <a
                href={whatsappUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="px-3.5 py-2.5 rounded-xl btn-whatsapp text-xs font-bold text-white shadow-soft-sm flex items-center justify-center"
                aria-label="WhatsApp Support"
              >
                <WhatsAppIcon className="w-4 h-4 text-white" />
              </a>
            </div>

          </div>
        </div>
      )}

    </div>
  );
}
