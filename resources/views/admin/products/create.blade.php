@extends('layouts.admin')

@section('title', 'Add New Product — Convora Admin')
@section('header_title', 'Add Product')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Back -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.products.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300 font-mono flex items-center space-x-1 mb-1">
                <span>← Back to Products List</span>
            </a>
            <h1 class="text-xl font-black text-white tracking-tight">Create New Product</h1>
        </div>
    </div>

    <!-- Create Form -->
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- General Info Card -->
        <div class="glass-card p-6 sm:p-8 rounded-2xl border-cyan-500/20 space-y-4">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider font-mono text-cyan-300">1. Product Information</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Product Title *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g., ChatGPT Plus (Shared Account)" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:border-cyan-400 outline-none">
                    @error('name') <span class="text-[11px] text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Category</label>
                    <select name="category_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:border-cyan-400 outline-none">
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Custom Slug (URL)</label>
                    <input type="text" name="slug" value="{{ old('slug') }}" placeholder="auto-generated from title" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white font-mono focus:border-cyan-400 outline-none">
                    @error('slug') <span class="text-[11px] text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku') }}" placeholder="e.g., SKU-CGPT-1M" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white font-mono focus:border-cyan-400 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Plan Duration</label>
                    <input type="text" name="duration" value="{{ old('duration', '1 Month') }}" required placeholder="1 Month, 1 Year, Lifetime" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:border-cyan-400 outline-none">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Short Description (for store cards)</label>
                    <textarea name="short_description" rows="2" placeholder="Brief summary of what the buyer receives" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white focus:border-cyan-400 outline-none">{{ old('short_description') }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Full Description</label>
                    <textarea name="description" rows="4" placeholder="Detailed product specifications, usage instructions, etc." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white focus:border-cyan-400 outline-none">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Pricing & Inventory Card -->
        <div class="glass-card p-6 sm:p-8 rounded-2xl border-cyan-500/20 space-y-4">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider font-mono text-cyan-300">2. Pricing, Stock & Fulfillment</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Regular Price (₹) *</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', '999.00') }}" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white font-mono focus:border-cyan-400 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Sale Price (₹)</label>
                    <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', '299.00') }}" placeholder="Leave blank if no discount" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white font-mono focus:border-cyan-400 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Currency</label>
                    <input type="text" name="currency" value="{{ old('currency', 'INR') }}" readonly class="w-full bg-slate-900/60 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-400 font-mono outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Fulfillment Mode *</label>
                    <select name="fulfillment_mode" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:border-cyan-400 outline-none">
                        <option value="key_inventory" {{ old('fulfillment_mode') === 'key_inventory' ? 'selected' : '' }}>Digital Vault / License Key</option>
                        <option value="account_access" {{ old('fulfillment_mode') === 'account_access' ? 'selected' : '' }}>Account / Invite Activation</option>
                        <option value="file_download" {{ old('fulfillment_mode') === 'file_download' ? 'selected' : '' }}>Protected File Download</option>
                        <option value="manual" {{ old('fulfillment_mode') === 'manual' ? 'selected' : '' }}>Manual Support Delivery</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Inventory Type *</label>
                    <select name="inventory_type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:border-cyan-400 outline-none">
                        <option value="finite" {{ old('inventory_type') === 'finite' ? 'selected' : '' }}>Finite (Tracked from Vault)</option>
                        <option value="unlimited" {{ old('inventory_type') === 'unlimited' ? 'selected' : '' }}>Unlimited / Service Based</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Purchase Channels *</label>
                    <select name="purchase_mode" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:border-cyan-400 outline-none">
                        <option value="both" {{ old('purchase_mode') === 'both' ? 'selected' : '' }}>Razorpay Checkout + WhatsApp</option>
                        <option value="checkout" {{ old('purchase_mode') === 'checkout' ? 'selected' : '' }}>Razorpay Checkout Only</option>
                        <option value="whatsapp" {{ old('purchase_mode') === 'whatsapp' ? 'selected' : '' }}>WhatsApp Inquiry Only</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Warranty (Days)</label>
                    <input type="number" name="warranty_days" value="{{ old('warranty_days', 30) }}" min="0" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white font-mono focus:border-cyan-400 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Credits / Units (if applicable)</label>
                    <input type="number" name="credits" value="{{ old('credits', 0) }}" min="0" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white font-mono focus:border-cyan-400 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Status *</label>
                    <select name="status" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:border-cyan-400 outline-none">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active (Live)</option>
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-6 pt-3 border-t border-slate-800">
                <div class="flex items-center space-x-2">
                    <input type="checkbox" name="show_in_store" id="show_in_store" value="1" checked class="rounded bg-slate-900 border-slate-700 text-cyan-400">
                    <label for="show_in_store" class="text-xs text-slate-300">Show in public store catalog (/store)</label>
                </div>

                <div class="flex items-center space-x-2">
                    <input type="checkbox" name="featured" id="featured" value="1" class="rounded bg-slate-900 border-slate-700 text-cyan-400">
                    <label for="featured" class="text-xs text-slate-300">Highlight as Featured Deal on Home</label>
                </div>
            </div>
        </div>

        <!-- Media & Assets Card -->
        <div class="glass-card p-6 sm:p-8 rounded-2xl border-cyan-500/20 space-y-4">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider font-mono text-cyan-300">3. Media & Creative Banner</h2>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Primary Product Cover Creative</label>
                <input type="file" name="primary_image_file" accept="image/*" class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-cyan-500/20 file:text-cyan-300 hover:file:bg-cyan-500/30">
                <span class="text-[10px] text-slate-500 block mt-1">Recommended: 1:1 square or 16:9 banner creative (PNG, JPG, WebP up to 5MB).</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Offer Badge Text</label>
                    <input type="text" name="badge_text" value="{{ old('badge_text', 'BEST DEAL') }}" placeholder="e.g., BESTSELLER, 80% OFF" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:border-cyan-400 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Button CTA Text</label>
                    <input type="text" name="cta_text" value="{{ old('cta_text', 'BUY NOW') }}" placeholder="BUY NOW / GET ACCESS" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:border-cyan-400 outline-none">
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('admin.products.index') }}" class="px-5 py-3 rounded-xl text-xs font-bold text-slate-400 hover:text-white">Cancel</a>
            <button type="submit" class="btn-primary-glow px-8 py-3 text-xs font-bold uppercase tracking-wider">
                Create Product →
            </button>
        </div>

    </form>

</div>
@endsection
