@extends('layouts.admin')

@section('title', 'Products & Catalog — Convora Admin')
@section('header_title', 'Products & Pricing')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-white tracking-tight">Products & Digital Offerings</h1>
            <p class="text-xs text-slate-400 mt-1">Manage catalog items, pricing variants, inventory stock, and fulfillment modes.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.categories.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-xs text-slate-300 hover:text-white hover:border-slate-600 transition-colors">
                Manage Categories
            </a>
            <a href="{{ route('admin.products.create') }}" class="btn-primary-glow px-4 py-2.5 text-xs font-bold uppercase tracking-wider flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Product</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="glass-card p-4 rounded-2xl border-cyan-500/20">
        <form method="GET" action="{{ route('admin.products.index') }}" class="flex flex-col sm:flex-row gap-3 items-center justify-between">
            <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <svg class="w-4 h-4 text-slate-500 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products, SKU..." class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-9 pr-3.5 py-2 text-xs text-white focus:border-cyan-400 outline-none">
                </div>

                <select name="category_id" class="bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:border-cyan-400 outline-none">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>

                <select name="status" class="bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:border-cyan-400 outline-none">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-cyan-300 rounded-xl text-xs font-semibold transition-colors">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'category_id', 'status']))
                    <a href="{{ route('admin.products.index') }}" class="text-xs text-slate-400 hover:text-white underline">Clear</a>
                @endif
            </div>

            <div class="text-xs text-slate-400">
                Showing <strong>{{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }}</strong> of <strong>{{ $products->total() }}</strong>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="glass-card rounded-2xl overflow-hidden border-cyan-500/20">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 uppercase font-mono text-[11px] bg-slate-900/60">
                        <th class="py-3.5 px-4">Product</th>
                        <th class="py-3.5 px-4">Category</th>
                        <th class="py-3.5 px-4">Pricing</th>
                        <th class="py-3.5 px-4">Stock</th>
                        <th class="py-3.5 px-4">Fulfillment</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="py-3 px-4">
                            <div class="flex items-center space-x-3">
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-lg object-contain bg-slate-900 border border-slate-800 p-0.5 shrink-0">
                                <div class="min-w-0">
                                    <div class="font-bold text-white text-sm truncate max-w-xs">{{ $product->name }}</div>
                                    <div class="flex items-center space-x-2 text-[10px] text-slate-400 font-mono mt-0.5">
                                        <span>SKU: {{ $product->sku ?? 'N/A' }}</span>
                                        <span>•</span>
                                        <span class="text-cyan-300">{{ $product->duration }}</span>
                                        @if($product->featured)
                                            <span>•</span>
                                            <span class="text-amber-400 font-bold">Featured</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="py-3 px-4">
                            @if($product->category)
                                <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 font-medium text-[11px]">
                                    {{ $product->category->name }}
                                </span>
                            @else
                                <span class="text-slate-500 italic">Uncategorized</span>
                            @endif
                        </td>

                        <td class="py-3 px-4 font-mono">
                            <div class="font-bold text-white text-sm">₹{{ number_format($product->effective_price, 2) }}</div>
                            @if($product->hasDiscount())
                                <div class="text-[10px] text-slate-500 line-through">₹{{ number_format($product->price, 2) }}</div>
                            @endif
                            @if($product->variants->count() > 1)
                                <div class="text-[10px] text-cyan-400 mt-0.5">{{ $product->variants->count() }} variants</div>
                            @endif
                        </td>

                        <td class="py-3 px-4">
                            @if($product->inventory_type === 'unlimited')
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-800 text-emerald-300 border border-emerald-500/20">
                                    Unlimited
                                </span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $product->available_stock > 0 ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : 'bg-rose-950/60 text-rose-300 border border-rose-500/30' }}">
                                    {{ $product->available_stock }} in Vault
                                </span>
                            @endif
                        </td>

                        <td class="py-3 px-4">
                            <span class="text-[11px] font-mono text-slate-300 capitalize">
                                {{ str_replace('_', ' ', $product->fulfillment_mode) }}
                            </span>
                        </td>

                        <td class="py-3 px-4">
                            @if($product->status === 'active')
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-950/60 text-emerald-300 border border-emerald-500/30">Active</span>
                            @elseif($product->status === 'draft')
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-950/60 text-amber-300 border border-amber-500/30">Draft</span>
                            @elseif($product->status === 'archived')
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-800 text-slate-400">Archived</span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-950/60 text-rose-300">Inactive</span>
                            @endif
                        </td>

                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('admin.products.edit', $product) }}" class="p-1.5 rounded-lg bg-slate-800 text-cyan-300 hover:bg-slate-700 transition-colors" title="Edit Product & Variants">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>

                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete or archive {{ $product->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-slate-800 text-rose-400 hover:bg-rose-950/40 transition-colors" title="Delete / Archive">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">
                            No products match your criteria. Click "Add Product" to create one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $products->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
