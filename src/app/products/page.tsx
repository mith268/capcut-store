import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { formatINR, calculateSavingsPercentage } from "@/lib/format";
import { StoreHeader } from "@/components/store/StoreHeader";
import { StoreFooter } from "@/components/store/StoreFooter";
import { Search, Sparkles, CheckCircle2, ArrowRight } from "lucide-react";

import { Category, Product, Plan } from "@prisma/client";

export const dynamic = "force-dynamic";

type ProductWithPlans = Product & {
  category: Category | null;
  plans: Plan[];
};

export default async function ProductsCatalogPage({
  searchParams,
}: {
  searchParams: { q?: string; category?: string; sort?: string };
}) {
  let storeName = "Convora Store";
  let categories: Category[] = [];
  let products: ProductWithPlans[] = [];

  try {
    const settings = await prisma.storeSetting.findMany();
    const settingsMap = new Map(settings.map((s) => [s.key, s.value]));
    if (settingsMap.get("store_name")) storeName = settingsMap.get("store_name")!;

    categories = await prisma.category.findMany({
      where: { isActive: true },
      orderBy: { displayOrder: "asc" },
    });

    const whereClause: Record<string, unknown> = {
      status: "PUBLISHED",
      visibility: "PUBLIC",
    };

    if (searchParams.category) {
      const matched = categories.find((c) => c.slug === searchParams.category);
      if (matched) whereClause.categoryId = matched.id;
    }

    if (searchParams.q) {
      whereClause.OR = [
        { title: { contains: searchParams.q } },
        { shortDescription: { contains: searchParams.q } },
        { tags: { contains: searchParams.q } },
      ];
    }

    products = await prisma.product.findMany({
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
  } catch {
    // Graceful fallback during build
  }

  return (
    <>
      <StoreHeader storeName={storeName} />

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        
        {/* Header & Filter Controls */}
        <div className="space-y-4 border-b border-[#E2E8F0] pb-6">
          <div className="flex flex-wrap items-center justify-between gap-4">
            <div>
              <h1 className="text-3xl sm:text-4xl font-black text-[#0F172A] tracking-tight">
                Product Catalog
              </h1>
              <p className="text-xs sm:text-sm text-[#64748B] mt-1">
                Browse all verified creator tools, digital software subscriptions, and educational resources.
              </p>
            </div>

            {/* Search Input */}
            <form action="/products" method="GET" className="w-full sm:w-72">
              <div className="relative flex items-center rounded-xl bg-white border border-[#E2E8F0] shadow-soft-sm focus-within:border-brand-blue p-1">
                <Search className="w-4 h-4 text-brand-blue ml-2 shrink-0" />
                <input
                  type="text"
                  name="q"
                  defaultValue={searchParams.q || ""}
                  placeholder="Search products..."
                  className="w-full bg-transparent border-none text-xs text-[#0F172A] placeholder-[#94A3B8] focus:outline-none px-2 py-1"
                />
              </div>
            </form>
          </div>

          {/* Category Chips */}
          <div className="flex flex-wrap gap-2 pt-2">
            <Link
              href="/products"
              className={`text-xs px-3.5 py-1.5 rounded-full border transition-all shadow-soft-sm ${
                !searchParams.category
                  ? "bg-brand-blue text-white font-bold border-brand-blue"
                  : "bg-white text-[#475569] border-[#E2E8F0] hover:border-blue-300 hover:text-[#0F172A]"
              }`}
            >
              All Categories
            </Link>
            {categories.map((cat) => (
              <Link
                key={cat.id}
                href={`/products?category=${cat.slug}`}
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

        {/* Product Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
          {products.map((product) => {
            const defaultPlan = product.plans[0];
            const lowestPrice = defaultPlan ? defaultPlan.price : 0;
            const comparePrice = defaultPlan?.compareAtPrice || 0;
            const savingsPct = calculateSavingsPercentage(comparePrice, lowestPrice);

            return (
              <div
                key={product.id}
                className="p-6 rounded-2xl bg-white border border-[#E2E8F0] shadow-soft-sm hover:shadow-soft-md hover:border-blue-300 transition-all flex flex-col justify-between group"
              >
                <div className="space-y-4">
                  <div className="flex items-center justify-between">
                    <span className="text-[10px] font-mono uppercase tracking-wider text-brand-blue font-bold bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                      {product.category?.name || "Digital Access"}
                    </span>
                    {product.isFeatured && (
                      <span className="badge-purple text-[10px]">⭐ POPULAR</span>
                    )}
                  </div>

                  <div>
                    <h2 className="text-xl font-black text-[#0F172A] group-hover:text-brand-blue transition-colors">
                      <Link href={`/products/${product.slug}`}>
                        {product.title}
                      </Link>
                    </h2>
                    <p className="text-xs text-[#64748B] mt-1.5 line-clamp-2 leading-relaxed">
                      {product.shortDescription || product.subtitle}
                    </p>
                  </div>

                  {product.bonusEnabled && (
                    <div className="p-2.5 rounded-xl bg-purple-50 border border-purple-200 text-purple-900 text-xs flex items-center space-x-2">
                      <Sparkles className="w-4 h-4 text-brand-purple shrink-0" />
                      <span className="font-bold text-[11px] truncate">
                        {product.bonusTitle}
                      </span>
                    </div>
                  )}
                </div>

                <div className="pt-6 border-t border-[#E2E8F0] mt-6 space-y-3">
                  <div className="flex items-baseline justify-between">
                    <div>
                      <div className="text-[10px] uppercase font-mono text-[#64748B] font-semibold">Starting At</div>
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
                      View Details
                    </Link>
                    <Link
                      href={`/checkout?product=${product.id}&plan=${defaultPlan?.id}`}
                      className="w-full py-2.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] hover:border-brand-blue text-[#0F172A] text-xs font-bold text-center block transition-colors"
                    >
                      Instant Buy
                    </Link>
                  </div>
                </div>

              </div>
            );
          })}
        </div>

      </main>

      <StoreFooter storeName={storeName} />
    </>
  );
}
