@extends('layouts.admin')

@section('title', 'Dashboard Overview — Admin')

@section('content')
<div class="space-y-8">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Store Dashboard</h1>
            <p class="text-xs text-slate-400 mt-1">Live overview of sales, inventory health, and customer orders.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.inventory.index') }}" class="btn-primary-glow px-4 py-2 text-xs font-bold uppercase">
                + Upload Inventory
            </a>
        </div>
    </div>

    <!-- Top Key Metrics Cards (4 Grid) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Today Sales -->
        <div class="glass-card p-5 rounded-2xl border-cyan-500/25 space-y-1">
            <span class="text-[11px] font-mono text-slate-400 uppercase tracking-wider block">TODAY'S REVENUE</span>
            <div class="text-2xl font-black text-white">₹{{ number_format($todaySales, 2) }}</div>
            <div class="text-[11px] text-cyan-400 font-medium">Month: ₹{{ number_format($monthSales, 2) }}</div>
        </div>

        <!-- Total Sales -->
        <div class="glass-card p-5 rounded-2xl border-cyan-500/25 space-y-1">
            <span class="text-[11px] font-mono text-slate-400 uppercase tracking-wider block">TOTAL REVENUE</span>
            <div class="text-2xl font-black text-emerald-400">₹{{ number_format($totalSales, 2) }}</div>
            <div class="text-[11px] text-slate-400 font-mono">{{ $paidOrders }} Paid Orders Total</div>
        </div>

        <!-- Inventory Stock Status -->
        <div class="glass-card p-5 rounded-2xl border-cyan-500/25 space-y-1">
            <span class="text-[11px] font-mono text-slate-400 uppercase tracking-wider block">AVAILABLE INVENTORY</span>
            <div class="text-2xl font-black {{ $availableInventory < 5 ? 'text-rose-400' : 'text-cyan-300' }}">
                {{ $availableInventory }} Accounts
            </div>
            <div class="text-[11px] text-slate-400 font-mono">{{ $soldInventory }} Accounts Sold</div>
        </div>

        <!-- Conversion Rate & Clicks -->
        <div class="glass-card p-5 rounded-2xl border-cyan-500/25 space-y-1">
            <span class="text-[11px] font-mono text-slate-400 uppercase tracking-wider block">CONVERSION METRICS</span>
            <div class="text-2xl font-black text-white">{{ $conversionRate }}%</div>
            <div class="text-[11px] text-slate-400 font-mono">{{ $whatsappClicks }} WhatsApp Clicks</div>
        </div>

    </div>

    <!-- Secondary Metrics Chips -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
        <div class="bg-slate-900/60 border border-slate-800 p-3 rounded-xl">
            <span class="text-[10px] text-slate-400 uppercase font-mono block">ALL ORDERS</span>
            <span class="text-base font-bold text-white">{{ $totalOrders }}</span>
        </div>
        <div class="bg-slate-900/60 border border-slate-800 p-3 rounded-xl">
            <span class="text-[10px] text-slate-400 uppercase font-mono block">PENDING ORDERS</span>
            <span class="text-base font-bold text-amber-400">{{ $pendingOrders }}</span>
        </div>
        <div class="bg-slate-900/60 border border-slate-800 p-3 rounded-xl">
            <span class="text-[10px] text-slate-400 uppercase font-mono block">CHECKOUT INITIATED</span>
            <span class="text-base font-bold text-cyan-300">{{ $checkoutStarts }}</span>
        </div>
        <div class="bg-slate-900/60 border border-slate-800 p-3 rounded-xl">
            <span class="text-[10px] text-slate-400 uppercase font-mono block">REFUNDED ORDERS</span>
            <span class="text-base font-bold text-slate-400">{{ $refundedOrders }}</span>
        </div>
    </div>

    <!-- Revenue Trend (14-Day Visual Bar Chart) -->
    <div class="glass-card p-6 rounded-2xl space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-white">Daily Revenue (Last 14 Days)</h2>
            <span class="text-xs text-slate-400 font-mono">Daily Settlement Totals</span>
        </div>

        @php
            $maxVal = max(array_merge([100], $chartRevenue));
        @endphp

        <div class="h-48 flex items-end justify-between gap-1 sm:gap-2 pt-6 pb-2 border-b border-slate-800">
            @foreach($chartRevenue as $idx => $rev)
                @php
                    $heightPercent = max(5, round(($rev / $maxVal) * 100));
                @endphp
                <div class="flex-1 flex flex-col items-center group relative h-full justify-end">
                    <!-- Tooltip -->
                    <div class="hidden group-hover:block absolute -top-8 bg-slate-800 text-cyan-300 text-[10px] font-mono px-2 py-1 rounded shadow-lg whitespace-nowrap z-20">
                        {{ $chartDates[$idx] }}: ₹{{ number_format($rev, 0) }} ({{ $chartOrders[$idx] }} orders)
                    </div>
                    <!-- Bar -->
                    <div style="height: {{ $heightPercent }}%;" 
                         class="w-full max-w-[28px] rounded-t bg-gradient-to-t from-[#1677FF] to-[#00D9FF] hover:brightness-125 transition-all"></div>
                </div>
            @endforeach
        </div>

        <!-- Date Labels -->
        <div class="flex justify-between text-[10px] text-slate-500 font-mono overflow-x-hidden">
            <span>{{ $chartDates[0] }}</span>
            <span>{{ $chartDates[6] ?? '' }}</span>
            <span>{{ end($chartDates) }}</span>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="glass-card rounded-2xl overflow-hidden space-y-4 p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-white">Recent Customer Orders</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-cyan-400 hover:underline">
                View All Orders →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="text-slate-400 border-b border-slate-800 uppercase font-mono text-[10px]">
                    <tr>
                        <th class="py-3 px-3">Order Number</th>
                        <th class="py-3 px-3">Customer</th>
                        <th class="py-3 px-3">Amount</th>
                        <th class="py-3 px-3">Payment</th>
                        <th class="py-3 px-3">Delivery</th>
                        <th class="py-3 px-3">Date</th>
                        <th class="py-3 px-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($recentOrders as $order)
                    <tr class="hover:bg-slate-900/40">
                        <td class="py-3 px-3 font-mono font-bold text-white">{{ $order->order_number }}</td>
                        <td class="py-3 px-3">
                            <div class="font-bold text-slate-200">{{ $order->customer_name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $order->customer_email }}</div>
                        </td>
                        <td class="py-3 px-3 font-mono font-bold text-cyan-300">₹{{ number_format($order->total_amount, 2) }}</td>
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $order->isPaid() ? 'bg-emerald-950 text-emerald-400 border border-emerald-500/30' : ($order->payment_status === 'pending' ? 'bg-amber-950 text-amber-400 border border-amber-500/30' : 'bg-rose-950 text-rose-400') }}">
                                {{ $order->payment_status }}
                            </span>
                        </td>
                        <td class="py-3 px-3">
                            <span class="text-[11px] font-mono {{ $order->delivery_status === 'delivered' ? 'text-emerald-400' : 'text-slate-400' }}">
                                {{ $order->delivery_status }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-slate-400 font-mono">{{ $order->created_at->format('M d, H:i') }}</td>
                        <td class="py-3 px-3 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-cyan-400 hover:text-cyan-300 font-bold">
                                View →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">No orders recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
