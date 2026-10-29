"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { ArrowLeft, Package, Plus, Loader2, Sparkles } from "lucide-react";

export default function AdminNewProductPage() {
  const router = useRouter();
  const [title, setTitle] = useState("");
  const [slug, setSlug] = useState("");
  const [priceINR, setPriceINR] = useState("299");
  const [duration, setDuration] = useState("1 Month");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  function handleTitleChange(val: string) {
    setTitle(val);
    if (!slug || slug === title.toLowerCase().replace(/[^a-z0-9]/g, "-")) {
      setSlug(val.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, ""));
    }
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError(null);
    setLoading(true);

    try {
      const res = await fetch("/api/admin/products", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          title,
          slug,
          priceINR: Number(priceINR),
          duration,
        }),
      });

      const data = await res.json();
      if (!res.ok) {
        setError(data.error || "Failed to create product.");
      } else {
        router.push(`/admin/products/${data.product.id}/edit`);
      }
    } catch {
      setError("An unexpected error occurred while creating the product.");
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="max-w-2xl mx-auto space-y-6">
      <div className="flex items-center gap-3">
        <Link
          href="/admin/products"
          className="p-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-white transition"
        >
          <ArrowLeft className="w-4 h-4" />
        </Link>
        <div>
          <h1 className="text-2xl font-bold text-white">Create New Product Offer</h1>
          <p className="text-xs text-slate-400">
            Set up the basic product information. You can customize the landing page sections and add more plans in the next step.
          </p>
        </div>
      </div>

      <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl p-6 sm:p-8 shadow-xl">
        <form onSubmit={handleSubmit} className="space-y-5">
          {error && (
            <div className="p-3.5 rounded-xl bg-red-950/50 border border-red-500/40 text-red-300 text-xs">
              {error}
            </div>
          )}

          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
              Product Title
            </label>
            <input
              type="text"
              placeholder="e.g. CapCut Pro Premium Account"
              value={title}
              onChange={(e) => handleTitleChange(e.target.value)}
              required
              className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 outline-none"
            />
          </div>

          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
              Landing Page URL Slug
            </label>
            <div className="flex items-center">
              <span className="bg-[#070B12] border border-r-0 border-slate-700 rounded-l-xl px-3 py-2.5 text-xs text-slate-400 font-mono">
                /products/
              </span>
              <input
                type="text"
                placeholder="capcut-pro"
                value={slug}
                onChange={(e) => setSlug(e.target.value.toLowerCase().replace(/[^a-z0-9-]/g, "-"))}
                required
                className="flex-1 bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-r-xl px-4 py-2.5 text-sm text-cyan-300 font-mono outline-none"
              />
            </div>
            <p className="text-[11px] text-slate-500 mt-1">
              Direct landing page URL for Meta ads and external traffic.
            </p>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                Starter Plan Price (₹ INR)
              </label>
              <input
                type="number"
                min="1"
                value={priceINR}
                onChange={(e) => setPriceINR(e.target.value)}
                required
                className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none"
              />
            </div>
            <div>
              <label className="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                Plan Duration
              </label>
              <input
                type="text"
                placeholder="e.g. 1 Month, 1 Year, Lifetime"
                value={duration}
                onChange={(e) => setDuration(e.target.value)}
                required
                className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none"
              />
            </div>
          </div>

          <div className="pt-4 border-t border-slate-800 flex items-center justify-end gap-3">
            <Link
              href="/admin/products"
              className="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white"
            >
              Cancel
            </Link>
            <button
              type="submit"
              disabled={loading}
              className="py-2.5 px-6 rounded-xl font-bold text-xs bg-gradient-to-r from-[#00C8F4] to-[#1677FF] text-white flex items-center gap-2 shadow-lg shadow-cyan-500/20 disabled:opacity-50"
            >
              {loading ? <Loader2 className="w-4 h-4 animate-spin" /> : <Plus className="w-4 h-4" />}
              Create & Open Editor
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
