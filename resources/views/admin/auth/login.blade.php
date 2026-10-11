<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administrator Login — CapCut Pro Store</title>

    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    @endif
</head>
<body class="bg-[#05070B] text-slate-100 min-h-screen flex items-center justify-center p-4 font-sans bg-grid-pattern">
    
    <div class="w-full max-w-md">
        
        <div class="glass-card p-8 rounded-3xl border-cyan-500/30 space-y-6 shadow-[0_0_50px_rgba(0,217,255,0.15)]">
            
            <div class="text-center space-y-2">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-[#00D9FF] to-[#1677FF] flex items-center justify-center mx-auto text-black font-black text-sm shadow-[0_0_20px_rgba(0,217,255,0.4)]">
                    CP
                </div>
                <h1 class="text-2xl font-black text-white tracking-tight">Admin Gateway</h1>
                <p class="text-xs text-slate-400">Restricted administrative access only.</p>
            </div>

            @if(session('error'))
                <div class="p-3.5 bg-rose-950/80 border border-rose-500/40 rounded-xl text-xs text-rose-300">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="p-3.5 bg-emerald-950/80 border border-emerald-500/40 rounded-xl text-xs text-emerald-300">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none">
                    @error('email')<span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Password</label>
                    <input type="password" id="password" name="password" required
                           class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none">
                    @error('password')<span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>@enderror
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center space-x-2 cursor-pointer text-slate-300">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-cyan-500 focus:ring-cyan-400">
                        <span>Remember session</span>
                    </label>
                    <span class="text-slate-500">Rate-limited</span>
                </div>

                <button type="submit" class="btn-primary-glow w-full py-3.5 text-sm font-bold uppercase tracking-wide">
                    AUTHENTICATE →
                </button>
            </form>

            <div class="text-center pt-2 border-t border-slate-800 text-[11px] text-slate-500">
                All login attempts and IP addresses are audited for security.
            </div>

        </div>

    </div>

</body>
</html>
