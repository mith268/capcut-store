@extends('layouts.admin')

@section('title', 'Order Details #' . $order->order_number . ' — Admin')

@section('content')
<div class="space-y-6 max-w-5xl">
    
    <!-- Top Nav / Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.orders.index') }}" class="text-xs text-slate-400 hover:text-white">← Orders</a>
                <span class="text-slate-600">/</span>
                <span class="text-xs font-mono text-cyan-400 font-bold">{{ $order->order_number }}</span>
            </div>
            <h1 class="text-2xl font-black text-white mt-1">Order #{{ $order->order_number }}</h1>
            <p class="text-xs text-slate-400">UUID: <code class="text-slate-300 font-mono">{{ $order->uuid }}</code></p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ $order->getSecureUrl() }}" target="_blank" class="bg-slate-800 hover:bg-slate-700 text-cyan-300 border border-cyan-500/30 text-xs font-bold px-3.5 py-2 rounded-xl">
                🔗 Customer Portal URL
            </a>

            @if($order->isPaid())
            <form method="POST" action="{{ route('admin.orders.resend_delivery', $order) }}">
                @csrf
                <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold px-3.5 py-2 rounded-xl">
                    📧 Resend Delivery Email
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Status Strip -->
    <div class="glass-card p-4 rounded-xl border-cyan-500/25 grid grid-cols-2 sm:grid-cols-5 gap-4 text-xs">
        <div>
            <span class="text-[10px] uppercase font-mono text-slate-400 block">PAYMENT STATUS</span>
            <span class="text-sm font-bold uppercase {{ $order->isPaid() ? 'text-emerald-400' : 'text-amber-400' }}">
                {{ $order->payment_status }}
            </span>
        </div>
        <div>
            <span class="text-[10px] uppercase font-mono text-slate-400 block">DELIVERY STATUS</span>
            <span class="text-sm font-bold uppercase text-cyan-300">
                {{ $order->delivery_status }}
            </span>
        </div>
        <div>
            <span class="text-[10px] uppercase font-mono text-slate-400 block">CANVA BONUS</span>
            <span class="text-sm font-bold uppercase {{ $order->canva_bonus_status === 'activated' ? 'text-emerald-400' : ($order->canva_bonus_status === 'requested' ? 'text-purple-400' : 'text-slate-400') }}">
                {{ $order->canva_bonus_status ?: 'None' }}
            </span>
        </div>
        <div>
            <span class="text-[10px] uppercase font-mono text-slate-400 block">TOTAL BILLED</span>
            <span class="text-sm font-bold font-mono text-white">
                ₹{{ number_format($order->total_amount, 2) }}
            </span>
        </div>
        <div>
            <span class="text-[10px] uppercase font-mono text-slate-400 block">ORDER DATE</span>
            <span class="text-sm font-bold font-mono text-slate-300">
                {{ $order->created_at->format('M d, Y H:i') }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
        
        <!-- Left: Customer, Payment & Credential Details -->
        <div class="md:col-span-7 space-y-6">
            
            <!-- Customer Information Card -->
            <div class="glass-card p-6 rounded-2xl border-cyan-500/20 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider font-mono">Customer & Lead Information</h2>
                    <span class="text-xs px-2 py-0.5 rounded font-mono font-bold uppercase
                        {{ $order->isPaid() ? 'bg-emerald-950 text-emerald-300 border border-emerald-500/30' : ($order->checkout_status === 'draft' ? 'bg-slate-800 text-slate-300' : 'bg-amber-950 text-amber-300') }}">
                        Status: {{ $order->checkout_status ?: 'draft' }}
                    </span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <span class="text-slate-400 block">Customer Name</span>
                        <span class="font-bold {{ !empty($order->customer_name) && $order->customer_name !== 'Customer' ? 'text-white' : 'text-slate-500 italic' }}">
                            {{ !empty($order->customer_name) && $order->customer_name !== 'Customer' ? $order->customer_name : 'Not provided' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Email Address</span>
                        @if(!empty($order->customer_email) && $order->customer_email !== 'customer@guest.local')
                            <a href="mailto:{{ $order->customer_email }}" class="text-cyan-400 hover:underline font-mono">{{ $order->customer_email }}</a>
                        @else
                            <span class="text-slate-500 italic">Not provided</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-400 block">WhatsApp Number</span>
                        <span class="font-mono {{ !empty($order->customer_phone) ? 'text-cyan-300 font-bold' : 'text-slate-500 italic' }}">
                            {{ !empty($order->customer_phone) ? $order->customer_phone : 'Not provided' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Checkout Session ID</span>
                        <span class="text-slate-400 font-mono text-[11px] truncate block">{{ $order->checkout_session_id ?: 'Not assigned' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">IP Address</span>
                        <span class="text-slate-400 font-mono">{{ $order->ip_address ?: 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Last Active</span>
                        <span class="text-slate-300 font-mono">{{ $order->last_activity_at ? $order->last_activity_at->format('M d, Y H:i:s') : $order->updated_at->format('M d, Y H:i:s') }}</span>
                    </div>
                </div>
            </div>

            <!-- Assigned Credential Card -->
            <div class="glass-card p-6 rounded-2xl border-cyan-500/20 space-y-3">
                <h2 class="text-sm font-bold text-white uppercase tracking-wider font-mono flex items-center justify-between">
                    <span>Provisioned Credential</span>
                    <span class="text-xs text-emerald-400">ENCRYPTED AT REST</span>
                </h2>

                @if($order->credential)
                    <div class="bg-slate-900/80 p-4 rounded-xl border border-slate-800 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-400 block font-mono">MASKED EMAIL</span>
                                <span class="font-mono text-cyan-300 font-bold text-sm">{{ $order->credential->masked_email }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-950 text-emerald-400 border border-emerald-500/30">
                                {{ $order->credential->status }}
                            </span>
                        </div>

                        <div class="pt-2 text-[11px] text-slate-400 font-mono">
                            Assigned At: {{ $order->credential->sold_at ? $order->credential->sold_at->format('M d, Y H:i') : 'Pending' }}
                        </div>
                    </div>
                @else
                    <div class="p-4 bg-amber-950/20 border border-amber-500/30 rounded-xl text-xs text-amber-200 space-y-2">
                        <span>⚠️ No credential has been assigned to this order yet.</span>
                        @if($order->isPaid())
                            <p class="text-[11px] text-slate-400">
                                This occurs if inventory was empty at checkout. Upload credentials in <a href="{{ route('admin.inventory.index') }}" class="text-cyan-400 underline">Inventory</a>, then click "Resend Delivery".
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Canva Pro Bonus Card -->
            <div class="glass-card p-6 rounded-2xl border-purple-500/30 space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider font-mono flex items-center gap-2">
                        <span>🎁 Canva Pro Bonus</span>
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $order->canva_bonus_status === 'activated' ? 'bg-emerald-950 text-emerald-400 border border-emerald-500/30' : ($order->canva_bonus_status === 'requested' ? 'bg-purple-950 text-purple-300 border border-purple-500/40' : 'bg-slate-900 text-slate-400 border border-slate-700') }}">
                        {{ $order->canva_bonus_status ?: 'not_requested' }}
                    </span>
                </div>

                <div class="bg-slate-900/80 p-4 rounded-xl border border-slate-800 space-y-2 text-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase font-mono block">Customer Canva Email</span>
                            <span class="font-mono text-purple-300 font-bold text-sm">
                                {{ $order->canva_email ?: 'No email submitted yet' }}
                            </span>
                        </div>
                        @if($order->canva_email)
                        <button type="button" onclick="navigator.clipboard.writeText('{{ $order->canva_email }}'); alert('Canva email copied!')"
                                class="text-[11px] bg-slate-800 hover:bg-slate-700 text-purple-200 border border-purple-500/30 px-2.5 py-1 rounded-lg">
                            📋 Copy Email
                        </button>
                        @endif
                    </div>
                    <p class="text-[11px] text-slate-400">
                        @if($order->canva_bonus_status === 'requested')
                            Customer requested activation for the above email. Add them to your Canva Team and update status to "Activated".
                        @elseif($order->canva_bonus_status === 'activated')
                            Canva Pro invite/access has been provisioned.
                        @elseif($order->canva_bonus_status === 'skipped')
                            Customer selected "Skip for now" in the post-payment modal.
                        @else
                            Customer has not submitted a Canva email yet.
                        @endif
                    </p>
                </div>
            </div>

            <!-- Payment & Gateway Details -->
            <div class="glass-card p-6 rounded-2xl border-cyan-500/20 space-y-3 text-xs">
                <h2 class="text-sm font-bold text-white uppercase tracking-wider font-mono">Payment Gateway Records</h2>
                
                @if($order->payment)
                    <div class="space-y-2 font-mono">
                        <div class="flex justify-between py-1 border-b border-slate-800">
                            <span class="text-slate-400">Gateway:</span>
                            <span class="text-white uppercase">{{ $order->payment->gateway }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800">
                            <span class="text-slate-400">Gateway Order ID:</span>
                            <span class="text-cyan-300">{{ $order->payment->gateway_order_id ?: '—' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800">
                            <span class="text-slate-400">Gateway Payment ID:</span>
                            <span class="text-cyan-300">{{ $order->payment->gateway_payment_id ?: '—' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800">
                            <span class="text-slate-400">Capture Status:</span>
                            <span class="text-emerald-400 uppercase font-bold">{{ $order->payment->status }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-slate-500">No gateway transaction captured.</p>
                @endif
            </div>

            <!-- Marketing Attribution (UTM & FBCLID) -->
            @if($order->utmAttribution)
            <div class="glass-card p-6 rounded-2xl border-cyan-500/20 space-y-2 text-xs">
                <h2 class="text-sm font-bold text-white uppercase tracking-wider font-mono">Marketing Attribution</h2>
                <div class="grid grid-cols-2 gap-2 font-mono text-[11px]">
                    <div><span class="text-slate-500">Source:</span> <span class="text-cyan-300">{{ $order->utmAttribution->utm_source ?: 'Direct' }}</span></div>
                    <div><span class="text-slate-500">Medium:</span> <span class="text-cyan-300">{{ $order->utmAttribution->utm_medium ?: '—' }}</span></div>
                    <div><span class="text-slate-500">Campaign:</span> <span class="text-cyan-300">{{ $order->utmAttribution->utm_campaign ?: '—' }}</span></div>
                    <div><span class="text-slate-500">FBCLID:</span> <span class="text-slate-400 truncate block">{{ $order->utmAttribution->fbclid ?: '—' }}</span></div>
                </div>
            </div>
            @endif

        </div>

        <!-- Right: Admin Actions & Audit History -->
        <div class="md:col-span-5 space-y-6">
            
            <!-- Admin Actions Box -->
            <div class="glass-card p-6 rounded-2xl border-cyan-500/25 space-y-4">
                <h2 class="text-sm font-bold text-white uppercase tracking-wider font-mono">Administrative Controls</h2>

                <!-- Mark as Paid Action -->
                @if(!$order->isPaid())
                <form method="POST" action="{{ route('admin.orders.mark_paid', $order) }}" class="space-y-2 p-3 bg-slate-900 rounded-xl border border-slate-800">
                    @csrf
                    <span class="text-[11px] font-bold text-amber-300 block">Mark Order as Paid (Manual)</span>
                    <input type="text" name="reason" placeholder="Audit Reason (e.g. Bank transfer verified)" required
                           class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-white outline-none">
                    <button type="submit" class="btn-primary-glow w-full py-2 text-xs font-bold uppercase">
                        Confirm Paid & Allocate Stock
                    </button>
                </form>
                @endif

                <!-- Refund Action -->
                @if($order->isPaid())
                <form method="POST" action="{{ route('admin.orders.refund', $order) }}" onsubmit="return confirm('Process refund for this order?')"
                      class="space-y-2 p-3 bg-slate-900 rounded-xl border border-slate-800">
                    @csrf
                    <span class="text-[11px] font-bold text-rose-400 block">Process Refund</span>
                    <input type="text" name="refund_reason" placeholder="Refund reason for audit log" required
                           class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-white outline-none">
                    <button type="submit" class="bg-rose-900 hover:bg-rose-800 text-rose-200 border border-rose-700 w-full py-2 rounded-lg text-xs font-bold uppercase transition-colors">
                        Mark Order Refunded
                    </button>
                </form>
                @endif

                <!-- Add Note -->
                <form method="POST" action="{{ route('admin.orders.note', $order) }}" class="space-y-2">
                    @csrf
                    <label class="block text-xs font-bold text-slate-300">Internal Order Note</label>
                    <textarea name="note" rows="2" placeholder="Add staff comment..." required
                              class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white outline-none"></textarea>
                    <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-cyan-300 font-bold px-4 py-2 rounded-xl text-xs">
                        Save Note
                    </button>
                </form>

                <!-- Fulfillment Lifecycle Control -->
                <form method="POST" action="{{ route('admin.orders.fulfillment', $order) }}" class="space-y-2 p-3 bg-slate-900 rounded-xl border border-slate-800">
                    @csrf
                    @method('PATCH')
                    <span class="text-[11px] font-bold text-cyan-300 block font-mono">⚡ Fulfillment Lifecycle</span>
                    <div class="flex gap-2">
                        <select name="fulfillment_status" class="flex-1 bg-slate-950 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-white outline-none">
                            <option value="pending" {{ $order->fulfillment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ $order->fulfillment_status === 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="delivered" {{ $order->fulfillment_status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="failed" {{ $order->fulfillment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="manual_review" {{ $order->fulfillment_status === 'manual_review' ? 'selected' : '' }}>Manual Review</option>
                        </select>
                        <button type="submit" class="bg-cyan-600 hover:bg-cyan-500 text-white px-3 py-1.5 rounded-lg text-xs font-bold uppercase transition-colors">
                            Update
                        </button>
                    </div>
                </form>

                <!-- Canva Pro Bonus Management -->
                <form method="POST" action="{{ route('admin.orders.canva_status', $order) }}" class="space-y-2 p-3 bg-slate-900 rounded-xl border border-purple-500/30">
                    @csrf
                    @method('PATCH')
                    <span class="text-[11px] font-bold text-purple-300 block font-mono">🎁 Canva Pro Bonus Management</span>
                    <div>
                        <label class="block text-[10px] text-slate-400 uppercase font-mono mb-1">Customer Canva Email</label>
                        <input type="email" name="canva_email" value="{{ $order->canva_email }}" placeholder="canva_user@gmail.com"
                               class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-white outline-none font-mono">
                    </div>
                    <div class="flex gap-2">
                        <select name="canva_bonus_status" class="flex-1 bg-slate-950 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-white outline-none">
                            <option value="not_requested" {{ $order->canva_bonus_status === 'not_requested' ? 'selected' : '' }}>Not Requested</option>
                            <option value="requested" {{ $order->canva_bonus_status === 'requested' ? 'selected' : '' }}>Requested (Pending)</option>
                            <option value="processing" {{ $order->canva_bonus_status === 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="activated" {{ $order->canva_bonus_status === 'activated' ? 'selected' : '' }}>Activated (Bonus Sent)</option>
                            <option value="skipped" {{ $order->canva_bonus_status === 'skipped' ? 'selected' : '' }}>Skipped by User</option>
                            <option value="failed" {{ $order->canva_bonus_status === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                        <button type="submit" class="bg-purple-600 hover:bg-purple-500 text-white px-3 py-1.5 rounded-lg text-xs font-bold uppercase transition-colors">
                            Save
                        </button>
                    </div>
                </form>

                @if($order->notes)
                <div class="p-3 bg-slate-900/60 rounded-xl border border-slate-800 text-[11px] text-slate-300 whitespace-pre-line font-mono">
                    {{ $order->notes }}
                </div>
                @endif
            </div>

            <!-- Audit Trail for this Order -->
            <div class="glass-card p-6 rounded-2xl border-cyan-500/20 space-y-3">
                <h2 class="text-sm font-bold text-white uppercase tracking-wider font-mono">Audit History</h2>

                <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                    @forelse($auditLogs as $log)
                    <div class="p-2.5 bg-slate-900/60 rounded-lg border border-slate-800 text-xs space-y-1">
                        <div class="flex items-center justify-between font-mono text-[10px]">
                            <span class="text-cyan-300 font-bold">{{ $log->action }}</span>
                            <span class="text-slate-500">{{ $log->created_at->format('M d, H:i') }}</span>
                        </div>
                        @if($log->details)
                            <p class="text-[11px] text-slate-400 font-mono">{{ $log->details }}</p>
                        @endif
                        <div class="text-[9px] text-slate-600 font-mono">IP: {{ $log->ip_address }}</div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-500">No audit records for this order yet.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
