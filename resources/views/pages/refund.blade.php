@extends('layouts.app')

@section('title', 'Refund & Replacement Policy — CapCut Pro Store')

@section('content')
<div class="py-14 md:py-20 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="glass-card p-8 sm:p-12 rounded-3xl border-cyan-500/20 space-y-8">
        
        <div>
            <span class="text-xs font-mono font-bold text-cyan-400 uppercase tracking-widest">TRANSPARENT GUARANTEE</span>
            <h1 class="text-3xl sm:text-4xl font-black text-white mt-1">{{ $refund->title ?? 'Refund & Replacement Policy' }}</h1>
            <p class="text-xs text-slate-400 mt-1">Last Updated: {{ date('F Y') }}</p>
        </div>

        <div class="space-y-6 text-sm text-slate-300 leading-relaxed">
            <section class="space-y-2">
                <h2 class="text-lg font-bold text-white">1. Refund Eligibility for Digital Products</h2>
                <p>{{ $refund->refund_conditions ?? 'Because digital credentials are intangible and immediately accessible upon purchase, full monetary refunds are issued strictly when credentials cannot be provided or cannot be activated due to seller technical faults within 24 hours of purchase.' }}</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold text-white">2. 30-Day Replacement Guarantee</h2>
                <p>{{ $refund->replacement_conditions ?? 'If an account experiences technical failure within the 30-day warranty period and adheres to device limits, a 1-to-1 replacement account is provided within 12 hours of reporting to support.' }}</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold text-rose-400">3. Excluded Scenarios</h2>
                <div class="bg-slate-900 p-4 rounded-xl border border-slate-800 text-xs text-slate-300 whitespace-pre-line leading-relaxed">
                    {{ $refund->excluded_conditions ?? "• Change of mind after credentials have been revealed or delivered.\n• Exceeding permitted device limits (more than 1 mobile + 1 laptop).\n• Modifying primary account email or password against seller instructions." }}
                </div>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold text-white">4. Filing a Claim</h2>
                <p>
                    To request assistance or a replacement, contact our WhatsApp support with your Order Number (#CAP-XXXXXX).
                </p>
            </section>
        </div>

    </div>
</div>
@endsection
