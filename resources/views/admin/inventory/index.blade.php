@extends('layouts.admin')

@section('title', 'Credential Inventory (Vault) — Admin')

@section('content')
<div class="space-y-6" x-data="{
    addModalOpen: false,
    importModalOpen: false,
    revealModalOpen: false,
    selectedId: null,
    adminPassword: '',
    revealLoading: false,
    revealError: '',
    revealedCred: null,

    openReveal(id) {
        this.selectedId = id;
        this.adminPassword = '';
        this.revealError = '';
        this.revealedCred = null;
        this.revealModalOpen = true;
    },

    submitReveal() {
        if (!this.adminPassword) {
            this.revealError = 'Please enter your administrator password.';
            return;
        }
        this.revealLoading = true;
        this.revealError = '';

        fetch(`/admin/inventory/${this.selectedId}/reveal`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
            },
            body: JSON.stringify({ admin_password: this.adminPassword })
        })
        .then(res => res.json())
        .then(data => {
            this.revealLoading = false;
            if (data.success) {
                this.revealedCred = data;
            } else {
                this.revealError = data.message || 'Authentication failed.';
            }
        })
        .catch(() => {
            this.revealLoading = false;
            this.revealError = 'Error communicating with server.';
        });
    }
}">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white">Credential Vault (Encrypted)</h1>
            <p class="text-xs text-slate-400 mt-1">
                Passwords and emails are stored using AES-256-CBC encryption at rest and decrypted only upon authorized delivery.
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <button @click="importModalOpen = true" class="bg-slate-800 hover:bg-slate-700 border border-cyan-500/30 text-cyan-300 font-bold px-4 py-2 rounded-xl text-xs uppercase">
                📥 Import CSV
            </button>
            <button @click="addModalOpen = true" class="btn-primary-glow px-4 py-2 text-xs font-bold uppercase">
                + Add Credential
            </button>
        </div>
    </div>

    <!-- Inventory Stats Row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('admin.inventory.index') }}" class="glass-card p-4 rounded-xl text-center {{ !$status ? 'border-cyan-400' : '' }}">
            <span class="text-[10px] font-mono uppercase text-slate-400 block">TOTAL CREDENTIALS</span>
            <span class="text-xl font-black text-white font-mono">{{ $stats['total'] }}</span>
        </a>
        <a href="{{ route('admin.inventory.index', ['status' => 'available']) }}" class="glass-card p-4 rounded-xl text-center {{ $status === 'available' ? 'border-cyan-400' : '' }}">
            <span class="text-[10px] font-mono uppercase text-slate-400 block">AVAILABLE</span>
            <span class="text-xl font-black text-emerald-400 font-mono">{{ $stats['available'] }}</span>
        </a>
        <a href="{{ route('admin.inventory.index', ['status' => 'sold']) }}" class="glass-card p-4 rounded-xl text-center {{ $status === 'sold' ? 'border-cyan-400' : '' }}">
            <span class="text-[10px] font-mono uppercase text-slate-400 block">DELIVERED / SOLD</span>
            <span class="text-xl font-black text-cyan-300 font-mono">{{ $stats['sold'] }}</span>
        </a>
        <a href="{{ route('admin.inventory.index', ['status' => 'disabled']) }}" class="glass-card p-4 rounded-xl text-center {{ $status === 'disabled' ? 'border-cyan-400' : '' }}">
            <span class="text-[10px] font-mono uppercase text-slate-400 block">DISABLED</span>
            <span class="text-xl font-black text-slate-500 font-mono">{{ $stats['disabled'] }}</span>
        </a>
    </div>

    <!-- Table Card -->
    <div class="glass-card rounded-2xl overflow-hidden p-6 space-y-4">
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="text-slate-400 border-b border-slate-800 uppercase font-mono text-[10px]">
                    <tr>
                        <th class="py-3 px-3">ID</th>
                        <th class="py-3 px-3">Product</th>
                        <th class="py-3 px-3">Masked Email</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3">Linked Order</th>
                        <th class="py-3 px-3">Added Date</th>
                        <th class="py-3 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($credentials as $cred)
                    <tr class="hover:bg-slate-900/40">
                        <td class="py-3 px-3 font-mono font-bold text-slate-400">#{{ $cred->id }}</td>
                        <td class="py-3 px-3 font-medium text-white">{{ $cred->product->name }}</td>
                        <td class="py-3 px-3 font-mono text-cyan-300 font-semibold">{{ $cred->masked_email }}</td>
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $cred->status === 'available' ? 'bg-emerald-950 text-emerald-400 border border-emerald-500/30' : 
                                   ($cred->status === 'sold' ? 'bg-cyan-950 text-cyan-400 border border-cyan-500/30' : 'bg-slate-800 text-slate-400') }}">
                                {{ $cred->status }}
                            </span>
                        </td>
                        <td class="py-3 px-3 font-mono">
                            @if($cred->order)
                                <a href="{{ route('admin.orders.show', $cred->order) }}" class="text-cyan-400 hover:underline">
                                    {{ $cred->order->order_number }}
                                </a>
                            @else
                                <span class="text-slate-600">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-slate-400 font-mono">{{ $cred->created_at->format('M d, Y') }}</td>
                        <td class="py-3 px-3 text-right space-x-2">
                            <button @click="openReveal({{ $cred->id }})" class="text-cyan-400 hover:text-cyan-300 font-bold">
                                Reveal
                            </button>

                            @if($cred->status !== 'sold')
                                <form method="POST" action="{{ route('admin.inventory.toggle', $cred) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-amber-400 hover:text-amber-300">
                                        {{ $cred->status === 'available' ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.inventory.destroy', $cred) }}" class="inline" onsubmit="return confirm('Permanently delete this credential?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">No credentials found for this filter.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4">
            {{ $credentials->links() }}
        </div>

    </div>

    <!-- Modal 1: Add Single Credential -->
    <div x-show="addModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="glass-card max-w-md w-full p-6 rounded-2xl border-cyan-500/30 space-y-4">
            <h3 class="text-lg font-bold text-white">Add Credential to Vault</h3>
            
            <form method="POST" action="{{ route('admin.inventory.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Target Product</label>
                    <select name="product_id" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-sm text-white">
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Account Login Email</label>
                    <input type="email" name="email" required placeholder="capcut.account@example.com"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-sm text-white outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Account Password</label>
                    <input type="text" name="password" required placeholder="SuperSecretPass#2026"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-sm text-white font-mono outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Specific Notes (Optional)</label>
                    <textarea name="notes" rows="2" placeholder="e.g. Profile 2 PIN: 1234"
                              class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white outline-none"></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 rounded-xl text-xs text-slate-400 hover:text-white">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary-glow px-5 py-2 text-xs font-bold uppercase">
                        Save Encrypted
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: CSV Import -->
    <div x-show="importModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="glass-card max-w-md w-full p-6 rounded-2xl border-cyan-500/30 space-y-4">
            <h3 class="text-lg font-bold text-white">Batch Import via CSV</h3>
            
            <p class="text-xs text-slate-300 leading-relaxed">
                Upload a standard CSV file. Format expected: <br>
                <code class="bg-slate-900 px-2 py-0.5 rounded text-cyan-300 font-mono text-[11px] block mt-1">email,password[,notes]</code>
            </p>

            <form method="POST" action="{{ route('admin.inventory.import_csv') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Target Product</label>
                    <select name="product_id" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-sm text-white">
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Choose CSV File</label>
                    <input type="file" name="csv_file" accept=".csv,.txt" required
                           class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-cyan-300 hover:file:bg-slate-700">
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="importModalOpen = false" class="px-4 py-2 rounded-xl text-xs text-slate-400 hover:text-white">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary-glow px-5 py-2 text-xs font-bold uppercase">
                        Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Secure Re-Authentication Credential Reveal -->
    <div x-show="revealModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-sm">
        <div class="glass-card max-w-md w-full p-6 rounded-2xl border-cyan-500/30 space-y-4">
            <h3 class="text-base font-bold text-white flex items-center">
                <span class="mr-2">🔐</span> Security Verification
            </h3>
            
            <div x-show="!revealedCred" class="space-y-4">
                <p class="text-xs text-slate-300">
                    To decrypt and view this credential, enter your current administrator account password. This action will be logged in the security audit trail.
                </p>

                <div>
                    <input type="password" x-model="adminPassword" placeholder="Your Admin Password"
                           class="w-full bg-slate-900 border border-slate-700 focus:border-cyan-400 rounded-xl px-4 py-2 text-sm text-white outline-none">
                    <span x-show="revealError" x-text="revealError" class="text-rose-400 text-xs mt-1 block"></span>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" @click="revealModalOpen = false" class="px-4 py-2 rounded-xl text-xs text-slate-400 hover:text-white">
                        Cancel
                    </button>
                    <button type="button" @click="submitReveal()" :disabled="revealLoading" class="btn-primary-glow px-5 py-2 text-xs font-bold uppercase">
                        <span x-show="!revealLoading">Confirm & Decrypt</span>
                        <span x-show="revealLoading" x-cloak>Verifying...</span>
                    </button>
                </div>
            </div>

            <!-- Revealed Content Box -->
            <div x-show="revealedCred" x-cloak class="space-y-3 pt-2">
                <div class="bg-slate-900 p-3 rounded-xl border border-slate-800 space-y-1">
                    <span class="text-[10px] text-slate-400 uppercase font-mono block">DECRYPTED EMAIL</span>
                    <span class="font-mono text-white text-sm font-bold select-all" x-text="revealedCred?.email"></span>
                </div>

                <div class="bg-slate-900 p-3 rounded-xl border border-slate-800 space-y-1">
                    <span class="text-[10px] text-slate-400 uppercase font-mono block">DECRYPTED PASSWORD</span>
                    <span class="font-mono text-cyan-300 text-sm font-bold select-all" x-text="revealedCred?.password"></span>
                </div>

                <template x-if="revealedCred?.notes">
                    <div class="bg-slate-900 p-3 rounded-xl border border-slate-800 space-y-1">
                        <span class="text-[10px] text-slate-400 uppercase font-mono block">NOTES</span>
                        <span class="text-xs text-slate-300" x-text="revealedCred?.notes"></span>
                    </div>
                </template>

                <div class="flex justify-end pt-2">
                    <button type="button" @click="revealModalOpen = false" class="btn-primary-glow px-4 py-2 text-xs font-bold">
                        Close Vault
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
