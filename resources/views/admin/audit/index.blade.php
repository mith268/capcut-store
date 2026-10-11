@extends('layouts.admin')

@section('title', 'Security Audit Trail — Admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white">Security & Audit Logs</h1>
            <p class="text-xs text-slate-400 mt-1">Immutable audit records of sensitive operations, credential views, and administrative actions.</p>
        </div>

        <form method="GET" action="{{ route('admin.audit.index') }}" class="flex space-x-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search action, entity, IP..."
                   class="bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-3.5 py-1.5 text-xs text-white placeholder-slate-500 outline-none w-64">
            <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-cyan-300 font-bold px-3 py-1.5 rounded-xl text-xs border border-cyan-500/30">
                Search
            </button>
        </form>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden p-6 space-y-4 border-cyan-500/25">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="text-slate-400 border-b border-slate-800 uppercase font-mono text-[10px]">
                    <tr>
                        <th class="py-3 px-3">Timestamp</th>
                        <th class="py-3 px-3">Actor</th>
                        <th class="py-3 px-3">Action</th>
                        <th class="py-3 px-3">Target Entity</th>
                        <th class="py-3 px-3">Details</th>
                        <th class="py-3 px-3 text-right">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300 font-mono text-[11px]">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-900/40">
                        <td class="py-3 px-3 text-slate-400 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td class="py-3 px-3 font-sans">
                            {{ $log->admin ? $log->admin->name : ($log->action === 'customer_credential_revealed' ? 'Customer' : 'System') }}
                        </td>
                        <td class="py-3 px-3">
                            <span class="text-cyan-300 font-bold">{{ $log->action }}</span>
                        </td>
                        <td class="py-3 px-3 text-slate-400">
                            {{ $log->entity }} {{ $log->entity_id ? '#' . $log->entity_id : '' }}
                        </td>
                        <td class="py-3 px-3 max-w-xs truncate text-slate-400 font-sans text-xs">
                            {{ $log->details ?: '—' }}
                        </td>
                        <td class="py-3 px-3 text-right text-slate-500">{{ $log->ip_address }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500 font-sans">No audit events recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
