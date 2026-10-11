@extends('layouts.admin')

@section('title', 'Store Settings — Admin')

@section('content')
<div class="space-y-6 max-w-4xl" x-data="{ 
    currentTab: '{{ request()->query('tab', 'general') }}',
    razorpayMode: '{{ $razorpayMode }}',
    showLiveConfirm: false,
    isTesting: false,
    testResult: null,
    selectMode(mode) {
        if (mode === 'live' && this.razorpayMode !== 'live') {
            this.showLiveConfirm = true;
        } else {
            this.razorpayMode = mode;
        }
    },
    requestLiveMode() {
        if (this.razorpayMode !== 'live') {
            this.showLiveConfirm = true;
        }
    },
    confirmLiveMode() {
        this.razorpayMode = 'live';
        this.showLiveConfirm = false;
    },
    async testConnection() {
        this.isTesting = true;
        this.testResult = null;
        try {
            const keyId = document.getElementById('admin_razorpay_key_id').value;
            const keySecret = document.getElementById('admin_razorpay_key_secret').value;
            const res = await fetch('{{ route('admin.settings.test_razorpay') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    razorpay_key_id: keyId,
                    razorpay_key_secret: keySecret,
                    razorpay_mode: this.razorpayMode
                })
            });
            const data = await res.json();
            this.testResult = data;
        } catch (e) {
            this.testResult = {
                success: false,
                message: '✕ Razorpay connection failed: Network error occurred.'
            };
        } finally {
            this.isTesting = false;
        }
    }
}">
    <div>
        <h1 class="text-2xl font-black text-white">Store Configuration</h1>
        <p class="text-xs text-slate-400 mt-1">Configure payment gateways, Meta Ads tracking, WhatsApp desk, and legal texts.</p>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex flex-wrap gap-2 border-b border-slate-800 pb-3 text-xs">
        <button @click="currentTab = 'general'" :class="currentTab === 'general' ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40' : 'bg-slate-900 text-slate-400'" class="px-4 py-2 rounded-xl font-bold border border-transparent transition-all">
            General & WhatsApp
        </button>
        <button @click="currentTab = 'media'" :class="currentTab === 'media' ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40' : 'bg-slate-900 text-slate-400'" class="px-4 py-2 rounded-xl font-bold border border-transparent transition-all">
            Hero Video & Canva Bonus
        </button>
        <button @click="currentTab = 'payment'" :class="currentTab === 'payment' ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40' : 'bg-slate-900 text-slate-400'" class="px-4 py-2 rounded-xl font-bold border border-transparent transition-all">
            Razorpay Gateway
        </button>
        <button @click="currentTab = 'tracking'" :class="currentTab === 'tracking' ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40' : 'bg-slate-900 text-slate-400'" class="px-4 py-2 rounded-xl font-bold border border-transparent transition-all">
            Meta Pixel & CAPI
        </button>
        <button @click="currentTab = 'announcement'" :class="currentTab === 'announcement' ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40' : 'bg-slate-900 text-slate-400'" class="px-4 py-2 rounded-xl font-bold border border-transparent transition-all">
            Announcement Bar
        </button>
        <button @click="currentTab = 'policies'" :class="currentTab === 'policies' ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40' : 'bg-slate-900 text-slate-400'" class="px-4 py-2 rounded-xl font-bold border border-transparent transition-all">
            Policies & Legal
        </button>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf

        <!-- TAB 1: General & WhatsApp -->
        <div x-show="currentTab === 'general'" class="glass-card p-6 sm:p-8 rounded-2xl border-cyan-500/20 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h2 class="text-base font-bold text-white">Store Identity & Branding</h2>
                <span class="text-xs font-mono text-cyan-400">SOURCE OF TRUTH</span>
            </div>

            <!-- Store Logo Upload & Preview -->
            <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-4">
                <label class="block text-xs font-bold uppercase text-slate-300 mb-2">Store Logo (Displayed in Navbar, Footer & Storefront Branding)</label>
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-xl bg-black/60 border border-cyan-500/30 p-2 flex items-center justify-center shrink-0 overflow-hidden shadow-inner">
                        <img id="logoPreviewImg" 
                             src="{{ !empty($settings['store_logo']->value) ? asset($settings['store_logo']->value) : asset('assets/images/default-store-logo.svg') }}" 
                             alt="Current Store Logo" class="w-full h-full object-contain">
                    </div>
                    <div class="flex-1 space-y-2">
                        <input type="file" id="logoFileInput" accept="image/png,image/jpeg,image/webp,image/svg+xml" onchange="previewLogoFile(this)"
                               class="text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-cyan-500/20 file:text-cyan-300 hover:file:bg-cyan-500/30 cursor-pointer">
                        <input type="hidden" name="store_logo_data" id="storeLogoData">
                        <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-400">
                            <span>PNG, JPG, SVG, WebP (Max 3MB). Replaces default logo everywhere.</span>
                            @if(!empty($settings['store_logo']->value))
                                <label class="flex items-center space-x-1.5 text-rose-400 cursor-pointer">
                                    <input type="checkbox" name="remove_store_logo" id="removeStoreLogoCheck" value="1" onchange="resetLogoPreview(this)" class="rounded bg-slate-900 border-slate-700 text-rose-500">
                                    <span class="font-bold">Reset to Default Logo</span>
                                </label>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Store Name</label>
                    <input type="text" name="store_name" value="{{ $settings['store_name']->value ?? 'CapCut Pro' }}"
                           class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white font-bold outline-none"
                           placeholder="e.g. DIGITAL PRO STORE">
                    <span class="text-[10px] text-slate-500 block mt-1">Updates navbar, hero, footer, page titles & checkout summaries dynamically.</span>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Optional Badge / Team Label</label>
                    <input type="text" name="store_badge" value="{{ $settings['store_badge']->value ?? 'PRO' }}"
                           class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white font-mono outline-none"
                           placeholder="e.g. PRO, TEAM, or leave blank">
                    <span class="text-[10px] text-slate-500 block mt-1">Small neon badge next to store name (leave blank to hide).</span>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Store Tagline / Subtitle</label>
                    <input type="text" name="store_tagline" value="{{ $settings['store_tagline']->value ?? 'Digital License Store' }}"
                           class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none"
                           placeholder="Digital License Store">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Support Email</label>
                    <input type="email" name="support_email" value="{{ $settings['support_email']->value ?? 'support@capcutstore.local' }}"
                           class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">WhatsApp Support Number (With Country Code)</label>
                    <input type="text" name="whatsapp_number" value="{{ $settings['whatsapp_number']->value ?? '919876543210' }}" placeholder="919876543210"
                           class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white font-mono outline-none">
                    <span class="text-[10px] text-slate-500 block mt-1">E.g. 919876543210 (digits only, no + or spaces).</span>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Default Warranty Days</label>
                    <input type="number" name="default_warranty_days" value="{{ $settings['default_warranty_days']->value ?? '30' }}"
                           class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white font-mono outline-none">
                    <span class="text-[10px] text-slate-500 block mt-1">Displayed across guarantee cards and terms (30 days).</span>
                </div>
            </div>
        </div>

        <!-- TAB: Media, Video & Canva Bonus -->
        <div x-show="currentTab === 'media'" x-cloak class="glass-card p-6 sm:p-8 rounded-2xl border-cyan-500/20 space-y-4">
            <h2 class="text-base font-bold text-white">Hero YouTube Video & Canva Pro Bonus</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Hero YouTube Video ID</label>
                    <input type="text" name="youtube_video_id" value="{{ $settings['youtube_video_id']->value ?? 'j_PZmYQDJQA' }}" placeholder="j_PZmYQDJQA"
                           class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white font-mono outline-none">
                    <span class="text-[10px] text-slate-500 block mt-1">From YouTube URL (e.g., https://youtu.be/<b>j_PZmYQDJQA</b>). Video embeds in glowing frame on hero right side (mobile: stacked).</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Canva Bonus Section Title</label>
                        <input type="text" name="canva_bonus_title" value="{{ $settings['canva_bonus_title']->value ?? '🎁 BONUS: CANVA PRO FREE' }}"
                               class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Canva Bonus Subtitle</label>
                        <input type="text" name="canva_bonus_subtitle" value="{{ $settings['canva_bonus_subtitle']->value ?? 'Complete your CapCut Pro purchase and claim your Canva Pro bonus.' }}"
                               class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Canva Pro Activation Explanation</label>
                    <textarea name="canva_bonus_explanation" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none">{{ $settings['canva_bonus_explanation']->value ?? 'Canva Pro activation requires an eligible email address. You can provide your email after successful payment.' }}</textarea>
                </div>
            </div>
        </div>

        <!-- TAB 2: Razorpay Gateway -->
        <div x-show="currentTab === 'payment'" x-cloak class="glass-card p-6 sm:p-8 rounded-2xl border-cyan-500/20 space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <span>Payment Settings</span>
                        <span class="text-[10px] font-mono bg-cyan-950 border border-cyan-500/40 text-cyan-300 px-2.5 py-0.5 rounded-full uppercase">Provider: Razorpay</span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Control live checkout gateway keys, test mode, and connection validation.</p>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Mode Status Indicator Badge -->
                    <template x-if="razorpayMode === 'live'">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-400 border border-emerald-400/40">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                            LIVE MODE
                        </span>
                    </template>
                    <template x-if="razorpayMode === 'test'">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-amber-500/20 text-amber-400 border border-amber-400/40">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            TEST MODE
                        </span>
                    </template>
                    <span class="text-[11px] font-mono text-cyan-400 bg-slate-900 border border-slate-800 px-2.5 py-1 rounded-lg">ENCRYPTED AT REST</span>
                </div>
            </div>

            <!-- Mode Selector with Live Mode Safety Confirmation -->
            <div class="space-y-2">
                <label class="block text-xs font-bold uppercase text-slate-300">Gateway Mode</label>
                <div class="grid grid-cols-2 gap-3 max-w-md">
                    <button type="button" 
                            @click="selectMode('test')"
                            :class="razorpayMode === 'test' ? 'bg-amber-500/20 border-amber-400/60 text-amber-300 shadow-[0_0_15px_rgba(245,158,11,0.2)]' : 'bg-slate-900/80 border-slate-700 text-slate-400 hover:text-white'"
                            class="px-4 py-2.5 rounded-xl border font-bold text-xs flex items-center justify-center gap-2 transition-all">
                        <span class="w-2 h-2 rounded-full" :class="razorpayMode === 'test' ? 'bg-amber-400' : 'bg-slate-600'"></span>
                        Test Mode
                    </button>
                    <button type="button" 
                            @click="requestLiveMode()"
                            :class="razorpayMode === 'live' ? 'bg-emerald-500/20 border-emerald-400/60 text-emerald-300 shadow-[0_0_15px_rgba(16,185,129,0.2)]' : 'bg-slate-900/80 border-slate-700 text-slate-400 hover:text-white'"
                            class="px-4 py-2.5 rounded-xl border font-bold text-xs flex items-center justify-center gap-2 transition-all">
                        <span class="w-2 h-2 rounded-full" :class="razorpayMode === 'live' ? 'bg-emerald-400' : 'bg-slate-600'"></span>
                        Live Mode
                    </button>
                </div>
                <input type="hidden" name="razorpay_mode" :value="razorpayMode">
                <p class="text-[11px] text-slate-500">
                    Test Mode uses test cards/mock flows without real charges. Live Mode processes authentic payments.
                </p>
            </div>

            <!-- Razorpay Key ID -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-bold uppercase text-slate-300">Razorpay Key ID</label>
                    @if(!empty($razorpayKeyIdMasked))
                        <span class="text-[11px] font-mono text-cyan-400">Current: {{ $razorpayKeyIdMasked }}</span>
                    @endif
                </div>
                <input type="text" name="razorpay_key_id" id="admin_razorpay_key_id" 
                       value="{{ $settings['razorpay_key_id']->value ?? '' }}" 
                       placeholder="e.g. rzp_test_xxxxxxxxxxxx or rzp_live_xxxxxxxxxxxx"
                       class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white font-mono outline-none">
                <span class="text-[11px] text-slate-500 block mt-1">Starts with <code class="text-amber-400">rzp_test_</code> for Test Mode or <code class="text-emerald-400">rzp_live_</code> for Live Mode.</span>
            </div>

            <!-- Razorpay Key Secret -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-bold uppercase text-slate-300">Razorpay Key Secret</label>
                    @if($razorpayHasSecret)
                        <span class="text-[11px] font-mono text-emerald-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            •••••••••••••••• (Encrypted in database)
                        </span>
                    @else
                        <span class="text-[11px] font-mono text-rose-400">Not configured yet</span>
                    @endif
                </div>
                <input type="password" name="razorpay_key_secret" id="admin_razorpay_key_secret" 
                       placeholder="{{ $razorpayHasSecret ? '•••••••••••••••• (Leave blank to keep existing encrypted secret)' : 'Paste Razorpay Key Secret' }}"
                       class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white font-mono outline-none">
                <span class="text-[11px] text-slate-500 block mt-1">Stored encrypted server-side via AES-256 with application key. Never sent to browser.</span>
            </div>

            <!-- Razorpay Webhook Secret -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-bold uppercase text-slate-300">Razorpay Webhook Secret (Optional)</label>
                    @if(!empty($settings['razorpay_webhook_secret']->value))
                        <span class="text-[11px] font-mono text-emerald-400">•••••••• (Encrypted)</span>
                    @endif
                </div>
                <input type="password" name="razorpay_webhook_secret" 
                       placeholder="{{ !empty($settings['razorpay_webhook_secret']->value) ? '•••••••• (Leave blank to keep existing secret)' : 'Paste Webhook Secret' }}"
                       class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white font-mono outline-none">
            </div>

            <!-- Webhook URL Info -->
            <div class="p-3.5 bg-slate-900/90 rounded-xl border border-slate-800 text-xs text-slate-400 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <strong class="text-slate-200">Webhook URL:</strong> 
                    <code class="text-cyan-300 font-mono ml-1">{{ url('/webhooks/razorpay') }}</code>
                </div>
                <span class="text-[11px] text-slate-500 font-mono">Event: payment.captured</span>
            </div>

            <!-- Action Buttons: Save & Test Connection -->
            <div class="pt-2 flex flex-wrap items-center gap-3 border-t border-slate-800/80">
                <button type="submit" class="btn-primary-glow px-6 py-2.5 text-xs font-bold uppercase tracking-wider">
                    Save Settings
                </button>
                <button type="button" 
                        @click="testConnection()"
                        :disabled="isTesting"
                        class="px-5 py-2.5 rounded-xl border border-cyan-500/40 bg-cyan-950/60 hover:bg-cyan-900/80 text-cyan-300 text-xs font-bold flex items-center gap-2 transition-all">
                    <svg x-show="!isTesting" class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <svg x-show="isTesting" class="w-4 h-4 animate-spin text-cyan-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span x-text="isTesting ? 'Testing Connection...' : 'Test Connection'"></span>
                </button>
            </div>

            <!-- Test Connection Result Feedback Banner -->
            <div x-show="testResult" x-cloak class="p-4 rounded-xl border text-xs flex items-start gap-3 transition-all"
                 :class="testResult && testResult.success ? 'bg-emerald-950/70 border-emerald-500/40 text-emerald-300' : 'bg-rose-950/70 border-rose-500/40 text-rose-300'">
                <template x-if="testResult && testResult.success">
                    <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </template>
                <template x-if="testResult && !testResult.success">
                    <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </template>
                <div>
                    <div class="font-bold text-sm" x-text="testResult && testResult.message"></div>
                    <div class="text-[11px] text-slate-300 mt-0.5" x-text="testResult && testResult.success ? 'Your server verified communication with Razorpay API using the configured credentials.' : 'Please verify that your Key ID and Key Secret match your Razorpay Dashboard in ' + (razorpayMode === 'live' ? 'Live' : 'Test') + ' mode.'"></div>
                </div>
            </div>

            <!-- Live Mode Confirmation Modal (Safety Check) -->
            <div x-show="showLiveConfirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
                <div @click.away="showLiveConfirm = false" class="bg-slate-900 border border-amber-500/40 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-[0_0_30px_rgba(245,158,11,0.2)]">
                    <div class="flex items-center space-x-3 text-amber-400">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Switch to Live Mode?</h3>
                            <p class="text-xs text-amber-300/90">Confirmation Required</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Switching to Live Mode will process real customer payments and charge actual bank accounts/cards. Ensure you have entered valid <strong class="text-white">Live Razorpay Credentials</strong> (<code class="text-emerald-300">rzp_live_...</code>).
                    </p>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="showLiveConfirm = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-400 hover:text-white bg-slate-800">
                            Cancel
                        </button>
                        <button type="button" @click="confirmLiveMode()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-900 bg-emerald-400 hover:bg-emerald-300 shadow-[0_0_15px_rgba(16,185,129,0.3)]">
                            Confirm Live Mode
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: Meta Pixel & Conversions API -->
        <div x-show="currentTab === 'tracking'" x-cloak class="glass-card p-6 sm:p-8 rounded-2xl border-cyan-500/20 space-y-4">
            <h2 class="text-base font-bold text-white">Meta Pixel & Conversions API (CAPI)</h2>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Meta Pixel ID</label>
                <input type="text" name="meta_pixel_id" value="{{ $settings['meta_pixel_id']->value ?? '' }}" placeholder="e.g. 123456789012345"
                       class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white font-mono outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Meta Conversions API Access Token</label>
                <input type="password" name="meta_access_token" placeholder="Leave blank to keep existing encrypted token"
                       class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white font-mono outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Meta Test Event Code (Optional)</label>
                <input type="text" name="meta_test_event_code" value="{{ $settings['meta_test_event_code']->value ?? '' }}" placeholder="e.g. TEST12345"
                       class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white font-mono outline-none">
            </div>
        </div>

        <!-- TAB 4: Announcement Bar -->
        <div x-show="currentTab === 'announcement'" x-cloak class="glass-card p-6 sm:p-8 rounded-2xl border-cyan-500/20 space-y-4">
            <h2 class="text-base font-bold text-white">Top Sticky Announcement Bar</h2>

            <div class="flex items-center space-x-2">
                <input type="checkbox" name="announcement_bar_enabled" value="1" {{ ($settings['announcement_bar_enabled']->value ?? '1') === '1' ? 'checked' : '' }}
                       class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-cyan-500">
                <span class="text-xs text-slate-300 font-bold">Enable Top Announcement Bar</span>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Announcement Text</label>
                <input type="text" name="announcement_bar_text" value="{{ $settings['announcement_bar_text']->value ?? '🔥 LIMITED-TIME DIGITAL OFFER • 1200 CREDITS + CANVA PRO FREE • 30-DAY WARRANTY' }}"
                       class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none">
            </div>
        </div>

        <!-- TAB 5: Policies & Legal -->
        <div x-show="currentTab === 'policies'" x-cloak class="glass-card p-6 sm:p-8 rounded-2xl border-cyan-500/20 space-y-4">
            <h2 class="text-base font-bold text-white">Policies & Brand Disclaimers</h2>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Independent Legal Disclaimer</label>
                <textarea name="legal_disclaimer" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none">{{ $settings['legal_disclaimer']->value ?? 'CapCut is a trademark of its respective owner. This store is independent unless otherwise stated. Canva is a trademark of Canva Pty Ltd. The Canva bonus is provided according to the stated offer terms.' }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Warranty Policy Content</label>
                <textarea name="warranty_content" rows="4" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none">{{ $warranty->policy_content ?? '' }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Refund Policy Content</label>
                <textarea name="refund_content" rows="4" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none">{{ $refund->refund_conditions ?? '' }}</textarea>
            </div>
        </div>

        <button type="submit" class="btn-primary-glow px-8 py-3 text-xs font-bold uppercase tracking-wider">
            Save All Settings
        </button>
    </form>
</div>

<script>
function previewLogoFile(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 3 * 1024 * 1024) {
            alert('Logo file must be under 3MB');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreviewImg').src = e.target.result;
            document.getElementById('storeLogoData').value = e.target.result;
            const resetCheck = document.getElementById('removeStoreLogoCheck');
            if (resetCheck) resetCheck.checked = false;
        };
        reader.readAsDataURL(file);
    }
}

function resetLogoPreview(checkbox) {
    if (checkbox.checked) {
        document.getElementById('logoPreviewImg').src = '{{ asset("assets/images/default-store-logo.svg") }}';
        document.getElementById('storeLogoData').value = '';
        const fileInput = document.getElementById('logoFileInput');
        if (fileInput) fileInput.value = '';
    }
}
</script>
@endsection
