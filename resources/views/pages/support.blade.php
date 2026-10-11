@extends('layouts.app')

@section('title', 'Customer Support & Contact — ' . ($storeSettings['store_name'] ?? 'CapCut Pro'))

@section('content')
<div class="py-14 md:py-20 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
        
        <!-- Support Info & WhatsApp -->
        <div class="md:col-span-5 space-y-6">
            <div class="glass-card p-6 rounded-2xl space-y-4">
                <span class="text-xs font-mono font-bold text-cyan-400 uppercase tracking-widest">GET HELP QUICKLY</span>
                <h1 class="text-2xl font-black text-white">Customer Support</h1>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Have an inquiry regarding your CapCut Pro subscription or need warranty assistance? We are here to help.
                </p>

                <div class="pt-2">
                    <a href="{{ $whatsappSupportUrl }}" target="_blank" rel="noopener noreferrer" 
                       class="btn-whatsapp-glow w-full py-3.5 text-xs font-bold uppercase tracking-wider flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.303-.058.116-.087.188-.173.289l-.26.303c-.087.086-.177.18-.076.353.101.173.45 1.428 1.547 2.404.352.314.647.411.821.498.174.086.275.072.376-.044.101-.116.433-.505.549-.679.116-.173.231-.144.39-.086.159.058 1.011.477 1.184.564.173.087.289.13.332.202.043.072.043.419-.101.824z"/></svg>
                        <span>WHATSAPP SUPPORT</span>
                    </a>
                </div>

                <div class="pt-4 border-t border-slate-800 space-y-2 text-xs text-slate-300">
                    <div><strong>Support Email:</strong> {{ $settings['support_email'] ?? 'support@capcutstore.local' }}</div>
                    <div><strong>Response Time:</strong> Typically under 2 hours</div>
                    <div><strong>Working Hours:</strong> 9:00 AM – 11:00 PM IST</div>
                </div>
            </div>

            <div class="glass-card p-6 rounded-2xl space-y-2 text-xs text-slate-400">
                <span class="text-cyan-400 font-bold flex items-center space-x-1.5 text-sm">
                    <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Looking for your credentials?</span>
                </span>
                <p>If you already paid, you can view your login email and password instantly on our order lookup page.</p>
                <div class="pt-2">
                    <a href="{{ route('order.lookup') }}" class="text-cyan-400 font-bold hover:underline block">
                        Go to Customer Order Lookup →
                    </a>
                </div>
            </div>
        </div>

        <!-- Contact Ticket Form -->
        <div class="md:col-span-7">
            <div class="glass-card p-6 sm:p-8 rounded-2xl space-y-5">
                <h2 class="text-lg font-bold text-white">Send Us a Message</h2>

                @if(session('success'))
                    <div class="p-4 bg-emerald-950/70 border border-emerald-500/40 rounded-xl text-xs text-emerald-300">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('page.support.submit') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1">Your Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none">
                        @error('name')<span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>@enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1">Email Address *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none">
                            @error('email')<span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>@enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1">Order Number (Optional)</label>
                            <input type="text" name="order_number" value="{{ old('order_number') }}" placeholder="CAP-XXXXXX"
                                   class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white font-mono uppercase outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1">Subject *</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" required placeholder="e.g. Warranty Replacement, Login Question"
                               class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none">
                        @error('subject')<span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1">Message *</label>
                        <textarea name="message" rows="4" required placeholder="Describe your question or issue in detail..."
                                  class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2.5 text-sm text-white outline-none">{{ old('message') }}</textarea>
                        @error('message')<span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>@enderror
                    </div>

                    <button type="submit" class="btn-primary-glow w-full py-3.5 text-sm font-bold uppercase tracking-wide">
                        SEND MESSAGE →
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
