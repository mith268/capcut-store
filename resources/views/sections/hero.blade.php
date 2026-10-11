@php
    $effectivePrice = $product ? $product->effective_price : 399.00;
    $regularPrice = $product ? $product->price : 1999.00;
    $hasDiscount = $product ? $product->hasDiscount() : true;
    $videoId = $content['video_id'] ?? ($product->video_id ?? 'GeXVI3p-BqE');
    $cleanVideoId = 'GeXVI3p-BqE';
    if (preg_match('/([a-zA-Z0-9_-]{11})/', $videoId, $m)) {
        $cleanVideoId = $m[1];
    }
    $whatsappUrl = (new \App\Services\WhatsApp\WhatsAppService())->generateCheckoutUrl($product ?? \App\Models\Product::first());
@endphp
<section class="relative pt-8 sm:pt-14 pb-12 overflow-hidden px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            
            <!-- Left Column: Copy, Pricing, CTAs (6 cols) -->
            <div class="lg:col-span-6 space-y-6 text-center lg:text-left">
                
                <!-- Kicker Pill -->
                <div>
                    <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-[11px] font-bold font-mono tracking-wider border {{ $isDark ? 'bg-cyan-950/60 text-cyan-300 border-cyan-500/30' : 'bg-cyan-50 text-cyan-800 border-cyan-200' }}">
                        <span>{{ $content['kicker'] ?? '🔥 LIMITED-TIME DIGITAL OFFER' }}</span>
                    </span>
                </div>

                <!-- Big Headline -->
                <div class="space-y-1">
                    <h1 class="text-4xl sm:text-6xl font-black tracking-tight leading-none text-white">
                        {{ $content['title'] ?? ($product->name ?? 'CapCut Pro') }}
                    </h1>
                    @if(!empty($content['highlight_title']))
                    <div class="text-3xl sm:text-5xl font-black bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                        {{ $content['highlight_title'] }}
                    </div>
                    @endif
                </div>

                <!-- Chips Row -->
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2.5 pt-1">
                    @if(!empty($content['chip_credits']))
                    <div class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl border {{ $isDark ? 'bg-slate-900/80 border-cyan-500/30 text-cyan-300' : 'bg-white border-slate-300 text-slate-800' }} text-xs font-bold">
                        <span>⚡</span>
                        <span>{{ $content['chip_credits'] }}</span>
                    </div>
                    @endif
                    @if(!empty($content['chip_bonus']))
                    <div class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl border {{ $isDark ? 'bg-purple-950/50 border-purple-500/40 text-purple-300' : 'bg-purple-50 border-purple-200 text-purple-800' }} text-xs font-bold">
                        <span>🎁</span>
                        <span>{{ $content['chip_bonus'] }}</span>
                    </div>
                    @endif
                </div>

                <!-- Subtitle -->
                <p class="text-xs sm:text-sm text-slate-400 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                    {{ $content['subtitle'] ?? 'Premium video editing access for supported mobile, Windows and Mac devices. Instant digital delivery with 30-day warranty.' }}
                </p>

                <!-- Pricing Display -->
                @if($content['show_price'] ?? true)
                <div class="flex items-baseline justify-center lg:justify-start space-x-3 pt-2">
                    <span class="text-4xl font-black text-cyan-300 font-mono">
                        ₹{{ number_format($effectivePrice, 0) }}
                    </span>
                    @if($hasDiscount)
                    <span class="text-base text-slate-500 line-through font-mono">
                        ₹{{ number_format($regularPrice, 0) }}
                    </span>
                    <span class="px-2 py-0.5 rounded-md bg-rose-500/20 text-rose-300 border border-rose-500/30 text-[11px] font-bold">
                        SAVE ₹{{ number_format($regularPrice - $effectivePrice, 0) }}
                    </span>
                    @endif
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3 pt-2">
                    <button type="button" @click="window.openCheckoutModal ? window.openCheckoutModal() : (window.location.href='{{ route('checkout.show') }}?product={{ $product->id ?? 1 }}')" class="btn-primary-glow w-full sm:w-auto px-8 py-4 text-sm font-black uppercase tracking-wider flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>{{ $content['cta_primary_label'] ?? 'GET CAPCUT PRO' }}</span>
                    </button>

                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-glow w-full sm:w-auto px-6 py-4 text-xs font-bold uppercase tracking-wider flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        <span>{{ $content['cta_whatsapp_label'] ?? 'WHATSAPP' }}</span>
                    </a>
                </div>

            </div>

            <!-- Right Column: Clean YouTube Video Embed (6 cols) -->
            <div class="lg:col-span-6 relative">
                <div class="hero-video-wrapper rounded-2xl overflow-hidden border border-cyan-500/30 shadow-[0_0_40px_rgba(0,200,238,0.2)]">
                    <iframe
                        width="560"
                        height="315"
                        src="https://www.youtube-nocookie.com/embed/{{ $cleanVideoId }}"
                        title="Product Demonstration"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen
                        class="w-full aspect-video">
                    </iframe>
                </div>
            </div>

        </div>
    </div>
</section>
