@extends('layouts.app')

@section('title', 'Complete Your Order — ' . ($storeSettings['store_name'] ?? 'CapCut Pro') . ' — 1 Month (1200 AI Credits)')
@section('meta_description', 'Secure checkout for CapCut Pro 1 Month with 1200 AI Credits + Canva Pro 1 Year Bonus. Fast digital delivery and 30-day warranty.')

@section('content')
<div class="py-10 md:py-16 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8" 
     x-data="checkoutApp({
         unitPrice: {{ $product->effective_price }},
         productId: {{ $product->id }},
         keyId: '{{ $keyId }}'
     })">
    
    <!-- Top Step Bar -->
    <div class="text-center max-w-xl mx-auto mb-10 space-y-2">
        <span class="text-xs font-mono font-bold text-cyan-400 uppercase tracking-widest">SECURE 256-BIT CHECKOUT</span>
        <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Complete Your Order</h1>
        <p class="text-xs sm:text-sm text-slate-400">Digital credentials revealed immediately after payment verification.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left Column: Checkout Form -->
        <div class="lg:col-span-7 space-y-6">
            
            <div class="glass-card p-6 sm:p-8 rounded-2xl space-y-5">
                <h2 class="text-lg font-bold text-white flex items-center">
                    <span class="w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-400 text-xs font-bold flex items-center justify-center mr-2 border border-cyan-500/40">1</span>
                    Customer Information
                </h2>

                <div class="space-y-4">
                    <!-- Full Name -->
                    <div>
                        <label for="customer_name" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                            Full Name <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" id="customer_name" x-model="form.customer_name" placeholder="John Doe"
                               class="w-full bg-slate-900/90 border border-slate-700/80 focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition-all">
                        <span x-show="errors.customer_name" x-text="errors.customer_name" class="text-rose-400 text-xs mt-1 block"></span>
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label for="customer_email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                            Email Address (For Digital Delivery) <span class="text-rose-400">*</span>
                        </label>
                        <input type="email" id="customer_email" x-model="form.customer_email" placeholder="john@example.com"
                               class="w-full bg-slate-900/90 border border-slate-700/80 focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition-all">
                        <span class="text-[11px] text-slate-400 mt-1 block">Order access link will be delivered to this email.</span>
                        <span x-show="errors.customer_email" x-text="errors.customer_email" class="text-rose-400 text-xs mt-1 block"></span>
                    </div>

                    <!-- WhatsApp Number -->
                    <div>
                        <label for="customer_phone" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                            WhatsApp Phone Number <span class="text-rose-400">*</span>
                        </label>
                        <input type="tel" id="customer_phone" x-model="form.customer_phone" placeholder="+91 98765 43210"
                               class="w-full bg-slate-900/90 border border-slate-700/80 focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition-all">
                        <span class="text-[11px] text-slate-400 mt-1 block">Used for swift priority support and account assistance.</span>
                        <span x-show="errors.customer_phone" x-text="errors.customer_phone" class="text-rose-400 text-xs mt-1 block"></span>
                    </div>
                </div>
            </div>

            <!-- Coupon Code Section -->
            <div class="glass-card p-6 rounded-2xl space-y-4">
                <h2 class="text-sm font-bold text-white flex items-center justify-between">
                    <span class="flex items-center">
                        <span class="w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-400 text-xs font-bold flex items-center justify-center mr-2 border border-cyan-500/40">2</span>
                        Have a Coupon Code?
                    </span>
                    <span class="text-xs text-cyan-400 font-mono">Optional</span>
                </h2>

                <div class="flex space-x-2">
                    <input type="text" x-model="couponCode" placeholder="Enter promo code (e.g. PRO50)" uppercase
                           class="flex-1 bg-slate-900/90 border border-slate-700/80 focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 uppercase font-mono outline-none">
                    <button type="button" @click="applyCoupon()" :disabled="couponLoading"
                            class="bg-slate-800 hover:bg-slate-700 border border-cyan-500/30 text-cyan-300 font-bold px-5 py-2.5 rounded-xl text-sm transition-all disabled:opacity-50">
                        <span x-show="!couponLoading">Apply</span>
                        <span x-show="couponLoading" x-cloak>Applying...</span>
                    </button>
                </div>

                <div x-show="couponMessage" x-cloak class="p-3 rounded-lg text-xs font-medium"
                     :class="couponApplied ? 'bg-emerald-950/60 text-emerald-300 border border-emerald-500/30' : 'bg-rose-950/60 text-rose-300 border border-rose-500/30'">
                    <span x-text="couponMessage"></span>
                </div>
            </div>

            <!-- Terms & Important Notice Acceptance -->
            <div class="glass-card p-5 rounded-2xl bg-amber-950/15 border-amber-500/25 space-y-3">
                <label class="flex items-start space-x-3 cursor-pointer">
                    <input type="checkbox" x-model="form.terms" class="w-4 h-4 mt-1 rounded bg-slate-900 border-slate-700 text-cyan-500 focus:ring-cyan-400">
                    <span class="text-xs text-slate-300 leading-relaxed">
                        I agree to the <a href="{{ route('page.terms') }}" target="_blank" class="text-cyan-400 underline">Terms of Service</a>, 
                        <a href="{{ route('page.warranty') }}" target="_blank" class="text-cyan-400 underline">30-Day Warranty Policy</a>, 
                        and understand that account login is strictly limited to <strong>1 mobile device + 1 laptop</strong>. 
                        I acknowledge this store is an independent service provider.
                    </span>
                </label>
                <span x-show="errors.terms" x-text="errors.terms" class="text-rose-400 text-xs mt-1 block"></span>
            </div>

            <!-- Payment Button Actions -->
            <div class="space-y-3">
                <button type="button" @click="initiatePayment()" :disabled="paymentLoading"
                        class="btn-primary-glow w-full py-4 text-base font-black tracking-wide uppercase shadow-[0_0_30px_rgba(0,217,255,0.4)] disabled:opacity-50">
                    <span x-show="!paymentLoading" class="flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span x-text="`PAY SECURELY — ₹${totalAmount.toFixed(0)}`"></span>
                    </span>
                    <span x-show="paymentLoading" x-cloak class="flex items-center justify-center space-x-2">
                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>Opening Secure Gateway...</span>
                    </span>
                </button>

                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" 
                   @click="window.trackConversionEvent('WhatsAppCheckoutClick', null, null, { source: 'checkout_secondary' })"
                   class="btn-whatsapp-glow w-full py-3.5 text-sm font-bold uppercase tracking-wider flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.303-.058.116-.087.188-.173.289l-.26.303c-.087.086-.177.18-.076.353.101.173.45 1.428 1.547 2.404.352.314.647.411.821.498.174.086.275.072.376-.044.101-.116.433-.505.549-.679.116-.173.231-.144.39-.086.159.058 1.011.477 1.184.564.173.087.289.13.332.202.043.072.043.419-.101.824z"/></svg>
                    <span>WHATSAPP</span>
                </a>
            </div>

            <!-- General Error Flash -->
            <div x-show="generalError" x-cloak class="p-4 bg-rose-950/70 border border-rose-500/40 rounded-xl text-xs text-rose-200">
                <span x-text="generalError"></span>
            </div>

        </div>

        <!-- Right Column: Order Summary & Trust Card -->
        <div class="lg:col-span-5 space-y-6">
            
            <div class="glass-card p-6 sm:p-7 rounded-2xl space-y-6 border-cyan-400/30">
                <h2 class="text-base font-bold text-white border-b border-slate-800 pb-3 flex items-center justify-between">
                    <span>Order Summary</span>
                    <span class="text-xs font-mono text-cyan-400 bg-cyan-950 px-2 py-0.5 rounded border border-cyan-500/30">1 ITEM</span>
                </h2>

                <!-- Product Box -->
                <div class="flex items-start space-x-3.5">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-tr from-cyan-950 to-slate-900 border border-cyan-500/30 flex items-center justify-center text-cyan-300 font-black text-sm shrink-0">
                        PRO
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-bold text-white truncate">{{ $product->name }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5">1-Month Seat • 1200 Monthly Credits</p>
                        <span class="inline-block mt-1 text-[10px] bg-emerald-950 text-emerald-300 font-semibold px-2 py-0.2 rounded border border-emerald-500/30">
                            30-Day Warranty Included
                        </span>
                    </div>
                </div>

                <!-- Price Breakdown -->
                <div class="space-y-2.5 pt-3 border-t border-slate-800/80 text-xs sm:text-sm">
                    <div class="flex items-center justify-between text-slate-300">
                        <span>Subtotal</span>
                        <span class="font-mono" x-text="`₹${unitPrice.toFixed(2)}`"></span>
                    </div>

                    <div x-show="discountAmount > 0" x-cloak class="flex items-center justify-between text-emerald-400 font-medium">
                        <span>Coupon Discount</span>
                        <span class="font-mono" x-text="`- ₹${discountAmount.toFixed(2)}`"></span>
                    </div>

                    <div class="flex items-center justify-between text-slate-300">
                        <span>Digital Delivery Fee</span>
                        <span class="text-emerald-400 font-bold uppercase text-xs">FREE</span>
                    </div>

                    <div class="pt-3 border-t border-slate-700/80 flex items-center justify-between">
                        <div>
                            <span class="text-base font-extrabold text-white block">Total Amount</span>
                            <span class="text-[10px] text-slate-400 font-mono">Server Calculated • All Taxes Included</span>
                        </div>
                        <span class="text-2xl font-black text-cyan-300 font-mono" x-text="`₹${totalAmount.toFixed(2)}`"></span>
                    </div>
                </div>

                <!-- Guarantees Grid -->
                <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-4 space-y-2 text-xs text-slate-300">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>Instant digital reveal upon verified payment</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>
                        <span>30-day full replacement guarantee</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="18" height="11" x="3" y="11" rx="2" ry="2" stroke-width="2"/><circle cx="12" cy="16" r="1.5" stroke-width="2"/><path stroke-width="2" stroke-linecap="round" d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <span>Official Razorpay 256-bit encrypted checkout</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Alpine Checkout Controller -->
