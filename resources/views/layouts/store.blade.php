<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Convora Store — Premium Digital Licenses & Subscriptions')</title>
    <meta name="description" content="@yield('meta_description', 'Browse premium subscriptions, software licenses and digital products with instant digital delivery and 30-day warranty.')">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', 'Convora Store')">
    <meta property="og:description" content="@yield('meta_description', 'Premium Digital Store & Verified Subscriptions')">
    <meta property="og:site_name" content="{{ $currentStoreName ?? 'Convora Store' }}">

    <!-- Google Font Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #F7F9FC;
            color: #20262B;
        }
        .store-card {
            background-color: #FFFFFF;
            border: 1px solid #E3E8EF;
            border-radius: 20px;
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .store-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px -4px rgba(15, 23, 42, 0.1);
            border-color: #CBD5E1;
        }
        .chip-pill {
            display: inline-flex;
            align-items: center;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 9999px;
        }
        .btn-store-lime {
            background-color: #C3FF43;
            color: #0F172A;
            font-weight: 700;
            transition: all 0.2s ease;
        }
        .btn-store-lime:hover {
            background-color: #B0F525;
            box-shadow: 0 0 20px rgba(195, 255, 67, 0.45);
        }
        .btn-store-cyan {
            background: linear-gradient(135deg, #00C8EE, #1677FF);
            color: #FFFFFF;
            font-weight: 700;
            transition: all 0.2s ease;
        }
        .btn-store-cyan:hover {
            opacity: 0.95;
            box-shadow: 0 0 20px rgba(0, 200, 238, 0.45);
        }
    </style>

    @stack('styles')
</head>
<body class="min-h-screen flex flex-col antialiased">

    @php
        $storeName = \App\Models\Setting::get('store_name', 'Convora Store');
        $rawWhatsapp = \App\Models\Setting::get('whatsapp_number', '919990798996');
        $whatsappDigits = preg_replace('/[^0-9]/', '', $rawWhatsapp) ?: '919990798996';
        $whatsappDeskUrl = "https://wa.me/{$whatsappDigits}?text=" . urlencode("Hi {$storeName}, I have a question regarding products on your store.");
    @endphp

    <!-- Top Announcement Bar (Light Mode) -->
    <div class="bg-slate-900 text-white text-[11px] sm:text-xs py-2 px-4 text-center font-medium tracking-wide">
        <div class="max-w-7xl mx-auto flex items-center justify-center space-x-2">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>⚡ Instant Digital Delivery • 30-Day Warranty on All Licenses • 24x7 WhatsApp Helpdesk</span>
        </div>
    </div>

    <!-- White Header Navigation -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between gap-4">
            
            <!-- Left: Brand Logo & Title -->
            <div class="flex items-center space-x-3 shrink-0">
                <a href="{{ route('store.index') }}" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#0F172A] to-[#1E293B] border border-slate-700 flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform">
                        <span class="text-cyan-400 font-black text-lg tracking-tighter">C</span>
                    </div>
                    <div>
                        <div class="text-base sm:text-lg font-black text-slate-900 tracking-tight leading-none">
                            {{ $storeName }}
                        </div>
                        <div class="text-[10px] font-mono text-cyan-600 font-bold uppercase tracking-wider mt-0.5">
                            Verified Digital Access
                        </div>
                    </div>
                </a>
            </div>

            <!-- Center Navigation (Desktop) -->
            <nav class="hidden md:flex items-center space-x-6 text-xs font-semibold text-slate-600">
                <a href="{{ route('store.index') }}" class="hover:text-cyan-600 transition-colors {{ request()->routeIs('store.index') && !request('category') ? 'text-cyan-600 font-bold' : '' }}">
                    Catalog Store
                </a>
                <a href="{{ route('category.show', 'subscriptions') }}" class="hover:text-cyan-600 transition-colors {{ request('category') === 'subscriptions' ? 'text-cyan-600 font-bold' : '' }}">
                    Subscriptions
                </a>
                <a href="{{ route('category.show', 'design-tools') }}" class="hover:text-cyan-600 transition-colors {{ request('category') === 'design-tools' ? 'text-cyan-600 font-bold' : '' }}">
                    Design Tools
                </a>
                <a href="{{ route('category.show', 'software-licenses') }}" class="hover:text-cyan-600 transition-colors {{ request('category') === 'software-licenses' ? 'text-cyan-600 font-bold' : '' }}">
                    Licenses
                </a>
                <a href="{{ route('home') }}" class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors flex items-center space-x-1">
                    <span>⚡ CapCut Special Offer</span>
                </a>
            </nav>

            <!-- Right Actions: Order Lookup & WhatsApp Support -->
            <div class="flex items-center space-x-3 shrink-0">
                <a href="{{ route('order.lookup') }}" class="hidden sm:flex items-center space-x-1.5 text-xs text-slate-600 hover:text-slate-900 font-medium px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 transition-colors">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Lookup Order</span>
                </a>

                <a href="{{ $whatsappDeskUrl }}" target="_blank" rel="noopener noreferrer" class="btn-store-lime px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-full text-xs flex items-center space-x-1.5 shadow-sm">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                    <span>WhatsApp</span>
                </a>
            </div>

        </div>
    </header>

    <!-- Main Store Content Body -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Store Footer -->
    <footer class="bg-white border-t border-slate-200 mt-16 pt-12 pb-8 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Col 1: Store Branding -->
                <div class="space-y-3 md:col-span-1">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-lg bg-slate-900 text-cyan-400 font-black flex items-center justify-center">C</div>
                        <span class="text-base font-black text-slate-900 tracking-tight">{{ $storeName }}</span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Authorized digital products, authentic software licenses, and verified subscription access. Fast digital delivery backed by warranty.
                    </p>
                </div>

                <!-- Col 2: Categories -->
                <div>
                    <h4 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider mb-3 font-mono">Categories</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('category.show', 'subscriptions') }}" class="hover:text-cyan-600 transition-colors">Subscriptions</a></li>
                        <li><a href="{{ route('category.show', 'design-tools') }}" class="hover:text-cyan-600 transition-colors">Design & Video Tools</a></li>
                        <li><a href="{{ route('category.show', 'software-licenses') }}" class="hover:text-cyan-600 transition-colors">Software Licenses</a></li>
                        <li><a href="{{ route('store.index') }}" class="hover:text-cyan-600 transition-colors">Browse All Products</a></li>
                    </ul>
                </div>

                <!-- Col 3: Customer Support -->
                <div>
                    <h4 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider mb-3 font-mono">Assistance</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('order.lookup') }}" class="hover:text-cyan-600 transition-colors">Lookup Order & Credentials</a></li>
                        <li><a href="{{ route('page.support') }}" class="hover:text-cyan-600 transition-colors">Customer Help Desk</a></li>
                        <li><a href="{{ route('page.warranty') }}" class="hover:text-cyan-600 transition-colors">30-Day Warranty Policy</a></li>
                        <li><a href="{{ route('page.refund') }}" class="hover:text-cyan-600 transition-colors">Refund & Replacement Terms</a></li>
                    </ul>
                </div>

                <!-- Col 4: Trust & Contact -->
                <div class="space-y-3">
                    <h4 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider mb-3 font-mono">Contact & Security</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        WhatsApp: <strong class="text-slate-800 font-mono">+{{ $whatsappDigits }}</strong><br>
                        Support Hours: 9:00 AM – 11:00 PM IST<br>
                        Secured with 256-bit SSL encryption.
                    </p>
                    <div class="pt-1">
                        <a href="{{ $whatsappDeskUrl }}" target="_blank" class="inline-flex items-center space-x-1.5 text-xs text-emerald-600 font-bold hover:underline">
                            <span>💬 Chat on WhatsApp Desk →</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright & Legal Links -->
            <div class="border-t border-slate-200 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-400">
                <p>© {{ date('Y') }} {{ $storeName }}. All rights reserved.</p>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('page.terms') }}" class="hover:text-slate-600">Terms of Service</a>
                    <a href="{{ route('page.privacy') }}" class="hover:text-slate-600">Privacy Policy</a>
                    <a href="{{ route('page.refund') }}" class="hover:text-slate-600">Refund Policy</a>
                    <a href="{{ route('page.warranty') }}" class="hover:text-slate-600">Warranty</a>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
