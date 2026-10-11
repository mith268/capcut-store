@extends('layouts.app')

@section('title', 'Too Many Requests (429) — CapCut Pro Store')

@section('content')
<div class="py-20 md:py-32 text-center max-w-lg mx-auto px-4">
    <div class="glass-card p-8 rounded-3xl border-rose-500/30 space-y-4">
        <div class="text-5xl font-black text-rose-400 font-mono">429</div>
        <h1 class="text-xl font-bold text-white">Rate Limit Exceeded</h1>
        <p class="text-xs text-slate-400 leading-relaxed">
            Too many requests have been detected from your IP address. Please pause for 60 seconds before trying again.
        </p>
        <div class="pt-4 flex justify-center">
            <a href="{{ route('home') }}" class="btn-primary-glow px-6 py-2.5 text-xs font-bold uppercase">
                Return to Storefront
            </a>
        </div>
    </div>
</div>
@endsection
