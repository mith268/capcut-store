import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { formatINR } from "@/lib/format";
import {
  DollarSign,
  ShoppingBag,
  Package,
  Database,
  AlertTriangle,
  ArrowRight,
  Plus,
  Clock,
  CheckCircle2,
  TrendingUp,
  ExternalLink,
} from "lucide-react";

export const dynamic = "force-dynamic";

export default async function AdminOverviewPage() {
  // Aggregate real database stats
  const totalOrdersCount = await prisma.order.count();
  const paidOrdersCount = await prisma.order.count({
    where: { paymentStatus: "PAID" },
  });
  const pendingFulfillmentCount = await prisma.order.count({
    where: {
      paymentStatus: "PAID",
      fulfillmentStatus: { in: ["PENDING", "MANUAL_PENDING"] },
    },
  });

  const paidOrders = await prisma.order.findMany({
    where: { paymentStatus: "PAID" },
    select: { totalAmount: true },
  });
  const totalRevenuePaise = paidOrders.reduce((sum, o) => sum + o.totalAmount, 0);

  const totalProductsCount = await prisma.product.count({
    where: { status: { not: "ARCHIVED" } },
  });

  const availableInventoryCount = await prisma.credentialInventory.count({
    where: { status: "AVAILABLE" },
  });

  // Recent 10 orders
  const recentOrders = await prisma.order.findMany({
    orderBy: { createdAt: "desc" },
    take: 10,
    include: { product: true, plan: true },
  });

  // Low stock inventory plans (less than 3 credentials available)
  const plans = await prisma.plan.findMany({
    where: { isActive: true },
    include: {
      product: { select: { title: true } },
    },
  });

  const plansWithStock = await Promise.all(
    plans.map(async (p) => {
      const stock = await prisma.credentialInventory.count({
        where: { planId: p.id, status: "AVAILABLE" },
      });
      return { ...p, stock };
    })
  );

  const lowStockPlans = plansWithStock.filter((p) => p.stock < 3);

  return (
    <div className="space-y-8">
      {/* Top Banner */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl sm:text-3xl font-extrabold text-white">Operations Dashboard</h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Real-time telemetry, revenue figures, order flow, and inventory provisioning.
          </p>
        </div>
        <div className="flex items-center gap-3">
          <Link
            href="/admin/products/new"
            className="px-4 py-2 rounded-xl bg-gradient-to-r from-[#00C8F4] to-[#1677FF] hover:from-[#38d4f7] hover:to-[#2582ff] text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-cyan-500/20 transition"
          >
            <Plus className="w-4 h-4" /> Create Product
          </Link>
          <Link
            href="/admin/inventory"
            className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs flex items-center gap-2 transition border border-slate-700"
          >
            <Database className="w-4 h-4 text-cyan-400" /> Vault Inventory
          </Link>
        </div>
      </div>

      {/* Metrics Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        {/* Total Revenue */}
        <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl p-5 shadow-lg relative overflow-hidden">
          <div className="flex items-center justify-between mb-3">
            <span className="text-xs font-semibold uppercase text-slate-400">Total Revenue</span>
            <div className="w-9 h-9 rounded-xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-[#00C8F4]">
              <DollarSign className="w-5 h-5" />
            </div>
          </div>
          <div className="text-2xl sm:text-3xl font-black text-white">
            {formatINR(totalRevenuePaise)}
          </div>
          <p className="text-[11px] text-emerald-400 mt-2 flex items-center gap-1">
            <TrendingUp className="w-3.5 h-3.5" /> From {paidOrdersCount} verified transactions
          </p>
        </div>

        {/* Total Orders */}
        <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl p-5 shadow-lg relative overflow-hidden">
          <div className="flex items-center justify-between mb-3">
            <span className="text-xs font-semibold uppercase text-slate-400">Total Orders</span>
            <div className="w-9 h-9 rounded-xl bg-blue-500/10 border border-blue-500/30 flex items-center justify-center text-blue-400">
              <ShoppingBag className="w-5 h-5" />
            </div>
          </div>
          <div className="text-2xl sm:text-3xl font-black text-white">
            {totalOrdersCount}
          </div>
          <p className="text-[11px] text-slate-400 mt-2">
            {paidOrdersCount} Paid • {totalOrdersCount - paidOrdersCount} Pending / Failed
          </p>
        </div>

        {/* Pending Fulfillment */}
        <div className="bg-[#0C111B] border border-amber-500/30 rounded-2xl p-5 shadow-lg relative overflow-hidden">
          <div className="flex items-center justify-between mb-3">
            <span className="text-xs font-semibold uppercase text-amber-300">Action Required</span>
            <div className="w-9 h-9 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400">
              <Clock className="w-5 h-5" />
            </div>
          </div>
          <div className="text-2xl sm:text-3xl font-black text-amber-400">
            {pendingFulfillmentCount}
          </div>
          <p className="text-[11px] text-slate-400 mt-2">
            Orders pending manual setup or dispatch
          </p>
        </div>

        {/* Vault Inventory */}
        <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl p-5 shadow-lg relative overflow-hidden">
          <div className="flex items-center justify-between mb-3">
            <span className="text-xs font-semibold uppercase text-slate-400">In-Stock Vault</span>
            <div className="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
              <Database className="w-5 h-5" />
            </div>
          </div>
          <div className="text-2xl sm:text-3xl font-black text-white">
            {availableInventoryCount}
          </div>
          <p className="text-[11px] text-slate-400 mt-2">
            Encrypted credentials ready for instant dispatch
          </p>
        </div>
      </div>

      {/* Low Stock Alerts (if any) */}
      {lowStockPlans.length > 0 && (
        <div className="bg-amber-950/30 border border-amber-500/40 rounded-2xl p-5">
          <div className="flex items-center gap-2 text-amber-300 font-bold text-sm mb-3">
            <AlertTriangle className="w-4 h-4 text-amber-400 shrink-0" />
            <span>Low Stock Warning — Restock Recommended</span>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            {lowStockPlans.map((lp) => (
              <div
                key={lp.id}
                className="bg-[#0A0E17] border border-amber-500/20 rounded-xl p-3 flex items-center justify-between"
              >
                <div>
                  <div className="text-xs font-bold text-white">{lp.product.title}</div>
                  <div className="text-[11px] text-slate-400">{lp.name}</div>
                </div>
                <div className="text-right">
                  <span className={`text-xs font-bold px-2 py-0.5 rounded ${lp.stock === 0 ? "bg-red-950 text-red-300 border border-red-500/40" : "bg-amber-950 text-amber-300 border border-amber-500/40"}`}>
                    {lp.stock} left
                  </span>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Recent Orders Section */}
      <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl shadow-xl overflow-hidden">
        <div className="p-5 border-b border-slate-800 flex items-center justify-between">
          <div>
            <h2 className="text-base font-bold text-white">Recent Customer Orders</h2>
            <p className="text-xs text-slate-400">Live order stream across all products and marketing channels.</p>
          </div>
          <Link
            href="/admin/orders"
            className="text-xs font-semibold text-[#00C8F4] hover:text-[#38d4f7] flex items-center gap-1"
          >
            View All Orders <ArrowRight className="w-3.5 h-3.5" />
          </Link>
        </div>

        {recentOrders.length === 0 ? (
          <div className="p-8 text-center text-slate-400 text-xs">
            No customer orders recorded yet. Initiate a test checkout to see live records here.
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-[#070B12] text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                <tr>
                  <th className="py-3 px-4">Order #</th>
                  <th className="py-3 px-4">Customer</th>
                  <th className="py-3 px-4">Product & Plan</th>
                  <th className="py-3 px-4">Amount</th>
                  <th className="py-3 px-4">Payment</th>
                  <th className="py-3 px-4">Delivery</th>
                  <th className="py-3 px-4">Date</th>
                  <th className="py-3 px-4 text-right">Action</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-800/60 text-slate-300">
                {recentOrders.map((ord) => (
                  <tr key={ord.id} className="hover:bg-slate-900/50 transition">
                    <td className="py-3 px-4 font-mono font-bold text-cyan-300">
                      {ord.orderNumber}
                    </td>
                    <td className="py-3 px-4">
                      <div className="font-semibold text-white">{ord.customerName}</div>
                      <div className="text-[11px] text-slate-400 truncate max-w-[150px]">{ord.customerEmail}</div>
                    </td>
                    <td className="py-3 px-4">
                      <div className="font-medium text-white">{ord.product.title}</div>
                      <div className="text-[11px] text-slate-400">{ord.plan.name}</div>
                    </td>
                    <td className="py-3 px-4 font-bold text-white">
                      {formatINR(ord.totalAmount)}
                    </td>
                    <td className="py-3 px-4">
                      <span
                        className={`inline-block px-2 py-0.5 rounded text-[10px] font-bold ${
                          ord.paymentStatus === "PAID"
                            ? "bg-emerald-950 text-emerald-300 border border-emerald-500/30"
                            : "bg-amber-950 text-amber-300 border border-amber-500/30"
                        }`}
                      >
                        {ord.paymentStatus}
                      </span>
                    </td>
                    <td className="py-3 px-4">
                      <span
                        className={`inline-block px-2 py-0.5 rounded text-[10px] font-bold ${
                          ord.fulfillmentStatus === "DELIVERED"
                            ? "bg-cyan-950 text-[#00C8F4] border border-cyan-500/30"
                            : ord.fulfillmentStatus === "MANUAL_PENDING"
                            ? "bg-purple-950 text-purple-300 border border-purple-500/30"
                            : "bg-slate-800 text-slate-400"
                        }`}
                      >
                        {ord.fulfillmentStatus}
                      </span>
                    </td>
                    <td className="py-3 px-4 text-slate-400 whitespace-nowrap">
                      {new Date(ord.createdAt).toLocaleDateString("en-IN", {
                        day: "numeric",
                        month: "short",
                        hour: "2-digit",
                        minute: "2-digit",
                      })}
                    </td>
                    <td className="py-3 px-4 text-right">
                      <Link
                        href={`/orders/${ord.publicId}`}
                        target="_blank"
                        className="inline-flex items-center gap-1 text-[11px] text-cyan-400 hover:text-cyan-300 font-semibold"
                      >
                        View <ExternalLink className="w-3 h-3" />
                      </Link>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
