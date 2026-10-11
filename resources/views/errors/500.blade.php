@extends('layouts.app')

@section('title', 'System Error (500) — CapCut Pro Store')

@section('content')
<div class="py-20 md:py-32 text-center max-w-lg mx-auto px-4">
    <div class="glass-card p-8 rounded-3xl border-rose-500/30 space-y-4">
        <div class="text-5xl font-black text-rose-400 font-mono">500</div>
        <h1 class="text-xl font-bold text-white">Temporary Service Disruption</h1>
        <p class="text-xs text-slate-400 leading-relaxed">
            Our systems encountered an unexpected internal error. If your payment was charged, your transaction is safe and recorded. Please contact our WhatsApp desk with your payment ID.
        </p>
        <div class="pt-4 flex flex-col sm:flex-row justify-center gap-3">
            <a href="{{ route('home') }}" class="btn-primary-glow px-6 py-2.5 text-xs font-bold uppercase">
                Return Home
            </a>
            <a href="{{ (new \App\Services\WhatsApp\WhatsAppService())->generateSupportUrl() }}" target="_blank" class="btn-whatsapp-glow px-6 py-2.5 text-xs font-bold uppercase">
                Contact WhatsApp
            </a>
        </div>
    </div>
</div>
@endsection
