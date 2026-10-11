@extends('layouts.app')

@section('title', 'Order #' . $order->order_number . ' — CapCut Pro Access')

@push('scripts')
@if($shouldFireMetaPurchase)
<script>
    // Fire client-side Meta Purchase event deduplicated with CAPI event_id
    if (typeof fbq === 'function') {
        fbq('track', 'Purchase', {
            content_name: '{{ $order->product->name }}',
            content_ids: ['{{ $order->product->slug }}'],
            content_type: 'product',
            value: {{ (float) $order->total_amount }},
            currency: '{{ $order->currency }}',
            order_id: '{{ $order->order_number }}'
        }, {
            eventID: 'order-{{ $order->id }}-purchase'
        });
    }
</script>
@endif
@endpush

@section('content')
<div class="py-12 md:py-18 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8" 
     x-data="{
         revealed: false,
         loadingReveal: false,
         revealError: '',
         credentials: { email: '', password: '', notes: '' },
         revealCredentials() {
             this.loadingReveal = true;
             this.revealError = '';
             fetch('{{ route('order.reveal', ['uuid' => $order->uuid, 'token' => $order->access_token]) }}', {
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/json',
                     'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                 }
             })
             .then(res => res.json())
             .then(data => {
                 this.loadingReveal = false;
                 if (data.success) {
                     this.credentials = data;
                     this.revealed = true;
                 } else {
                     this.revealError = data.message || 'Unable to reveal credentials.';
                 }
             })
             .catch(() => {
                 this.loadingReveal = false;
                 this.revealError = 'Connection error. Please try again.';
             });
         }
     }">

    <!-- Order Header Card -->
    <div class="glass-card p-6 sm:p-8 rounded-3xl border-cyan-400/30 text-center mb-8 relative overflow-hidden shadow-[0_0_40px_rgba(0,217,255,0.15)]">
        
        @if($order->isPaid())
            <div class="w-16 h-16 rounded-full bg-emerald-500/20 border border-emerald-400/50 text-emerald-300 text-3xl flex items-center justify-center mx-auto mb-4 shadow-[0_0_25px_rgba(0,208,132,0.4)]">
                🎉
            </div>
            <span class="text-xs font-mono font-bold text-emerald-400 uppercase tracking-widest">PAYMENT VERIFIED & COMPLETED</span>
            <h1 class="text-2xl sm:text-3xl font-black text-white mt-1">Your CapCut Pro Order is Ready!</h1>
            <p class="text-xs sm:text-sm text-slate-300 mt-2 max-w-lg mx-auto">
                Thank you, <strong>{{ $order->customer_name }}</strong>. Your Team access with 1200 Credits is provisioned.
            </p>
        @elseif($order->payment_status === 'pending')
            <div class="w-16 h-16 rounded-full bg-amber-500/20 border border-amber-400/50 text-amber-300 text-3xl flex items-center justify-center mx-auto mb-4">
                ⏳
            </div>
            <span class="text-xs font-mono font-bold text-amber-400 uppercase tracking-widest">PAYMENT VERIFICATION IN PROGRESS</span>
            <h1 class="text-2xl sm:text-3xl font-black text-white mt-1">Processing Your Payment</h1>
            <p class="text-xs sm:text-sm text-slate-300 mt-2 max-w-lg mx-auto">
                Please wait a moment while our server confirms your transaction. Do not submit duplicate payments.
            </p>
            <div class="mt-4">
                <button onclick="window.location.reload()" class="bg-slate-800 hover:bg-slate-700 text-cyan-300 border border-cyan-500/30 font-bold px-4 py-2 rounded-xl text-xs">
                    🔄 Refresh Status
                </button>
            </div>
        @else
            <div class="w-16 h-16 rounded-full bg-rose-500/20 border border-rose-400/50 text-rose-300 text-3xl flex items-center justify-center mx-auto mb-4">
                ⚠️
            </div>
            <span class="text-xs font-mono font-bold text-rose-400 uppercase tracking-widest">PAYMENT NOT COMPLETED</span>
            <h1 class="text-2xl sm:text-3xl font-black text-white mt-1">Payment Incomplete</h1>
            <p class="text-xs sm:text-sm text-slate-300 mt-2">
                This order was not finalized. You can retry payment below or complete via WhatsApp.
            </p>
            <div class="mt-4 flex justify-center gap-3">
                <a href="{{ route('checkout.show', ['order_id' => $order->id]) }}" class="btn-primary-glow px-6 py-2.5 text-xs font-bold uppercase">
                    Retry Payment
                </a>
                <a href="{{ $supportWhatsappUrl }}" target="_blank" class="btn-whatsapp-glow px-5 py-2.5 text-xs font-bold uppercase flex items-center space-x-1.5">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.303-.058.116-.087.188-.173.289l-.26.303c-.087.086-.177.18-.076.353.101.173.45 1.428 1.547 2.404.352.314.647.411.821.498.174.086.275.072.376-.044.101-.116.433-.505.549-.679.116-.173.231-.144.39-.086.159.058 1.011.477 1.184.564.173.087.289.13.332.202.043.072.043.419-.101.824z"/></svg>
                    <span>WHATSAPP</span>
                </a>
            </div>
        @endif

        <!-- Order Metadata Chips -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-6 border-t border-slate-800 text-xs">
            <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800/80">
                <span class="text-[10px] text-slate-400 block font-mono">ORDER ID</span>
                <span class="font-bold text-white font-mono">{{ $order->order_number }}</span>
            </div>
            <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800/80">
                <span class="text-[10px] text-slate-400 block font-mono">AMOUNT</span>
                <span class="font-bold text-cyan-300 font-mono">₹{{ number_format($order->total_amount, 2) }}</span>
            </div>
            <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800/80">
                <span class="text-[10px] text-slate-400 block font-mono">STATUS</span>
                <span class="font-bold {{ $order->isPaid() ? 'text-emerald-400' : 'text-amber-400' }} uppercase">
                    {{ $order->payment_status }} ✓
                </span>
            </div>
            <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800/80">
                <span class="text-[10px] text-slate-400 block font-mono">DELIVERY</span>
                <span class="font-bold text-cyan-300 uppercase">
                    {{ $order->delivery_status }}
                </span>
            </div>
        </div>

    </div>

    <!-- Credential Delivery Area -->
    @if($order->isPaid())
    <div class="glass-card p-6 sm:p-8 rounded-3xl border-cyan-500/30 mb-8 space-y-6">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-4">
            <div>
                <span class="text-xs font-mono font-bold text-cyan-400 uppercase tracking-widest">SECURE CREDENTIAL DELIVERY</span>
                <h2 class="text-xl font-bold text-white">Your Access Details</h2>
            </div>
            <span class="text-xs font-mono bg-cyan-950 text-cyan-300 px-2.5 py-1 rounded border border-cyan-500/30 self-start sm:self-auto">
                ENCRYPTED VAULT
            </span>
        </div>

        <!-- Unrevealed State -->
        <div x-show="!revealed" class="text-center py-6 space-y-4">
            <div class="w-12 h-12 rounded-full bg-cyan-950 border border-cyan-500/40 text-cyan-300 flex items-center justify-center mx-auto text-xl">
                🔐
            </div>
            <div>
                <p class="text-sm font-semibold text-white">Your credentials are protected and ready for viewing.</p>
                <p class="text-xs text-slate-400 max-w-md mx-auto mt-1">
                    Click the button below to decrypt and reveal your CapCut Pro access details. Never share these credentials publicly.
                </p>
            </div>
            <div>
                <button type="button" @click="revealCredentials()" :disabled="loadingReveal"
                        class="btn-primary-glow px-8 py-3.5 text-sm font-bold uppercase tracking-wider">
                    <span x-show="!loadingReveal">👉 REVEAL MY LOGIN CREDENTIALS</span>
                    <span x-show="loadingReveal" x-cloak>Decrypting Access...</span>
                </button>
            </div>
            <div x-show="revealError" x-cloak class="text-rose-400 text-xs mt-2" x-text="revealError"></div>
        </div>

        <!-- Revealed State -->
        <div x-show="revealed" x-cloak class="space-y-4">
            
            <!-- Email Box -->
            <div class="bg-slate-900/90 border border-cyan-500/30 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-0.5">
                    <span class="text-[11px] font-mono uppercase text-slate-400 block">ACCOUNT LOGIN EMAIL</span>
                    <span class="text-base font-mono font-bold text-white select-all" x-text="credentials.email"></span>
                </div>
                <button type="button" id="copy-email-btn"
                        @click="window.copyToClipboard(credentials.email, 'copy-email-btn')"
                        class="bg-slate-800 hover:bg-slate-700 border border-cyan-500/30 text-cyan-300 font-bold px-4 py-2 rounded-lg text-xs self-start sm:self-auto transition-colors">
                    Copy Email
                </button>
            </div>

            <!-- Password Box -->
            <div class="bg-slate-900/90 border border-cyan-500/30 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-0.5">
                    <span class="text-[11px] font-mono uppercase text-slate-400 block">ACCOUNT PASSWORD</span>
                    <span class="text-base font-mono font-bold text-cyan-300 select-all" x-text="credentials.password"></span>
                </div>
                <button type="button" id="copy-pwd-btn"
                        @click="window.copyToClipboard(credentials.password, 'copy-pwd-btn')"
                        class="bg-slate-800 hover:bg-slate-700 border border-cyan-500/30 text-cyan-300 font-bold px-4 py-2 rounded-lg text-xs self-start sm:self-auto transition-colors">
                    Copy Password
                </button>
            </div>

            <!-- Optional Custom Notes -->
            <template x-if="credentials.notes">
                <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-4 text-xs text-slate-300 space-y-1">
                    <span class="text-[10px] uppercase font-mono text-cyan-400 font-bold block">SPECIFIC ACCOUNT INSTRUCTIONS</span>
                    <p x-text="credentials.notes" class="leading-relaxed"></p>
                </div>
            </template>

            <!-- Important Security Notice -->
            <div class="p-3 bg-amber-950/20 border border-amber-500/25 rounded-xl text-xs text-amber-200 space-y-1">
                <div class="font-bold flex items-center">
                    <span class="mr-1.5">⚠️</span> Security & Device Limit Reminder
                </div>
                <p class="text-slate-300 leading-relaxed text-[11px]">
                    Login is permitted on maximum <strong>1 mobile device + 1 laptop/desktop</strong>. 
                    <strong>DO NOT CHANGE THE ACCOUNT EMAIL.</strong> Modifying the email revokes the team license and terminates your 30-day warranty immediately.
                </p>
            </div>

        </div>

    </div>

    <!-- Canva Pro Bonus Section -->
    <div class="glass-card p-6 sm:p-8 rounded-3xl border-purple-500/35 mb-8 space-y-4">
        <div class="flex items-center space-x-3">
            <span class="text-2xl">🎁</span>
            <div>
                <h3 class="text-base font-bold text-white">Canva Pro FREE Bonus</h3>
                <p class="text-xs text-purple-200">Included complimentary with your CapCut Pro 1 Month purchase</p>
            </div>
        </div>

        @if($order->canva_bonus_status === 'requested' || $order->canva_bonus_status === 'activated')
            <div class="bg-purple-950/60 border border-purple-500/40 rounded-xl p-4 text-xs text-purple-200 space-y-1">
                <div class="font-bold text-white flex items-center">
                    <span class="text-emerald-400 mr-1.5">✓</span> Canva Pro Bonus Registered
                </div>
                <p>Registered Email: <strong>{{ $order->canva_email }}</strong></p>
                <p class="text-slate-400 text-[11px]">Activation will be provided according to the stated offer terms.</p>
            </div>
        @else
            <div class="bg-slate-900/80 border border-purple-500/30 rounded-xl p-4 space-y-3" x-data="{ canvaEmail: '', canvaClaimed: false, canvaMsg: '', submitting: false }">
                <p class="text-xs text-slate-300">
                    Canva Pro activation requires an eligible email address. Enter your email below to claim:
                </p>
                <div class="flex flex-col sm:flex-row gap-2" x-show="!canvaClaimed">
                    <input type="email" 
                           x-model="canvaEmail" 
                           placeholder="Enter your email address" 
                           class="bg-slate-950 border border-purple-500/40 text-white rounded-xl px-3.5 py-2 text-xs flex-1 focus:outline-none focus:border-purple-400">
                    <button type="button" 
                            :disabled="submitting"
                            @click="
                                if (!canvaEmail.trim()) return;
                                submitting = true;
                                fetch('{{ route('order.canva_bonus', ['uuid' => $order->uuid, 'token' => $order->access_token]) }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                                    },
                                    body: JSON.stringify({ canva_email: canvaEmail.trim() })
                                })
                                .then(res => res.json())
                                .then(data => {
                                    submitting = false;
                                    if (data.success) {
                                        canvaClaimed = true;
                                        canvaMsg = data.message;
                                    } else {
                                        canvaMsg = data.message || 'Error claiming bonus.';
                                    }
                                })
                                .catch(() => {
                                    submitting = false;
                                    canvaMsg = 'Connection error.';
                                });
                            "
                            class="bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs px-5 py-2 rounded-xl cursor-pointer">
                        <span x-text="submitting ? 'Claiming...' : 'Claim Canva Pro Bonus'"></span>
                    </button>
                </div>
                <template x-if="canvaMsg">
                    <p class="text-xs" :class="canvaClaimed ? 'text-emerald-400 font-semibold' : 'text-rose-400'" x-text="canvaMsg"></p>
                </template>
            </div>
        @endif
    </div>
    @endif

    <!-- Warranty & Support Block -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Warranty Card -->
        <div class="glass-card p-6 rounded-2xl space-y-3">
            <div class="flex items-center space-x-2 text-cyan-400 font-bold text-sm">
                <span>🛡️</span>
                <span>30-Day Digital Warranty Active</span>
            </div>
            <p class="text-xs text-slate-300 leading-relaxed">
                Your order is covered under our 30-day replacement policy. If you experience unexpected login errors adhering to device limits, contact support with your Order ID for prompt resolution.
            </p>
            <div class="text-[11px] text-slate-400 font-mono">
                Warranty valid until: {{ $order->paid_at ? $order->paid_at->addDays(30)->format('M d, Y') : 'Active upon payment' }}
            </div>
        </div>

        <!-- Direct Order WhatsApp Support -->
        <div class="glass-card p-6 rounded-2xl space-y-3">
            <div class="flex items-center space-x-2 text-emerald-400 font-bold text-sm">
                <span>📱</span>
                <span>Priority Order Assistance</span>
            </div>
            <p class="text-xs text-slate-300 leading-relaxed">
                Need help logging in or navigating CapCut Pro features? Our dedicated team is available directly on WhatsApp.
            </p>
            <div>
                <a href="{{ $supportWhatsappUrl }}" target="_blank" rel="noopener noreferrer" 
                   class="btn-whatsapp-glow w-full py-2.5 text-xs font-bold uppercase tracking-wider block text-center">
                    CHAT ABOUT ORDER #{{ $order->order_number }}
                </a>
            </div>
        </div>

    </div>

</div>
@endsection
