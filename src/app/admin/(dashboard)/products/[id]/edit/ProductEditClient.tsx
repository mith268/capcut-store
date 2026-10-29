"use client";

import { useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { formatINR } from "@/lib/format";
import {
  ArrowLeft,
  Save,
  Eye,
  Plus,
  Trash2,
  CheckCircle2,
  AlertCircle,
  Loader2,
  Package,
  Layers,
  Video,
  Gift,
  Activity,
  Sliders,
  DollarSign,
  ExternalLink,
} from "lucide-react";

interface PlanItem {
  id: string;
  name: string;
  sku: string;
  duration: string;
  priceINR: number;
  compareAtPriceINR?: number;
  deliveryMethod: string;
  stockPolicy: string;
  availableQuantity: number;
  isActive: boolean;
  isDefault: boolean;
  isRecommended: boolean;
  displayOrder: number;
  benefits: string;
}

interface ProductEditClientProps {
  initialProduct: any;
  categories: Array<{ id: string; name: string }>;
}

export function ProductEditClient({ initialProduct, categories }: ProductEditClientProps) {
  const router = useRouter();
  const [activeTab, setActiveTab] = useState<"general" | "hero" | "plans" | "bonus" | "tracking">("general");

  // Form State
  const [title, setTitle] = useState(initialProduct.title || "");
  const [slug, setSlug] = useState(initialProduct.slug || "");
  const [tagline, setTagline] = useState(initialProduct.tagline || "");
  const [categoryId, setCategoryId] = useState(initialProduct.categoryId || "");
  const [isPublished, setIsPublished] = useState(initialProduct.isPublished);
  const [isFeatured, setIsFeatured] = useState(initialProduct.isFeatured);

  // Hero & Video
  const [heroTitle, setHeroTitle] = useState(initialProduct.heroTitle || "");
  const [heroSubtitle, setHeroSubtitle] = useState(initialProduct.heroSubtitle || "");
  const [heroBadge, setHeroBadge] = useState(initialProduct.heroBadge || "");
  const [videoUrl, setVideoUrl] = useState(initialProduct.videoUrl || "");
  const [videoPoster, setVideoPoster] = useState(initialProduct.videoPoster || "");

  // Bonus
  const [bonusEnabled, setBonusEnabled] = useState(initialProduct.bonusEnabled);
  const [bonusTitle, setBonusTitle] = useState(initialProduct.bonusTitle || "");
  const [bonusBadge, setBonusBadge] = useState(initialProduct.bonusBadge || "FREE BONUS");
  const [bonusSubtitle, setBonusSubtitle] = useState(initialProduct.bonusSubtitle || "");
  const [bonusDescription, setBonusDescription] = useState(initialProduct.bonusDescription || "");
  const [bonusExplanation, setBonusExplanation] = useState(initialProduct.bonusExplanation || "");

  // Sticky Bar
  const [stickyBarEnabled, setStickyBarEnabled] = useState(initialProduct.stickyBarEnabled);
  const [stickyBarText, setStickyBarText] = useState(initialProduct.stickyBarText || "");
  const [stickyBarButtonLabel, setStickyBarButtonLabel] = useState(initialProduct.stickyBarButtonLabel || "Buy Now");

  // Tracking overrides
  const [pixelIdOverride, setPixelIdOverride] = useState(initialProduct.pixelIdOverride || "");
  const [metaAccessTokenOverride, setMetaAccessTokenOverride] = useState(initialProduct.metaAccessTokenOverride || "");

  // Plans State
  const [plans, setPlans] = useState<PlanItem[]>(
    (initialProduct.plans || []).map((p: any) => ({
      id: p.id,
      name: p.name,
      sku: p.sku,
      duration: p.duration,
      priceINR: p.price / 100,
      compareAtPriceINR: p.compareAtPrice ? p.compareAtPrice / 100 : undefined,
      deliveryMethod: p.deliveryMethod,
      stockPolicy: p.stockPolicy,
      availableQuantity: p.availableQuantity,
      isActive: p.isActive,
      isDefault: p.isDefault,
      isRecommended: p.isRecommended,
      displayOrder: p.displayOrder,
      benefits: p.benefits || "[]",
    }))
  );

  // Status
  const [saving, setSaving] = useState(false);
  const [saveSuccess, setSaveSuccess] = useState(false);
  const [error, setError] = useState<string | null>(null);

  function handleAddPlan() {
    const newId = `new_${Date.now()}`;
    const newPlan: PlanItem = {
      id: newId,
      name: "1 Year Access",
      sku: `${slug.toUpperCase()}-1YEAR-${Date.now().toString().slice(-4)}`,
      duration: "1 Year",
      priceINR: 599,
      compareAtPriceINR: 1199,
      deliveryMethod: "CREDENTIALS",
      stockPolicy: "UNLIMITED",
      availableQuantity: 50,
      isActive: true,
      isDefault: false,
      isRecommended: false,
      displayOrder: plans.length,
      benefits: JSON.stringify(["Full Pro Features", "All Devices", "1 Year Warranty"]),
    };
    setPlans([...plans, newPlan]);
  }

  function handleUpdatePlan(index: number, field: keyof PlanItem, value: any) {
    const updated = [...plans];
    updated[index] = { ...updated[index], [field]: value };
    setPlans(updated);
  }

  function handleDeletePlan(index: number) {
    if (plans.length <= 1) {
      alert("A product must have at least one active plan.");
      return;
    }
    const updated = plans.filter((_, i) => i !== index);
    setPlans(updated);
  }

  async function handleSave(e: React.FormEvent) {
    e.preventDefault();
    setSaving(true);
    setError(null);
    setSaveSuccess(false);

    try {
      const res = await fetch(`/api/admin/products/${initialProduct.id}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          title,
          slug,
          tagline,
          categoryId: categoryId || null,
          isPublished,
          isFeatured,
          heroTitle,
          heroSubtitle,
          heroBadge,
          videoUrl,
          videoPoster,
          bonusEnabled,
          bonusTitle,
          bonusBadge,
          bonusSubtitle,
          bonusDescription,
          bonusExplanation,
          stickyBarEnabled,
          stickyBarText,
          stickyBarButtonLabel,
          pixelIdOverride: pixelIdOverride.trim() || null,
          metaAccessTokenOverride: metaAccessTokenOverride.trim() || null,
          plans,
        }),
      });

      const data = await res.json();
      if (!res.ok) {
        setError(data.error || "Failed to update product.");
      } else {
        setSaveSuccess(true);
        setTimeout(() => setSaveSuccess(false), 3000);
        router.refresh();
      }
    } catch {
      setError("An unexpected error occurred while saving.");
    } finally {
      setSaving(false);
    }
  }

  return (
    <form onSubmit={handleSave} className="space-y-6">
      {/* Top Header & Action Bar */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div className="flex items-center gap-3">
          <Link
            href="/admin/products"
            className="p-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-white transition"
          >
            <ArrowLeft className="w-4 h-4" />
          </Link>
          <div>
            <div className="flex items-center gap-2">
              <h1 className="text-xl sm:text-2xl font-bold text-white truncate max-w-md">
                {title || "Edit Product"}
              </h1>
              {isPublished ? (
                <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-950 text-emerald-300 border border-emerald-500/30">
                  LIVE
                </span>
              ) : (
                <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-400">
                  DRAFT
                </span>
              )}
            </div>
            <p className="text-xs text-slate-400 font-mono">/products/{slug}</p>
          </div>
        </div>

        <div className="flex items-center gap-2 self-start sm:self-auto">
          <Link
            href={`/products/${slug}`}
            target="_blank"
            className="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold flex items-center gap-1.5 transition border border-slate-700"
          >
            <Eye className="w-3.5 h-3.5" /> View Landing Page
          </Link>
          <button
            type="submit"
            disabled={saving}
            className="px-5 py-2 rounded-xl bg-gradient-to-r from-[#00C8F4] to-[#1677FF] hover:from-[#38d4f7] hover:to-[#2582ff] text-white text-xs font-bold flex items-center gap-1.5 shadow-lg shadow-cyan-500/20 transition disabled:opacity-50"
          >
            {saving ? <Loader2 className="w-3.5 h-3.5 animate-spin" /> : <Save className="w-3.5 h-3.5" />}
            Save Changes
          </button>
        </div>
      </div>

      {/* Alerts */}
      {saveSuccess && (
        <div className="p-3.5 rounded-xl bg-emerald-950/70 border border-emerald-500/50 text-emerald-300 text-xs flex items-center gap-2">
          <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
          <span>Product settings and landing page saved successfully!</span>
        </div>
      )}
      {error && (
        <div className="p-3.5 rounded-xl bg-red-950/70 border border-red-500/50 text-red-300 text-xs flex items-center gap-2">
          <AlertCircle className="w-4 h-4 text-red-400 shrink-0" />
          <span>{error}</span>
        </div>
      )}

      {/* Tabs */}
      <div className="flex items-center gap-1 border-b border-slate-800 overflow-x-auto pb-px">
        <button
          type="button"
          onClick={() => setActiveTab("general")}
          className={`px-4 py-2.5 text-xs font-semibold rounded-t-xl transition whitespace-nowrap flex items-center gap-2 ${
            activeTab === "general"
              ? "bg-[#0C111B] text-[#00C8F4] border-t border-x border-cyan-500/30"
              : "text-slate-400 hover:text-white"
          }`}
        >
          <Package className="w-3.5 h-3.5" /> General & Catalog
        </button>
        <button
          type="button"
          onClick={() => setActiveTab("hero")}
          className={`px-4 py-2.5 text-xs font-semibold rounded-t-xl transition whitespace-nowrap flex items-center gap-2 ${
            activeTab === "hero"
              ? "bg-[#0C111B] text-[#00C8F4] border-t border-x border-cyan-500/30"
              : "text-slate-400 hover:text-white"
          }`}
        >
          <Video className="w-3.5 h-3.5" /> Hero & Video Embed
        </button>
        <button
          type="button"
          onClick={() => setActiveTab("plans")}
          className={`px-4 py-2.5 text-xs font-semibold rounded-t-xl transition whitespace-nowrap flex items-center gap-2 ${
            activeTab === "plans"
              ? "bg-[#0C111B] text-[#00C8F4] border-t border-x border-cyan-500/30"
              : "text-slate-400 hover:text-white"
          }`}
        >
          <DollarSign className="w-3.5 h-3.5" /> Plans & Prices ({plans.length})
        </button>
        <button
          type="button"
          onClick={() => setActiveTab("bonus")}
          className={`px-4 py-2.5 text-xs font-semibold rounded-t-xl transition whitespace-nowrap flex items-center gap-2 ${
            activeTab === "bonus"
              ? "bg-[#0C111B] text-[#00C8F4] border-t border-x border-cyan-500/30"
              : "text-slate-400 hover:text-white"
          }`}
        >
          <Gift className="w-3.5 h-3.5" /> Bonus Offer {bonusEnabled && "✓"}
        </button>
        <button
          type="button"
          onClick={() => setActiveTab("tracking")}
          className={`px-4 py-2.5 text-xs font-semibold rounded-t-xl transition whitespace-nowrap flex items-center gap-2 ${
            activeTab === "tracking"
              ? "bg-[#0C111B] text-[#00C8F4] border-t border-x border-cyan-500/30"
              : "text-slate-400 hover:text-white"
          }`}
        >
          <Activity className="w-3.5 h-3.5" /> Meta Ad Attribution
        </button>
      </div>

      {/* Tab 1: General & Catalog */}
      {activeTab === "general" && (
        <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl p-6 space-y-5">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
                Product Title
              </label>
              <input
                type="text"
                value={title}
                onChange={(e) => setTitle(e.target.value)}
                required
                className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none"
              />
            </div>
            <div>
              <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
                URL Slug
              </label>
              <input
                type="text"
                value={slug}
                onChange={(e) => setSlug(e.target.value.toLowerCase().replace(/[^a-z0-9-]/g, "-"))}
                required
                className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-cyan-300 font-mono outline-none"
              />
              <p className="text-[11px] text-slate-500 mt-1">
                Changing this creates an automatic 301 permanent redirect from the old slug.
              </p>
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
              Catalog Tagline / Short Description
            </label>
            <input
              type="text"
              value={tagline}
              onChange={(e) => setTagline(e.target.value)}
              placeholder="e.g. Instant Official Digital License & Setup Guide"
              className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none"
            />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
            <div>
              <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
                Category
              </label>
              <select
                value={categoryId}
                onChange={(e) => setCategoryId(e.target.value)}
                className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-xs text-white outline-none"
              >
                <option value="">Uncategorized</option>
                {categories.map((c) => (
                  <option key={c.id} value={c.id}>
                    {c.name}
                  </option>
                ))}
              </select>
            </div>

            <div className="flex items-center gap-3 pt-6">
              <input
                type="checkbox"
                id="isPublished"
                checked={isPublished}
                onChange={(e) => setIsPublished(e.target.checked)}
                className="w-4 h-4 rounded accent-cyan-500"
              />
              <label htmlFor="isPublished" className="text-xs font-semibold text-white cursor-pointer">
                Publish on Storefront & Ads (Active)
              </label>
            </div>

            <div className="flex items-center gap-3 pt-6">
              <input
                type="checkbox"
                id="isFeatured"
                checked={isFeatured}
                onChange={(e) => setIsFeatured(e.target.checked)}
                className="w-4 h-4 rounded accent-cyan-500"
              />
              <label htmlFor="isFeatured" className="text-xs font-semibold text-white cursor-pointer">
                Featured Product (Top of Catalog)
              </label>
            </div>
          </div>
        </div>
      )}

      {/* Tab 2: Hero & Video Embed */}
      {activeTab === "hero" && (
        <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl p-6 space-y-5">
          <div>
            <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
              Hero Top Badge Text
            </label>
            <input
              type="text"
              value={heroBadge}
              onChange={(e) => setHeroBadge(e.target.value)}
              placeholder="e.g. INSTANT DIGITAL ACTIVATION"
              className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none"
            />
          </div>

          <div>
            <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
              Hero Headline (H1)
            </label>
            <input
              type="text"
              value={heroTitle}
              onChange={(e) => setHeroTitle(e.target.value)}
              placeholder="e.g. CapCut Pro Premium Private Account"
              className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none"
            />
          </div>

          <div>
            <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
              Hero Subtitle / Value Proposition
            </label>
            <textarea
              rows={3}
              value={heroSubtitle}
              onChange={(e) => setHeroSubtitle(e.target.value)}
              placeholder="Full access to 4K 60fps export, pro auto-captions, AI effects..."
              className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none"
            />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
                YouTube Video Demo URL / Video ID
              </label>
              <input
                type="text"
                value={videoUrl}
                onChange={(e) => setVideoUrl(e.target.value)}
                placeholder="https://www.youtube.com/watch?v=... or embed URL"
                className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none"
              />
              <p className="text-[11px] text-slate-500 mt-1">
                Embedded directly on the product landing page with click-to-load video player.
              </p>
            </div>
            <div>
              <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
                Custom Video Poster Image URL (Optional)
              </label>
              <input
                type="text"
                value={videoPoster}
                onChange={(e) => setVideoPoster(e.target.value)}
                placeholder="https://..."
                className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none"
              />
            </div>
          </div>
        </div>
      )}

      {/* Tab 3: Plans & Pricing */}
      {activeTab === "plans" && (
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <p className="text-xs text-slate-400">
              Configure available duration tiers, pricing, delivery mode, and stock limits.
            </p>
            <button
              type="button"
              onClick={handleAddPlan}
              className="px-3.5 py-1.5 rounded-xl bg-cyan-950 border border-cyan-500/40 text-cyan-300 hover:text-white font-bold text-xs flex items-center gap-1.5 transition"
            >
              <Plus className="w-3.5 h-3.5" /> Add New Plan
            </button>
          </div>

          {plans.map((plan, index) => (
            <div
              key={plan.id}
              className="bg-[#0C111B] border border-slate-800 rounded-2xl p-5 space-y-4 relative"
            >
              <div className="flex items-center justify-between border-b border-slate-800 pb-3">
                <span className="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                  <span className="w-5 h-5 rounded-full bg-cyan-950 border border-cyan-500/40 flex items-center justify-center text-[10px] text-cyan-300">
                    {index + 1}
                  </span>
                  {plan.name || "Untitled Plan"}
                </span>
                <button
                  type="button"
                  onClick={() => handleDeletePlan(index)}
                  className="p-1.5 rounded-lg text-slate-500 hover:text-red-400 transition"
                  title="Delete Plan"
                >
                  <Trash2 className="w-4 h-4" />
                </button>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                  <label className="block text-[11px] font-semibold text-slate-400 mb-1">
                    Plan Name
                  </label>
                  <input
                    type="text"
                    value={plan.name}
                    onChange={(e) => handleUpdatePlan(index, "name", e.target.value)}
                    className="w-full bg-[#05090F] border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-white"
                  />
                </div>
                <div>
                  <label className="block text-[11px] font-semibold text-slate-400 mb-1">
                    SKU Code
                  </label>
                  <input
                    type="text"
                    value={plan.sku}
                    onChange={(e) => handleUpdatePlan(index, "sku", e.target.value)}
                    className="w-full bg-[#05090F] border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-cyan-300 font-mono"
                  />
                </div>
                <div>
                  <label className="block text-[11px] font-semibold text-slate-400 mb-1">
                    Duration
                  </label>
                  <input
                    type="text"
                    value={plan.duration}
                    onChange={(e) => handleUpdatePlan(index, "duration", e.target.value)}
                    placeholder="1 Month, 1 Year"
                    className="w-full bg-[#05090F] border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-white"
                  />
                </div>
                <div>
                  <label className="block text-[11px] font-semibold text-slate-400 mb-1">
                    Price (₹ INR)
                  </label>
                  <input
                    type="number"
                    min="1"
                    value={plan.priceINR}
                    onChange={(e) => handleUpdatePlan(index, "priceINR", Number(e.target.value))}
                    className="w-full bg-[#05090F] border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-white font-bold"
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                  <label className="block text-[11px] font-semibold text-slate-400 mb-1">
                    Compare Price (₹ Strike-through)
                  </label>
                  <input
                    type="number"
                    value={plan.compareAtPriceINR || ""}
                    onChange={(e) => handleUpdatePlan(index, "compareAtPriceINR", e.target.value ? Number(e.target.value) : undefined)}
                    className="w-full bg-[#05090F] border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-slate-300"
                  />
                </div>
                <div>
                  <label className="block text-[11px] font-semibold text-slate-400 mb-1">
                    Delivery Method
                  </label>
                  <select
                    value={plan.deliveryMethod}
                    onChange={(e) => handleUpdatePlan(index, "deliveryMethod", e.target.value)}
                    className="w-full bg-[#05090F] border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-white"
                  >
                    <option value="CREDENTIALS">Encrypted Vault Credentials (Login:Pass)</option>
                    <option value="ACTIVATION_CODE">Digital Activation Key / Code</option>
                    <option value="ACTIVATION_LINK">Instant Invite / Activation URL</option>
                    <option value="DOWNLOAD">Digital Software Download Link</option>
                    <option value="MANUAL">Manual Customer Setup (WhatsApp / Email)</option>
                  </select>
                </div>
                <div className="flex items-center gap-4 pt-4">
                  <label className="flex items-center gap-1.5 text-xs text-slate-300 cursor-pointer">
                    <input
                      type="checkbox"
                      checked={plan.isRecommended}
                      onChange={(e) => handleUpdatePlan(index, "isRecommended", e.target.checked)}
                      className="rounded accent-cyan-500"
                    />
                    <span>Recommended Badge</span>
                  </label>
                  <label className="flex items-center gap-1.5 text-xs text-slate-300 cursor-pointer">
                    <input
                      type="checkbox"
                      checked={plan.isDefault}
                      onChange={(e) => handleUpdatePlan(index, "isDefault", e.target.checked)}
                      className="rounded accent-cyan-500"
                    />
                    <span>Default Selection</span>
                  </label>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Tab 4: Bonus Offer */}
      {activeTab === "bonus" && (
        <div className="bg-[#0C111B] border border-purple-500/25 rounded-2xl p-6 space-y-5">
          <div className="flex items-center gap-3 border-b border-slate-800 pb-4">
            <input
              type="checkbox"
              id="bonusEnabled"
              checked={bonusEnabled}
              onChange={(e) => setBonusEnabled(e.target.checked)}
              className="w-4 h-4 rounded accent-purple-500"
            />
            <label htmlFor="bonusEnabled" className="text-sm font-bold text-white cursor-pointer">
              Enable Free Bonus Offer for this Product (e.g. Canva Pro 1 Year Free)
            </label>
          </div>

          {bonusEnabled && (
            <div className="space-y-4">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
                    Bonus Title
                  </label>
                  <input
                    type="text"
                    value={bonusTitle}
                    onChange={(e) => setBonusTitle(e.target.value)}
                    placeholder="e.g. Canva Pro 1-Year Free Access"
                    className="w-full bg-[#05090F] border border-slate-700 focus:border-purple-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
                    Bonus Badge Label
                  </label>
                  <input
                    type="text"
                    value={bonusBadge}
                    onChange={(e) => setBonusBadge(e.target.value)}
                    placeholder="e.g. INCLUDED FREE BONUS (WORTH ₹499)"
                    className="w-full bg-[#05090F] border border-slate-700 focus:border-purple-400 rounded-xl px-4 py-2.5 text-sm text-purple-300 outline-none"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
                  Bonus Description & Instructions
                </label>
                <textarea
                  rows={3}
                  value={bonusDescription}
                  onChange={(e) => setBonusDescription(e.target.value)}
                  placeholder="Customers will be asked for their Canva email on the order delivery page to receive their invite."
                  className="w-full bg-[#05090F] border border-slate-700 focus:border-purple-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none"
                />
              </div>
            </div>
          )}
        </div>
      )}

      {/* Tab 5: Meta Tracking Overrides */}
      {activeTab === "tracking" && (
        <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl p-6 space-y-5">
          <div className="border-b border-slate-800 pb-3">
            <h3 className="text-sm font-bold text-white">Campaign Tracking Overrides</h3>
            <p className="text-xs text-slate-400">
              Leave blank to inherit the global Pixel and Conversions API settings configured in Store Settings.
            </p>
          </div>

          <div>
            <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
              Product-Specific Meta Pixel ID (Optional)
            </label>
            <input
              type="text"
              value={pixelIdOverride}
              onChange={(e) => setPixelIdOverride(e.target.value)}
              placeholder="e.g. 1234567890123456"
              className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white font-mono outline-none"
            />
          </div>

          <div>
            <label className="block text-xs font-semibold uppercase text-slate-300 mb-2">
              Product-Specific Conversions API Access Token (Optional)
            </label>
            <input
              type="password"
              value={metaAccessTokenOverride}
              onChange={(e) => setMetaAccessTokenOverride(e.target.value)}
              placeholder="EAAG..."
              className="w-full bg-[#05090F] border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white font-mono outline-none"
            />
          </div>
        </div>
      )}
    </form>
  );
}
