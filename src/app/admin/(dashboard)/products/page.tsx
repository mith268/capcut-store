import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { formatINR } from "@/lib/format";
import {
  Package,
  Plus,
  ExternalLink,
  Edit,
  Eye,
  CheckCircle2,
  XCircle,
  Tag,
  ShoppingBag,
} from "lucide-react";

export const dynamic = "force-dynamic";

export default async function AdminProductsPage() {
  const products = await prisma.product.findMany({
    where: { status: { not: "ARCHIVED" } },
    include: {
      category: true,
      plans: {
        where: { isActive: true },
        orderBy: { price: "asc" },
      },
      _count: {
        select: { orders: true, sections: true },
      },
    },
    orderBy: [{ isFeatured: "desc" }, { createdAt: "desc" }],
  });

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl sm:text-3xl font-extrabold text-white">Product Catalog</h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Create and edit digital products, plans, video demos, bonus offers, and landing pages.
          </p>
        </div>
        <Link
          href="/admin/products/new"
          className="px-4 py-2.5 rounded-xl bg-gradient-to-r from-[#00C8F4] to-[#1677FF] hover:from-[#38d4f7] hover:to-[#2582ff] text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-cyan-500/20 transition self-start sm:self-auto"
        >
          <Plus className="w-4 h-4" /> Create New Product
        </Link>
      </div>

      <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl shadow-xl overflow-hidden">
        {products.length === 0 ? (
          <div className="p-12 text-center">
            <Package className="w-12 h-12 text-slate-600 mx-auto mb-3" />
            <h3 className="text-sm font-bold text-white mb-1">No products found</h3>
            <p className="text-xs text-slate-400 mb-4">Click below to add your first digital product offer.</p>
            <Link
              href="/admin/products/new"
              className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-cyan-500 text-slate-950 font-bold text-xs"
            >
              <Plus className="w-4 h-4" /> Add Product
            </Link>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-[#070B12] text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                <tr>
                  <th className="py-3.5 px-4">Product</th>
                  <th className="py-3.5 px-4">Category</th>
                  <th className="py-3.5 px-4">Plans & Price</th>
                  <th className="py-3.5 px-4">Orders</th>
                  <th className="py-3.5 px-4">Status</th>
                  <th className="py-3.5 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-800/60 text-slate-300">
                {products.map((p) => {
                  const minPrice = p.plans[0]?.price || 0;
                  const maxPrice = p.plans[p.plans.length - 1]?.price || minPrice;

                  return (
                    <tr key={p.id} className="hover:bg-slate-900/40 transition">
                      <td className="py-3.5 px-4">
                        <div className="font-bold text-white text-sm flex items-center gap-2">
                          <span>{p.title}</span>
                          {p.isFeatured && (
                            <span className="px-1.5 py-0.5 rounded text-[9px] font-bold bg-cyan-950 text-[#00C8F4] border border-cyan-500/30">
                              FEATURED
                            </span>
                          )}
                          {p.bonusEnabled && (
                            <span className="px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-950 text-purple-300 border border-purple-500/30">
                              BONUS
                            </span>
                          )}
                        </div>
                        <div className="text-[11px] text-slate-500 font-mono mt-0.5">
                          /products/{p.slug}
                        </div>
                      </td>
                      <td className="py-3.5 px-4">
                        <span className="px-2 py-1 rounded-md bg-slate-800 text-slate-300 text-[11px] font-medium">
                          {p.category?.name || "Uncategorized"}
                        </span>
                      </td>
                      <td className="py-3.5 px-4">
                        <div className="font-semibold text-white">
                          {p.plans.length > 0 ? (
                            minPrice === maxPrice
                              ? formatINR(minPrice)
                              : `${formatINR(minPrice)} – ${formatINR(maxPrice)}`
                          ) : (
                            <span className="text-slate-500">No active plans</span>
                          )}
                        </div>
                        <div className="text-[11px] text-slate-400">
                          {p.plans.length} plan{p.plans.length !== 1 ? "s" : ""}
                        </div>
                      </td>
                      <td className="py-3.5 px-4">
                        <div className="flex items-center gap-1.5 font-semibold text-white">
                          <ShoppingBag className="w-3.5 h-3.5 text-slate-400" />
                          <span>{p._count.orders}</span>
                        </div>
                      </td>
                      <td className="py-3.5 px-4">
                        {p.status === "PUBLISHED" ? (
                          <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-950 text-emerald-300 border border-emerald-500/30">
                            <CheckCircle2 className="w-3 h-3 text-emerald-400" /> Live
                          </span>
                        ) : (
                          <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">
                            <XCircle className="w-3 h-3 text-slate-400" /> Draft
                          </span>
                        )}
                      </td>
                      <td className="py-3.5 px-4 text-right space-x-2">
                        <Link
                          href={`/products/${p.slug}`}
                          target="_blank"
                          className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] font-medium transition"
                        >
                          <Eye className="w-3 h-3" /> Live
                        </Link>
                        <Link
                          href={`/admin/products/${p.id}/edit`}
                          className="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-cyan-950/80 hover:bg-cyan-900 border border-cyan-500/40 text-[#00C8F4] text-[11px] font-bold transition"
                        >
                          <Edit className="w-3 h-3" /> Edit
                        </Link>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
