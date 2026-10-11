<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $currentStoreName = $storeSettings['store_name'] ?? 'CapCut Pro';
        $currentStoreBadge = $storeSettings['store_badge'] ?? 'PRO';
        $currentStoreTagline = $storeSettings['store_tagline'] ?? 'Digital License Store';
        $currentStoreLogo = !empty($storeSettings['store_logo']) ? asset($storeSettings['store_logo']) : asset('assets/images/default-store-logo.svg');
    @endphp

    <title>@yield('title', $currentStoreName . ' — CapCut Pro 1 Month (1200 AI Credits) + Canva Pro 1 Year FREE | 30-Day Warranty')</title>
    <meta name="description" content="@yield('meta_description', 'Get CapCut Pro access for 1 month with 1200 AI credits, plus a Canva Pro 1-year bonus. Powerful editing tools, 30-day warranty, and fast digital delivery.')">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('og_title', $currentStoreName . ' — CapCut Pro 1 Month (1200 AI Credits) + Canva Pro 1 Year FREE')">
    <meta property="og:description" content="@yield('og_description', 'Get CapCut Pro access with 1200 AI credits, Canva Pro 1-year bonus, and 30-day warranty.')">
    <meta property="og:image" content="{{ $currentStoreLogo }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', $currentStoreName . ' — CapCut Pro 1 Month (1200 AI Credits) + Canva Pro 1 Year FREE')">
    <meta name="twitter:description" content="@yield('og_description', 'Get CapCut Pro access with 1200 AI credits, Canva Pro 1-year bonus, and 30-day warranty.')">
    <meta name="twitter:image" content="{{ $currentStoreLogo }}">

    <!-- Preload fonts / CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Tailwind / Vite Assets -->
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
        <script defer src="{{ asset('assets/js/app.js') }}"></script>
    @endif

    <!-- Razorpay Checkout SDK -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    @php
        $pixelId = \App\Models\Setting::get('meta_pixel_id') ?: env('META_PIXEL_ID');
    @endphp

    <!-- Meta Pixel Code -->
    @if(!empty($pixelId))
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $pixelId }}');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"
    /></noscript>
    @endif

    @stack('schema')
