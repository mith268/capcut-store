@extends('layouts.admin')

@section('title', 'Edit Product — ' . $product->name)

@section('content')
<div class="max-w-4xl space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-white">Edit Product Details</h1>
            <p class="text-xs text-slate-400 mt-1">Changes here immediately reflect on the public landing page and checkout.</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="text-xs text-slate-400 hover:text-white">
            ← Back to Products
        </a>
    </div>

    <!-- Product Update Form -->
    <div class="glass-card p-6 sm:p-8 rounded-2xl border-cyan-500/25 space-y-6">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1">Product Name *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                           class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1">Original Price (₹) *</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required
                           class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white font-mono outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1">Sale / Offer Price (₹)</label>
                    <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" placeholder="Leave blank if no discount"
                           class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white font-mono outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1">Credits Allotment *</label>
                    <input type="number" name="credits" value="{{ old('credits', $product->credits) }}" required
                           class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white font-mono outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1">Duration Label *</label>
                    <input type="text" name="duration" value="{{ old('duration', $product->duration) }}" required
                           class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1">Warranty Days *</label>
                    <input type="number" name="warranty_days" value="{{ old('warranty_days', $product->warranty_days) }}" required
                           class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white font-mono outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1">Currency Code *</label>
                    <input type="text" name="currency" value="{{ old('currency', $product->currency) }}" required
                           class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white font-mono uppercase outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1">Short Description</label>
                <input type="text" name="short_description" value="{{ old('short_description', $product->short_description) }}"
                       class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1">Full Description</label>
                <textarea name="description" rows="3" class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="flex items-center space-x-6 pt-2">
                <label class="flex items-center space-x-2 cursor-pointer text-xs font-bold text-slate-300">
                    <input type="checkbox" name="active" value="1" {{ old('active', $product->active) ? 'checked' : '' }} class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-cyan-500">
                    <span>Product Active (Listed on Store)</span>
                </label>
            </div>

            <button type="submit" class="btn-primary-glow px-6 py-3 text-xs font-bold uppercase">
                Save Product Changes
            </button>
        </form>
    </div>

    <!-- Features Management -->
    <div class="glass-card p-6 sm:p-8 rounded-2xl border-cyan-500/25 space-y-6">
        <h2 class="text-base font-bold text-white">Manage Product Features</h2>

        <!-- Current Features List -->
        <div class="space-y-3">
            @foreach($product->features as $feat)
            <div class="flex items-center justify-between p-3.5 bg-slate-900/60 rounded-xl border border-slate-800">
                <div>
                    <span class="font-bold text-sm text-white block">{{ $feat->title }}</span>
                    <span class="text-xs text-slate-400">{{ $feat->description }}</span>
                </div>
                <form method="POST" action="{{ route('admin.products.features.destroy', $feat) }}" onsubmit="return confirm('Remove this feature?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 font-bold px-2 py-1">
                        Remove
                    </button>
                </form>
            </div>
            @endforeach
        </div>

        <!-- Add Feature Form -->
        <form method="POST" action="{{ route('admin.products.features.store', $product) }}" class="space-y-3 pt-4 border-t border-slate-800">
            @csrf
            <h3 class="text-xs font-bold uppercase tracking-wider text-cyan-400">Add New Feature</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <input type="text" name="title" placeholder="Feature Title" required
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none">
                </div>
                <div>
                    <input type="number" name="display_order" placeholder="Display Order (e.g. 1)" value="{{ $product->features->count() + 1 }}"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white font-mono outline-none">
                </div>
            </div>
            <div>
                <input type="text" name="description" placeholder="Short description"
                       class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none">
            </div>

            <button type="submit" class="bg-slate-800 hover:bg-slate-700 border border-cyan-500/30 text-cyan-300 font-bold px-4 py-2 rounded-xl text-xs">
                + Add Feature
            </button>
        </form>
    </div>
</div>
@endsection
