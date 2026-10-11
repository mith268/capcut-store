@extends('layouts.store')

@section('title', ($selectedCategory ? $selectedCategory->name . ' — ' : '') . 'Convora Store — Premium Digital Licenses & Subscriptions')
@section('meta_description', $selectedCategory->description ?? 'Browse premium subscriptions, creative design tools, and software licenses with instant delivery and 30-day warranty.')

@section('content')
<div class="space-y-12 pb-16">

    <!-- ==========================================
         HERO SECTION: SEARCH & CATEGORY CHIPS
    =========================================== -->
    <section class="pt-10 pb-6 text-center px-4">
        <div class="max-w-4xl mx-auto space-y-4">
            
            <!-- Headline -->
            <h1 class="text-3xl sm:text-5xl font-black text-slate-900 tracking-tight leading-tight">
                {{ $settings['hero_headline'] ?? 'Get Premium Digital Access In Minutes.' }}
            </h1>

            <!-- Subheadline -->
            <p class="text-sm sm:text-base text-slate-500 max-w-2xl mx-auto leading-relaxed">
                {{ $settings['hero_subheadline'] ?? 'Browse verified subscriptions, software licenses and digital tools. Instant digital delivery with 30-day customer warranty.' }}
            </p>

            <!-- Wide Pill-Shaped Search Bar -->
            <div class="pt-2 max-w-xl mx-auto">
                <form method="GET" action="{{ route('store.index') }}" class="relative flex items-center bg-white border border-slate-300 rounded-full p-1.5 shadow-sm hover:border-slate-400 focus-within:border-cyan-500 focus-within:ring-2 focus-within:ring-cyan-500/20 transition-all">
                    <div class="pl-3.5 pr-2 text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search products, software, subscriptions..." class="w-full bg-transparent text-xs sm:text-sm text-slate-800 outline-none pr-3">
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs px-5 py-2.5 rounded-full transition-colors shrink-0">
                        Search
                    </button>
                </form>
            </div>

            <!-- Category Chips Row -->
            <div class="pt-4 flex flex-wrap items-center justify-center gap-2">
                <a href="{{ route('store.index') }}" class="px-3.5 py-1.5 rounded-full text-xs font-semibold border transition-all {{ !request('category') ? 'bg-slate-900 text-white border-slate-900 shadow-xs' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                    All Products
                </a>
                @foreach($categories as $cat)
                <a href="{{ route('category.show', $cat->slug) }}" class="px-3.5 py-1.5 rounded-full text-xs font-semibold border transition-all flex items-center space-x-1.5 {{ request('category') === $cat->slug ? 'bg-slate-900 text-white border-slate-900 shadow-xs' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                    <span>{{ $cat->name }}</span>
                    @if($cat->products_count > 0)
                        <span class="text-[10px] opacity-75 font-mono">({{ $cat->products_count }})</span>
                    @endif
                </a>
                @endforeach
            </div>

        </div>
    </section>

    <!-- ==========================================
         TRUST STRIP (4 ACCURATE BADGES)
    =========================================== -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white border border-slate-200 rounded-2xl py-3 px-4 shadow-xs grid grid-cols-2 md:grid-cols-4 gap-3 text-xs text-slate-600">
            <div class="flex items-center space-x-2.5 justify-center py-1">
                <span class="text-emerald-500 font-bold text-sm">✓</span>
                <span class="font-semibold text-slate-800">Instant Delivery</span>
            </div>
            <div class="flex items-center space-x-2.5 justify-center py-1">
                <span class="text-cyan-500 font-bold text-sm">✓</span>
                <span class="font-semibold text-slate-800">WhatsApp Helpdesk</span>
            </div>
            <div class="flex items-center space-x-2.5 justify-center py-1">
                <span class="text-blue-500 font-bold text-sm">✓</span>
                <span class="font-semibold text-slate-800">Verified Licenses</span>
            </div>
            <div class="flex items-center space-x-2.5 justify-center py-1">
                <span class="text-purple-500 font-bold text-sm">✓</span>
                <span class="font-semibold text-slate-800">30-Day Warranty</span>
            </div>
        </div>
    </section>

    <!-- ==========================================
         FEATURED BEST SELLING DEALS (3 WIDE CARDS)
    =========================================== -->
    @if($featuredDeals->isNotEmpty())
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        <div class="text-center space-y-1 mb-8">
            <span class="inline-block px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold uppercase tracking-wider font-mono">
                🔥 Hot Offers
            </span>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Best Selling Deals</h2>
            <p class="text-xs sm:text-sm text-slate-500 max-w-lg mx-auto">Top-rated digital subscriptions and licenses chosen by creators.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($featuredDeals as $deal)
            <div class="store-card flex flex-col overflow-hidden">
                <!-- Image Header with Category & Discount -->
                <div class="relative bg-slate-900 aspect-16/10 flex items-center justify-center p-4 overflow-hidden">
                    <img src="{{ $deal->image_url }}" alt="{{ $deal->name }}" class="max-h-full max-w-full object-contain hover:scale-105 transition-transform duration-300">
                    
                    @if($deal->category)
                    <span class="absolute top-3 left-3 bg-slate-900/80 backdrop-blur-md text-white text-[10px] font-bold px-2.5 py-1 rounded-lg border border-slate-700">
                        {{ $deal->category->name }}
                    </span>
                    @endif

                    @if($deal->hasDiscount())
                    <span class="absolute top-3 right-3 bg-rose-500 text-white text-[10px] font-black px-2 py-0.5 rounded-md shadow-sm">
                        SAVE {{ $deal->savings_percentage }}%
                    </span>
                    @endif
                </div>

                <!-- Content Area -->
                <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <!-- Chip -->
                        <div class="flex items-center space-x-2">
                            <span class="chip-pill bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ $deal->duration }}
                            </span>
                            @if($deal->badge_text)
                            <span class="chip-pill bg-purple-50 text-purple-700 border border-purple-200">
                                {{ $deal->badge_text }}
                            </span>
                            @endif
                        </div>

                        <!-- Title -->
                        <h3 class="font-bold text-slate-900 text-base leading-snug line-clamp-2">
                            <a href="{{ route('products.show', $deal->slug) }}" class="hover:text-cyan-600 transition-colors">
                                {{ $deal->name }}
                            </a>
                        </h3>

                        <!-- Short Description -->
                        <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                            {{ $deal->short_description ?: $deal->description }}
                        </p>
                    </div>

                    <!-- Price & CTA -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                        <div>
                            <div class="text-lg font-black text-slate-900 font-mono leading-none">
                                ₹{{ number_format($deal->effective_price, 0) }}
                            </div>
                            @if($deal->hasDiscount())
                                <div class="text-[11px] text-slate-400 line-through font-mono mt-0.5">
                                    ₹{{ number_format($deal->price, 0) }}
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center space-x-2">
                            <a href="{{ route('products.show', $deal->slug) }}" class="btn-store-cyan px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- ==========================================
         FULL CATALOG SECTION (4 COLUMNS)
    =========================================== -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        
        <!-- Section Header with Count & Sort -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-6 border-b border-slate-200 pb-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                    {{ $selectedCategory ? $selectedCategory->name : 'Complete Digital Catalog' }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Showing {{ $products->total() }} available digital products
                </p>
            </div>

            <!-- Sort dropdown -->
            <form method="GET" action="{{ url()->current() }}" class="flex items-center space-x-2">
                @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
                @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
                
                <label for="sort_select" class="text-xs text-slate-500 font-medium whitespace-nowrap">Sort By:</label>
                <select id="sort_select" name="sort" onchange="this.form.submit()" class="bg-white border border-slate-300 rounded-xl px-3 py-1.5 text-xs text-slate-800 font-medium focus:border-cyan-500 outline-none">
                    <option value="featured" {{ request('sort') === 'featured' ? 'selected' : '' }}>Featured</option>
                    <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                    <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest</option>
                </select>
            </form>
        </div>

        <!-- 4-Column Catalog Grid -->
        @if($products->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($products as $product)
            @php
                $itemWhatsapp = (new \App\Services\WhatsApp\WhatsAppService())->generateCheckoutUrl($product);
            @endphp
            <div class="store-card flex flex-col justify-between overflow-hidden">
                <!-- Image Header with Category & Badge -->
                <div class="relative bg-slate-900 aspect-square flex items-center justify-center p-3 overflow-hidden">
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="max-h-full max-w-full object-contain hover:scale-105 transition-transform duration-300">
                    
                    @if($product->category)
                    <span class="absolute top-2.5 left-2.5 bg-slate-900/80 backdrop-blur-md text-white text-[10px] font-bold px-2 py-0.5 rounded-md border border-slate-700">
                        {{ $product->category->name }}
                    </span>
                    @endif

                    @if($product->hasDiscount())
                    <span class="absolute top-2.5 right-2.5 bg-rose-500 text-white text-[10px] font-black px-1.5 py-0.5 rounded shadow-sm">
                        -{{ $product->savings_percentage }}%
                    </span>
                    @endif
                </div>

                <!-- Card Body -->
                <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                    <div class="space-y-1.5">
                        <!-- Chips row -->
                        <div class="flex items-center space-x-1.5 flex-wrap gap-1">
                            <span class="chip-pill bg-slate-100 text-slate-700 font-mono text-[10px]">
                                {{ $product->duration }}
                            </span>
                            @if($product->inventory_type === 'unlimited')
                                <span class="chip-pill bg-emerald-50 text-emerald-700 text-[10px]">Instant</span>
                            @endif
                        </div>

                        <!-- Product Title -->
                        <h3 class="font-bold text-slate-900 text-sm leading-snug line-clamp-2">
                            <a href="{{ route('products.show', $product->slug) }}" class="hover:text-cyan-600 transition-colors">
                                {{ $product->name }}
                            </a>
                        </h3>

                        <!-- Short Description -->
                        <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed">
                            {{ $product->short_description ?: $product->description }}
                        </p>
                    </div>

                    <!-- Price & Actions -->
                    <div class="pt-3 border-t border-slate-100 space-y-2">
                        <div class="flex items-baseline justify-between">
                            <div class="flex items-baseline space-x-2">
                                <span class="text-base font-black text-slate-900 font-mono">
                                    ₹{{ number_format($product->effective_price, 0) }}
                                </span>
                                @if($product->hasDiscount())
                                    <span class="text-[11px] text-slate-400 line-through font-mono">
                                        ₹{{ number_format($product->price, 0) }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-[10px] text-emerald-600 font-bold uppercase">
                                Verified
                            </span>
                        </div>

                        <!-- Button Actions -->
                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <a href="{{ route('products.show', $product->slug) }}" class="btn-store-cyan py-2 rounded-xl text-xs font-bold text-center">
                                View Details
                            </a>
                            <a href="{{ $itemWhatsapp }}" target="_blank" rel="noopener noreferrer" class="btn-store-lime py-2 rounded-xl text-xs font-bold text-center flex items-center justify-center space-x-1">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                <span>WhatsApp</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
        <div class="pt-10 flex justify-center">
            {{ $products->links() }}
        </div>
        @endif

        @else
        <!-- Empty State -->
        <div class="bg-white border border-slate-200 rounded-3xl p-12 text-center max-w-md mx-auto space-y-4">
            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-2xl text-slate-400">
                🔍
            </div>
            <h3 class="text-lg font-bold text-slate-900">No matching products found</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                We couldn't find any products matching your current search or category filter. Try clearing filters or search terms.
            </p>
            <div>
                <a href="{{ route('store.index') }}" class="btn-store-cyan px-5 py-2.5 rounded-full text-xs font-bold inline-block">
                    View All Products
                </a>
            </div>
        </div>
        @endif

    </section>

</div>
@endsection
