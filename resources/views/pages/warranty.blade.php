@extends('layouts.app')

@section('title', '30-Day Digital Warranty Policy — ' . ($storeSettings['store_name'] ?? 'CapCut Pro'))

@section('content')
<div class="py-14 md:py-20 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="glass-card p-8 sm:p-12 rounded-3xl border-cyan-500/20 space-y-8">
        
        <div>
            <span class="text-xs font-mono font-bold text-cyan-400 uppercase tracking-widest">WARRANTY PROTECTION</span>
            <h1 class="text-3xl sm:text-4xl font-black text-white mt-1">{{ $warranty->title ?? '30-Day Digital Warranty Policy' }}</h1>
            <p class="text-xs text-slate-400 mt-1">Coverage Window: 30 Days From Purchase</p>
        </div>

        <div class="space-y-6 text-sm text-slate-300 leading-relaxed">
            <section class="space-y-2">
                <h2 class="text-lg font-bold text-white">Warranty Overview</h2>
                <p>{{ $warranty->policy_content ?? 'We stand behind the reliability of our digital service. All CapCut Pro 1-month subscriptions are backed by an explicit 30-day warranty against unexpected subscription cancellations, credential invalidation, or access disconnections during legitimate use.' }}</p>
            </section>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                <div class="bg-cyan-950/40 border border-cyan-500/30 rounded-2xl p-6 space-y-2">
                    <h3 class="text-sm font-bold text-cyan-300 uppercase tracking-wider">✓ Eligible Issues</h3>
                    <div class="text-xs text-slate-300 whitespace-pre-line leading-relaxed">
                        {{ $warranty->eligible_issues ?? "• Account credentials become invalid through no fault of customer.\n• Pro features or credit allotment deactivates prematurely.\n• Team subscription seat is revoked by automated platform refresh." }}
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-2">
                    <h3 class="text-sm font-bold text-rose-400 uppercase tracking-wider">✗ Excluded Issues</h3>
                    <div class="text-xs text-slate-400 whitespace-pre-line leading-relaxed">
                        {{ $warranty->excluded_issues ?? "• Changing primary account email.\n• Exceeding permitted device limits (more than 1 mobile + 1 laptop).\n• Suspension resulting from platform community guideline violations.\n• Requests filed after 30 days." }}
                    </div>
                </div>
            </div>

            <section class="space-y-2 pt-4">
                <h2 class="text-lg font-bold text-white">How to Claim</h2>
                <p>
                    Simply message our WhatsApp support desk with your Order Number and a brief description of the issue. Our team provides replacement credentials within 12 hours.
                </p>
            </section>
        </div>

    </div>
</div>
@endsection
