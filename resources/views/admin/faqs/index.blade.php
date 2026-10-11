@extends('layouts.admin')

@section('title', 'FAQ Management — Admin')

@section('content')
<div class="space-y-6" x-data="{ addModalOpen: false }">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-white">Frequently Asked Questions</h1>
            <p class="text-xs text-slate-400 mt-1">Manage public landing page questions and answers.</p>
        </div>
        <button @click="addModalOpen = true" class="btn-primary-glow px-4 py-2 text-xs font-bold uppercase">
            + Add FAQ Item
        </button>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden p-6 space-y-4 border-cyan-500/25">
        <div class="space-y-3">
            @foreach($faqs as $faq)
            <div class="p-4 bg-slate-900/60 rounded-xl border border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-sm text-white flex items-center">
                        <span class="text-xs font-mono text-cyan-400 mr-2">#{{ $faq->display_order }}</span>
                        {{ $faq->question }}
                    </span>
                    <form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" onsubmit="return confirm('Delete this FAQ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 font-bold">
                            Delete
                        </button>
                    </form>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed">{{ $faq->answer }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Create FAQ Modal -->
    <div x-show="addModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="glass-card max-w-md w-full p-6 rounded-2xl border-cyan-500/30 space-y-4">
            <h3 class="text-base font-bold text-white">Add FAQ Question</h3>

            <form method="POST" action="{{ route('admin.faqs.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Question *</label>
                    <input type="text" name="question" required placeholder="e.g. Can I use on multiple devices?"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-sm text-white outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Display Order</label>
                    <input type="number" name="display_order" value="{{ $faqs->count() + 1 }}" required
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white font-mono outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Answer *</label>
                    <textarea name="answer" rows="4" required placeholder="Clear, honest explanation..."
                              class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none"></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 rounded-xl text-xs text-slate-400 hover:text-white">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary-glow px-5 py-2 text-xs font-bold uppercase">
                        Save FAQ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
