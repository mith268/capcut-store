@extends('layouts.store')

@section('title', $product->name . ' — Convora Store')
@section('meta_description', $product->short_description ?: Str::limit(strip_tags($product->description), 150))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12"
     x-data="{
        selectedVariant: {{ json_encode($product->variants->firstWhere('is_default', true) ?: $product->variants->first()) }},
        variants: {{ json_encode($product->variants) }},
        selectVariant(v) {
            this.selectedVariant = v;
        },
        get currentPrice() {
            return this.selectedVariant ? parseFloat(this.selectedVariant.price) : {{ (float) $product->price }};
        },
        get currentEffectivePrice() {
            if (this.selectedVariant && this.selectedVariant.sale_price && parseFloat(this.selectedVariant.sale_price) > 0) {
                return parseFloat(this.selectedVariant.sale_price);
            }
            return this.currentPrice;
        },
        get currentSavings() {
            return Math.max(0, this.currentPrice - this.currentEffectivePrice);
        },
        get hasDiscount() {
            return this.currentSavings > 0;
        }
     }">

    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 font-medium">
        <a href="{{ route('store.index') }}" class="hover:text-cyan-600 transition-colors">Store</a>
        <span>/</span>
        @if($product->category)
            <a href="{{ route('category.show', $product->category->slug) }}" class="hover:text-cyan-600 transition-colors">{{ $product->category->name }}</a>
            <span>/</span>
        @endif
        <span class="text-slate-800 font-bold truncate max-w-xs">{{ $product->name }}</span>
    </nav>

    <!-- ==========================================
         TOP TWO-COLUMN PRODUCT PANEL
    =========================================== -->
    <div class="store-card p-6 sm:p-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
            
            <!-- Left: Creative Image & Gallery (5 cols) -->
            <div class="lg:col-span-5 space-y-4">
                <div class="relative bg-slate-900 rounded-2xl aspect-square flex items-center justify-center p-6 overflow-hidden border border-slate-800 shadow-inner">
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="max-h-full max-w-full object-contain">
                    
                    @if($product->badge_text)
                    <span class="absolute top-3 left-3 bg-amber-400 text-slate-950 text-[11px] font-black px-2.5 py-1 rounded-lg shadow-sm">
                        {{ $product->badge_text }}
                    </span>
                    @endif
                </div>

                <!-- Gallery Thumbs (if multiple media exists) -->
                @if($product->media->count() > 1)
                <div class="grid grid-cols-4 gap-2">
                    @foreach($product->media as $item)
                    <div class="aspect-square bg-slate-900 rounded-xl p-1 border border-slate-800 overflow-hidden cursor-pointer hover:border-cyan-500 transition-colors">
                        <img src="{{ $item->url }}" alt="{{ $item->alt_text }}" class="w-full h-full object-contain">
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Right: Product Information & Purchase Controls (7 cols) -->
            <div class="lg:col-span-7 space-y-6">
                
                <!-- Category & Title -->
                <div class="space-y-2">
                    @if($product->category)
                    <div class="flex items-center space-x-2">
                        <span class="chip-pill bg-slate-100 text-slate-700 font-mono text-[11px]">
                            {{ $product->category->name }}
                        </span>
                        <span class="text-xs text-slate-400 font-mono">SKU: <span x-text="selectedVariant ? selectedVariant.sku : '{{ $product->sku }}'"></span></span>
                    </div>
                    @endif

                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-snug">
                        {{ $product->name }}
                    </h1>

                    <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                        {{ $product->short_description ?: $product->description }}
                    </p>
                </div>

                <!-- Live Price Row -->
                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 flex items-baseline justify-between">
                    <div>
                        <div class="flex items-baseline space-x-3">
                            <span class="text-3xl font-black text-slate-900 font-mono">
                                <span x-text="'₹' + currentEffectivePrice.toLocaleString('en-IN', {maximumFractionDigits: 0})">₹{{ number_format($product->effective_price, 0) }}</span>
                            </span>
                            <template x-if="hasDiscount">
                                <span class="text-sm text-slate-400 line-through font-mono">
                                    <span x-text="'₹' + currentPrice.toLocaleString('en-IN', {maximumFractionDigits: 0})">₹{{ number_format($product->price, 0) }}</span>
                                </span>
                            </template>
                        </div>
                        <template x-if="hasDiscount">
                            <span class="inline-block mt-1 text-[11px] font-bold text-rose-600">
                                You Save ₹<span x-text="currentSavings.toLocaleString('en-IN', {maximumFractionDigits: 0})"></span> (One-time payment)
                            </span>
                        </template>
                    </div>

                    <!-- Stock Status Indicator -->
                    <div class="text-right">
                        @if($product->inventory_type === 'unlimited')
                            <span class="chip-pill bg-emerald-100 text-emerald-800 font-bold">
                                In Stock • Instant
                            </span>
                        @else
                            <span class="chip-pill {{ $product->available_stock > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }} font-bold">
                                {{ $product->available_stock > 0 ? $product->available_stock . ' Available' : 'Restocking' }}
                            </span>
                        @endif
                        <span class="text-[10px] text-slate-400 block mt-1 font-mono">No Auto-Renewal</span>
                    </div>
                </div>

                <!-- Variant Selector (if product has multiple plans) -->
                <template x-if="variants && variants.length > 1">
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider font-mono">
                            Select Plan / Duration:
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <template x-for="v in variants" :key="v.id">
                                <div @click="selectVariant(v)"
                                     :class="selectedVariant && selectedVariant.id === v.id ? 'border-cyan-600 bg-cyan-50/50 ring-2 ring-cyan-500/20' : 'border-slate-200 bg-white hover:border-slate-300'"
                                     class="border rounded-xl p-3 cursor-pointer transition-all">
                                    <div class="font-bold text-xs text-slate-900" x-text="v.name"></div>
                                    <div class="text-xs font-mono font-black text-slate-800 mt-1">
                                        ₹<span x-text="(v.sale_price && parseFloat(v.sale_price) > 0 ? parseFloat(v.sale_price) : parseFloat(v.price)).toLocaleString('en-IN')"></span>
                                    </div>
                                    <div class="text-[10px] text-slate-500 mt-0.5" x-text="v.duration || ''"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Bonus Highlight (if product has bonus) -->
                @if($product->bonus_title)
                <div class="p-4 rounded-xl bg-purple-50/70 border border-purple-200 flex items-start space-x-3 text-xs text-purple-900">
                    <span class="text-base shrink-0">🎁</span>
                    <div>
                        <strong class="font-bold block">{{ $product->bonus_title }}</strong>
                        <p class="text-purple-700 text-[11px] mt-0.5">{{ $product->bonus_description }}</p>
                    </div>
                </div>
                @endif

                <!-- Purchase Action CTAs -->
                <div class="space-y-3 pt-2">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <a :href="'{{ route('checkout.show') }}?product={{ $product->id }}' + (selectedVariant ? '&variant=' + selectedVariant.id : '')"
                           class="btn-store-cyan w-full py-3.5 rounded-xl text-center text-xs font-bold uppercase tracking-wider shadow-md hover:shadow-lg flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span>Buy Online (Instant)</span>
                        </a>

                        <a href="{{ $whatsappUrl }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="btn-store-lime w-full py-3.5 rounded-xl text-center text-xs font-bold uppercase tracking-wider flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                            </svg>
                            <span>Order on WhatsApp</span>
                        </a>
                    </div>

                    <!-- Trust Strip -->
                    <div class="grid grid-cols-3 gap-2 text-center text-[10px] text-slate-500 pt-2 border-t border-slate-100">
                        <div>🔒 256-Bit SSL Encrypted</div>
                        <div>⚡ Instant Digital Access</div>
                        <div>🛡️ {{ $product->warranty_days }} Days Warranty</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ==========================================
         STRUCTURED FEATURES & SPECIFICATIONS
    =========================================== -->
    @if($product->features->isNotEmpty())
    <div class="space-y-4">
        <h2 class="text-xl font-bold text-slate-900 tracking-tight">Key Features & Inclusions</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($product->features as $feature)
            <div class="bg-white border border-slate-200 rounded-2xl p-4 space-y-1">
                <div class="flex items-center space-x-2 text-cyan-600 font-bold text-xs uppercase font-mono">
                    <span>✓</span>
                    <span>{{ $feature->title }}</span>
                </div>
                @if($feature->description)
                <p class="text-xs text-slate-500 leading-relaxed">{{ $feature->description }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- ==========================================
         FULL DESCRIPTION & ACTIVATION GUIDELINES
    =========================================== -->
    @if($product->description)
    <div class="store-card p-6 sm:p-8 space-y-4">
        <h2 class="text-lg font-bold text-slate-900">Product Specifications & Activation</h2>
        <div class="text-xs sm:text-sm text-slate-600 leading-relaxed space-y-3 prose max-w-none">
            {!! nl2br(e($product->description)) !!}
        </div>
    </div>
    @endif

    <!-- ==========================================
         RELATED PRODUCTS IN CATEGORY
    =========================================== -->
    @if($relatedProducts->isNotEmpty())
    <div class="space-y-6 pt-4">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
            <h2 class="text-xl font-bold text-slate-900">You May Also Like</h2>
            <a href="{{ route('store.index') }}" class="text-xs text-cyan-600 font-bold hover:underline">View All Catalog →</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($relatedProducts as $related)
            <div class="store-card flex flex-col justify-between overflow-hidden">
                <div class="relative bg-slate-900 aspect-square flex items-center justify-center p-3">
                    <img src="{{ $related->image_url }}" alt="{{ $related->name }}" class="max-h-full max-w-full object-contain">
                </div>
                <div class="p-4 space-y-2">
                    <h3 class="font-bold text-slate-900 text-xs line-clamp-1">
                        <a href="{{ route('products.show', $related->slug) }}" class="hover:text-cyan-600">{{ $related->name }}</a>
                    </h3>
                    <div class="flex items-baseline justify-between pt-1">
                        <span class="text-sm font-black text-slate-900 font-mono">₹{{ number_format($related->effective_price, 0) }}</span>
                        <a href="{{ route('products.show', $related->slug) }}" class="text-xs text-cyan-600 font-bold hover:underline">View →</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
