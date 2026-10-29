import { notFound } from "next/navigation";
import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { formatINR, calculateSavingsPercentage } from "@/lib/format";
import { StoreHeader } from "@/components/store/StoreHeader";
import { StoreFooter } from "@/components/store/StoreFooter";

import { Category, Product, Plan } from "@prisma/client";

export const dynamic = "force-dynamic";

type CategoryWithProducts = Category & {
  products: (Product & {
    plans: Plan[];
  })[];
};

interface Props {
  params: { slug: string };
}

export default async function CategoryPage({ params }: Props) {
  let category: CategoryWithProducts | null = null;
  let storeName = "Convora Store";

  try {
    category = await prisma.category.findUnique({
      where: { slug: params.slug },
      include: {
        products: {
          where: { status: "PUBLISHED", visibility: "PUBLIC" },
          include: {
            plans: {
              where: { isActive: true },
              orderBy: { price: "asc" },
            },
          },
        },
      },
    });

    const settings = await prisma.storeSetting.findMany();
    const settingsMap = new Map(settings.map((s) => [s.key, s.value]));
    if (settingsMap.get("store_name")) storeName = settingsMap.get("store_name")!;
  } catch {
    // Graceful fallback during build
  }

  if (!category) {
    notFound();
  }

  return (
    <>
      <StoreHeader storeName={storeName} />

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8 bg-[#F8FAFC]">
        
        {/* Category Header */}
        <div className="space-y-2 border-b border-[#E2E8F0] pb-6">
          <div className="text-xs font-mono uppercase text-brand-blue font-bold tracking-wider">
            Category
          </div>
          <h1 className="text-3xl sm:text-4xl font-black text-[#0F172A] tracking-tight">
            {category.name}
          </h1>
          {category.description && (
            <p className="text-xs sm:text-sm text-[#64748B] max-w-2xl leading-relaxed">
              {category.description}
            </p>
          )}
        </div>

        {/* Product Cards Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
          {category.products.map((product: any) => {
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
                  <h2 className="text-xl font-black text-[#0F172A] group-hover:text-brand-blue transition-colors">
                    <Link href={`/products/${product.slug}`}>
                      {product.title}
                    </Link>
                  </h2>
                  <p className="text-xs text-[#64748B] line-clamp-2 leading-relaxed">
                    {product.shortDescription || product.subtitle}
                  </p>
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
