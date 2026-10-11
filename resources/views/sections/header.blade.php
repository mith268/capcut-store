@php
    $storeName = $content['store_name'] ?? 'CONVORA';
    $storeBadge = $content['store_badge'] ?? 'STORE';
    $offerSummary = $content['offer_summary'] ?? ($product ? $product->duration : '1 Month • 1200 AI Credits');
    $links = $content['links'] ?? [];
    $isSticky = $design['sticky'] ?? true;
@endphp
<header class="{{ $isSticky ? 'sticky top-0 z-40' : 'relative' }} {{ $isDark ? 'bg-[#080B12]/95 border-b border-cyan-500/20 backdrop-blur-md' : 'bg-white border-b border-slate-200' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between gap-4">
        
        <!-- Brand -->
        <div class="flex items-center space-x-3 shrink-0">
            <a href="{{ route('home') }}" class="flex items-center space-x-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center shadow-[0_0_15px_rgba(0,200,238,0.5)]">
                    <span class="text-white font-black text-base">C</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-lg font-black text-white tracking-wider">{{ $storeName }}</span>
                    @if($storeBadge)
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-widest bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                            {{ $storeBadge }}
                        </span>
                    @endif
                </div>
            </a>
            @if($offerSummary)
                <span class="hidden xl:inline text-xs font-mono text-slate-400 pl-3 border-l border-slate-800">{{ $offerSummary }}</span>
            @endif
        </div>

        <!-- Section Anchors -->
        <nav class="hidden lg:flex items-center space-x-5 text-xs font-semibold text-slate-300">
            @foreach($links as $link)
                <a href="{{ $link['url'] }}" class="hover:text-cyan-300 transition-colors">{{ $link['label'] }}</a>
            @endforeach
            @if($content['show_store_link'] ?? true)
                <a href="{{ route('store.index') }}" class="text-cyan-400 hover:text-cyan-300 flex items-center space-x-1 pl-3 border-l border-slate-800">
                    <span>Catalog Store →</span>
                </a>
            @endif
        </nav>

        <!-- Right CTA -->
        <div class="flex items-center space-x-3">
            <a href="{{ route('order.lookup') }}" class="hidden sm:inline text-xs text-slate-400 hover:text-white font-mono">
                Lookup Order
            </a>
            <a href="{{ $content['cta_target'] ?? '#pricing' }}" class="btn-primary-glow px-4 sm:px-5 py-2 sm:py-2.5 text-xs font-bold uppercase tracking-wider">
                {{ $content['cta_label'] ?? 'GET ACCESS' }}
            </a>
        </div>

    </div>
</header>
