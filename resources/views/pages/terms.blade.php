@extends('layouts.app')

@section('title', 'Terms and Conditions — ' . ($storeSettings['store_name'] ?? 'CapCut Pro'))

@section('content')
<div class="py-14 md:py-20 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="glass-card p-8 sm:p-12 rounded-3xl border-cyan-500/20 space-y-8">
        
        <div>
            <span class="text-xs font-mono font-bold text-cyan-400 uppercase tracking-widest">LEGAL DOCUMENTATION</span>
            <h1 class="text-3xl sm:text-4xl font-black text-white mt-1">Terms of Service & Purchase Notice</h1>
            <p class="text-xs text-slate-400 mt-1">Last Updated: {{ date('F Y') }}</p>
        </div>

        <div class="space-y-6 text-sm text-slate-300 leading-relaxed">
            <section class="space-y-2">
                <h2 class="text-lg font-bold text-white">1. Nature of the Product</h2>
                <p>
                    All items sold on this website are intangible digital products and electronic account licenses. Delivery is executed electronically through your customer dashboard immediately after payment verification.
                </p>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold text-white">2. Device and Login Restrictions</h2>
                <div class="p-4 bg-amber-950/30 border border-amber-500/30 rounded-xl text-amber-200 text-xs">
                    <strong>Critical Login Rule:</strong> Access is permitted strictly on a maximum of <strong>1 mobile device + 1 laptop or desktop</strong>. Logging into excess devices triggers automatic platform security defenses and voids your warranty.
                </div>
                <p>
                    Customers must strictly adhere to the following rules:
                </p>
                <ul class="list-disc pl-5 space-y-1 text-slate-400 text-xs">
                    <li>Do not change the primary account email under any circumstances.</li>
                    <li>Do not share account credentials publicly or resell access.</li>
                    <li>If password modifications are allowed, follow explicit instructions provided with delivery.</li>
                </ul>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold text-white">3. Warranty and Replacements</h2>
                <p>
                    Every CapCut Pro 1 Month purchase includes a 30-day digital warranty against unexpected credential failure or access interruption during legitimate authorized use.
                </p>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold text-white">4. Independent Merchant Disclaimer</h2>
                <p class="text-xs bg-slate-900 p-3 rounded-lg border border-slate-800">
                    {{ $settings['legal_disclaimer'] ?? 'Independent digital service provider. Not affiliated with or endorsed by CapCut/ByteDance unless explicitly stated otherwise.' }}
                </p>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold text-white">5. Governing Law</h2>
                <p>
                    Purchases are governed under applicable local electronic commerce regulations. Inquiries may be addressed to our legal support desk via our support portal.
                </p>
            </section>
        </div>

    </div>
</div>
@endsection
