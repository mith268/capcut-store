@extends('layouts.admin')

@section('title', 'Discount Coupons — Admin')

@section('content')
<div class="space-y-6" x-data="{ addModalOpen: false }">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-white">Coupons & Promos</h1>
            <p class="text-xs text-slate-400 mt-1">Manage promotional discount codes with usage and expiry limits.</p>
        </div>
        <button @click="addModalOpen = true" class="btn-primary-glow px-4 py-2 text-xs font-bold uppercase">
            + Create Coupon
        </button>
    </div>

    <!-- Coupons Table -->
    <div class="glass-card rounded-2xl overflow-hidden p-6 space-y-4 border-cyan-500/25">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="text-slate-400 border-b border-slate-800 uppercase font-mono text-[10px]">
                    <tr>
                        <th class="py-3 px-3">Code</th>
                        <th class="py-3 px-3">Type & Value</th>
                        <th class="py-3 px-3">Min Order</th>
                        <th class="py-3 px-3">Max Discount</th>
                        <th class="py-3 px-3">Usage</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($coupons as $coupon)
                    <tr class="hover:bg-slate-900/40">
                        <td class="py-3 px-3 font-mono font-bold text-cyan-300 text-sm">{{ $coupon->code }}</td>
                        <td class="py-3 px-3 font-mono">
                            {{ $coupon->type === 'percentage' ? $coupon->value . '%' : '₹' . number_format($coupon->value, 2) }}
                        </td>
                        <td class="py-3 px-3 font-mono">₹{{ number_format($coupon->minimum_amount, 2) }}</td>
                        <td class="py-3 px-3 font-mono">{{ $coupon->maximum_discount ? '₹' . number_format($coupon->maximum_discount, 2) : 'No Cap' }}</td>
                        <td class="py-3 px-3 font-mono text-[11px]">
                            {{ $coupon->used_count }} / {{ $coupon->usage_limit ?: '∞' }}
                        </td>
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $coupon->is_active ? 'bg-emerald-950 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-500' }}">
                                {{ $coupon->is_active ? 'Active' : 'Disabled' }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-right">
                            <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" class="inline" onsubmit="return confirm('Delete this coupon?')">
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
                        <td colspan="7" class="py-8 text-center text-slate-500">No coupon codes configured yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Coupon Modal -->
    <div x-show="addModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="glass-card max-w-md w-full p-6 rounded-2xl border-cyan-500/30 space-y-4">
            <h3 class="text-base font-bold text-white">Create New Coupon</h3>

            <form method="POST" action="{{ route('admin.coupons.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Coupon Code *</label>
                    <input type="text" name="code" required placeholder="e.g. FLASH50"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-sm text-white font-mono uppercase outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Type *</label>
                        <select name="type" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                            <option value="fixed">Flat (₹)</option>
                            <option value="percentage">Percentage (%)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Discount Value *</label>
                        <input type="number" step="0.01" name="value" required placeholder="50.00"
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white font-mono outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Min Order (₹)</label>
                        <input type="number" step="0.01" name="minimum_amount" value="0.00"
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white font-mono outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Max Cap (₹)</label>
                        <input type="number" step="0.01" name="maximum_discount" placeholder="Optional"
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white font-mono outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Total Usage Limit</label>
                        <input type="number" name="usage_limit" placeholder="e.g. 100 (Optional)"
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white font-mono outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Per-Customer Limit *</label>
                        <input type="number" name="per_customer_limit" value="1" required
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white font-mono outline-none">
                    </div>
                </div>

                <div class="flex items-center space-x-2 pt-1">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-cyan-500">
                    <span class="text-xs text-slate-300">Active immediately</span>
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 rounded-xl text-xs text-slate-400 hover:text-white">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary-glow px-5 py-2 text-xs font-bold uppercase">
                        Save Coupon
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
