import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { formatINR, calculateSavingsPercentage } from "@/lib/format";
import { StoreHeader } from "@/components/store/StoreHeader";
import { StoreFooter } from "@/components/store/StoreFooter";
import {
  BookOpen,
  Sparkles,
  Zap,
  ShieldCheck,
  CheckCircle2,
  ArrowRight,
  Search,
  Lock,
  FileText,
  Clock,
} from "lucide-react";

export const revalidate = 60; // 1 min ISR

const EBOOK_CATEGORY_SLUGS = [
  "pain-relief-spine",
  "gut-health-digestion",
  "posture-ergonomics",
  "sleep-stress",
];

export default async function StoreHomePage({
  searchParams,
}: {
  searchParams: { q?: string; category?: string };
}) {
  const settings = await prisma.storeSetting.findMany();
  const settingsMap = new Map(settings.map((s) => [s.key, s.value]));

  const storeName = settingsMap.get("store_name") || "Convora Store";
  const storeBadge = settingsMap.get("store_badge") || "VERIFIED";
  const whatsappNumber = settingsMap.get("whatsapp_number") || "919990798996";

  // Query only eBook / digital wellness categories for homepage compliance
  const categories = await prisma.category.findMany({
    where: {
      slug: { in: EBOOK_CATEGORY_SLUGS },
      isActive: true,
    },
    orderBy: { displayOrder: "asc" },
  });

  const categoryIds = categories.map((c) => c.id);

  // Query products belonging to eBook categories
  const whereClause: Record<string, unknown> = {
    status: "PUBLISHED",
    visibility: "PUBLIC",
    categoryId: { in: categoryIds },
  };

  if (searchParams.category) {
    const matchedCat = categories.find((c) => c.slug === searchParams.category);
    if (matchedCat) {
      whereClause.categoryId = matchedCat.id;
    }
  }

  if (searchParams.q) {
    whereClause.OR = [
      { title: { contains: searchParams.q } },
      { shortDescription: { contains: searchParams.q } },
      { subtitle: { contains: searchParams.q } },
      { tags: { contains: searchParams.q } },
    ];
  }

  const products = await prisma.product.findMany({
    where: whereClause,
    include: {
      category: true,
      plans: {
        where: { isActive: true },
        orderBy: { price: "asc" },
      },
    },
    orderBy: [{ isFeatured: "desc" }, { displayOrder: "asc" }],
  });

  return (
    <>
      <StoreHeader
        storeName={storeName}
        storeBadge={storeBadge}
        whatsappNumber={whatsappNumber}
      />

      <main className="space-y-16 sm:space-y-24 pb-20">
        {/* HERO SECTION: Compliant Digital eBook Storefront */}
        <section className="pt-10 sm:pt-16 pb-12 px-4 sm:px-6 lg:px-8 border-b border-[#E2E8F0] bg-gradient-to-b from-white to-transparent">
          <div className="max-w-4xl mx-auto text-center space-y-6">
            
            {/* Pill Kicker */}
            <div className="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-blue-50 border border-blue-200 text-brand-blue text-xs font-bold tracking-wider shadow-soft-sm">
              <BookOpen className="w-3.5 h-3.5 text-brand-blue" />
              <span>EVIDENCE-BASED DIGITAL GUIDES & EBOOKS</span>
            </div>

            {/* Main Headline */}
            <h1 className="text-3xl sm:text-5xl md:text-6xl font-black text-[#0F172A] tracking-tight leading-tight">
              Evidence-Based Guides for{" "}
              <span className="text-transparent bg-clip-text bg-gradient-to-r from-brand-blue via-indigo-600 to-blue-700">
                Pain Relief, Gut Health & Daily Wellness
              </span>
            </h1>

            {/* Subtitle */}
            <p className="text-sm sm:text-base text-[#475569] max-w-2xl mx-auto leading-relaxed">
              Step-by-step clinical protocols, physical therapy exercises, and nutrition blueprints delivered instantly in downloadable PDF format. Accessible immediately on your phone, tablet, or computer.
            </p>

            {/* Search Pill Form */}
            <form action="/" method="GET" className="max-w-xl mx-auto pt-2">
              <div className="relative flex items-center shadow-soft-md rounded-2xl bg-white border border-[#E2E8F0] focus-within:border-brand-blue transition-all p-1.5">
                <div className="pl-3.5 pr-2 text-[#64748B]">
                  <Search className="w-4 h-4 text-brand-blue" />
                </div>
                <input
                  type="text"
                  name="q"
                  defaultValue={searchParams.q || ""}
                  placeholder="Search Cervical Pain, Gut Reset, Lower Back guides..."
                  className="w-full bg-transparent border-none text-xs sm:text-sm text-[#0F172A] placeholder-[#94A3B8] focus:outline-none py-2 px-1"
                />
                {searchParams.category && (
                  <input type="hidden" name="category" value={searchParams.category} />
                )}
                <button
                  type="submit"
                  className="shrink-0 px-4 py-2 rounded-xl btn-primary-blue text-xs font-bold text-white shadow-soft-sm"
                >
                  Search
                </button>
              </div>
            </form>

            {/* Category Filter Chips */}
            <div className="flex flex-wrap items-center justify-center gap-2 pt-2">
              <Link
                href="/"
                className={`text-xs px-3.5 py-1.5 rounded-full border transition-all shadow-soft-sm ${
                  !searchParams.category
                    ? "bg-brand-blue text-white font-bold border-brand-blue"
                    : "bg-white text-[#475569] border-[#E2E8F0] hover:border-blue-300 hover:text-[#0F172A]"
                }`}
              >
                All Digital Guides
              </Link>
              {categories.map((cat) => (
                <Link
                  key={cat.id}
                  href={`/?category=${cat.slug}`}
                  className={`text-xs px-3.5 py-1.5 rounded-full border transition-all shadow-soft-sm ${
                    searchParams.category === cat.slug
                      ? "bg-brand-blue text-white font-bold border-brand-blue"
                      : "bg-white text-[#475569] border-[#E2E8F0] hover:border-blue-300 hover:text-[#0F172A]"
                  }`}
                >
                  {cat.name}
                </Link>
              ))}
            </div>

          </div>
        </section>

        {/* PRODUCTS CATALOG SECTION: Digital Guides & eBooks */}
        <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
          <div className="flex items-end justify-between border-b border-[#E2E8F0] pb-4">
            <div>
              <h2 className="text-2xl sm:text-3xl font-black text-[#0F172A] tracking-tight">
                {searchParams.category
                  ? categories.find((c) => c.slug === searchParams.category)?.name
                  : "Featured Digital Guides & Protocols"}
              </h2>
              <p className="text-xs sm:text-sm text-[#64748B] mt-1">
                Showing {products.length} evidence-based wellness guides with instant PDF delivery
              </p>
            </div>
            <div className="hidden sm:flex items-center space-x-2 text-xs text-brand-blue font-bold">
              <Zap className="w-3.5 h-3.5" />
              <span>Instant Download via Razorpay</span>
            </div>
          </div>

          {products.length === 0 ? (
            <div className="p-12 text-center max-w-md mx-auto space-y-4 rounded-2xl bg-white border border-[#E2E8F0] shadow-soft-sm">
              <div className="w-12 h-12 rounded-full bg-blue-50 border border-blue-100 mx-auto flex items-center justify-center text-[#64748B]">
                <Search className="w-6 h-6 text-brand-blue" />
              </div>
              <h3 className="text-base font-bold text-[#0F172A]">No guides found</h3>
              <p className="text-xs text-[#64748B]">
                No guides match your search. Try resetting your query or category filters.
              </p>
              <Link
                href="/"
                className="inline-block px-4 py-2 rounded-xl btn-primary-blue text-xs font-bold text-white shadow-soft-sm"
              >
                View All Guides
              </Link>
            </div>
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
              {products.map((product) => {
                const defaultPlan = product.plans.find((p) => p.isDefault) || product.plans[0];
                const lowestPrice = defaultPlan ? defaultPlan.price : 0;
                const comparePrice = defaultPlan?.compareAtPrice || 0;
                const savingsPct = calculateSavingsPercentage(comparePrice, lowestPrice);

                return (
                  <div
                    key={product.id}
                    className="p-6 rounded-2xl bg-white border border-[#E2E8F0] shadow-soft-sm hover:shadow-soft-md hover:border-blue-300 transition-all flex flex-col justify-between group relative overflow-hidden"
                  >
                    {/* Top Accent bar on hover */}
                    <div className="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-brand-blue to-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity" />

                    <div className="space-y-4">
                      {/* Badge & Category */}
                      <div className="flex items-center justify-between">
                        <span className="text-[10px] font-mono uppercase tracking-wider text-brand-blue font-bold bg-blue-50 px-2 py-0.5 rounded border border-blue-200 flex items-center gap-1">
                          <BookOpen className="w-3 h-3 text-brand-blue" />
                          {product.category?.name || "Digital Protocol"}
                        </span>
                        {product.isFeatured ? (
                          <span className="badge-purple text-[10px]">
                            ⭐ BESTSELLER
                          </span>
                        ) : (
                          <span className="text-[10px] font-mono text-emerald-700 font-bold bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded">
                            PDF EDITION
                          </span>
                        )}
                      </div>

                      {/* Title & Subtitle */}
                      <div>
                        <h3 className="text-xl font-black text-[#0F172A] group-hover:text-brand-blue transition-colors">
                          <Link href={`/products/${product.slug}`}>
                            {product.title}
                          </Link>
                        </h3>
                        <p className="text-xs text-[#64748B] mt-1.5 line-clamp-2 leading-relaxed">
                          {product.shortDescription || product.subtitle}
                        </p>
                      </div>

                      {/* Benefits Bullets */}
                      {defaultPlan && defaultPlan.benefits && (
                        <div className="space-y-1.5 pt-2 border-t border-[#E2E8F0]">
                          {(() => {
                            try {
                              const list = JSON.parse(defaultPlan.benefits) as string[];
                              return list.slice(0, 3).map((item, idx) => (
                                <div key={idx} className="flex items-center space-x-2 text-xs text-[#475569]">
                                  <CheckCircle2 className="w-3.5 h-3.5 text-brand-blue shrink-0" />
                                  <span className="truncate">{item}</span>
                                </div>
                              ));
                            } catch {
                              return null;
                            }
                          })()}
                        </div>
                      )}

                      {/* Format Badge */}
                      <div className="p-2.5 rounded-xl bg-blue-50/70 border border-blue-100 text-[#1E40AF] text-xs flex items-center space-x-2">
                        <FileText className="w-4 h-4 shrink-0 text-brand-blue" />
                        <span className="font-medium text-[11px] truncate">
                          Instant Download • Compatible with Phone, Tablet & PC
                        </span>
                      </div>
                    </div>

                    {/* Bottom Pricing & Action */}
                    <div className="pt-6 border-t border-[#E2E8F0] mt-6 space-y-3">
                      <div className="flex items-baseline justify-between">
                        <div>
                          <div className="text-[10px] uppercase font-mono text-[#64748B] font-semibold">Instant Digital Access</div>
                          <div className="flex items-baseline space-x-2">
                            <span className="text-2xl font-black text-[#0F172A] font-mono">
                              {formatINR(lowestPrice)}
                            </span>
                            {comparePrice > lowestPrice && (
                              <span className="text-xs text-[#94A3B8] line-through font-mono">
                                {formatINR(comparePrice)}
                              </span>
                            )}
                          </div>
                        </div>

                        {savingsPct > 0 && (
                          <span className="text-[10px] font-bold font-mono px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Save {savingsPct}%
                          </span>
                        )}
                      </div>

                      <div className="grid grid-cols-2 gap-2">
                        <Link
                          href={`/products/${product.slug}`}
                          className="w-full py-2.5 rounded-xl btn-primary-blue text-xs font-bold text-center block text-white shadow-soft-sm"
                        >
                          View Guide
                        </Link>
                        <Link
                          href={`/checkout?product=${product.id}&plan=${defaultPlan?.id}`}
                          className="w-full py-2.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] hover:border-brand-blue text-[#0F172A] text-xs font-bold text-center block transition-colors"
                        >
                          Buy Now
                        </Link>
                      </div>
                    </div>

                  </div>
                );
              })}
            </div>
          )}
        </section>

        {/* HOW DIGITAL DELIVERY WORKS */}
        <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="p-8 sm:p-12 rounded-3xl bg-white border border-[#E2E8F0] shadow-soft-md space-y-8">
            <div className="text-center max-w-2xl mx-auto space-y-2">
              <span className="badge-blue">INSTANT 3-STEP PROCESS</span>
              <h2 className="text-2xl sm:text-4xl font-black text-[#0F172A] tracking-tight">
                How Instant Digital Download Works
              </h2>
              <p className="text-xs sm:text-sm text-[#64748B]">
                From checkout to your digital guide in less than 30 seconds.
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div className="p-6 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] space-y-3 shadow-soft-sm">
                <div className="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 text-brand-blue flex items-center justify-center font-black font-mono">
                  01
                </div>
                <h3 className="text-base font-bold text-[#0F172A]">Choose Your Guide</h3>
                <p className="text-xs text-[#64748B] leading-relaxed">
                  Select your evidence-based health protocol. Complete with illustrated exercise routines, printable checklists, and meal plans.
                </p>
              </div>

              <div className="p-6 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] space-y-3 shadow-soft-sm">
                <div className="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 text-brand-blue flex items-center justify-center font-black font-mono">
                  02
                </div>
                <h3 className="text-base font-bold text-[#0F172A]">Secure Razorpay Payment</h3>
                <p className="text-xs text-[#64748B] leading-relaxed">
                  Pay securely via UPI (Google Pay, PhonePe, Paytm, CRED), Debit/Credit Cards, or NetBanking with 256-bit bank encryption.
                </p>
              </div>

              <div className="p-6 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] space-y-3 shadow-soft-sm">
                <div className="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 text-brand-blue flex items-center justify-center font-black font-mono">
                  03
                </div>
                <h3 className="text-base font-bold text-[#0F172A]">Instant PDF Download</h3>
                <p className="text-xs text-[#64748B] leading-relaxed">
                  Your PDF eBook download button appears on your screen immediately after payment, with a permanent backup copy emailed to you.
                </p>
              </div>
            </div>
          </div>
        </section>

      </main>

      <StoreFooter storeName={storeName} />
    </>
  );
}
