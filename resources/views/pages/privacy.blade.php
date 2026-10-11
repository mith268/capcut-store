@extends('layouts.app')

@section('title', 'Privacy Policy — CapCut Pro Store')

@section('content')
<div class="py-14 md:py-20 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="glass-card p-8 sm:p-12 rounded-3xl border-cyan-500/20 space-y-8">
        
        <div>
            <span class="text-xs font-mono font-bold text-cyan-400 uppercase tracking-widest">LEGAL DOCUMENTATION</span>
            <h1 class="text-3xl sm:text-4xl font-black text-white mt-1">Privacy Policy</h1>
            <p class="text-xs text-slate-400 mt-1">Last Updated: {{ date('F Y') }}</p>
        </div>

        <div class="space-y-6 text-sm text-slate-300 leading-relaxed">
            <section class="space-y-2">
                <h2 class="text-lg font-bold text-white">1. Information We Collect</h2>
                <p>
                    When you purchase digital products on our website, we collect only information necessary to deliver your digital access and provide customer support:
                </p>
                <ul class="list-disc pl-5 space-y-1 text-slate-400">
                    <li>Customer Full Name</li>
                    <li>Email Address (used exclusively for order delivery and receipt)</li>
                    <li>WhatsApp Phone Number (used for support and delivery verification)</li>
                    <li>Billing Country</li>
                    <li>IP Address and browser user-agent (for anti-fraud and security logs)</li>
                </ul>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold text-white">2. Payment Processing Security</h2>
                <p>
                    All electronic payments are processed through Razorpay's PCI-DSS Level 1 compliant gateway. 
                    <strong>We never store or have access to your credit/debit card numbers, UPI PINs, or banking passwords.</strong> 
                    Our servers receive only cryptographic tokens and verified payment identifiers.
                </p>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold text-white">3. How Your Information is Used</h2>
                <p>We use your information strictly for:</p>
                <ul class="list-disc pl-5 space-y-1 text-slate-400">
                    <li>Transmitting digital credentials and order verification links.</li>
                    <li>Honoring your 30-Day Digital Warranty and resolving login inquiries.</li>
                    <li>Preventing fraudulent orders and duplicate charge attempts.</li>
                </ul>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold text-white">4. Cookies and Analytics</h2>
                <p>
                    We utilize essential cookies for session handling and CSRF protection. If enabled, we utilize Meta Pixel to measure conversion effectiveness. You may disable cookies in your browser settings at any time.
                </p>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold text-white">5. Independent Provider Disclosure</h2>
                <p>
                    {{ $settings['legal_disclaimer'] ?? 'Independent digital service provider. Not affiliated with or endorsed by CapCut/ByteDance unless explicitly stated otherwise.' }}
                </p>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold text-white">6. Contacting Our Data Officer</h2>
                <p>
                    If you have questions regarding your data or wish to request data erasure following your warranty period, please contact: <a href="mailto:{{ $settings['support_email'] ?? 'support@capcutstore.local' }}" class="text-cyan-400 underline">{{ $settings['support_email'] ?? 'support@capcutstore.local' }}</a>.
                </p>
            </section>
        </div>

    </div>
</div>
@endsection
