@extends('layouts.admin')

@section('title', 'Customer Orders — Admin')

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white">Orders Management</h1>
            <p class="text-xs text-slate-400 mt-1">Review customer transactions, verification states, and fulfillment.</p>
        </div>
        
        <!-- Search -->
        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex space-x-2">
            @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
            <input type="text" name="search" value="{{ $search }}" placeholder="Search order ID, email, name..."
                   class="bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-3.5 py-1.5 text-xs text-white placeholder-slate-500 outline-none w-64">
            <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-cyan-300 font-bold px-3 py-1.5 rounded-xl text-xs border border-cyan-500/30">
                Search
            </button>
        </form>
    </div>

    <!-- Status Tabs -->
    <div class="flex flex-wrap gap-2 text-xs">
        <a href="{{ route('admin.orders.index') }}" class="px-3.5 py-1.5 rounded-xl font-bold transition-all {{ !$status ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/40' : 'bg-slate-900/60 text-slate-400 hover:text-white' }}">
            All ({{ $counts['all'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'paid']) }}" class="px-3.5 py-1.5 rounded-xl font-bold transition-all {{ $status === 'paid' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-slate-900/60 text-slate-400 hover:text-white' }}">
            Paid ({{ $counts['paid'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'draft']) }}" class="px-3.5 py-1.5 rounded-xl font-bold transition-all {{ $status === 'draft' ? 'bg-slate-700 text-white border border-slate-500' : 'bg-slate-900/60 text-slate-400 hover:text-white' }}">
            Drafts ({{ $counts['draft'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'abandoned']) }}" class="px-3.5 py-1.5 rounded-xl font-bold transition-all {{ $status === 'abandoned' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'bg-slate-900/60 text-slate-400 hover:text-white' }}">
            Abandoned ({{ $counts['abandoned'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="px-3.5 py-1.5 rounded-xl font-bold transition-all {{ $status === 'pending' ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/40' : 'bg-slate-900/60 text-slate-400 hover:text-white' }}">
            Pending Pay ({{ $counts['pending'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'failed']) }}" class="px-3.5 py-1.5 rounded-xl font-bold transition-all {{ $status === 'failed' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40' : 'bg-slate-900/60 text-slate-400 hover:text-white' }}">
            Failed ({{ $counts['failed'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'refunded']) }}" class="px-3.5 py-1.5 rounded-xl font-bold transition-all {{ $status === 'refunded' ? 'bg-slate-700 text-white' : 'bg-slate-900/60 text-slate-400 hover:text-white' }}">
            Refunded ({{ $counts['refunded'] }})
        </a>
    </div>

    <!-- Orders Table -->
    <div class="glass-card rounded-2xl overflow-hidden p-6 space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="text-slate-400 border-b border-slate-800 uppercase font-mono text-[10px]">
                    <tr>
                        <th class="py-3 px-3">Order Number</th>
                        <th class="py-3 px-3">Customer</th>
                        <th class="py-3 px-3">Product</th>
                        <th class="py-3 px-3">Total</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3">Delivery / Bonus</th>
                        <th class="py-3 px-3">Activity</th>
                        <th class="py-3 px-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($orders as $order)
                    <tr class="hover:bg-slate-900/40">
                        <td class="py-3 px-3 font-mono font-bold text-white">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-cyan-400 hover:underline">
                                {{ $order->order_number }}
                            </a>
                            @if($order->isDraft() || $order->isAbandoned())
                                <span class="block text-[9px] text-amber-400 font-mono">LEAD / INCOMPLETE</span>
                            @endif
                        </td>
                        <td class="py-3 px-3">
                            <div class="font-bold text-slate-200">
                                {{ !empty($order->customer_name) && $order->customer_name !== 'Customer' ? $order->customer_name : 'Not provided' }}
                            </div>
                            <div class="text-[11px] {{ !empty($order->customer_email) && $order->customer_email !== 'customer@guest.local' ? 'text-slate-400' : 'text-slate-500 italic' }}">
                                {{ !empty($order->customer_email) && $order->customer_email !== 'customer@guest.local' ? $order->customer_email : 'Email: Not provided' }}
                            </div>
                            <div class="text-[10px] font-mono {{ !empty($order->customer_phone) ? 'text-cyan-400' : 'text-slate-500 italic' }}">
                                {{ !empty($order->customer_phone) ? 'WhatsApp: ' . $order->customer_phone : 'WhatsApp: Not provided' }}
                            </div>
                        </td>
                        <td class="py-3 px-3">
                            <span class="text-slate-200 font-medium">{{ $order->product->name }}</span>
                        </td>
                        <td class="py-3 px-3 font-mono font-bold text-cyan-300">
                            ₹{{ number_format($order->total_amount, 2) }}
                        </td>
                        <td class="py-3 px-3 space-y-1">
                            <div>
                                @if($order->isPaid())
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-950 text-emerald-400 border border-emerald-500/30">
                                        Paid
                                    </span>
                                @elseif($order->checkout_status === 'draft')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-800 text-slate-300 border border-slate-700">
                                        Draft Lead
                                    </span>
                                @elseif($order->checkout_status === 'abandoned')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-950/80 text-amber-400 border border-amber-500/30">
                                        Abandoned
                                    </span>
                                @elseif($order->checkout_status === 'payment_pending')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-cyan-950 text-cyan-300 border border-cyan-500/30">
                                        Pending Pay
                                    </span>
                                @elseif($order->payment_status === 'failed' || $order->checkout_status === 'payment_failed')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-rose-950 text-rose-400 border border-rose-500/30">
                                        Failed
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-800 text-slate-400">
                                        {{ $order->payment_status }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 px-3">
                            <div class="text-[11px] font-mono {{ $order->delivery_status === 'delivered' ? 'text-emerald-400' : 'text-slate-400' }}">
                                {{ $order->delivery_status }}
                            </div>
                            @if($order->canva_bonus_status && $order->canva_bonus_status !== 'not_requested')
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold uppercase font-mono
                                        {{ $order->canva_bonus_status === 'activated' ? 'bg-emerald-950 text-emerald-400 border border-emerald-500/30' : 
                                           ($order->canva_bonus_status === 'requested' ? 'bg-purple-950 text-purple-300 border border-purple-500/40' : 'bg-slate-800 text-slate-400') }}">
                                        Canva: {{ $order->canva_bonus_status }}
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-slate-400 font-mono text-[11px]">
                            <div>{{ $order->created_at->format('M d, H:i') }}</div>
                            @if($order->last_activity_at)
                                <div class="text-[10px] text-cyan-400/80">Active: {{ $order->last_activity_at->diffForHumans() }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn-primary-glow px-3 py-1 text-[11px] font-bold uppercase">
                                View Details →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500">No orders matching criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4">
            {{ $orders->links() }}
        </div>
    </div>

</div>
@endsection
