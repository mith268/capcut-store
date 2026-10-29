"use client";

import { useState } from "react";
import Link from "next/link";
import { formatINR } from "@/lib/format";
import {
  ShoppingBag,
  Search,
  Filter,
  CheckCircle2,
  Clock,
  AlertTriangle,
  RefreshCw,
  ExternalLink,
  ShieldCheck,
  Eye,
  X,
  Copy,
  Check,
  MessageCircle,
  Gift,
  Loader2,
} from "lucide-react";

interface AdminOrdersClientProps {
  initialOrders: any[];
}

export function AdminOrdersClient({ initialOrders }: AdminOrdersClientProps) {
  const [orders, setOrders] = useState(initialOrders);
  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState("ALL");
  const [selectedOrderId, setSelectedOrderId] = useState<string | null>(null);
  const [orderDetails, setOrderDetails] = useState<any | null>(null);
  const [loadingDetails, setLoadingDetails] = useState(false);

  // Replace Credential Modal
  const [replacing, setReplacing] = useState(false);
  const [replaceReason, setReplaceReason] = useState("");
  const [replaceLoading, setReplaceLoading] = useState(false);
  const [replaceMsg, setReplaceMsg] = useState<{ type: "success" | "error"; text: string } | null>(null);

  // Bonus Status update
  const [updatingBonus, setUpdatingBonus] = useState(false);

  // Filtered orders
  const filteredOrders = orders.filter((o) => {
    const matchesSearch =
      o.orderNumber.toLowerCase().includes(search.toLowerCase()) ||
      o.customerName.toLowerCase().includes(search.toLowerCase()) ||
      o.customerEmail.toLowerCase().includes(search.toLowerCase()) ||
      (o.customerPhone && o.customerPhone.includes(search));

    if (!matchesSearch) return false;

    if (statusFilter === "ALL") return true;
    if (statusFilter === "PAID") return o.paymentStatus === "PAID";
    if (statusFilter === "PENDING_SETUP")
      return o.paymentStatus === "PAID" && (o.fulfillmentStatus === "MANUAL_PENDING" || o.fulfillmentStatus === "PENDING");
    if (statusFilter === "DELIVERED") return o.fulfillmentStatus === "DELIVERED";
    if (statusFilter === "BONUS_PENDING") return o.bonusStatus === "PROCESSING" || o.bonusStatus === "PENDING_INPUT";

    return true;
  });

  async function openOrderModal(orderId: string) {
    setSelectedOrderId(orderId);
    setLoadingDetails(true);
    setReplaceMsg(null);
    setReplacing(false);

    try {
      const res = await fetch(`/api/admin/orders/${orderId}`);
      const data = await res.json();
      if (res.ok) {
        setOrderDetails(data.order);
      }
    } catch (e) {
      console.error("Error fetching order details:", e);
    } finally {
      setLoadingDetails(false);
    }
  }

  async function handleReplaceCredential() {
    if (!selectedOrderId || !replaceReason.trim()) return;
    setReplaceLoading(true);
    setReplaceMsg(null);

    try {
      const res = await fetch(`/api/admin/orders/${selectedOrderId}/replace-credential`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ reason: replaceReason.trim() }),
      });

      const data = await res.json();
      if (!res.ok) {
        setReplaceMsg({ type: "error", text: data.error || "Failed to replace credentials." });
      } else {
        setReplaceMsg({ type: "success", text: "Fresh credential assigned and logged in audit trail!" });
        setReplacing(false);
        setReplaceReason("");
        // Reload details
        openOrderModal(selectedOrderId);
      }
    } catch {
      setReplaceMsg({ type: "error", text: "Network error occurred." });
    } finally {
      setReplaceLoading(false);
    }
  }

  async function handleMarkBonusDelivered() {
    if (!selectedOrderId) return;
    setUpdatingBonus(true);
    try {
      const res = await fetch(`/api/admin/orders/${selectedOrderId}`, {
        method: "PATCH",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ bonusStatus: "DELIVERED" }),
      });
      if (res.ok) {
        openOrderModal(selectedOrderId);
      }
    } catch (e) {
      console.error("Error updating bonus:", e);
    } finally {
      setUpdatingBonus(false);
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl sm:text-3xl font-extrabold text-white">Orders & Fulfillment</h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Track customer purchases, inspect encrypted credentials, handle replacements, and fulfill manual orders.
          </p>
        </div>
      </div>

      {/* Search & Filters */}
      <div className="flex flex-col sm:flex-row gap-3">
        <div className="relative flex-1">
          <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
            <Search className="w-4 h-4" />
          </div>
          <input
            type="text"
            placeholder="Search by Order #, Customer Name, Email, or Phone..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full bg-[#0C111B] border border-slate-800 focus:border-cyan-400 rounded-xl pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 outline-none"
          />
        </div>

        <div className="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
          {[
            { id: "ALL", label: "All Orders" },
            { id: "PAID", label: "Paid" },
            { id: "PENDING_SETUP", label: "Pending Setup" },
            { id: "DELIVERED", label: "Delivered" },
            { id: "BONUS_PENDING", label: "Bonus Pending" },
          ].map((tab) => (
            <button
              key={tab.id}
              onClick={() => setStatusFilter(tab.id)}
              className={`px-3 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition ${
                statusFilter === tab.id
                  ? "bg-cyan-500/20 text-[#00C8F4] border border-cyan-500/30"
                  : "bg-[#0C111B] text-slate-400 hover:text-white border border-slate-800"
              }`}
            >
              {tab.label}
            </button>
          ))}
        </div>
      </div>

      {/* Orders Table */}
      <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl shadow-xl overflow-hidden">
        {filteredOrders.length === 0 ? (
          <div className="p-12 text-center text-slate-400 text-xs">
            No matching orders found.
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-[#070B12] text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                <tr>
                  <th className="py-3.5 px-4">Order #</th>
                  <th className="py-3.5 px-4">Customer</th>
                  <th className="py-3.5 px-4">Product & Plan</th>
                  <th className="py-3.5 px-4">Amount</th>
                  <th className="py-3.5 px-4">Payment</th>
                  <th className="py-3.5 px-4">Delivery</th>
                  <th className="py-3.5 px-4">Bonus</th>
                  <th className="py-3.5 px-4">Date</th>
                  <th className="py-3.5 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-800/60 text-slate-300">
                {filteredOrders.map((ord) => (
                  <tr key={ord.id} className="hover:bg-slate-900/40 transition">
                    <td className="py-3.5 px-4 font-mono font-bold text-cyan-300">
                      {ord.orderNumber}
                    </td>
                    <td className="py-3.5 px-4">
                      <div className="font-semibold text-white">{ord.customerName}</div>
                      <div className="text-[11px] text-slate-400 truncate max-w-[140px]">{ord.customerEmail}</div>
                    </td>
                    <td className="py-3.5 px-4">
                      <div className="font-medium text-white">{ord.product.title}</div>
                      <div className="text-[11px] text-slate-400">{ord.plan.name}</div>
                    </td>
                    <td className="py-3.5 px-4 font-bold text-white">
                      {formatINR(ord.totalAmount)}
                    </td>
                    <td className="py-3.5 px-4">
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
                    <td className="py-3.5 px-4">
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
                    <td className="py-3.5 px-4">
                      {ord.bonusStatus !== "NOT_APPLICABLE" ? (
                        <span
                          className={`inline-block px-2 py-0.5 rounded text-[10px] font-bold ${
                            ord.bonusStatus === "DELIVERED"
                              ? "bg-emerald-950 text-emerald-300"
                              : ord.bonusStatus === "PROCESSING"
                              ? "bg-purple-950 text-purple-300 border border-purple-500/30"
                              : "bg-slate-800 text-slate-400"
                          }`}
                        >
                          {ord.bonusStatus}
                        </span>
                      ) : (
                        <span className="text-slate-600">—</span>
                      )}
                    </td>
                    <td className="py-3.5 px-4 text-slate-400 whitespace-nowrap">
                      {new Date(ord.createdAt).toLocaleDateString("en-IN", {
                        day: "numeric",
                        month: "short",
                        hour: "2-digit",
                        minute: "2-digit",
                      })}
                    </td>
                    <td className="py-3.5 px-4 text-right space-x-2">
                      <button
                        onClick={() => openOrderModal(ord.id)}
                        className="px-3 py-1 rounded-lg bg-cyan-950/80 hover:bg-cyan-900 border border-cyan-500/40 text-[#00C8F4] text-[11px] font-bold transition"
                      >
                        Inspect & Fulfill
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Order Details & Fulfillment Modal */}
      {selectedOrderId && (
        <div className="fixed inset-0 z-50 bg-[#05090F]/80 backdrop-blur-md flex items-center justify-center p-4">
          <div className="bg-[#0C111B] border border-cyan-500/30 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 shadow-2xl relative">
            <button
              onClick={() => setSelectedOrderId(null)}
              className="absolute top-4 right-4 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition"
            >
              <X className="w-5 h-5" />
            </button>

            {loadingDetails || !orderDetails ? (
              <div className="py-12 flex flex-col items-center justify-center text-slate-400 text-xs">
                <Loader2 className="w-6 h-6 animate-spin mb-2 text-cyan-400" />
                Loading order telemetry & decrypting credentials...
              </div>
            ) : (
              <div className="space-y-6">
                {/* Header */}
                <div>
                  <div className="flex items-center gap-2 mb-1">
                    <span className="text-xs uppercase text-slate-400 font-mono">Order Details</span>
                    <span className="px-2 py-0.5 rounded text-[11px] font-bold font-mono bg-cyan-950 text-cyan-300 border border-cyan-500/30">
                      {orderDetails.orderNumber}
                    </span>
                  </div>
                  <h2 className="text-xl font-bold text-white">{orderDetails.product.title}</h2>
                  <p className="text-xs text-slate-400">{orderDetails.plan.name} • {formatINR(orderDetails.totalAmount)}</p>
                </div>

                {replaceMsg && (
                  <div
                    className={`p-3 rounded-xl text-xs flex items-center gap-2 ${
                      replaceMsg.type === "success"
                        ? "bg-emerald-950 border border-emerald-500/40 text-emerald-300"
                        : "bg-red-950 border border-red-500/40 text-red-300"
                    }`}
                  >
                    <span>{replaceMsg.text}</span>
                  </div>
                )}

                {/* Customer Information */}
                <div className="bg-[#070B12] border border-slate-800 rounded-xl p-4 grid grid-cols-2 gap-3 text-xs">
                  <div>
                    <span className="text-slate-500 block mb-0.5">Customer Name</span>
                    <span className="text-white font-semibold">{orderDetails.customerName}</span>
                  </div>
                  <div>
                    <span className="text-slate-500 block mb-0.5">Email</span>
                    <span className="text-white font-mono">{orderDetails.customerEmail}</span>
                  </div>
                  <div>
                    <span className="text-slate-500 block mb-0.5">Phone / WhatsApp</span>
                    <span className="text-white font-mono">{orderDetails.customerPhone || "Not provided"}</span>
                  </div>
                  <div>
                    <span className="text-slate-500 block mb-0.5">Razorpay Payment ID</span>
                    <span className="text-cyan-300 font-mono text-[11px] truncate block">
                      {orderDetails.razorpayPaymentId || "None"}
                    </span>
                  </div>
                </div>

                {/* Delivered Credentials Box */}
                <div className="border border-slate-800 rounded-xl p-4 space-y-3">
                  <div className="flex items-center justify-between">
                    <span className="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-1.5">
                      <ShieldCheck className="w-4 h-4 text-cyan-400" /> Active Delivered Credentials
                    </span>
                    <span className="text-[11px] text-slate-400 font-mono">
                      Fulfillment: {orderDetails.fulfillmentStatus}
                    </span>
                  </div>

                  {orderDetails.fulfillments && orderDetails.fulfillments.length > 0 ? (
                    <div className="space-y-2">
                      {orderDetails.fulfillments.map((f: any, idx: number) => (
                        <div
                          key={f.id}
                          className={`p-3 rounded-lg text-xs font-mono ${
                            idx === 0 ? "bg-cyan-950/30 border border-cyan-500/30" : "bg-slate-900 border border-slate-800 opacity-60"
                          }`}
                        >
                          <div className="flex items-center justify-between mb-1 text-[10px] text-slate-400">
                            <span>{idx === 0 ? "CURRENT ACTIVE" : `PREVIOUS (${f.replacedReason || "Replaced"})`}</span>
                            <span>{new Date(f.createdAt).toLocaleString()}</span>
                          </div>
                          {f.credentials?.emailOrUsername && (
                            <div>Login: <span className="text-white font-bold">{f.credentials.emailOrUsername}</span></div>
                          )}
                          {f.credentials?.password && (
                            <div>Pass: <span className="text-white font-bold">{f.credentials.password}</span></div>
                          )}
                          {f.credentials?.activationCode && (
                            <div>Key: <span className="text-white font-bold">{f.credentials.activationCode}</span></div>
                          )}
                          {f.credentials?.activationLink && (
                            <div className="truncate">URL: <span className="text-cyan-400">{f.credentials.activationLink}</span></div>
                          )}
                        </div>
                      ))}
                    </div>
                  ) : (
                    <div className="text-xs text-amber-300 p-3 rounded-lg bg-amber-950/40 border border-amber-500/30">
                      No automated inventory credential has been assigned to this order yet.
                    </div>
                  )}

                  {/* Replace Credential Action */}
                  {!replacing ? (
                    <div className="pt-2 flex items-center gap-2">
                      <button
                        type="button"
                        onClick={() => setReplacing(true)}
                        className="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300 flex items-center gap-1.5 transition"
                      >
                        <RefreshCw className="w-3.5 h-3.5 text-cyan-400" />
                        Replace with Fresh Inventory Credential
                      </button>
                    </div>
                  ) : (
                    <div className="p-3 rounded-lg bg-[#070B12] border border-cyan-500/30 space-y-3">
                      <span className="text-xs font-bold text-white block">
                        Confirm Credential Replacement
                      </span>
                      <p className="text-[11px] text-slate-400">
                        This will automatically retire the current credential and assign a fresh one from this plan's inventory vault.
                      </p>
                      <input
                        type="text"
                        placeholder="Reason for replacement (e.g. Customer reported password issue)..."
                        value={replaceReason}
                        onChange={(e) => setReplaceReason(e.target.value)}
                        className="w-full bg-[#05090F] border border-slate-700 rounded-lg px-3 py-2 text-xs text-white outline-none"
                      />
                      <div className="flex items-center gap-2">
                        <button
                          type="button"
                          onClick={() => setReplacing(false)}
                          className="px-3 py-1.5 rounded-lg text-xs text-slate-400 hover:text-white"
                        >
                          Cancel
                        </button>
                        <button
                          type="button"
                          disabled={replaceLoading || !replaceReason.trim()}
                          onClick={handleReplaceCredential}
                          className="px-4 py-1.5 rounded-lg bg-cyan-500 text-slate-950 font-bold text-xs flex items-center gap-1.5 disabled:opacity-50"
                        >
                          {replaceLoading ? <Loader2 className="w-3.5 h-3.5 animate-spin" /> : <Check className="w-3.5 h-3.5" />}
                          Confirm Replacement
                        </button>
                      </div>
                    </div>
                  )}
                </div>

                {/* Bonus Section (if applicable) */}
                {orderDetails.bonusStatus !== "NOT_APPLICABLE" && (
                  <div className="bg-purple-950/20 border border-purple-500/30 rounded-xl p-4 space-y-2">
                    <div className="flex items-center justify-between">
                      <span className="text-xs font-bold text-purple-300 uppercase tracking-wider flex items-center gap-1.5">
                        <Gift className="w-4 h-4 text-purple-400" /> Canva Free Bonus Offer
                      </span>
                      <span className="text-[10px] font-bold px-2 py-0.5 rounded bg-purple-950 text-purple-300">
                        {orderDetails.bonusStatus}
                      </span>
                    </div>
                    <div className="text-xs text-slate-300">
                      Customer Submitted Email:{" "}
                      <span className="text-white font-mono font-bold">
                        {orderDetails.bonusCustomerInput || "Awaiting customer input"}
                      </span>
                    </div>
                    {orderDetails.bonusStatus === "PROCESSING" && (
                      <button
                        type="button"
                        disabled={updatingBonus}
                        onClick={handleMarkBonusDelivered}
                        className="mt-2 px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs flex items-center gap-1.5 transition"
                      >
                        {updatingBonus ? <Loader2 className="w-3.5 h-3.5 animate-spin" /> : <Check className="w-3.5 h-3.5" />}
                        Mark Bonus as Delivered
                      </button>
                    )}
                  </div>
                )}

                {/* Footer links */}
                <div className="pt-2 flex items-center justify-between border-t border-slate-800 text-xs">
                  <Link
                    href={`/orders/${orderDetails.publicId}`}
                    target="_blank"
                    className="text-cyan-400 hover:text-cyan-300 font-semibold flex items-center gap-1"
                  >
                    Open Customer Order Page <ExternalLink className="w-3.5 h-3.5" />
                  </Link>
                  <button
                    onClick={() => setSelectedOrderId(null)}
                    className="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white"
                  >
                    Close
                  </button>
                </div>
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