<script>
function checkoutApp(config) {
    return {
        unitPrice: config.unitPrice,
        discountAmount: 0.00,
        totalAmount: config.unitPrice,
        productId: config.productId,
        keyId: config.keyId,
        couponCode: '',
        couponLoading: false,
        couponApplied: false,
        couponMessage: '',
        paymentLoading: false,
        generalError: '',
        form: {
            customer_name: '',
            customer_email: '',
            customer_phone: '',
            customer_country: 'IN',
            terms: false
        },
        errors: {},

        applyCoupon() {
            if (!this.couponCode.trim()) {
                this.couponMessage = 'Please enter a coupon code.';
                this.couponApplied = false;
                return;
            }

            this.couponLoading = true;
            this.couponMessage = '';

            fetch('{{ route("checkout.calculate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    coupon_code: this.couponCode,
                    customer_email: this.form.customer_email
                })
            })
            .then(res => res.json())
            .then(data => {
                this.couponLoading = false;
                if (data.coupon_applied) {
                    this.couponApplied = true;
                    this.discountAmount = parseFloat(data.discount_amount);
                    this.totalAmount = parseFloat(data.total_amount);
                    this.couponMessage = data.coupon_message;
                } else {
                    this.couponApplied = false;
                    this.discountAmount = 0.00;
                    this.totalAmount = this.unitPrice;
                    this.couponMessage = data.coupon_message || 'Invalid coupon code.';
                }
            })
            .catch(() => {
                this.couponLoading = false;
                this.couponMessage = 'Failed to validate coupon. Please try again.';
            });
        },

        validate() {
            this.errors = {};
            let valid = true;

            if (!this.form.customer_name.trim()) {
                this.errors.customer_name = 'Please enter your full name.';
                valid = false;
            }
            if (!this.form.customer_email.trim() || !this.form.customer_email.includes('@')) {
                this.errors.customer_email = 'Please provide a valid delivery email address.';
                valid = false;
            }
            if (!this.form.customer_phone.trim()) {
                this.errors.customer_phone = 'WhatsApp number is required.';
                valid = false;
            }
            if (!this.form.terms) {
                this.errors.terms = 'You must accept the Important Purchase Notice and Terms.';
                valid = false;
            }

            return valid;
        },

        initiatePayment() {
            if (!this.validate()) {
                window.scrollTo({ top: 150, behavior: 'smooth' });
                return;
            }

            this.paymentLoading = true;
            this.generalError = '';

            fetch('{{ route("checkout.initiate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    customer_name: this.form.customer_name,
                    customer_email: this.form.customer_email,
                    customer_phone: this.form.customer_phone,
                    customer_country: this.form.customer_country,
                    coupon_code: this.couponApplied ? this.couponCode : null,
                    terms: this.form.terms
                })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    this.paymentLoading = false;
                    this.generalError = data.message || 'Unable to start checkout. Please try again.';
                    return;
                }

                // If running in development/mock mode without live keys:
                if (data.is_mock) {
                    console.log('Simulating Razorpay payment in mock mode...');
                    this.verifyMockPayment(data);
                    return;
                }

                // Launch Razorpay standard modal
                const options = {
                    key: data.key_id,
                    amount: data.amount,
                    currency: data.currency,
                    name: "{{ $storeSettings['store_name'] ?? 'CapCut Pro' }}",
                    description: "{{ $product->name ?? 'CapCut Pro — 1 Month (1200 AI Credits)' }}",
                    order_id: data.gateway_order_id,
                    handler: (response) => {
                        this.verifyLivePayment(data.order_id, response);
                    },
                    prefill: {
                        name: data.customer.name,
                        email: data.customer.email,
                        contact: data.customer.contact
                    },
                    theme: {
                        color: "#00D9FF"
                    },
                    modal: {
                        ondismiss: () => {
                            this.paymentLoading = false;
                            fetch('{{ route("payment.failure") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    order_id: data.order_id,
                                    error_code: 'DISMISSED',
                                    error_description: 'Customer dismissed payment gateway.'
                                })
                            });
                        }
                    }
                };

                const rzp = new Razorpay(options);
                rzp.on('payment.failed', (response) => {
                    this.paymentLoading = false;
                    this.generalError = 'Payment failed: ' + (response.error.description || 'Unknown error');
                });
                rzp.open();
            })
            .catch(err => {
                this.paymentLoading = false;
                this.generalError = 'Network error initiating order. Please check your connection.';
            });
        },

        verifyLivePayment(orderId, razorpayResponse) {
            fetch('{{ route("payment.verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    order_id: orderId,
                    razorpay_order_id: razorpayResponse.razorpay_order_id,
                    razorpay_payment_id: razorpayResponse.razorpay_payment_id,
                    razorpay_signature: razorpayResponse.razorpay_signature
                })
            })
            .then(res => res.json())
            .then(resData => {
                if (resData.success) {
                    window.location.href = resData.redirect_url;
                } else {
                    this.paymentLoading = false;
                    this.generalError = resData.message;
                }
            })
            .catch(() => {
                this.paymentLoading = false;
                this.generalError = 'Verification failed. Please check your order page or contact support.';
            });
        },

        verifyMockPayment(data) {
            // Simulated payment verification for testing/demo environments
            fetch('{{ route("payment.verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    order_id: data.order_id,
                    razorpay_order_id: data.gateway_order_id,
                    razorpay_payment_id: 'pay_mock_' + Math.random().toString(36).substring(7),
                    razorpay_signature: 'mock_valid_signature'
                })
            })
            .then(res => res.json())
            .then(resData => {
                if (resData.success) {
                    window.location.href = resData.redirect_url;
                } else {
                    this.paymentLoading = false;
                    this.generalError = resData.message;
                }
            });
        }
    };
}
</script>
@endsection
