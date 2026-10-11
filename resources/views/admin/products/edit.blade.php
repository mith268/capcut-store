@extends('layouts.admin')

@section('title', 'Edit ' . $product->name . ' — Convora Admin')
@section('header_title', 'Edit Product')

@section('content')
<div class="max-w-5xl mx-auto space-y-8" x-data="{ variantModal: false, editVariantData: {}, editVariantModal: false }">

    <!-- Top Breadcrumb & Navigation -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
        <div>
            <a href="{{ route('admin.products.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300 font-mono flex items-center space-x-1 mb-1">
                <span>← Back to Products List</span>
            </a>
            <h1 class="text-xl font-black text-white tracking-tight flex items-center space-x-3">
                <span>{{ $product->name }}</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-mono uppercase {{ $product->status === 'active' ? 'bg-emerald-950/60 text-emerald-300 border border-emerald-500/30' : 'bg-slate-800 text-slate-400' }}">
                    {{ $product->status }}
                </span>
            </h1>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-xs text-cyan-300 hover:bg-slate-800 transition-colors flex items-center space-x-1.5">
                <span>Preview Page</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    </div>

    <!-- 1. Product Details Form -->
    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- General Info Card -->
        <div class="glass-card p-6 sm:p-8 rounded-2xl border-cyan-500/20 space-y-4">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider font-mono text-cyan-300">1. Product Information & Classification</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Product Title *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:border-cyan-400 outline-none">
                    @error('name') <span class="text-[11px] text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Category</label>
                    <select name="category_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:border-cyan-400 outline-none">
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Slug (URL)</label>
                    <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white font-mono focus:border-cyan-400 outline-none">
                    @error('slug') <span class="text-[11px] text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white font-mono focus:border-cyan-400 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Duration Tag</label>
                    <input type="text" name="duration" value="{{ old('duration', $product->duration) }}" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:border-cyan-400 outline-none">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Short Description</label>
                    <textarea name="short_description" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white focus:border-cyan-400 outline-none">{{ old('short_description', $product->short_description) }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Full Description</label>
                    <textarea name="description" rows="4" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white focus:border-cyan-400 outline-none">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Pricing & Inventory Card -->
        <div class="glass-card p-6 sm:p-8 rounded-2xl border-cyan-500/20 space-y-4">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider font-mono text-cyan-300">2. Pricing, Stock & Fulfillment Rules</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Regular Price (₹) *</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white font-mono focus:border-cyan-400 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Sale / Offer Price (₹)</label>
                    <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" placeholder="Leave blank if no discount" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white font-mono focus:border-cyan-400 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Currency</label>
                    <input type="text" name="currency" value="{{ old('currency', $product->currency) }}" readonly class="w-full bg-slate-900/60 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-400 font-mono outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Fulfillment Mode *</label>
                    <select name="fulfillment_mode" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:border-cyan-400 outline-none">
                        <option value="key_inventory" {{ old('fulfillment_mode', $product->fulfillment_mode) === 'key_inventory' ? 'selected' : '' }}>Digital Vault / License Key</option>
                        <option value="account_access" {{ old('fulfillment_mode', $product->fulfillment_mode) === 'account_access' ? 'selected' : '' }}>Account / Invite Activation</option>
                        <option value="file_download" {{ old('fulfillment_mode', $product->fulfillment_mode) === 'file_download' ? 'selected' : '' }}>Protected File Download</option>
                        <option value="manual" {{ old('fulfillment_mode', $product->fulfillment_mode) === 'manual' ? 'selected' : '' }}>Manual Support Delivery</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Inventory Type *</label>
                    <select name="inventory_type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:border-cyan-400 outline-none">
                        <option value="finite" {{ old('inventory_type', $product->inventory_type) === 'finite' ? 'selected' : '' }}>Finite (Vault Credentials)</option>
                        <option value="unlimited" {{ old('inventory_type', $product->inventory_type) === 'unlimited' ? 'selected' : '' }}>Unlimited / Service Based</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Purchase Channels *</label>
                    <select name="purchase_mode" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:border-cyan-400 outline-none">
                        <option value="both" {{ old('purchase_mode', $product->purchase_mode) === 'both' ? 'selected' : '' }}>Razorpay Checkout + WhatsApp</option>
                        <option value="checkout" {{ old('purchase_mode', $product->purchase_mode) === 'checkout' ? 'selected' : '' }}>Razorpay Checkout Only</option>
                        <option value="whatsapp" {{ old('purchase_mode', $product->purchase_mode) === 'whatsapp' ? 'selected' : '' }}>WhatsApp Inquiry Only</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Warranty (Days)</label>
                    <input type="number" name="warranty_days" value="{{ old('warranty_days', $product->warranty_days) }}" min="0" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white font-mono focus:border-cyan-400 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Credits / Units</label>
                    <input type="number" name="credits" value="{{ old('credits', $product->credits) }}" min="0" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white font-mono focus:border-cyan-400 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Status *</label>
                    <select name="status" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:border-cyan-400 outline-none">
                        <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active (Live)</option>
                        <option value="draft" {{ old('status', $product->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="archived" {{ old('status', $product->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-6 pt-3 border-t border-slate-800">
                <div class="flex items-center space-x-2">
                    <input type="checkbox" name="show_in_store" id="show_in_store" value="1" {{ old('show_in_store', $product->show_in_store) ? 'checked' : '' }} class="rounded bg-slate-900 border-slate-700 text-cyan-400">
                    <label for="show_in_store" class="text-xs text-slate-300">Show in public store catalog (/store)</label>
                </div>

                <div class="flex items-center space-x-2">
                    <input type="checkbox" name="featured" id="featured" value="1" {{ old('featured', $product->featured) ? 'checked' : '' }} class="rounded bg-slate-900 border-slate-700 text-cyan-400">
                    <label for="featured" class="text-xs text-slate-300">Highlight as Featured Deal</label>
                </div>
            </div>
        </div>

        <!-- Cover & Media Card -->
        <div class="glass-card p-6 sm:p-8 rounded-2xl border-cyan-500/20 space-y-4">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider font-mono text-cyan-300">3. Primary Creative & Media Cover</h2>

            <div class="flex flex-col sm:flex-row gap-6 items-start">
                <div class="w-28 h-28 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center overflow-hidden shrink-0">
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-contain p-1">
                </div>

                <div class="flex-1 space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Replace Product Creative</label>
                        <input type="file" name="primary_image_file" accept="image/*" class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-cyan-500/20 file:text-cyan-300 hover:file:bg-cyan-500/30">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Offer Badge Text</label>
                            <input type="text" name="badge_text" value="{{ old('badge_text', $product->badge_text) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white focus:border-cyan-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Button CTA Text</label>
                            <input type="text" name="cta_text" value="{{ old('cta_text', $product->cta_text) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white focus:border-cyan-400 outline-none">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bonus & Marketing Details -->
        <div class="glass-card p-6 sm:p-8 rounded-2xl border-cyan-500/20 space-y-4">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider font-mono text-cyan-300">4. Bonus Offer & Video Settings</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Bonus Offer Title</label>
                    <input type="text" name="bonus_title" value="{{ old('bonus_title', $product->bonus_title) }}" placeholder="e.g., 🎁 BONUS: CANVA PRO 1 YEAR FREE" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">YouTube Video ID (Hero Embed)</label>
                    <input type="text" name="video_id" value="{{ old('video_id', $product->video_id) }}" placeholder="e.g., GeXVI3p-BqE" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white font-mono outline-none">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Bonus Description</label>
                    <textarea name="bonus_description" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none">{{ old('bonus_description', $product->bonus_description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex items-center justify-end space-x-3">
            <button type="submit" class="btn-primary-glow px-8 py-3 text-xs font-bold uppercase tracking-wider">
                Save Product Changes
            </button>
        </div>

    </form>

    <!-- 2. Product Variants Section -->
    <div class="glass-card p-6 sm:p-8 rounded-2xl border-cyan-500/20 space-y-6">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div>
                <h2 class="text-base font-bold text-white">Pricing Variants & Plans</h2>
                <p class="text-xs text-slate-400 mt-0.5">Offer durations, multi-seat licenses, and packages for this product.</p>
            </div>
            <button type="button" @click="variantModal = true" class="px-4 py-2 rounded-xl bg-cyan-500/20 border border-cyan-500/30 text-xs font-bold text-cyan-300 hover:bg-cyan-500/30 transition-colors">
                + Add Plan Variant
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 uppercase font-mono text-[11px]">
                        <th class="py-2.5 px-3">Variant Name</th>
                        <th class="py-2.5 px-3">SKU</th>
                        <th class="py-2.5 px-3">Price</th>
                        <th class="py-2.5 px-3">Duration</th>
                        <th class="py-2.5 px-3">Default</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($product->variants as $variant)
                    <tr class="hover:bg-slate-900/40">
                        <td class="py-3 px-3 font-bold text-white">{{ $variant->name }}</td>
                        <td class="py-3 px-3 font-mono text-slate-400">{{ $variant->sku ?? '-' }}</td>
                        <td class="py-3 px-3 font-mono">
                            <span class="text-white font-bold">₹{{ number_format($variant->effective_price, 2) }}</span>
                            @if($variant->hasDiscount())
                                <span class="text-[10px] text-slate-500 line-through">₹{{ number_format($variant->price, 2) }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-slate-300">{{ $variant->duration ?? '-' }}</td>
                        <td class="py-3 px-3">
                            @if($variant->is_default)
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">Default</span>
                            @else
                                <span class="text-slate-500 text-[11px]">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $variant->is_active ? 'bg-emerald-950/60 text-emerald-300' : 'bg-slate-800 text-slate-400' }}">
                                {{ $variant->is_active ? 'Active' : 'Disabled' }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button type="button" @click="editVariantData = {{ json_encode($variant) }}; editVariantModal = true" class="text-cyan-400 hover:underline">Edit</button>
                                @if(!$variant->is_default)
                                <form method="POST" action="{{ route('admin.products.variants.destroy', $variant) }}" onsubmit="return confirm('Delete this variant?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:underline">Delete</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-4 text-center text-slate-500">No variants created yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. Features Management Section -->
    <div class="glass-card p-6 sm:p-8 rounded-2xl border-cyan-500/20 space-y-6">
        <div class="border-b border-slate-800 pb-4">
            <h2 class="text-base font-bold text-white">Highlighted Features & Benefits</h2>
            <p class="text-xs text-slate-400 mt-0.5">Displayed on product cards and feature sections.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($product->features as $feature)
            <div class="p-3.5 bg-slate-900/60 rounded-xl border border-slate-800 flex items-center justify-between">
                <div>
                    <span class="font-bold text-xs text-white block">{{ $feature->title }}</span>
                    @if($feature->description)
                        <span class="text-[11px] text-slate-400 block line-clamp-1">{{ $feature->description }}</span>
                    @endif
                </div>
                <form method="POST" action="{{ route('admin.products.features.destroy', $feature) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs font-mono">&times;</button>
                </form>
            </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('admin.products.features.store', $product) }}" class="flex flex-col sm:flex-row gap-3 pt-2">
            @csrf
            <input type="text" name="title" required placeholder="Feature title (e.g., 2 Devices Supported)" class="flex-1 bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white outline-none">
            <input type="text" name="description" placeholder="Short description" class="flex-1 bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white outline-none">
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 text-cyan-300 font-bold text-xs uppercase hover:bg-slate-700">Add Feature</button>
        </form>
    </div>

    <!-- Create Variant Modal -->
    <div x-show="variantModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div @click.away="variantModal = false" class="bg-[#0B101B] border border-cyan-500/30 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Add Pricing Variant</h3>
                <button type="button" @click="variantModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.products.variants.store', $product) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Variant Name *</label>
                    <input type="text" name="name" required placeholder="e.g., 3 Months • 3600 Credits" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Regular Price (₹) *</label>
                        <input type="number" step="0.01" name="price" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white font-mono outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Sale Price (₹)</label>
                        <input type="number" step="0.01" name="sale_price" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white font-mono outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Duration Tag</label>
                        <input type="text" name="duration" placeholder="e.g., 3 Months" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">SKU</label>
                        <input type="text" name="sku" placeholder="e.g., CP-3M" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white font-mono outline-none">
                    </div>
                </div>

                <div class="flex items-center space-x-2 pt-1">
                    <input type="checkbox" name="is_default" id="variant_is_default" value="1" class="rounded bg-slate-900 border-slate-700 text-cyan-400">
                    <label for="variant_is_default" class="text-xs text-slate-300">Set as default selected plan</label>
                </div>

                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-800">
                    <button type="button" @click="variantModal = false" class="px-4 py-2 text-xs font-semibold text-slate-400 hover:text-white">Cancel</button>
                    <button type="submit" class="btn-primary-glow px-5 py-2 text-xs font-bold uppercase">Save Variant</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Variant Modal -->
    <div x-show="editVariantModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div @click.away="editVariantModal = false" class="bg-[#0B101B] border border-cyan-500/30 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Edit Pricing Variant</h3>
                <button type="button" @click="editVariantModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" :action="'{{ url('admin/products/variants') }}/' + editVariantData.id" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Variant Name *</label>
                    <input type="text" name="name" x-model="editVariantData.name" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Regular Price (₹) *</label>
                        <input type="number" step="0.01" name="price" x-model="editVariantData.price" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white font-mono outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Sale Price (₹)</label>
                        <input type="number" step="0.01" name="sale_price" x-model="editVariantData.sale_price" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white font-mono outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Duration Tag</label>
                        <input type="text" name="duration" x-model="editVariantData.duration" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">SKU</label>
                        <input type="text" name="sku" x-model="editVariantData.sku" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white font-mono outline-none">
                    </div>
                </div>

                <div class="flex items-center space-x-2 pt-1">
                    <input type="checkbox" name="is_default" id="edit_variant_is_default" value="1" :checked="editVariantData.is_default" class="rounded bg-slate-900 border-slate-700 text-cyan-400">
                    <label for="edit_variant_is_default" class="text-xs text-slate-300">Default selected plan</label>
                </div>

                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-800">
                    <button type="button" @click="editVariantModal = false" class="px-4 py-2 text-xs font-semibold text-slate-400 hover:text-white">Cancel</button>
                    <button type="submit" class="btn-primary-glow px-5 py-2 text-xs font-bold uppercase">Update Variant</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
