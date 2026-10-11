@extends('layouts.admin')

@section('title', 'Testimonials Management — Admin')

@section('content')
<div class="space-y-6" x-data="{ addModalOpen: false }">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-white">Genuine Testimonials</h1>
            <p class="text-xs text-slate-400 mt-1">Manage real customer feedback. Do not fabricate fake reviews.</p>
        </div>
        <button @click="addModalOpen = true" class="btn-primary-glow px-4 py-2 text-xs font-bold uppercase">
            + Add Testimonial
        </button>
    </div>

    <!-- Table -->
    <div class="glass-card rounded-2xl overflow-hidden p-6 space-y-4 border-cyan-500/25">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="text-slate-400 border-b border-slate-800 uppercase font-mono text-[10px]">
                    <tr>
                        <th class="py-3 px-3">Customer</th>
                        <th class="py-3 px-3">Review</th>
                        <th class="py-3 px-3">Rating</th>
                        <th class="py-3 px-3">Verified</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($testimonials as $review)
                    <tr class="hover:bg-slate-900/40">
                        <td class="py-3 px-3">
                            <span class="font-bold text-white block">{{ $review->customer_name }}</span>
                            <span class="text-slate-500 font-mono text-[10px]">Initials: {{ $review->initial }}</span>
                        </td>
                        <td class="py-3 px-3 max-w-xs truncate text-slate-300">
                            "{{ $review->review_text }}"
                        </td>
                        <td class="py-3 px-3 text-amber-400 font-mono">
                            {{ str_repeat('★', $review->rating) }}
                        </td>
                        <td class="py-3 px-3">
                            <span class="text-xs {{ $review->verified ? 'text-emerald-400 font-bold' : 'text-slate-500' }}">
                                {{ $review->verified ? 'Verified' : 'Unverified' }}
                            </span>
                        </td>
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $review->published ? 'bg-emerald-950 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400' }}">
                                {{ $review->published ? 'Published' : 'Draft / Hidden' }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-right">
                            <form method="POST" action="{{ route('admin.testimonials.destroy', $review) }}" class="inline" onsubmit="return confirm('Delete this review?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-400 hover:text-rose-300 font-bold">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500">No testimonials published yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Testimonial Modal -->
    <div x-show="addModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="glass-card max-w-md w-full p-6 rounded-2xl border-cyan-500/30 space-y-4">
            <h3 class="text-base font-bold text-white">Add Customer Review</h3>

            <form method="POST" action="{{ route('admin.testimonials.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Customer Name *</label>
                    <input type="text" name="customer_name" required placeholder="e.g. Rahul Sharma"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-sm text-white outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Initials (Optional)</label>
                        <input type="text" name="customer_initial" placeholder="RS"
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white font-mono uppercase outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Star Rating (1-5) *</label>
                        <select name="rating" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                            <option value="5">★★★★★ (5 Stars)</option>
                            <option value="4">★★★★☆ (4 Stars)</option>
                            <option value="3">★★★☆☆ (3 Stars)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Review Text *</label>
                    <textarea name="review_text" rows="3" required placeholder="Paste genuine customer review..."
                              class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none"></textarea>
                </div>

                <div class="flex items-center space-x-4 pt-1 text-xs">
                    <label class="flex items-center space-x-1.5 cursor-pointer text-slate-300">
                        <input type="checkbox" name="verified" value="1" checked class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-cyan-500">
                        <span>Verified Buyer</span>
                    </label>

                    <label class="flex items-center space-x-1.5 cursor-pointer text-slate-300">
                        <input type="checkbox" name="published" value="1" checked class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-cyan-500">
                        <span>Publish Live</span>
                    </label>
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 rounded-xl text-xs text-slate-400 hover:text-white">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary-glow px-5 py-2 text-xs font-bold uppercase">
                        Save Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
