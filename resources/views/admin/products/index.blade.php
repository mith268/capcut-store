@extends('layouts.admin')

@section('title', 'Products & Pricing — Admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-white">Products & Pricing</h1>
            <p class="text-xs text-slate-400 mt-1">Configure product pricing, warranty duration, credits, and copywriting.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6">
        @foreach($products as $product)
        <div class="glass-card p-6 sm:p-8 rounded-2xl space-y-6 border-cyan-500/25">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
                <div>
                    <span class="text-xs font-mono text-cyan-400 font-bold uppercase">{{ $product->duration }} • {{ $product->credits }} CREDITS</span>
                    <h2 class="text-xl font-black text-white mt-0.5">{{ $product->name }}</h2>
                    <span class="text-xs text-slate-400">Slug: <code class="text-cyan-300 font-mono">{{ $product->slug }}</code></span>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.products.edit', $product) }}" class="btn-primary-glow px-4 py-2 text-xs font-bold uppercase">
                        Edit Product & Features →
                    </a>
                </div>
            </div>

            <!-- Quick Specs Row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800">
                    <span class="text-slate-400 block font-mono text-[10px]">CURRENT PRICE</span>
                    <div class="text-base font-black text-white font-mono">₹{{ number_format($product->effective_price, 2) }}</div>
                    @if($product->hasDiscount())
                        <span class="text-[10px] text-slate-500 line-through">₹{{ number_format($product->price, 2) }}</span>
                    @endif
                </div>

                <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800">
                    <span class="text-slate-400 block font-mono text-[10px]">AVAILABLE STOCK</span>
                    <div class="text-base font-black text-cyan-300 font-mono">{{ $product->available_stock }} Accounts</div>
                </div>

                <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800">
                    <span class="text-slate-400 block font-mono text-[10px]">WARRANTY</span>
                    <div class="text-base font-black text-white font-mono">{{ $product->warranty_days }} Days</div>
                </div>

                <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800">
                    <span class="text-slate-400 block font-mono text-[10px]">STATUS</span>
                    <div class="text-base font-black {{ $product->active ? 'text-emerald-400' : 'text-slate-400' }}">
                        {{ $product->active ? 'Active' : 'Disabled' }}
                    </div>
                </div>
            </div>

            <!-- Features list preview -->
            <div class="space-y-2">
                <span class="text-[10px] font-mono uppercase text-slate-400 block font-bold">ACTIVE FEATURES ({{ $product->features->count() }})</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                    @foreach($product->features as $feature)
                        <div class="bg-slate-900/40 p-2.5 rounded-lg border border-slate-800/80 text-xs">
                            <span class="font-bold text-white block">{{ $feature->title }}</span>
                            <span class="text-slate-400 text-[11px] line-clamp-1">{{ $feature->description }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
        @endforeach
    </div>
</div>
@endsection
