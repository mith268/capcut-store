@extends('layouts.app')

@section('title', 'Lookup Your Order — ' . ($storeSettings['store_name'] ?? 'CapCut Pro'))
@section('meta_description', 'Retrieve your CapCut Pro access details and credentials using your Order ID and email.')

@section('content')
<div class="py-16 md:py-24 max-w-md mx-auto px-4 sm:px-6">
    <div class="glass-card p-6 sm:p-8 rounded-3xl border-cyan-500/30 space-y-6 shadow-[0_0_40px_rgba(0,217,255,0.15)]">
        
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-xl bg-cyan-950 border border-cyan-500/40 text-cyan-300 flex items-center justify-center mx-auto text-xl shadow-[0_0_15px_rgba(0,217,255,0.2)]">
                🔍
            </div>
            <h1 class="text-2xl font-black text-white">Find Your Order</h1>
            <p class="text-xs text-slate-400">Enter your order ID and the email you used during checkout.</p>
        </div>

        @if(session('error'))
            <div class="p-3.5 bg-rose-950/70 border border-rose-500/40 rounded-xl text-xs text-rose-300">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('order.find') }}" class="space-y-4">
            @csrf

            <div>
                <label for="order_number" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                    Order Number
                </label>
                <input type="text" id="order_number" name="order_number" value="{{ old('order_number') }}" required placeholder="CAP-XXXXXX-XXX"
                       class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-3 text-sm text-white font-mono uppercase outline-none">
                @error('order_number')
                    <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                    Email Address
                </label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="your.email@domain.com"
                       class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-3 text-sm text-white outline-none">
                @error('email')
                    <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn-primary-glow w-full py-3.5 text-sm font-bold uppercase tracking-wide">
                LOOKUP ORDER →
            </button>
        </form>

        <div class="text-center pt-2 border-t border-slate-800 text-xs text-slate-400">
            Lost your order ID? <a href="{{ (new \App\Services\WhatsApp\WhatsAppService())->generateSupportUrl() }}" target="_blank" class="text-cyan-400 hover:underline">Contact WhatsApp Support</a>
        </div>

    </div>
</div>
@endsection