</head>
<body class="bg-[#05070B] text-slate-100 min-h-screen flex flex-col font-sans selection:bg-[#00D9FF] selection:text-black">

    <!-- Top Announcement Bar -->
    @php
        $announcementEnabled = (\App\Models\Setting::get('announcement_bar_enabled', '1')) === '1';
        $announcementText = \App\Models\Setting::get('announcement_bar_text', '🔥 LIMITED-TIME DIGITAL OFFER • 1200 AI CREDITS + CANVA PRO FREE • 30-DAY WARRANTY');
    @endphp

    @if($announcementEnabled)
    <div class="bg-gradient-to-r from-[#00D9FF] via-[#1677FF] to-[#00D084] text-black font-extrabold text-[11px] sm:text-xs tracking-wider uppercase py-2 px-3 sm:px-4 text-center sticky top-0 z-50 shadow-lg flex items-center justify-center leading-snug">
        <span>{{ $announcementText }}</span>
    </div>
    @endif

    <!-- Header Navigation -->
    <header x-data="{ mobileMenuOpen: false }" class="bg-[#080B12]/80 backdrop-blur-md border-b border-cyan-500/15 sticky {{ $announcementEnabled ? 'top-[33px]' : 'top-0' }} z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-18">
                <!-- Brand / Logo -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2.5 group">
                        <div class="w-10 h-10 rounded-xl overflow-hidden bg-black/40 border border-cyan-400/40 p-1.5 flex items-center justify-center shadow-[0_0_15px_rgba(0,217,255,0.4)] group-hover:scale-105 transition-transform">
                            <img src="{{ $currentStoreLogo }}" alt="{{ $currentStoreName }}" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <div class="font-black text-xl tracking-tight leading-none text-white flex items-center uppercase">
                                <span>{{ $currentStoreName }}</span>
                                @if(!empty($currentStoreBadge))
                                    <span class="ml-2 text-[10px] bg-cyan-950/80 border border-cyan-500/30 text-cyan-300 font-mono px-1.5 py-0.5 rounded uppercase">{{ $currentStoreBadge }}</span>
                                @endif
                            </div>
                            <span class="text-[10px] text-slate-400 block tracking-tight">{{ $currentStoreTagline }}</span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Nav Links -->
                <nav class="hidden lg:flex items-center space-x-6 text-sm font-semibold text-slate-300">
                    <a href="{{ route('home') }}#benefits" class="hover:text-cyan-400 transition-colors">Benefits</a>
                    <a href="{{ route('home') }}#canva-bonus" class="hover:text-purple-400 transition-colors flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5 text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="20 12 20 22 4 22 4 12" stroke-width="2"/><rect x="2" y="7" width="20" height="5" stroke-width="2"/><line x1="12" y1="22" x2="12" y2="7" stroke-width="2"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z" stroke-width="2"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z" stroke-width="2"/></svg>
                        <span>Canva Bonus</span>
                    </a>
                    <a href="{{ route('home') }}#how-it-works" class="hover:text-cyan-400 transition-colors">How It Works</a>
                    <a href="{{ route('home') }}#guidelines" class="hover:text-cyan-400 transition-colors">Guidelines</a>
                    <a href="{{ route('home') }}#warranty" class="hover:text-cyan-400 transition-colors">30-Day Warranty</a>
                    <a href="{{ route('home') }}#faq" class="hover:text-cyan-400 transition-colors">FAQ</a>
                    <a href="{{ route('order.lookup') }}" class="hover:text-cyan-400 text-slate-400 text-xs transition-colors flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Lookup
                    </a>
                </nav>

                <!-- Desktop CTA Button (Opens Inline Checkout Modal) -->
                <div class="hidden sm:flex items-center space-x-3">
                    <button type="button" 
                            onclick="window.dispatchEvent(new CustomEvent('open-checkout-modal'))"
                            class="btn-primary-glow px-5 py-2.5 text-sm font-bold tracking-wide cursor-pointer flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>GET CAPCUT PRO</span>
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>

                <!-- Mobile Hamburger Button -->
                <div class="flex items-center lg:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none" aria-label="Toggle menu">
                        <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Drawer -->
        <div x-show="mobileMenuOpen" x-cloak @click.away="mobileMenuOpen = false" class="lg:hidden bg-[#080B12] border-b border-cyan-500/20 px-4 pt-3 pb-6 space-y-3">
            <a @click="mobileMenuOpen = false" href="{{ route('home') }}#benefits" class="block py-2 text-base font-medium text-slate-300 hover:text-cyan-400">Benefits</a>
            <a @click="mobileMenuOpen = false" href="{{ route('home') }}#canva-bonus" class="py-2 text-base font-medium text-purple-300 hover:text-purple-400 flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="20 12 20 22 4 22 4 12" stroke-width="2"/><rect x="2" y="7" width="20" height="5" stroke-width="2"/><line x1="12" y1="22" x2="12" y2="7" stroke-width="2"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z" stroke-width="2"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z" stroke-width="2"/></svg>
                <span>Canva Pro Bonus</span>
            </a>
            <a @click="mobileMenuOpen = false" href="{{ route('home') }}#how-it-works" class="block py-2 text-base font-medium text-slate-300 hover:text-cyan-400">How It Works</a>
            <a @click="mobileMenuOpen = false" href="{{ route('home') }}#guidelines" class="block py-2 text-base font-medium text-amber-300 hover:text-amber-400">Usage Guidelines</a>
            <a @click="mobileMenuOpen = false" href="{{ route('home') }}#warranty" class="block py-2 text-base font-medium text-slate-300 hover:text-cyan-400">30-Day Warranty</a>
            <a @click="mobileMenuOpen = false" href="{{ route('home') }}#faq" class="block py-2 text-base font-medium text-slate-300 hover:text-cyan-400">FAQ</a>
            <a @click="mobileMenuOpen = false" href="{{ route('order.lookup') }}" class="py-2 text-base font-medium text-slate-400 hover:text-cyan-400 flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span>Check Order Status</span>
            </a>
            <div class="pt-2">
                <button type="button" 
                        @click="mobileMenuOpen = false; window.dispatchEvent(new CustomEvent('open-checkout-modal'))"
                        class="btn-primary-glow w-full py-3 text-center text-sm font-bold flex items-center justify-center space-x-1.5 cursor-pointer">
                    <svg class="w-4 h-4 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>GET CAPCUT PRO NOW</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-[#080B12] border-t border-cyan-500/15 pt-12 pb-24 text-slate-400 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-10">
                <!-- Col 1: Store Bio -->
                <div class="md:col-span-2 space-y-4">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-lg overflow-hidden bg-black/40 border border-cyan-400/40 p-1 flex items-center justify-center">
                            <img src="{{ $currentStoreLogo }}" alt="{{ $currentStoreName }}" class="w-full h-full object-contain">
                        </div>
                        <span class="font-extrabold text-white text-lg tracking-tight uppercase">{{ $currentStoreName }}</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed max-w-md">
                        CapCut Pro 1 Month access with 1200 AI Credits, plus a Canva Pro 1 Year bonus. Dedicated support and backed by our 30-day warranty.
                    </p>
                    <div class="bg-cyan-950/40 border border-cyan-500/20 rounded-lg p-3 text-[11px] text-cyan-200/90 leading-normal max-w-md space-y-1">
                        <p><strong>Legal Notice:</strong> CapCut is a trademark of its respective owner. This store is independent unless otherwise stated.</p>
                        <p>Canva is a trademark of Canva Pty Ltd. The Canva bonus is provided according to the stated offer terms.</p>
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div>
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-4">Quick Navigation</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('home') }}#benefits" class="hover:text-cyan-400 transition-colors">Features & Benefits</a></li>
                        <li><a href="{{ route('home') }}#canva-bonus" class="hover:text-cyan-400 transition-colors">Canva Pro Bonus</a></li>
                        <li><a href="{{ route('home') }}#how-it-works" class="hover:text-cyan-400 transition-colors">How Delivery Works</a></li>
                        <li><a href="{{ route('home') }}#guidelines" class="hover:text-cyan-400 transition-colors">Usage Guidelines</a></li>
                        <li><a href="{{ route('home') }}#warranty" class="hover:text-cyan-400 transition-colors">30-Day Warranty</a></li>
                        <li><a href="{{ route('home') }}#faq" class="hover:text-cyan-400 transition-colors">Frequently Asked Questions</a></li>
                        <li><a href="{{ route('order.lookup') }}" class="hover:text-cyan-400 transition-colors">Customer Order Lookup</a></li>
                    </ul>
                </div>

                <!-- Col 3: Legal & Support -->
                <div>
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-4">Policies & Support</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('page.privacy') }}" class="hover:text-cyan-400 transition-colors">Privacy Policy</a></li>
                        <li><a href="{{ route('page.terms') }}" class="hover:text-cyan-400 transition-colors">Terms & Conditions</a></li>
                        <li><a href="{{ route('page.refund') }}" class="hover:text-cyan-400 transition-colors">Refund Policy</a></li>
                        <li><a href="{{ route('page.warranty') }}" class="hover:text-cyan-400 transition-colors">30-Day Warranty Policy</a></li>
                        <li><a href="{{ route('page.support') }}" class="hover:text-cyan-400 transition-colors">Contact Support</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-800/80 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 space-y-3 sm:space-y-0">
                <p>© {{ date('Y') }} {{ $currentStoreName }}. All rights reserved.</p>
                <p>Secured with 256-bit SSL Encryption • Fast Digital Delivery • 30-Day Warranty</p>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Support Button -->
    @php
        $whatsappSupportUrl = (new \App\Services\WhatsApp\WhatsAppService())->generateCheckoutUrl($product ?? \App\Models\Product::first());
    @endphp
    <div class="fixed bottom-22 sm:bottom-6 right-5 z-40 group" x-data>
        <a href="{{ $whatsappSupportUrl }}" 
           target="_blank" 
           rel="noopener noreferrer"
           @click="window.trackConversionEvent('WhatsAppCheckoutClicked', null, null, { action: 'floating_whatsapp_btn' })"
           class="w-14 h-14 rounded-full bg-gradient-to-tr from-[#00D084] to-[#10D98A] text-white flex items-center justify-center shadow-[0_0_25px_rgba(0,208,132,0.6)] hover:scale-110 active:scale-95 transition-all duration-300 relative group animate-float"
           aria-label="Order or Chat on WhatsApp">
            <!-- Ripple Pulse -->
            <span class="absolute inset-0 rounded-full bg-[#00D084] opacity-40 animate-ping pointer-events-none"></span>
            
            <svg class="w-8 h-8 relative z-10" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.303-.058.116-.087.188-.173.289l-.26.303c-.087.086-.177.18-.076.353.101.173.45 1.428 1.547 2.404.352.314.647.411.821.498.174.086.275.072.376-.044.101-.116.433-.505.549-.679.116-.173.231-.144.39-.086.159.058 1.011.477 1.184.564.173.087.289.13.332.202.043.072.043.419-.101.824z"/>
            </svg>
            
            <!-- Tooltip -->
            <span class="hidden group-hover:block absolute right-16 top-1/2 -translate-y-1/2 bg-slate-900 border border-green-500/30 text-green-300 font-semibold text-xs py-1.5 px-3 rounded-lg whitespace-nowrap shadow-xl">
                Chat on WhatsApp
            </span>
        </a>
    </div>

    @stack('scripts')
</body>
</html>

