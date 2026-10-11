@extends('layouts.app')

@section('title', 'Page Not Found (404) — CapCut Pro Store')

@section('content')
<div class="py-20 md:py-32 text-center max-w-lg mx-auto px-4">
    <div class="glass-card p-8 rounded-3xl border-cyan-500/30 space-y-4">
        <div class="text-5xl font-black text-cyan-300 font-mono">404</div>
        <h1 class="text-xl font-bold text-white">Page Not Found</h1>
        <p class="text-xs text-slate-400 leading-relaxed">
            The page you requested does not exist or has been moved. You can return to our storefront or look up your order.
        </p>
        <div class="pt-4 flex flex-col sm:flex-row justify-center gap-3">
            <a href="{{ route('home') }}" class="btn-primary-glow px-6 py-2.5 text-xs font-bold uppercase">
                Return to Storefront
            </a>
            <a href="{{ route('order.lookup') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 px-6 py-2.5 rounded-xl text-xs font-bold uppercase">
                Lookup Order
            </a>
        </div>
    </div>
</div>
@endsection
