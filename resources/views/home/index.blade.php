@extends('layouts.app')

@php
    $storeTitle = $storeSettings['store_name'] ?? 'CapCut Pro';
@endphp

@section('title', $storeTitle . ' — CapCut Pro 1 Month (1200 AI Credits) + Canva Pro 1 Year FREE | 30-Day Warranty')
@section('meta_description', 'Get CapCut Pro access for 1 month with 1200 AI credits, plus a Canva Pro 1-year bonus. Supports mobile, Windows and Mac, with access for up to 2 devices.')

@push('schema')
<script type="application/ld+json">
{!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
<div class="relative overflow-hidden bg-grid-pattern" 
     x-data="{
        checkoutModalOpen: false,
        checkoutStep: 'select_method',
        loadingPayment: false,
        paymentError: '',
        customerName: '',
        customerEmail: '',
        customerPhone: '',
        whatsappError: '',
        emailError: '',
        couponCode: '',
        couponApplied: false,
        couponMessage: '',
        discountAmount: 0,
        unitPrice: {{ (float) $product->effective_price }},
        totalAmount: {{ (float) $product->effective_price }},
        orderId: null,
        orderNumber: '',
        orderUuid: '',
        orderAccessToken: '',
        canvaEmail: '',
        canvaLoading: false,
        canvaSuccess: false,
        canvaMessage: '',
        keyId: '{{ \App\Models\Setting::get('razorpay_key_id') ?: env('RAZORPAY_KEY_ID', 'rzp_test_placeholder') }}',
        checkoutSessionId: '',
        autosaveTimer: null,

        init() {
            // Restore or generate unique session ID
            let storedSession = localStorage.getItem('cp_checkout_session');
            if (!storedSession) {
                storedSession = (typeof crypto !== 'undefined' && crypto.randomUUID) 
                    ? crypto.randomUUID() 
                    : 'sess_' + Math.random().toString(36).substring(2, 11) + '_' + Date.now();
                localStorage.setItem('cp_checkout_session', storedSession);
            }
            this.checkoutSessionId = storedSession;

            // Restore previously typed lead info
            const savedPhone = localStorage.getItem('cp_lead_phone');
            const savedEmail = localStorage.getItem('cp_lead_email');
            const savedName = localStorage.getItem('cp_lead_name');
            if (savedPhone) this.customerPhone = savedPhone;
            if (savedEmail) this.customerEmail = savedEmail;
            if (savedName) this.customerName = savedName;

            if (window.location.search.includes('checkout=1')) {
                this.openModal();
            }
            window.addEventListener('open-checkout-modal', () => {
                this.openModal();
            });

            // Mark abandoned on page unload if visitor interacted but didn't finish
            window.addEventListener('beforeunload', () => {
                if (this.checkoutStep !== 'success' && (this.customerPhone || this.customerEmail || this.customerName)) {
                    this.triggerAutosave('abandoned', true);
                }
            });
        },

        queueAutosave(delay = 600) {
            if (this.autosaveTimer) clearTimeout(this.autosaveTimer);
            this.autosaveTimer = setTimeout(() => {
                this.triggerAutosave('draft');
            }, delay);
        },

        triggerAutosave(status = 'draft', useBeacon = false) {
            // Cache to local storage
            if (this.customerPhone) localStorage.setItem('cp_lead_phone', this.customerPhone);
            if (this.customerEmail) localStorage.setItem('cp_lead_email', this.customerEmail);
            if (this.customerName) localStorage.setItem('cp_lead_name', this.customerName);

            // Never overwrite completed orders with draft/abandoned
            if (this.checkoutStep === 'success') return;

            // Only persist if at least one field has data
            if (!this.customerPhone?.trim() && !this.customerEmail?.trim() && !this.customerName?.trim()) {
                return;
            }

            const payload = {
                checkout_session_id: this.checkoutSessionId,
                customer_phone: this.customerPhone?.trim() || null,
                customer_email: this.customerEmail?.trim() || null,
                customer_name: this.customerName?.trim() || null,
                coupon_code: this.couponApplied ? this.couponCode?.trim() : null,
                checkout_status: status
            };

            const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '';

            fetch('/checkout/autosave', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload),
                keepalive: useBeacon
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.order_id) {
                    this.orderId = data.order_id;
                    this.orderNumber = data.order_number;
                }
            })
            .catch(() => {});
        },

        openModal() {
            this.checkoutModalOpen = true;
            this.paymentError = '';
            this.whatsappError = '';
            this.emailError = '';
            if (typeof window.trackConversionEvent === 'function') {
                window.trackConversionEvent('InitiateCheckout', null, null, {
                    content_name: 'CapCut Pro — 1 Month (1200 AI Credits)',
                    value: this.totalAmount,
                    currency: 'INR'
                });
            }
        },

        closeModal() {
            this.checkoutModalOpen = false;
            if (this.checkoutStep !== 'success' && (this.customerPhone || this.customerEmail || this.customerName)) {
                this.triggerAutosave('abandoned');
            }
        },

        applyCoupon() {
            if (!this.couponCode.trim()) return;
            fetch('{{ route('checkout.calculate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                },
                body: JSON.stringify({ coupon_code: this.couponCode.trim() })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.coupon_applied) {
                    this.couponApplied = true;
                    this.discountAmount = data.discount_amount;
                    this.totalAmount = data.total_amount;
                    this.couponMessage = data.coupon_message;
                } else {
                    this.couponApplied = false;
                    this.discountAmount = 0;
                    this.totalAmount = this.unitPrice;
                    this.couponMessage = data.coupon_message || 'Invalid coupon code.';
                }
            })
            .catch(() => {
                this.couponMessage = 'Could not verify coupon.';
            });
        },

        startRazorpay() {
            this.whatsappError = '';
            this.emailError = '';
            this.paymentError = '';

            const cleanedPhone = this.customerPhone.replace(/[^0-9]/g, '');
            if (!cleanedPhone || cleanedPhone.length < 10) {
                this.whatsappError = 'Please enter a valid WhatsApp number (at least 10 digits).';
                return;
            }

            if (!this.customerEmail.trim() || !this.customerEmail.includes('@')) {
                this.emailError = 'Please enter a valid email address for digital delivery.';
                return;
            }

            this.loadingPayment = true;
            if (typeof window.trackConversionEvent === 'function') {
                window.trackConversionEvent('RazorpayCheckoutOpened', null, null, {
                    value: this.totalAmount,
                    currency: 'INR'
                });
            }

            fetch('/api/create-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    coupon_code: this.couponApplied ? this.couponCode : null,
                    amount: Math.round(this.totalAmount * 100),
                    currency: 'INR',
                    checkout_session_id: this.checkoutSessionId,
                    customer_name: this.customerName.trim() || 'Customer',
                    customer_email: this.customerEmail.trim(),
                    customer_phone: cleanedPhone,
                    create_order_record: true
                })
            })
            .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, data })))
            .then(({ status, ok, data }) => {
                if (!ok || !data.order_id) {
                    this.loadingPayment = false;
                    this.paymentError = data.error || data.message || 'Unable to create Razorpay order.';
                    return;
                }

                this.orderId = data.local_order_id || data.order_id;
                this.orderNumber = data.local_order_number || data.order_id;
                this.orderUuid = data.uuid || '';
                this.orderAccessToken = data.access_token || '';

                // Handle test/mock mode if gateway is mock
                if (data.is_mock) {
                    this.verifyMockPayment(this.orderId, data.order_id);
                    return;
                }

                const options = {
                    key: data.key_id || '{{ env('RAZORPAY_KEY_ID') }}',
                    amount: data.amount,
                    currency: data.currency || 'INR',
                    name: '{{ $storeSettings['store_name'] ?? 'CapCut Pro' }}',
                    description: 'CapCut Pro 1 Month + 1200 AI Credits',
                    order_id: data.order_id,
                    prefill: {
                        name: this.customerName.trim() || 'Customer',
                        email: this.customerEmail.trim(),
                        contact: cleanedPhone
                    },
                    theme: {
                        color: '#00D9FF'
                    },
                    modal: {
                        ondismiss: () => {
                            this.loadingPayment = false;
                            fetch('{{ route('payment.failure') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || ''
                                },
                                body: JSON.stringify({
                                    order_id: this.orderId,
                                    error_code: 'MODAL_DISMISSED',
                                    error_description: 'Customer dismissed Razorpay payment checkout.'
                                })
                            }).catch(() => {});
                        }
                    },
                    handler: (response) => {
                        this.verifyRealPayment(response);
                    }
                };

                const rzp = new Razorpay(options);
                rzp.on('payment.failed', (resp) => {
                    this.loadingPayment = false;
                    this.checkoutStep = 'failed';
                    this.paymentError = resp.error?.description || 'Payment was declined by your bank/provider.';
                });
                rzp.open();
            })
            .catch(err => {
                this.loadingPayment = false;
                this.paymentError = 'Connection error. Please try again.';
            });
        },

        verifyRealPayment(response) {
            this.loadingPayment = true;
            fetch('/api/verify-payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    order_id: this.orderId,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_signature: response.razorpay_signature
                })
            })
            .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, data })))
            .then(({ status, ok, data: verifyData }) => {
                this.loadingPayment = false;
                if (ok && verifyData.success) {
                    this.checkoutStep = 'success';
                    if (verifyData.uuid) this.orderUuid = verifyData.uuid;
                    if (verifyData.access_token) this.orderAccessToken = verifyData.access_token;

                    if (typeof window.trackConversionEvent === 'function') {
                        window.trackConversionEvent('Purchase', 'order-' + this.orderId + '-purchase', this.orderId, {
                            value: this.totalAmount,
                            currency: 'INR',
                            order_id: this.orderNumber
                        });
                        window.trackConversionEvent('PaymentSuccess', null, this.orderId);
                    }
                } else {
                    this.checkoutStep = 'failed';
                    this.paymentError = verifyData.error || verifyData.message || 'Signature verification could not be confirmed.';
                }
            })
            .catch(() => {
                this.loadingPayment = false;
                this.checkoutStep = 'failed';
                this.paymentError = 'Server verification check failed. Please refresh your order page shortly.';
            });
        },

        verifyMockPayment(orderId, gatewayOrderId) {
            fetch('{{ route('payment.verify') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                },
                body: JSON.stringify({
                    order_id: orderId,
                    razorpay_order_id: gatewayOrderId,
                    razorpay_payment_id: 'pay_mock_' + Date.now(),
                    razorpay_signature: 'mock_valid_signature'
                })
            })
            .then(res => res.json())
            .then(verifyData => {
                this.loadingPayment = false;
                if (verifyData.success) {
                    this.checkoutStep = 'success';
                } else {
                    this.checkoutStep = 'failed';
                    this.paymentError = verifyData.message || 'Verification failed.';
                }
            })
            .catch(() => {
                this.loadingPayment = false;
                this.checkoutStep = 'failed';
                this.paymentError = 'Error confirming test payment.';
            });
        },

        submitCanvaBonus(skip = false) {
            if (!skip && !this.canvaEmail.trim()) {
                this.canvaMessage = 'Please enter an eligible email address.';
                return;
            }
            this.canvaLoading = true;
            this.canvaMessage = '';

            fetch('{{ route('checkout.canva_bonus') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                },
                body: JSON.stringify({
                    order_id: this.orderId,
                    canva_email: skip ? null : this.canvaEmail.trim(),
                    skip: skip
                })
            })
            .then(res => res.json())
            .then(data => {
                this.canvaLoading = false;
                if (data.success) {
                    this.canvaSuccess = true;
                    this.canvaMessage = data.message;
                    if (!skip && typeof window.trackConversionEvent === 'function') {
                        window.trackConversionEvent('CanvaBonusRequested', null, this.orderId, {
                            email: this.canvaEmail
                        });
                    }
                } else {
                    this.canvaMessage = data.message || 'Unable to register Canva email.';
                }
            })
            .catch(() => {
                this.canvaLoading = false;
                this.canvaMessage = 'Failed to submit Canva bonus request.';
            });
        }
     }">

    <!-- Ambient Glow Orbs -->
    <div class="absolute top-10 left-1/2 -translate-x-1/2 w-[700px] h-[450px] bg-gradient-to-tr from-[#00D9FF]/15 to-[#A855F7]/12 rounded-full blur-[130px] pointer-events-none -z-10"></div>
    <div class="absolute top-[800px] right-0 w-[520px] h-[520px] bg-[#1677FF]/12 rounded-full blur-[140px] pointer-events-none -z-10"></div>
    <div class="absolute top-[2200px] left-[-100px] w-[500px] h-[500px] bg-[#00D084]/10 rounded-full blur-[140px] pointer-events-none -z-10"></div>

    <!-- ==========================================
         HERO SECTION: PRODUCT → BENEFIT → PRICE → CTA → VIDEO
    =========================================== -->
    <section class="relative pt-6 pb-12 md:pt-14 md:pb-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center">
            
            <!-- Hero Left Column: Copy, Badges, Pricing & CTAs -->
            <div class="lg:col-span-6 space-y-5 text-center lg:text-left">
                
                <!-- Minimal Eyebrow -->
                <div class="inline-flex items-center justify-center lg:justify-start">
                    <span class="glass-badge px-3.5 py-1 text-xs font-bold text-cyan-300 flex items-center space-x-2 shadow-[0_0_15px_rgba(0,217,255,0.35)]">
                        <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                        <span class="tracking-wider uppercase">{{ $settings['hero_eyebrow'] ?? 'PREMIUM VIDEO EDITING' }}</span>
                    </span>
                </div>

                <!-- Main Headline & Subheadline (With Decorative Floating Sticker) -->
                <div class="space-y-4">
                    @php
                        $heroMain = 'CapCut Pro';
                        $heroSub = '1 Month';
                        if (!empty($product->hero_title)) {
                            $parts = explode('—', $product->hero_title);
                            $heroMain = trim($parts[0]);
                            $heroSub = isset($parts[1]) ? trim($parts[1]) : '1 Month';
                        }
                    @endphp
                    <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight text-white leading-[1.08]">
                        {{ $heroMain }} <br class="hidden sm:inline">
                        <span class="text-gradient-cyan">{{ $heroSub }}</span>
                    </h1>

                    <!-- Decorative Floating CapCut Pro Sticker & Key Value Props -->
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 sm:gap-6 pt-2">
                        <!-- Real Promotional Sticker with Neon Glow Pulse (~100-130px on desktop) -->
                        <div class="relative shrink-0 flex items-center justify-center">
                            <img src="{{ asset('assets/images/capcut-pro-sticker.png') }}" 
                                 alt="CapCut Pro Official Sticker" 
                                 class="w-24 h-24 sm:w-28 sm:h-28 lg:w-32 lg:h-32 object-contain capcut-sticker-flicker pointer-events-none select-none drop-shadow-[0_12px_28px_rgba(0,217,255,0.45)]">
                        </div>

                        <!-- Value Props Stack -->
                        <div class="space-y-2 text-center sm:text-left">
                            <div class="inline-flex items-center gap-2 bg-slate-900/90 border border-cyan-500/40 rounded-2xl px-4 py-2 shadow-[0_0_20px_rgba(0,217,255,0.2)]">
                                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <span class="text-lg sm:text-xl font-black text-white">1200 AI Credits Included</span>
                            </div>
                            <div class="flex items-center justify-center sm:justify-start gap-2 text-sm sm:text-base font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-400 to-amber-300">
                                <svg class="w-4 h-4 text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="20 12 20 22 4 22 4 12" stroke-width="2"/><rect x="2" y="7" width="20" height="5" stroke-width="2"/><line x1="12" y1="22" x2="12" y2="7" stroke-width="2"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z" stroke-width="2"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z" stroke-width="2"/></svg>
                                <span>Includes Canva Pro 1 Year as a bonus</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supporting Description -->
                <p class="text-sm sm:text-base text-slate-300 max-w-xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    Premium video editing access for supported mobile, Windows and Mac devices.
                </p>

                <!-- Dynamic Pricing Display Area -->
                <div class="inline-flex items-baseline space-x-3 pt-1">
                    <span class="text-3xl sm:text-4xl font-black text-white">₹{{ number_format($product->effective_price, 0) }}</span>
                    @if($product->hasDiscount())
                        <span class="text-lg text-slate-400 line-through">₹{{ number_format($product->price, 0) }}</span>
                        <span class="bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 text-xs font-black px-2.5 py-1 rounded-full uppercase">
                            Save ₹{{ number_format($product->savings, 0) }}
                        </span>
                    @endif
                </div>

                <!-- Primary & Secondary CTAs -->
                <div class="pt-1 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3.5">
                    <button type="button" 
                            @click="openModal()" 
                            class="btn-primary-glow w-full sm:w-auto px-8 py-4 text-base font-extrabold tracking-wide uppercase group shadow-[0_0_28px_rgba(0,217,255,0.45)] cursor-pointer flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5 text-cyan-300 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>GET CAPCUT PRO</span>
                        <svg class="w-5 h-5 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </button>

                    <a href="{{ $whatsappUrl }}" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       @click="window.trackConversionEvent('WhatsAppCheckoutClicked', null, null, { source: 'hero_whatsapp_btn' })"
                       class="btn-whatsapp-glow w-full sm:w-auto px-6 py-4 text-sm font-bold uppercase tracking-wider flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.303-.058.116-.087.188-.173.289l-.26.303c-.087.086-.177.18-.076.353.101.173.45 1.428 1.547 2.404.352.314.647.411.821.498.174.086.275.072.376-.044.101-.116.433-.505.549-.679.116-.173.231-.144.39-.086.159.058 1.011.477 1.184.564.173.087.289.13.332.202.043.072.043.419-.101.824z"/></svg>
                        <span>WHATSAPP</span>
                    </a>
                </div>

                <!-- MOBILE-ONLY: YouTube Video (Clean Embed, No Clutter Below) -->
                <div class="block lg:hidden pt-4 pb-1">
                    <div class="hero-video-wrapper">
                        <iframe
                            width="560"
                            height="315"
                            src="https://www.youtube-nocookie.com/embed/j_PZmYQDJQA?si=vpZnqozaGSsuNix_"
                            title="YouTube video player"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>

            </div>

            <!-- Hero Right Column: YouTube Video Embed (Desktop Only) -->
            <div class="hidden lg:block lg:col-span-6 relative">
                
                <!-- Clean Video Frame -->
                <div class="hero-video-wrapper">
                    <iframe
                        width="560"
                        height="315"
                        src="https://www.youtube-nocookie.com/embed/j_PZmYQDJQA?si=vpZnqozaGSsuNix_"
                        title="YouTube video player"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen>
                    </iframe>
                </div>

            </div>

        </div>
    </section>

    <!-- ==========================================
         SECTION: TUTORIAL STEPS TO LOGIN TO CAPCUT
    =========================================== -->
    <section id="login-tutorial" class="pt-6 pb-12 md:pt-10 md:pb-16 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-cyan-500/15">
        <div id="access-guide" class="text-center max-w-2xl mx-auto mb-8 sm:mb-10 space-y-2.5">
            <!-- Professional Section Eyebrow with Video/Play Icon -->
            <div class="inline-flex items-center space-x-2 bg-cyan-500/15 border border-cyan-400/40 text-cyan-300 text-xs font-black px-3.5 py-1 rounded-full uppercase tracking-wider shadow-[0_0_15px_rgba(0,217,255,0.25)]">
                <svg class="w-3.5 h-3.5 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>LOGIN TUTORIAL</span>
            </div>
            
            <!-- Section Heading (allows natural wrapping on mobile) -->
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white tracking-tight leading-tight">
                Tutorial Steps to Login to CapCut
            </h2>
            
            <!-- Short Supporting Description -->
            <p class="text-xs sm:text-sm text-slate-400 max-w-xl mx-auto leading-relaxed">
                Follow these simple steps to connect, sign in, verify your access, and start editing.
            </p>
        </div>

        <!-- 2x2 Step Cards Grid (Row 1: 01 & 02 | Row 2: 03 & 04) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 mb-5">
            
            <!-- Step 01 -->
            <div class="glass-card glass-card-hover p-6 rounded-2xl relative border-cyan-500/25 flex flex-col items-center text-center space-y-3">
                <span class="w-8 h-8 rounded-full bg-cyan-500/20 border border-cyan-400/50 text-cyan-300 font-black text-xs flex items-center justify-center font-mono shrink-0">01</span>
                <div class="w-12 h-12 rounded-xl bg-cyan-950/80 border border-cyan-500/30 flex items-center justify-center text-cyan-400 shadow-[0_0_15px_rgba(0,217,255,0.2)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>
                </div>
                <h3 class="text-base font-bold text-white tracking-tight">Download &amp; Connect VPN</h3>
                <p class="text-xs text-slate-400 leading-relaxed">Download a VPN application that works on your device and connect to an appropriate server before opening CapCut, where regional access requires it.</p>
            </div>

            <!-- Step 02 -->
            <div class="glass-card glass-card-hover p-6 rounded-2xl relative border-cyan-500/25 flex flex-col items-center text-center space-y-3">
                <span class="w-8 h-8 rounded-full bg-cyan-500/20 border border-cyan-400/50 text-cyan-300 font-black text-xs flex items-center justify-center font-mono shrink-0">02</span>
                <div class="w-12 h-12 rounded-xl bg-cyan-950/80 border border-cyan-500/30 flex items-center justify-center text-cyan-400 shadow-[0_0_15px_rgba(0,217,255,0.2)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                </div>
                <h3 class="text-base font-bold text-white tracking-tight">Download CapCut</h3>
                <p class="text-xs text-slate-400 leading-relaxed">Download the official CapCut application from the official website or appropriate official app source available for your region.</p>
            </div>

            <!-- Step 03 -->
            <div class="glass-card glass-card-hover p-6 rounded-2xl relative border-cyan-500/25 flex flex-col items-center text-center space-y-3">
                <span class="w-8 h-8 rounded-full bg-cyan-500/20 border border-cyan-400/50 text-cyan-300 font-black text-xs flex items-center justify-center font-mono shrink-0">03</span>
                <div class="w-12 h-12 rounded-xl bg-cyan-950/80 border border-cyan-500/30 flex items-center justify-center text-cyan-400 shadow-[0_0_15px_rgba(0,217,255,0.2)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                </div>
                <h3 class="text-base font-bold text-white tracking-tight">Login With Your Provided Account</h3>
                <p class="text-xs text-slate-400 leading-relaxed">Open CapCut and sign in using the email address and password provided with your order.</p>
            </div>

            <!-- Step 04 -->
            <div class="glass-card glass-card-hover p-6 rounded-2xl relative border-cyan-500/25 flex flex-col items-center text-center space-y-3">
                <span class="w-8 h-8 rounded-full bg-cyan-500/20 border border-cyan-400/50 text-cyan-300 font-black text-xs flex items-center justify-center font-mono shrink-0">04</span>
                <div class="w-12 h-12 rounded-xl bg-cyan-950/80 border border-cyan-500/30 flex items-center justify-center text-cyan-400 shadow-[0_0_15px_rgba(0,217,255,0.2)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="10" stroke-width="2"/></svg>
                </div>
                <h3 class="text-base font-bold text-white tracking-tight">Verify CapCut Pro Access</h3>
                <p class="text-xs text-slate-400 leading-relaxed">Confirm that your CapCut Pro access and applicable AI credits are active, then start editing with the available Pro features.</p>
                <span class="block text-[11px] text-slate-400/90 pt-1 border-t border-slate-800/80">Your Canva Pro 1 Year bonus is provided separately according to your order instructions.</span>
            </div>

        </div>

        <!-- Full-Width Card 1: Windows & Mac Desktop Login -->
        <div class="glass-card p-6 sm:p-7 rounded-2xl border-cyan-500/25 space-y-3.5 mb-4">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800/80 pb-3">
                <div class="flex items-center space-x-2.5 text-cyan-300 font-bold text-sm sm:text-base">
                    <div class="w-8 h-8 rounded-lg bg-cyan-950/80 border border-cyan-500/30 flex items-center justify-center text-cyan-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M20 16V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v9m16 0H4m16 0 1.28 2.55a1 1 0 0 1-.9 1.45H3.62a1 1 0 0 1-.9-1.45L4 16"/></svg>
                    </div>
                    <span>Windows &amp; Mac Desktop Login</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-mono text-cyan-300 bg-cyan-950/90 border border-cyan-500/40 px-2.5 py-0.5 rounded flex items-center gap-1">
                        <svg class="w-3 h-3 text-cyan-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="14" height="20" x="5" y="2" rx="2" ry="2" stroke-width="2"/><line x1="11" y1="18" x2="13" y2="18" stroke-width="2" stroke-linecap="round"/></svg>
                        <span>PHONE LOGIN FIRST</span>
                    </span>
                    <span class="text-[10px] font-mono text-purple-300 bg-purple-950/80 border border-purple-500/30 px-2.5 py-0.5 rounded flex items-center gap-1">
                        <svg class="w-3 h-3 text-purple-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M21 21v.01"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/><path d="M3 12h.01"/><path d="M12 3h.01"/><path d="M12 16v.01"/><path d="M16 12h1"/><path d="M21 12v.01"/><path d="M12 21v-1"/></svg>
                        <span>QR CODE LOGIN</span>
                    </span>
                </div>
            </div>

            <!-- Sequence Banner -->
            <div class="bg-black/50 border border-cyan-500/20 rounded-xl px-4 py-2 text-xs sm:text-sm font-mono font-bold text-slate-300 flex flex-wrap items-center gap-2 justify-center sm:justify-start">
                <span class="text-cyan-300">PHONE LOGIN FIRST</span>
                <span class="text-slate-500">→</span>
                <span class="text-slate-200">DESKTOP VPN</span>
                <span class="text-slate-500">→</span>
                <span class="text-cyan-300">QR LOGIN</span>
                <span class="text-slate-500">→</span>
                <span class="text-slate-200">SCAN</span>
                <span class="text-slate-500">→</span>
                <span class="text-emerald-300">DESKTOP ACCESS</span>
            </div>

            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                Complete login on your phone first, then connect VPN on desktop if required and use QR-code login. Open CapCut on your Windows PC or Mac, select the QR-code login option, and scan the QR code using the CapCut mobile app to complete the login.
            </p>
        </div>

        <!-- Full-Width Card 2: Regional Availability Guidance -->
        <div class="glass-card p-6 sm:p-7 rounded-2xl border-cyan-500/25 space-y-3">
            <div class="flex items-center space-x-2.5 text-cyan-300 font-bold text-sm sm:text-base border-b border-slate-800/80 pb-3">
                <div class="w-8 h-8 rounded-lg bg-cyan-950/80 border border-cyan-500/30 flex items-center justify-center text-cyan-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><line x1="2" y1="12" x2="22" y2="12" stroke-width="2"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" stroke-width="2"/></svg>
                </div>
                <span>Regional Availability Guidance</span>
            </div>
            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                Availability can vary by region and device. Follow the regional access method supplied with your order and use the service in accordance with applicable local laws and CapCut's applicable terms.
            </p>
            <div class="text-[11px] text-slate-400 bg-black/40 border border-slate-800/80 rounded-xl p-2.5 flex items-center space-x-2">
                <svg class="w-3.5 h-3.5 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>
                <span>Connect your device to your network before launching CapCut if regional access is needed.</span>
            </div>
        </div>

    </section>

    <!-- ==========================================
         SECTION: SUPPORTED DEVICES (REQUIREMENT 12)
    =========================================== -->
    <section class="py-14 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-cyan-500/15">
        <div class="text-center max-w-3xl mx-auto mb-10 space-y-2">
            <div class="inline-flex items-center space-x-2 bg-amber-500/15 border border-amber-400/40 text-amber-300 text-xs font-black px-3.5 py-1 rounded-full uppercase tracking-wider">
                <svg class="w-3.5 h-3.5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                <span>2 DEVICES ACCESS</span>
            </div>
            <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight">
                Use CapCut Across Your Devices
            </h2>
            <p class="text-sm text-slate-400">
                Seamless editing workflows across your phone, laptop, and desktop computer.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Mobile Card -->
            <div class="glass-card glass-card-hover p-6 sm:p-7 md:p-8 rounded-2xl relative group border-cyan-500/25 text-center md:text-left">
                <div class="w-12 h-12 rounded-xl bg-cyan-950/80 border border-cyan-500/30 flex items-center justify-center text-cyan-400 mx-auto md:mx-0 mb-4 group-hover:scale-110 group-hover:border-cyan-400 transition-all shadow-[0_0_15px_rgba(0,217,255,0.2)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="14" height="20" x="5" y="2" rx="2" ry="2" stroke-width="2"/><line x1="11" y1="18" x2="13" y2="18" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <div class="flex flex-col md:flex-row items-center md:justify-between gap-1.5 md:gap-0 mb-2.5">
                    <h3 class="text-xl font-bold text-white text-center md:text-left">Mobile</h3>
                    <span class="text-[10px] font-mono text-cyan-300 bg-cyan-950/80 border border-cyan-500/30 px-2.5 py-0.5 rounded inline-block text-center">Android + iOS</span>
                </div>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed text-center md:text-left">
                    CapCut mobile access supported where available. Edit on the go with touch controls, instant reel templates, and high-speed cloud sync.
                </p>
            </div>

            <!-- Windows Card -->
            <div class="glass-card glass-card-hover p-6 sm:p-7 md:p-8 rounded-2xl relative group border-cyan-500/25 text-center md:text-left">
                <div class="w-12 h-12 rounded-xl bg-cyan-950/80 border border-cyan-500/30 flex items-center justify-center text-cyan-400 mx-auto md:mx-0 mb-4 group-hover:scale-110 group-hover:border-cyan-400 transition-all shadow-[0_0_15px_rgba(0,217,255,0.2)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M20 16V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v9m16 0H4m16 0 1.28 2.55a1 1 0 0 1-.9 1.45H3.62a1 1 0 0 1-.9-1.45L4 16"/></svg>
                </div>
                <div class="flex flex-col md:flex-row items-center md:justify-between gap-1.5 md:gap-0 mb-2.5">
                    <h3 class="text-xl font-bold text-white text-center md:text-left">Windows</h3>
                    <span class="text-[10px] font-mono text-cyan-300 bg-cyan-950/80 border border-cyan-500/30 px-2.5 py-0.5 rounded inline-block text-center">PC & Laptop</span>
                </div>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed text-center md:text-left">
                    Windows PC / Laptop. Desktop workflow and account login for heavy 4K timelines, multi-track audio, and hardware acceleration.
                </p>
            </div>

            <!-- Mac Card -->
            <div class="glass-card glass-card-hover p-6 sm:p-7 md:p-8 rounded-2xl relative group border-cyan-500/25 text-center md:text-left">
                <div class="w-12 h-12 rounded-xl bg-cyan-950/80 border border-cyan-500/30 flex items-center justify-center text-cyan-400 mx-auto md:mx-0 mb-4 group-hover:scale-110 group-hover:border-cyan-400 transition-all shadow-[0_0_15px_rgba(0,217,255,0.2)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="3" rx="2" stroke-width="2"/><line x1="8" y1="21" x2="16" y2="21" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="17" x2="12" y2="21" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <div class="flex flex-col md:flex-row items-center md:justify-between gap-1.5 md:gap-0 mb-2.5">
                    <h3 class="text-xl font-bold text-white text-center md:text-left">Mac</h3>
                    <span class="text-[10px] font-mono text-cyan-300 bg-cyan-950/80 border border-cyan-500/30 px-2.5 py-0.5 rounded inline-block text-center">Apple Silicon & Intel</span>
                </div>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed text-center md:text-left">
                    MacBook / Mac. Desktop access for supported CapCut macOS versions with fluid metal acceleration and ProRes rendering.
                </p>
            </div>
        </div>
    </section>

    <!-- ==========================================
         SECTION: TRUST & SECURITY REASSURANCE BAR
    =========================================== -->
    <section class="border-y border-cyan-500/15 bg-[#080B12]/80 py-5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 text-center">
                
                <div class="glass-badge py-2 px-3 flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="18" height="11" x="3" y="11" rx="2" ry="2" stroke-width="2"/><circle cx="12" cy="16" r="1.5" stroke-width="2"/><path stroke-width="2" stroke-linecap="round" d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <span class="text-xs font-bold text-slate-200">Secure Payment</span>
                </div>

                <div class="glass-badge py-2 px-3 flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="text-xs font-bold text-slate-200">Fast Delivery</span>
                </div>

                <div class="glass-badge py-2 px-3 flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>
                    <span class="text-xs font-bold text-emerald-300">30-Day Warranty</span>
                </div>

                <div class="glass-badge py-2 px-3 flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4 text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="20 12 20 22 4 22 4 12" stroke-width="2"/><rect x="2" y="7" width="20" height="5" stroke-width="2"/><line x1="12" y1="22" x2="12" y2="7" stroke-width="2"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z" stroke-width="2"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z" stroke-width="2"/></svg>
                    <span class="text-xs font-bold text-purple-300">Canva Pro 1 Year Bonus</span>
                </div>

                <div class="glass-badge py-2 px-3 flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                    <span class="text-xs font-bold text-emerald-300">WhatsApp Support</span>
                </div>

            </div>
        </div>
    </section>

    <!-- ==========================================
         SECTION: OFFER / BENEFITS CARDS
    =========================================== -->
    <section id="benefits" class="py-16 md:py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-14 space-y-3">
            <span class="text-xs font-mono font-bold text-cyan-400 uppercase tracking-widest">PRODUCT CAPABILITIES</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                Everything You Need to <span class="text-gradient-cyan">Create Better Videos</span>
            </h2>
            <p class="text-sm sm:text-base text-slate-400">
                Unlock full access to CapCut Pro tools, 1200 AI Credits allotment, and an exclusive Canva Pro 1-year bonus.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- Card 1: CapCut Pro Access -->
            <div class="glass-card glass-card-hover p-6 rounded-2xl relative group">
                <div class="flex items-center justify-between mb-5">
                    <div class="w-12 h-12 rounded-xl bg-cyan-950/80 border border-cyan-500/30 flex items-center justify-center text-cyan-400 group-hover:scale-110 group-hover:border-cyan-400 transition-all shadow-[0_0_15px_rgba(0,217,255,0.2)]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <span class="text-[10px] bg-cyan-950/90 text-cyan-300 font-mono font-bold px-2 py-0.5 rounded border border-cyan-500/30 uppercase inline-flex items-center gap-1">
                        <svg class="w-3 h-3 text-cyan-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>1 MONTH</span>
                    </span>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">CapCut Pro Access</h3>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">Premium CapCut Pro access for 1 month according to the selected product plan.</p>
            </div>

            <!-- Card 2: 1200 AI Credits -->
            <div class="glass-card glass-card-hover p-6 rounded-2xl relative group">
                <div class="w-12 h-12 rounded-xl bg-cyan-950/80 border border-cyan-500/30 flex items-center justify-center text-cyan-400 mb-5 group-hover:scale-110 group-hover:border-cyan-400 transition-all shadow-[0_0_15px_rgba(0,217,255,0.2)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">1200 AI Credits</h3>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">Includes 1200 AI credits for auto-captions, smart background cutout, AI effects, and avatars.</p>
            </div>

            <!-- Card 3: Premium Editing Tools -->
            <div class="glass-card glass-card-hover p-6 rounded-2xl relative group">
                <div class="w-12 h-12 rounded-xl bg-cyan-950/80 border border-cyan-500/30 flex items-center justify-center text-cyan-400 mb-5 group-hover:scale-110 group-hover:border-cyan-400 transition-all shadow-[0_0_15px_rgba(0,217,255,0.2)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Premium Editing Tools</h3>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">Access all Pro transitions, filters, velocity curves, text animations, and high-bitrate exports.</p>
            </div>

            <!-- Card 4: Canva Pro 1 Year Bonus -->
            <div class="glass-card glass-card-hover p-6 rounded-2xl relative group border-purple-500/40 shadow-[0_0_25px_rgba(168,85,247,0.18)]">
                <div class="flex items-center justify-between mb-5">
                    <div class="w-12 h-12 rounded-xl bg-purple-950/80 border border-purple-500/40 flex items-center justify-center text-purple-300 group-hover:scale-110 transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                    </div>
                    <span class="text-[10px] bg-gradient-to-r from-purple-500 to-pink-500 text-white font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow-[0_0_10px_rgba(168,85,247,0.4)] flex items-center gap-1">
                        <svg class="w-3 h-3 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="20 12 20 22 4 22 4 12" stroke-width="2"/><rect x="2" y="7" width="20" height="5" stroke-width="2"/><line x1="12" y1="22" x2="12" y2="7" stroke-width="2"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z" stroke-width="2"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z" stroke-width="2"/></svg>
                        <span>1 YEAR BONUS</span>
                    </span>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Canva Pro — 1 Year Bonus</h3>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">Receive Canva Pro with 1-year validity as a complimentary bonus after completing your purchase.</p>
            </div>

            <!-- Card 5: 2 Devices Supported -->
            <div class="glass-card glass-card-hover p-6 rounded-2xl relative group">
                <div class="w-12 h-12 rounded-xl bg-cyan-950/80 border border-cyan-500/30 flex items-center justify-center text-cyan-400 mb-5 group-hover:scale-110 transition-all shadow-[0_0_15px_rgba(0,217,255,0.2)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">2 Devices Supported</h3>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">Supports 1 mobile device and 1 laptop/desktop device (Windows & Mac).</p>
            </div>

            <!-- Card 6: 30-Day Warranty -->
            <div class="glass-card glass-card-hover p-6 rounded-2xl relative group">
                <div class="w-12 h-12 rounded-xl bg-emerald-950/80 border border-emerald-500/30 flex items-center justify-center text-emerald-400 mb-5 group-hover:scale-110 transition-all shadow-[0_0_15px_rgba(0,208,132,0.2)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">30-Day Warranty</h3>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">Product support and warranty coverage for 30 days, subject to the applicable product terms.</p>
            </div>

            <!-- Card 7: Instant Digital Delivery -->
            <div class="glass-card glass-card-hover p-6 rounded-2xl relative group md:col-span-2 lg:col-span-1 lg:col-start-2">
                <div class="w-12 h-12 rounded-xl bg-cyan-950/80 border border-cyan-500/30 flex items-center justify-center text-cyan-400 mb-5 group-hover:scale-110 transition-all shadow-[0_0_15px_rgba(0,217,255,0.2)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Instant Digital Delivery</h3>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">Access credentials delivered immediately upon successful payment verification.</p>
            </div>

        </div>

    </section>

    <!-- ==========================================
         SECTION: CANVA PRO BONUS PROMINENT HIGHLIGHT
    =========================================== -->
    <section id="canva-bonus" class="py-16 bg-[#080B12]/90 border-y border-purple-500/25 relative overflow-hidden">
        
        <!-- Purple Ambient Glow -->
        <div class="absolute -right-16 top-1/2 -translate-y-1/2 w-96 h-96 bg-purple-600/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="glass-card p-8 sm:p-12 rounded-3xl border-purple-500/35 relative overflow-hidden shadow-[0_0_50px_rgba(168,85,247,0.2)]">
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    
                    <!-- Left: Canva Pro Sticker Visual -->
                    <div class="lg:col-span-5 flex items-center justify-center relative">
                        <div class="relative w-56 h-56 sm:w-64 sm:h-64 flex items-center justify-center">
                            <!-- Canva Pro Transparent Sticker -->
                            <img src="{{ asset('assets/images/canva-pro-sticker.png') }}" 
                                 alt="Canva Pro 1 Year Bonus" 
                                 class="w-48 h-48 sm:w-56 sm:h-56 object-contain animate-sticker-canva pointer-events-none select-none drop-shadow-[0_15px_35px_rgba(168,85,247,0.55)]">
                        </div>
                    </div>

                    <!-- Right: Bonus Information & Clear Post-Payment Note -->
                    <div class="lg:col-span-7 space-y-4 text-center lg:text-left">
                        <div class="inline-flex items-center space-x-2 text-xs font-bold text-purple-300 uppercase tracking-widest bg-purple-950/80 px-3 py-1 rounded-full border border-purple-500/40">
                            <svg class="w-3.5 h-3.5 text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="20 12 20 22 4 22 4 12" stroke-width="2"/><rect x="2" y="7" width="20" height="5" stroke-width="2"/><line x1="12" y1="22" x2="12" y2="7" stroke-width="2"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z" stroke-width="2"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z" stroke-width="2"/></svg>
                            <span>COMPLIMENTARY UPGRADE</span>
                        </div>

                        <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight flex items-center justify-center lg:justify-start gap-2.5">
                            <svg class="w-7 h-7 text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="20 12 20 22 4 22 4 12" stroke-width="2"/><rect x="2" y="7" width="20" height="5" stroke-width="2"/><line x1="12" y1="22" x2="12" y2="7" stroke-width="2"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z" stroke-width="2"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z" stroke-width="2"/></svg>
                            <span>BONUS: Canva Pro — 1 Year</span>
                        </h2>

                        <p class="text-base sm:text-lg text-purple-200 font-semibold leading-relaxed">
                            Included as a complimentary 1-year bonus with your CapCut Pro purchase (1 Month + 1200 AI Credits).
                        </p>

                        <div class="bg-purple-950/40 border border-purple-500/30 rounded-2xl p-4 sm:p-5 text-xs sm:text-sm text-slate-300 space-y-2">
                            <div class="flex items-center space-x-2 text-amber-300 font-bold">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><line x1="12" y1="16" x2="12" y2="12" stroke-width="2"/><line x1="12" y1="8" x2="12.01" y2="8" stroke-width="2"/></svg>
                                <span>Post-Payment Claim Process:</span>
                            </div>
                            <p class="leading-relaxed font-medium text-white">
                                Want Canva Pro 1 Year FREE? Add your Canva email address after checkout to claim your bonus.
                            </p>
                            <p class="text-xs text-slate-400 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="18" height="11" x="3" y="11" rx="2" ry="2" stroke-width="2"/><circle cx="12" cy="16" r="1.5" stroke-width="2"/><path stroke-width="2" stroke-linecap="round" d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <span>We do NOT ask for your Canva email before payment. The optional claim field appears only after your order is confirmed.</span>
                            </p>
                        </div>

                        <div class="pt-2 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                            <button type="button" 
                                    @click="openModal()" 
                                    class="btn-primary-glow px-8 py-3.5 text-sm font-bold uppercase tracking-wider cursor-pointer flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <span>Get CapCut Pro + Canva Bonus</span>
                            </button>
                            <span class="text-xs text-slate-400">Canva Pro 1 Year • Included 100% Free</span>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

    <!-- ==========================================
         SECTION: PRICING / OFFER CARD
    =========================================== -->
    <section class="py-16 md:py-20 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="glass-card p-8 sm:p-12 rounded-3xl border-cyan-400/40 relative overflow-hidden shadow-[0_0_50px_rgba(0,217,255,0.2)]">
            
            <!-- Free Gift Sticker Badge on Corner -->
            <div class="absolute -top-1 -right-1 w-20 h-20 sm:w-24 sm:h-24 pointer-events-none">
                <img src="{{ asset('assets/images/free-gift-sticker.png') }}" alt="Free Gift Sticker" class="w-full h-full object-contain animate-sticker-gift">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                
                <div class="md:col-span-7 space-y-4">
                    <div class="inline-flex items-center space-x-2">
                        <span class="glass-badge px-3 py-1 text-xs font-bold text-amber-300 border border-amber-400/40 flex items-center space-x-1.5">
                            <svg class="w-3.5 h-3.5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span>LIMITED-TIME OFFER</span>
                        </span>
                        <span class="text-xs font-mono font-bold text-cyan-300 uppercase tracking-widest bg-cyan-950/80 px-3 py-1 rounded-full border border-cyan-500/30">
                            ALL-IN-ONE PACKAGE
                        </span>
                    </div>
                    <h3 class="text-3xl sm:text-4xl font-black text-white">CapCut Pro — 1 Month</h3>
                    <p class="text-base text-cyan-300 font-bold">1200 AI Credits Included</p>
                    <p class="text-sm font-extrabold text-purple-300 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="20 12 20 22 4 22 4 12" stroke-width="2"/><rect x="2" y="7" width="20" height="5" stroke-width="2"/><line x1="12" y1="22" x2="12" y2="7" stroke-width="2"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z" stroke-width="2"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z" stroke-width="2"/></svg>
                        <span>BONUS: Canva Pro — 1 Year</span>
                    </p>
                    
                    <div class="space-y-2.5 text-sm text-slate-300 pt-2 font-medium">
                        <div class="flex items-center space-x-2.5">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M20 6L9 17l-5-5"/></svg>
                            <span>Dedicated monthly allotment of 1200 AI Credits</span>
                        </div>
                        <div class="flex items-center space-x-2.5">
                            <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M20 6L9 17l-5-5"/></svg>
                            <span>2 Devices (Mobile + Windows / Mac Supported)</span>
                        </div>
                        <div class="flex items-center space-x-2.5 text-purple-300 font-bold">
                            <svg class="w-4 h-4 text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="20 12 20 22 4 22 4 12" stroke-width="2"/><rect x="2" y="7" width="20" height="5" stroke-width="2"/><line x1="12" y1="22" x2="12" y2="7" stroke-width="2"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z" stroke-width="2"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z" stroke-width="2"/></svg>
                            <span>Canva Pro 1 Year Bonus Included</span>
                        </div>
                        <div class="flex items-center space-x-2.5">
                            <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>
                            <span>30-Day Warranty subject to usage terms</span>
                        </div>
                        <div class="flex items-center space-x-2.5">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span>Instant Digital Delivery & WhatsApp Support</span>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-5 text-center bg-slate-900/90 border border-cyan-500/30 rounded-2xl p-6 sm:p-8 space-y-4 shadow-xl">
                    <span class="text-xs text-slate-400 uppercase font-mono tracking-wider block">Special Offer Price</span>
                    <div class="flex items-baseline justify-center space-x-2">
                        <span class="text-4xl sm:text-5xl font-black text-white">₹{{ number_format($product->effective_price, 0) }}</span>
                        @if($product->hasDiscount())
                            <span class="text-base text-slate-500 line-through">₹{{ number_format($product->price, 0) }}</span>
                        @endif
                    </div>
                    @if($product->hasDiscount())
                    <div class="inline-block bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 text-xs font-bold px-3 py-1 rounded-full">
                        SAVE ₹{{ number_format($product->savings, 0) }} INSTANTLY
                    </div>
                    @endif

                    <div class="space-y-2 pt-2">
                        <button type="button" 
                                @click="openModal()" 
                                class="btn-primary-glow w-full py-3.5 text-sm font-bold uppercase tracking-wider cursor-pointer flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span>GET CAPCUT PRO NOW</span>
                        </button>
                        <a href="{{ $whatsappUrl }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           @click="window.trackConversionEvent('WhatsAppCheckoutClicked', null, null, { source: 'offer_card' })"
                           class="btn-whatsapp-glow w-full py-3 text-xs font-bold uppercase tracking-wider flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.303-.058.116-.087.188-.173.289l-.26.303c-.087.086-.177.18-.076.353.101.173.45 1.428 1.547 2.404.352.314.647.411.821.498.174.086.275.072.376-.044.101-.116.433-.505.549-.679.116-.173.231-.144.39-.086.159.058 1.011.477 1.184.564.173.087.289.13.332.202.043.072.043.419-.101.824z"/></svg>
                            <span>WHATSAPP</span>
                        </a>
                    </div>
                    <span class="text-[11px] text-slate-400 block pt-1">One-time payment • No hidden auto-renewals</span>
                </div>

            </div>
        </div>

    </section>

    <!-- ==========================================
         SECTION: HOW IT WORKS (5 STEPS) & LOGIN GUIDE (REQUIREMENTS 11 & 25)
    =========================================== -->
    <section id="how-it-works" class="py-16 md:py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-cyan-500/15">
        
        <div class="text-center max-w-3xl mx-auto mb-14 space-y-2">
            <span class="text-xs font-mono font-bold text-cyan-400 uppercase tracking-widest">HOW IT WORKS</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight flex items-center justify-center gap-2.5">
                <svg class="w-7 h-7 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>How It Works</span>
            </h2>
            <p class="text-sm sm:text-base text-slate-400">
                Transparent 5-step process from plan selection to Pro activation.
            </p>
        </div>

        <!-- 5 Steps -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-16">
            <div class="glass-card p-5 rounded-2xl space-y-2">
                <div class="w-8 h-8 rounded-full bg-cyan-500/20 border border-cyan-400/50 text-cyan-300 font-black text-xs flex items-center justify-center">01</div>
                <h4 class="text-sm font-bold text-white">Choose Your Plan</h4>
                <p class="text-xs text-slate-400 leading-relaxed">Select CapCut Pro — 1 Month + 1200 AI Credits.</p>
            </div>
            <div class="glass-card p-5 rounded-2xl space-y-2">
                <div class="w-8 h-8 rounded-full bg-cyan-500/20 border border-cyan-400/50 text-cyan-300 font-black text-xs flex items-center justify-center">02</div>
                <h4 class="text-sm font-bold text-white">Complete Payment</h4>
                <p class="text-xs text-slate-400 leading-relaxed">Pay securely through the integrated Razorpay checkout.</p>
            </div>
            <div class="glass-card p-5 rounded-2xl space-y-2">
                <div class="w-8 h-8 rounded-full bg-cyan-500/20 border border-cyan-400/50 text-cyan-300 font-black text-xs flex items-center justify-center">03</div>
                <h4 class="text-sm font-bold text-white">Receive Your Order</h4>
                <p class="text-xs text-slate-400 leading-relaxed">Instant digital access credentials delivered upon payment confirmation.</p>
            </div>
            <div class="glass-card p-5 rounded-2xl space-y-2">
                <div class="w-8 h-8 rounded-full bg-cyan-500/20 border border-cyan-400/50 text-cyan-300 font-black text-xs flex items-center justify-center">04</div>
                <h4 class="text-sm font-bold text-white">Activate CapCut Pro</h4>
                <p class="text-xs text-slate-400 leading-relaxed">Follow the supplied step-by-step device login instructions.</p>
            </div>
            <div class="glass-card p-5 rounded-2xl space-y-2 border-purple-500/40">
                <div class="w-8 h-8 rounded-full bg-purple-500/20 border border-purple-400/50 text-purple-300 font-black text-xs flex items-center justify-center">05</div>
                <h4 class="text-sm font-bold text-white">Claim Canva Bonus</h4>
                <p class="text-xs text-slate-400 leading-relaxed">After payment, optionally provide your Canva email to claim the 1-Year Bonus.</p>
            </div>
        </div>
    </section>

    <!-- ==========================================
         SECTION: IMPORTANT USAGE GUIDELINES (11 RULES)
    =========================================== -->
    <section id="guidelines" class="py-16 bg-[#080B12]/95 border-y border-amber-500/25">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <div class="text-center space-y-2">
                <span class="text-xs font-mono font-bold text-amber-400 uppercase tracking-widest bg-amber-950/80 px-3 py-1 rounded-full border border-amber-500/30">
                    COMPLIANCE & TERMS
                </span>
                <h2 class="text-3xl font-extrabold text-white flex items-center justify-center gap-2">
                    <svg class="w-7 h-7 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="8" height="4" x="8" y="2" rx="1" ry="1" stroke-width="2"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" stroke-width="2"/><path d="m9 14 2 2 4-4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Important Usage Guidelines</span>
                </h2>
                <p class="text-sm text-amber-200/90 font-medium">
                    CapCut Pro — 1 Month • 2 Devices Supported • Your access is intended for the usage configuration provided with this product.
                </p>
            </div>

            <div class="border border-amber-500/30 bg-amber-950/20 rounded-2xl p-6 sm:p-8 space-y-4">
                <ol class="space-y-3 text-xs sm:text-sm text-slate-300 list-decimal list-inside leading-relaxed">
                    <li><strong class="text-white">Login is limited to:</strong> 1 mobile device and 1 laptop/desktop device (up to 2 devices).</li>
                    <li><strong class="text-white">Do not repeatedly log in and out</strong> across multiple devices.</li>
                    <li><strong class="text-white">Avoid excessive device switching</strong> or repeated login attempts.</li>
                    <li><strong class="text-white">Maximum device/login restrictions</strong> may affect access.</li>
                    <li><strong class="text-white">No warranty is provided</strong> for issues caused by exceeding the stated device/login limitations.</li>
                    <li><strong class="text-white">If access becomes temporarily blocked,</strong> follow the supplied account recovery/reset instructions and allow the recommended waiting period before trying again.</li>
                    <li><strong class="text-white">Do not change the account email.</strong></li>
                    <li><strong class="text-white">Password changes</strong> may be permitted where applicable.</li>
                    <li><strong class="text-white">Do not share account credentials</strong> publicly.</li>
                    <li><strong class="text-white">Do not attempt to add</strong> multiple unauthorized devices.</li>
                    <li><strong class="text-white">Warranty coverage applies</strong> according to the stated 30-day warranty terms.</li>
                </ol>
            </div>

        </div>
    </section>

    <!-- ==========================================
         SECTION: DEDICATED 30-DAY WARRANTY CARD
    =========================================== -->
    <section id="warranty" class="py-16 md:py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="glass-card p-8 sm:p-12 rounded-3xl border-emerald-500/30 relative overflow-hidden shadow-[0_0_40px_rgba(0,208,132,0.15)]">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                
                <div class="lg:col-span-7 space-y-4">
                    <div class="inline-flex items-center space-x-2 text-xs font-bold text-emerald-300 uppercase tracking-widest">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span>OFFICIAL STORE GUARANTEE</span>
                    </div>

                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight flex items-center gap-2.5">
                        <svg class="w-8 h-8 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>
                        <span>30-Day Warranty</span>
                    </h2>

                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                        30-Day Warranty: Product support and warranty coverage for 30 days, subject to the applicable product terms and stated usage conditions.
                    </p>

                    <div class="bg-slate-900/80 border border-slate-800 rounded-xl p-5 space-y-2 text-xs text-slate-300">
                        <h4 class="font-bold text-rose-400 uppercase tracking-wider">Warranty Exclusions:</h4>
                        <p class="text-slate-400 leading-relaxed">
                            Warranty does not cover issues caused by:
                        </p>
                        <ul class="space-y-1 text-slate-400 list-disc list-inside">
                            <li>Exceeding device limits (more than 1 mobile + 1 laptop/desktop)</li>
                            <li>Repeated unauthorized login attempts</li>
                            <li>Account misuse or modifying account email</li>
                            <li>Sharing credentials beyond permitted use</li>
                            <li>Violating the stated usage instructions</li>
                        </ul>
                        <div class="pt-2">
                            <a href="{{ route('page.refund') }}" class="text-cyan-400 hover:underline font-semibold text-xs">
                                View our full Refund & Replacement Policy →
                            </a>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 text-center bg-slate-900/90 border border-emerald-500/30 rounded-2xl p-8 space-y-4">
                    <div class="text-5xl font-black text-emerald-400 tracking-tight">30 DAYS</div>
                    <div class="text-sm font-bold text-white uppercase tracking-wider">REPLACEMENT WARRANTY</div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Swift WhatsApp and ticket claim resolution. Simply message support with your verified Order Number for assistance.
                    </p>
                    <button type="button" 
                            @click="openModal()" 
                            class="btn-primary-glow w-full py-3.5 text-sm font-bold uppercase tracking-wider cursor-pointer flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>Get Protected Access Now →</span>
                    </button>
                </div>

            </div>
        </div>

    </section>

    <!-- ==========================================
         SECTION: FAQ ACCORDION (ALL 11 QUESTIONS)
    =========================================== -->
    <section id="faq" class="py-16 md:py-24 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ activeFaq: null }">
        
        <div class="text-center mb-14 space-y-2">
            <span class="text-xs font-mono font-bold text-cyan-400 uppercase tracking-widest">FREQUENTLY ASKED QUESTIONS</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                Frequently Asked Questions
            </h2>
            <p class="text-sm text-slate-400">Everything you need to know about CapCut Pro, 1200 AI Credits, 30-day warranty, and Canva bonus.</p>
        </div>

        <div class="space-y-3">
            @foreach($faqs as $faq)
            <div class="glass-card rounded-xl overflow-hidden border-cyan-500/20">
                <button 
                    @click="activeFaq = (activeFaq === {{ $faq->id }} ? null : {{ $faq->id }}); if (activeFaq === {{ $faq->id }} && typeof window.trackConversionEvent === 'function') { window.trackConversionEvent('FAQOpened', null, null, { faq_id: {{ $faq->id }} }); }" 
                    type="button" 
                    class="w-full py-4 px-6 text-left flex items-center justify-between text-sm sm:text-base font-bold text-white hover:text-cyan-300 transition-colors focus:outline-none cursor-pointer"
                    :aria-expanded="activeFaq === {{ $faq->id }}">
                    <span>{{ $faq->question }}</span>
                    <svg class="w-5 h-5 text-cyan-400 transition-transform duration-200 shrink-0 ml-3" 
                         :class="{ 'rotate-180': activeFaq === {{ $faq->id }} }" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="activeFaq === {{ $faq->id }}" x-cloak class="px-6 pb-4 pt-1 text-xs sm:text-sm text-slate-300 leading-relaxed border-t border-slate-800/60 bg-slate-950/40">
                    {{ $faq->answer }}
                </div>
            </div>
            @endforeach
        </div>

    </section>

    <!-- ==========================================
         SECTION: FINAL CTA
    =========================================== -->
    <section class="py-16 md:py-24 text-center max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-cyan-950/60 via-slate-900 to-purple-950/60 border border-cyan-500/35 rounded-3xl p-8 sm:p-14 space-y-6 shadow-[0_0_50px_rgba(0,217,255,0.2)]">
            <span class="text-xs font-mono font-bold text-cyan-400 uppercase tracking-widest">START CREATING TODAY</span>
            <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight">
                Ready to Edit Like a Pro?
            </h2>
            <p class="text-sm sm:text-base text-slate-300 max-w-xl mx-auto leading-relaxed">
                Get CapCut Pro for 1 Month with 1200 AI Credits + Canva Pro 1 Year FREE. Supports up to 2 devices.
            </p>
            <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-4">
                <button type="button" 
                        @click="openModal()" 
                        class="btn-primary-glow px-9 py-4 text-base font-extrabold uppercase tracking-wide shadow-[0_0_30px_rgba(0,217,255,0.45)] cursor-pointer flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Get CapCut Pro — ₹{{ number_format($product->effective_price, 0) }}</span>
                </button>
                <a href="{{ $whatsappUrl }}" 
                   target="_blank" 
                   rel="noopener noreferrer" 
                   @click="window.trackConversionEvent('WhatsAppCheckoutClicked', null, null, { source: 'final_cta' })"
                   class="btn-whatsapp-glow px-7 py-4 text-sm font-bold uppercase tracking-wider flex items-center space-x-2">
                    <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.303-.058.116-.087.188-.173.289l-.26.303c-.087.086-.177.18-.076.353.101.173.45 1.428 1.547 2.404.352.314.647.411.821.498.174.086.275.072.376-.044.101-.116.433-.505.549-.679.116-.173.231-.144.39-.086.159.058 1.011.477 1.184.564.173.087.289.13.332.202.043.072.043.419-.101.824z"/></svg>
                    <span>WHATSAPP</span>
                </a>
            </div>
            <div class="text-xs text-slate-400 pt-2 font-medium">
                Secure payment • 30-day warranty • Instant digital delivery
            </div>
        </div>
    </section>

    <!-- ==========================================
         STICKY BOTTOM PURCHASE BAR (REQUIREMENT 22)
    =========================================== -->
    <div class="fixed bottom-0 left-0 right-0 z-30 bg-[#080B12]/95 backdrop-blur-md border-t border-cyan-500/25 px-3 sm:px-6 py-2.5 sm:py-3 pb-safe shadow-[0_-5px_25px_rgba(0,0,0,0.8)]">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-2">
            
            <!-- Left: Product & Price -->
            <div class="min-w-0 flex-1">
                <!-- Desktop title -->
                <div class="hidden sm:flex items-center text-xs sm:text-sm font-bold text-white tracking-tight">
                    <span>CapCut Pro • 1 Month • 1200 AI Credits</span>
                    <span class="text-[11px] text-purple-300 font-semibold ml-2">• Canva Pro 1 Year FREE</span>
                </div>
                <!-- Mobile title -->
                <div class="block sm:hidden text-[11px] font-bold text-white leading-tight truncate">
                    CapCut Pro • 1 Month • 1200 AI Credits
                </div>
                <div class="block sm:hidden text-[10px] text-purple-300 font-semibold leading-tight truncate">
                    Canva Pro 1 Year FREE
                </div>

                <!-- Price Row -->
                <div class="flex items-baseline space-x-1.5 mt-0.5 sm:mt-1">
                    <span class="text-sm sm:text-lg font-black text-cyan-300">₹{{ number_format($product->effective_price, 0) }}</span>
                    @if($product->hasDiscount())
                        <span class="text-[10px] sm:text-xs text-slate-500 line-through">₹{{ number_format($product->price, 0) }}</span>
                    @endif
                </div>
            </div>

            <!-- Right: Actions -->
            <div class="flex items-center space-x-1.5 sm:space-x-3 shrink-0">
                <button type="button" 
                        @click="openModal()" 
                        class="btn-primary-glow px-3 sm:px-6 py-2 sm:py-2.5 text-[11px] sm:text-sm font-extrabold tracking-wide uppercase cursor-pointer whitespace-nowrap shadow-[0_0_15px_rgba(0,217,255,0.4)] flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 text-cyan-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>GET CAPCUT PRO</span>
                </button>
                <a href="{{ $whatsappUrl }}" 
                   target="_blank" 
                   rel="noopener noreferrer" 
                   @click="window.trackConversionEvent('WhatsAppCheckoutClicked', null, null, { source: 'sticky_bar' })"
                   class="btn-whatsapp-glow px-2.5 sm:px-4 py-2 sm:py-2.5 text-[11px] sm:text-xs font-bold uppercase flex items-center space-x-1.5 whitespace-nowrap">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.303-.058.116-.087.188-.173.289l-.26.303c-.087.086-.177.18-.076.353.101.173.45 1.428 1.547 2.404.352.314.647.411.821.498.174.086.275.072.376-.044.101-.116.433-.505.549-.679.116-.173.231-.144.39-.086.159.058 1.011.477 1.184.564.173.087.289.13.332.202.043.072.043.419-.101.824z"/></svg>
                    <span>WHATSAPP</span>
                </a>
            </div>

        </div>
    </div>

    <!-- ==========================================
         INLINE CHECKOUT MODAL (REQUIREMENTS 7, 8, 9, 10)
    =========================================== -->
    <div x-show="checkoutModalOpen" 
         x-cloak 
         @keydown.escape.window="closeModal()" 
         class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6"
         role="dialog" 
         aria-modal="true" 
         aria-labelledby="checkout-modal-title">
        
        <div class="relative w-full max-w-lg glass-card p-6 sm:p-8 rounded-3xl border border-cyan-400/40 shadow-[0_0_50px_rgba(0,217,255,0.25)] text-left"
             @click.away="if (checkoutStep !== 'processing' && !loadingPayment) closeModal()">
            
            <!-- Close Button -->
            <button @click="closeModal()" 
                    type="button" 
                    class="absolute top-5 right-5 text-slate-400 hover:text-white p-1 rounded-lg focus:outline-none cursor-pointer"
                    aria-label="Close modal">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <!-- STEP 1: CHECKOUT DETAILS & PAYMENT -->
            <template x-if="checkoutStep === 'select_method'">
                <div class="space-y-4">
                    
                    <div>
                        <span class="text-[11px] font-mono uppercase tracking-widest text-cyan-400 font-bold">INSTANT SECURE CHECKOUT</span>
                        <h3 id="checkout-modal-title" class="text-xl sm:text-2xl font-black text-white mt-0.5">
                            Complete Your Order
                        </h3>
                        <p class="text-xs text-slate-300 mt-1">
                            CapCut Pro — 1 Month (1200 AI Credits) + Canva Pro 1 Year Bonus
                        </p>
                    </div>

                    <!-- Price Card -->
                    <div class="bg-slate-900/90 border border-cyan-500/25 rounded-2xl p-4 flex items-center justify-between">
                        <div>
                            <span class="text-[11px] text-slate-400 block font-mono">TOTAL DUE</span>
                            <div class="flex items-baseline space-x-2">
                                <span class="text-3xl font-black text-cyan-300" x-text="'₹' + Number(totalAmount).toFixed(0)"></span>
                                <template x-if="discountAmount > 0">
                                    <span class="text-xs text-emerald-400 font-bold" x-text="'(-₹' + Number(discountAmount).toFixed(0) + ')'"></span>
                                </template>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-bold text-emerald-400 flex items-center justify-end gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M20 6L9 17l-5-5"/></svg>
                                <span>30-Day Warranty</span>
                            </span>
                            <span class="text-[11px] text-purple-300 flex items-center justify-end gap-1 mt-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="20 12 20 22 4 22 4 12" stroke-width="2"/><rect x="2" y="7" width="20" height="5" stroke-width="2"/><line x1="12" y1="22" x2="12" y2="7" stroke-width="2"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z" stroke-width="2"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z" stroke-width="2"/></svg>
                                <span>Canva 1 Year Bonus</span>
                            </span>
                        </div>
                    </div>

                    <!-- Customer Contact Details Form (Autosaved continuously to database) -->
                    <div class="space-y-3 pt-1">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-300 mb-1">
                                WhatsApp Number <span class="text-rose-400">*</span>
                            </label>
                            <input type="tel" 
                                   x-model="customerPhone" 
                                   @input="queueAutosave(600)"
                                   @blur="triggerAutosave('draft')"
                                   placeholder="Enter your WhatsApp number (e.g. 9876543210)" 
                                   class="bg-slate-950 border border-cyan-500/30 text-white rounded-xl px-4 py-2.5 text-xs w-full focus:outline-none focus:border-cyan-400 font-mono">
                            <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                                <span>We'll use WhatsApp to send your order/delivery instructions and provide support.</span>
                            </p>
                            <template x-if="whatsappError">
                                <p class="text-[11px] text-rose-400 font-semibold mt-1" x-text="whatsappError"></p>
                            </template>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-300 mb-1">
                                    Email Address <span class="text-rose-400">*</span>
                                </label>
                                <input type="email" 
                                       x-model="customerEmail" 
                                       @input="queueAutosave(600)"
                                       @blur="triggerAutosave('draft')"
                                       placeholder="Your email for access" 
                                       class="bg-slate-950 border border-cyan-500/30 text-white rounded-xl px-3.5 py-2.5 text-xs w-full focus:outline-none focus:border-cyan-400">
                                <template x-if="emailError">
                                    <p class="text-[11px] text-rose-400 font-semibold mt-1" x-text="emailError"></p>
                                </template>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-300 mb-1">
                                    Full Name <span class="text-slate-500">(Optional)</span>
                                </label>
                                <input type="text" 
                                       x-model="customerName" 
                                       @input="queueAutosave(600)"
                                       @blur="triggerAutosave('draft')"
                                       placeholder="Your name" 
                                       class="bg-slate-950 border border-slate-700 text-white rounded-xl px-3.5 py-2.5 text-xs w-full focus:outline-none focus:border-cyan-400">
                            </div>
                        </div>
                    </div>

                    <!-- Optional Coupon Toggle -->
                    <div x-data="{ showCoupon: false }" class="pt-1">
                        <button type="button" @click="showCoupon = !showCoupon" class="text-xs text-cyan-400 hover:underline flex items-center cursor-pointer">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7" stroke-width="2" stroke-linecap="round"/></svg>
                                <span x-text="showCoupon ? '▲ Hide coupon code' : 'Have a coupon code?'"></span>
                            </span>
                        </button>
                        <div x-show="showCoupon" x-cloak class="mt-2 flex space-x-2">
                            <input type="text" 
                                   x-model="couponCode" 
                                   @input="queueAutosave(600)"
                                   placeholder="Enter coupon code" 
                                   class="bg-slate-950 border border-cyan-500/30 text-white rounded-xl px-3 py-2 text-xs w-full focus:outline-none focus:border-cyan-400 uppercase font-mono">
                            <button type="button" 
                                    @click="applyCoupon()" 
                                    class="bg-cyan-500 hover:bg-cyan-400 text-black font-extrabold text-xs px-4 py-2 rounded-xl cursor-pointer">
                                Apply
                            </button>
                        </div>
                        <template x-if="couponMessage">
                            <p class="text-[11px] mt-1.5" :class="couponApplied ? 'text-emerald-400' : 'text-rose-400'" x-text="couponMessage"></p>
                        </template>
                    </div>

                    <!-- Payment Buttons (Requirement 7: Official Razorpay Brand Asset) -->
                    <div class="space-y-3 pt-2">
                        <!-- Official Razorpay Payment Button -->
                        <button type="button" 
                                @click="startRazorpay()" 
                                :disabled="loadingPayment"
                                class="btn-primary-glow w-full py-4 px-5 text-sm font-extrabold uppercase tracking-wide flex items-center justify-between cursor-pointer group">
                            <div class="flex items-center space-x-3 text-left">
                                <div class="h-7 bg-white/10 border border-white/20 rounded-lg px-2.5 py-1 flex items-center justify-center shrink-0">
                                    <img src="{{ asset('assets/images/razorpay-logo.svg') }}" alt="Razorpay Official Logo" class="h-4 w-auto object-contain">
                                </div>
                                <div>
                                    <div class="leading-none text-xs sm:text-sm font-black flex items-center">
                                        <span>PAY WITH RAZORPAY</span>
                                        <span class="ml-2 text-[10px] bg-emerald-400/20 text-emerald-300 border border-emerald-400/40 px-1.5 py-0.5 rounded font-mono">SECURE</span>
                                    </div>
                                    <div class="text-[10px] text-cyan-100/90 font-normal tracking-normal mt-1">UPI • Cards • Net Banking • Wallets</div>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-white group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </button>

                        <!-- Option 2: WhatsApp Chat Purchase -->
                        <a :href="'https://wa.me/{{ preg_replace('/[^0-9]/', '', \App\Models\Setting::get('whatsapp_number', '919876543210')) }}?text=' + encodeURIComponent('Hi, I want to purchase CapCut Pro 1 Month + 1200 AI Credits. My WhatsApp: ' + (customerPhone || 'N/A') + ', Email: ' + (customerEmail || 'N/A'))" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           @click="window.trackConversionEvent('WhatsAppCheckoutClicked', null, null, { source: 'checkout_modal' })"
                           class="btn-whatsapp-glow w-full py-3.5 px-5 text-sm font-bold uppercase tracking-wider flex items-center justify-between cursor-pointer">
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-white shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.303-.058.116-.087.188-.173.289l-.26.303c-.087.086-.177.18-.076.353.101.173.45 1.428 1.547 2.404.352.314.647.411.821.498.174.086.275.072.376-.044.101-.116.433-.505.549-.679.116-.173.231-.144.39-.086.159.058 1.011.477 1.184.564.173.087.289.13.332.202.043.072.043.419-.101.824z"/></svg>
                                <div class="text-left">
                                    <div class="leading-none text-xs sm:text-sm">WHATSAPP</div>
                                    <div class="text-[10px] text-emerald-100 font-normal tracking-normal mt-0.5">Order directly with our support desk</div>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    <template x-if="paymentError">
                        <div class="bg-rose-950/60 border border-rose-500/40 text-rose-300 text-xs p-3 rounded-xl" x-text="paymentError"></div>
                    </template>

                    <div class="text-[11px] text-slate-400 text-center pt-1 font-medium flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="18" height="11" x="3" y="11" rx="2" ry="2" stroke-width="2"/><circle cx="12" cy="16" r="1.5" stroke-width="2"/><path stroke-width="2" stroke-linecap="round" d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <span>Secure Payment • Official Razorpay • UPI • Cards • Net Banking</span>
                    </div>

                </div>
            </template>

            <!-- STEP 2: PROCESSING / SPINNER -->
            <template x-if="loadingPayment">
                <div class="py-12 text-center space-y-4">
                    <div class="w-12 h-12 border-3 border-cyan-400 border-t-transparent rounded-full animate-spin mx-auto"></div>
                    <h4 class="text-lg font-bold text-white">Opening Secure Gateway...</h4>
                    <p class="text-xs text-slate-400">Please complete the payment in the Razorpay window.</p>
                </div>
            </template>

            <!-- STEP 3: POST-PAYMENT SUCCESS & OPTIONAL CANVA BONUS (REQUIREMENT 9) -->
            <template x-if="checkoutStep === 'success'">
                <div class="space-y-5 text-center">
                    
                    <div class="w-16 h-16 rounded-full bg-emerald-500/20 border border-emerald-400/50 text-emerald-300 text-3xl flex items-center justify-center mx-auto shadow-[0_0_25px_rgba(0,208,132,0.4)]">
                        <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M20 6L9 17l-5-5"/></svg>
                    </div>

                    <div>
                        <span class="text-xs font-mono font-bold text-emerald-400 uppercase tracking-widest">PAYMENT SUCCESSFUL</span>
                        <h3 class="text-2xl font-black text-white mt-1">Your Order is Confirmed!</h3>
                        <p class="text-xs text-slate-300 mt-1">
                            Order <strong class="text-cyan-300" x-text="'#' + orderNumber"></strong> is ready.
                        </p>
                    </div>

                    <!-- Canva Bonus Box (Requirement 9: Asked ONLY after payment, optional) -->
                    <div class="bg-purple-950/50 border border-purple-500/40 rounded-2xl p-5 text-left space-y-3">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 rounded-lg bg-purple-900/60 border border-purple-500/40 flex items-center justify-center text-purple-300 shrink-0">
                                <svg class="w-4 h-4 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="20 12 20 22 4 22 4 12" stroke-width="2"/><rect x="2" y="7" width="20" height="5" stroke-width="2"/><line x1="12" y1="22" x2="12" y2="7" stroke-width="2"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z" stroke-width="2"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z" stroke-width="2"/></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-purple-200">Claim Your Canva Pro Bonus</h4>
                                <p class="text-[11px] text-purple-300/90 font-medium">Canva Pro — 1 Year Validity</p>
                            </div>
                        </div>
                        
                        <template x-if="!canvaSuccess">
                            <div class="space-y-2.5">
                                <label for="canva-email-input" class="text-xs text-slate-300 block font-medium">
                                    Want to claim your Canva Pro bonus? Enter your email address below.
                                </label>
                                <input type="email" 
                                       id="canva-email-input"
                                       x-model="canvaEmail" 
                                       placeholder="Enter your Canva email address" 
                                       class="bg-slate-950 border border-purple-500/40 text-white rounded-xl px-3.5 py-2.5 text-xs w-full focus:outline-none focus:border-purple-400">
                                
                                <p class="text-[11px] text-slate-400">
                                    Optional — provide this only if you want to claim the Canva Pro bonus.
                                </p>

                                <div class="flex items-center gap-2 pt-1">
                                    <button type="button" 
                                            @click="submitCanvaBonus(false)" 
                                            :disabled="canvaLoading"
                                            class="bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs px-4 py-2.5 rounded-xl flex-1 cursor-pointer">
                                        <span x-text="canvaLoading ? 'Submitting...' : 'Claim Canva Pro Bonus'"></span>
                                    </button>
                                    <button type="button" 
                                            @click="submitCanvaBonus(true)" 
                                            class="text-xs text-slate-400 hover:text-white px-3 py-2 cursor-pointer">
                                        Skip for now
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template x-if="canvaSuccess">
                            <div class="bg-purple-900/40 border border-purple-400/40 rounded-xl p-3 text-xs text-purple-200 font-semibold" x-text="canvaMessage"></div>
                        </template>
                    </div>

                    <!-- Direct Link to Access Details -->
                    <div class="pt-2">
                        <a :href="'/order/' + orderUuid + '/' + orderAccessToken" 
                           class="btn-primary-glow w-full py-3.5 text-sm font-bold uppercase tracking-wider flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="18" height="11" x="3" y="11" rx="2" ry="2" stroke-width="2"/><circle cx="12" cy="16" r="1.5" stroke-width="2"/><path stroke-width="2" stroke-linecap="round" d="M7 11V7a5 5 0 0 1 9.9-1"/></svg>
                            <span>Reveal Account Details & Credentials →</span>
                        </a>
                    </div>

                </div>
            </template>

            <!-- STEP 4: PAYMENT FAILED -->
            <template x-if="checkoutStep === 'failed'">
                <div class="space-y-5 text-center">
                    <div class="w-14 h-14 rounded-full bg-rose-500/20 border border-rose-400/50 text-rose-300 text-2xl flex items-center justify-center mx-auto">
                        <svg class="w-7 h-7 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Payment could not be completed.</h3>
                        <p class="text-xs text-slate-300 mt-1" x-text="paymentError || 'Your transaction was cancelled or declined.'"></p>
                    </div>

                    <div class="space-y-2 pt-2">
                        <button type="button" 
                                @click="checkoutStep = 'select_method'; startRazorpay()" 
                                class="btn-primary-glow w-full py-3 text-xs font-bold uppercase cursor-pointer flex items-center justify-center space-x-1.5">
                            <svg class="w-3.5 h-3.5 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                            <span>Try Razorpay Again</span>
                        </button>
                        <a href="{{ $whatsappUrl }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="btn-whatsapp-glow w-full py-3 text-xs font-bold uppercase flex items-center justify-center space-x-1.5">
                            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.303-.058.116-.087.188-.173.289l-.26.303c-.087.086-.177.18-.076.353.101.173.45 1.428 1.547 2.404.352.314.647.411.821.498.174.086.275.072.376-.044.101-.116.433-.505.549-.679.116-.173.231-.144.39-.086.159.058 1.011.477 1.184.564.173.087.289.13.332.202.043.072.043.419-.101.824z"/></svg>
                            <span>WHATSAPP</span>
                        </a>
                        <a href="{{ route('page.support') }}" class="text-xs text-cyan-400 hover:underline block pt-1">
                            Contact Support
                        </a>
                    </div>
                </div>
            </template>

        </div>
    </div>

</div>
@endsection
