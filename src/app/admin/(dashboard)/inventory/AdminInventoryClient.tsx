"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import {
  Database,
  Plus,
  ShieldCheck,
  CheckCircle2,
  AlertTriangle,
  Lock,
  Loader2,
  Layers,
  X,
  Sparkles,
} from "lucide-react";

interface InventoryStat {
  planId: string;
  planName: string;
  productTitle: string;
  sku: string;
  deliveryMethod: string;
  available: number;
  assigned: number;
  retired: number;
  total: number;
}

interface RecentItem {
  id: string;
  planName: string;
  productTitle: string;
  status: string;
  identifier: string;
  credentialType: string;
  createdAt: string;
}

interface AdminInventoryClientProps {
  initialStats: InventoryStat[];
  initialRecent: RecentItem[];
  plansList: Array<{ id: string; name: string; productTitle: string }>;
}

export function AdminInventoryClient({
  initialStats,
  initialRecent,
  plansList,
}: AdminInventoryClientProps) {
  const router = useRouter();
  const [stats, setStats] = useState(initialStats);
  const [recent, setRecent] = useState(initialRecent);

  // Import Modal State
  const [showImportModal, setShowImportModal] = useState(false);
  const [selectedPlanId, setSelectedPlanId] = useState(plansList[0]?.id || "");
  const [credentialType, setCredentialType] = useState("CREDENTIALS");
  const [rawText, setRawText] = useState("");
  const [setupInstructions, setSetupInstructions] = useState("");
  const [importing, setImporting] = useState(false);
  const [importSuccess, setImportSuccess] = useState<string | null>(null);
  const [importError, setImportError] = useState<string | null>(null);

  async function handleImport(e: React.FormEvent) {
    e.preventDefault();
    setImportError(null);
    setImportSuccess(null);
    setImporting(true);

    try {
      const res = await fetch("/api/admin/inventory", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          planId: selectedPlanId,
          credentialType,
          rawCredentials: rawText,
          setupInstructions: setupInstructions.trim() || undefined,
        }),
      });

      const data = await res.json();
      if (!res.ok) {
        setImportError(data.error || "Failed to import credentials.");
      } else {
        setImportSuccess(data.message || "Credentials imported successfully!");
        setRawText("");
        setTimeout(() => {
          setShowImportModal(false);
          setImportSuccess(null);
          router.refresh();
        }, 1500);
      }
    } catch {
      setImportError("Network error occurred during import.");
    } finally {
      setImporting(false);
    }
  }

  const totalAvailable = stats.reduce((acc, s) => acc + s.available, 0);
  const totalAssigned = stats.reduce((acc, s) => acc + s.assigned, 0);

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl sm:text-3xl font-extrabold text-white">Encrypted Inventory Vault</h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Bulk upload and manage AES-256 encrypted digital credentials, activation keys, and direct invites.
          </p>
        </div>
        <button
          onClick={() => {
            setImportError(null);
            setImportSuccess(null);
            setShowImportModal(true);
          }}
          className="px-4 py-2.5 rounded-xl bg-gradient-to-r from-[#00C8F4] to-[#1677FF] hover:from-[#38d4f7] hover:to-[#2582ff] text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-cyan-500/20 transition self-start sm:self-auto"
        >
          <Plus className="w-4 h-4" /> Bulk Import Credentials
        </button>
      </div>

      {/* Summary Metrics */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl p-5">
          <span className="text-xs font-semibold uppercase text-slate-400 block mb-1">
            Total Ready in Vault
          </span>
          <div className="text-2xl sm:text-3xl font-black text-[#00C8F4]">
            {totalAvailable}
          </div>
          <p className="text-[11px] text-slate-400 mt-1">Ready for automated instant delivery</p>
        </div>
        <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl p-5">
          <span className="text-xs font-semibold uppercase text-slate-400 block mb-1">
            Total Delivered & Assigned
          </span>
          <div className="text-2xl sm:text-3xl font-black text-emerald-400">
            {totalAssigned}
          </div>
          <p className="text-[11px] text-slate-400 mt-1">Successfully bound to customer orders</p>
        </div>
        <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl p-5">
          <span className="text-xs font-semibold uppercase text-slate-400 block mb-1">
            Vault Encryption Standard
          </span>
          <div className="text-lg font-bold text-white flex items-center gap-1.5 mt-1">
            <Lock className="w-4 h-4 text-cyan-400" /> AES-256-GCM
          </div>
          <p className="text-[11px] text-slate-400 mt-1">Encrypted at rest with random 96-bit IVs</p>
        </div>
      </div>

      {/* Vault Inventory Breakdown by Plan */}
      <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl shadow-xl overflow-hidden">
        <div className="p-5 border-b border-slate-800">
          <h2 className="text-base font-bold text-white">Stock Level by Product & Plan</h2>
          <p className="text-xs text-slate-400">Automated inventory tracking for instant checkout dispatch.</p>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-[#070B12] text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
              <tr>
                <th className="py-3.5 px-4">Product Offer</th>
                <th className="py-3.5 px-4">Plan Name & SKU</th>
                <th className="py-3.5 px-4">Delivery Mode</th>
                <th className="py-3.5 px-4 text-center">Available</th>
                <th className="py-3.5 px-4 text-center">Assigned</th>
                <th className="py-3.5 px-4 text-center">Retired</th>
                <th className="py-3.5 px-4">Stock Status</th>
                <th className="py-3.5 px-4 text-right">Action</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-800/60 text-slate-300">
              {stats.map((item) => (
                <tr key={item.planId} className="hover:bg-slate-900/40 transition">
                  <td className="py-3.5 px-4 font-bold text-white">
                    {item.productTitle}
                  </td>
                  <td className="py-3.5 px-4">
                    <div className="font-semibold text-slate-200">{item.planName}</div>
                    <div className="text-[11px] text-cyan-400 font-mono">{item.sku}</div>
                  </td>
                  <td className="py-3.5 px-4 text-slate-400">
                    {item.deliveryMethod}
                  </td>
                  <td className="py-3.5 px-4 text-center font-bold text-base text-[#00C8F4]">
                    {item.available}
                  </td>
                  <td className="py-3.5 px-4 text-center font-medium text-emerald-400">
                    {item.assigned}
                  </td>
                  <td className="py-3.5 px-4 text-center font-medium text-slate-500">
                    {item.retired}
                  </td>
                  <td className="py-3.5 px-4">
                    {item.available >= 5 ? (
                      <span className="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-950 text-emerald-300 border border-emerald-500/30">
                        In Stock ({item.available})
                      </span>
                    ) : item.available > 0 ? (
                      <span className="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-950 text-amber-300 border border-amber-500/30">
                        Low Stock ({item.available})
                      </span>
                    ) : (
                      <span className="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-950 text-red-300 border border-red-500/30">
                        Empty Vault
                      </span>
                    )}
                  </td>
                  <td className="py-3.5 px-4 text-right">
                    <button
                      onClick={() => {
                        setSelectedPlanId(item.planId);
                        setShowImportModal(true);
                      }}
                      className="px-3 py-1 rounded-lg bg-cyan-950/80 hover:bg-cyan-900 border border-cyan-500/40 text-[#00C8F4] text-[11px] font-bold transition"
                    >
                      + Add Stock
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Bulk Import Modal */}
      {showImportModal && (
        <div className="fixed inset-0 z-50 bg-[#05090F]/80 backdrop-blur-md flex items-center justify-center p-4">
          <div className="bg-[#0C111B] border border-cyan-500/30 rounded-2xl max-w-xl w-full p-6 shadow-2xl relative">
            <button
              onClick={() => setShowImportModal(false)}
              className="absolute top-4 right-4 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition"
            >
              <X className="w-5 h-5" />
            </button>

            <div className="mb-5">
              <h3 className="text-xl font-bold text-white flex items-center gap-2">
                <Database className="w-5 h-5 text-cyan-400" />
                Bulk Encrypt & Import Credentials
              </h3>
              <p className="text-xs text-slate-400 mt-1">
                Paste credentials one per line. Each line will be securely encrypted with AES-256 before storage.
              </p>
            </div>

            <form onSubmit={handleImport} className="space-y-4">
              {importSuccess && (
                <div className="p-3 rounded-xl bg-emerald-950/70 border border-emerald-500/50 text-emerald-300 text-xs flex items-center gap-2">
                  <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
                  <span>{importSuccess}</span>
                </div>
              )}
              {importError && (
                <div className="p-3 rounded-xl bg-red-950/70 border border-red-500/50 text-red-300 text-xs flex items-center gap-2">
                  <AlertTriangle className="w-4 h-4 text-red-400 shrink-0" />
                  <span>{importError}</span>
                </div>
              )}

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    Select Plan
                  </label>
                  <select
                    value={selectedPlanId}
                    onChange={(e) => setSelectedPlanId(e.target.value)}
                    required
                    className="w-full bg-[#05090F] border border-slate-700 rounded-xl px-3 py-2 text-xs text-white"
                  >
                    {plansList.map((p) => (
                      <option key={p.id} value={p.id}>
                        {p.productTitle} — {p.name}
                      </option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-xs font-semibold text-slate-300 mb-1">
                    Format / Type
                  </label>
                  <select
                    value={credentialType}
                    onChange={(e) => setCredentialType(e.target.value)}
                    className="w-full bg-[#05090F] border border-slate-700 rounded-xl px-3 py-2 text-xs text-white"
                  >
                    <option value="CREDENTIALS">Account Logins (email:password)</option>
                    <option value="ACTIVATION_CODE">License / Activation Codes</option>
                    <option value="LINK">Invite / Activation URLs</option>
                    <option value="DOWNLOAD">Download URLs</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">
                  Raw Credentials (One Per Line)
                </label>
                <textarea
                  rows={6}
                  value={rawText}
                  onChange={(e) => setRawText(e.target.value)}
                  placeholder={
                    credentialType === "CREDENTIALS"
                      ? "pro_user1@gmail.com:SecretPass123\npro_user2@gmail.com:SecretPass456\npro_user3@gmail.com:SecretPass789"
                      : "CAP-PRO-2026-ABCD-1234\nCAP-PRO-2026-EFGH-5678"
                  }
                  required
                  className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl p-3 text-xs font-mono text-white outline-none"
                />
                <p className="text-[11px] text-slate-500 mt-1">
                  Format: <span className="font-mono text-cyan-300">username:password</span> or single activation keys.
                </p>
              </div>

              <div className="pt-2 flex items-center justify-end gap-3">
                <button
                  type="button"
                  onClick={() => setShowImportModal(false)}
                  className="px-4 py-2 rounded-xl text-xs text-slate-400 hover:text-white"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={importing || !rawText.trim()}
                  className="px-5 py-2 rounded-xl bg-gradient-to-r from-[#00C8F4] to-[#1677FF] text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-cyan-500/20 disabled:opacity-50"
                >
                  {importing ? <Loader2 className="w-4 h-4 animate-spin" /> : <ShieldCheck className="w-4 h-4" />}
                  Encrypt & Save to Vault
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
