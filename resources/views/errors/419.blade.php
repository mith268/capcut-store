@extends('layouts.app')

@section('title', 'Page Expired (419) — CapCut Pro Store')

@section('content')
<div class="py-20 md:py-32 text-center max-w-lg mx-auto px-4">
    <div class="glass-card p-8 rounded-3xl border-cyan-500/30 space-y-4">
        <div class="text-5xl font-black text-amber-300 font-mono">419</div>
        <h1 class="text-xl font-bold text-white">Session Expired</h1>
        <p class="text-xs text-slate-400 leading-relaxed">
            Your security verification token expired due to inactivity. Please refresh the page and try your action again.
        </p>
        <div class="pt-4 flex justify-center">
            <button onclick="window.location.reload()" class="btn-primary-glow px-6 py-2.5 text-xs font-bold uppercase">
                🔄 Reload Page
            </button>
        </div>
    </div>
</div>
@endsection
