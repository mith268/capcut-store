@extends('layouts.admin')

@section('title', 'Categories — Convora Admin')
@section('header_title', 'Category Management')

@section('content')
<div class="space-y-6" x-data="{ createModal: false, editModal: false, editData: {} }">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-white tracking-tight">Product Categories</h1>
            <p class="text-xs text-slate-400 mt-1">Organize your digital products, subscriptions, and licenses for the public store.</p>
        </div>
        <button type="button" @click="createModal = true" class="btn-primary-glow px-4 py-2.5 text-xs font-bold uppercase tracking-wider flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add Category</span>
        </button>
    </div>

    <!-- Categories List -->
    <div class="glass-card rounded-2xl overflow-hidden border-cyan-500/20">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 uppercase font-mono text-[11px] bg-slate-900/60">
                        <th class="py-3.5 px-4">Order</th>
                        <th class="py-3.5 px-4">Category Name</th>
                        <th class="py-3.5 px-4">Slug</th>
                        <th class="py-3.5 px-4">Icon</th>
                        <th class="py-3.5 px-4">Products</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($categories as $category)
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="py-3 px-4 font-mono text-slate-400">#{{ $category->display_order }}</td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-white text-sm">{{ $category->name }}</div>
                            @if($category->description)
                                <div class="text-[11px] text-slate-400 truncate max-w-xs">{{ $category->description }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-mono text-cyan-300">{{ $category->slug }}</td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-800 text-slate-300 font-mono text-[11px]">
                                {{ $category->icon ?: 'sparkles' }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $category->products_count > 0 ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : 'bg-slate-800 text-slate-400' }}">
                                {{ $category->products_count }} {{ Str::plural('product', $category->products_count) }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            @if($category->is_active)
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-950/60 text-emerald-300 border border-emerald-500/30">Active</span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-800 text-slate-400">Inactive</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button type="button" @click="editData = {{ json_encode($category) }}; editModal = true" class="p-1.5 rounded-lg bg-slate-800 text-cyan-300 hover:bg-slate-700 transition-colors" title="Edit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Are you sure you want to delete category {{ $category->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-slate-800 text-rose-400 hover:bg-rose-950/40 transition-colors" title="Delete">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">
                            No categories created yet. Click "Add Category" above to create your first category.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Category Modal -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div @click.away="createModal = false" class="bg-[#0B101B] border border-cyan-500/30 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Create New Category</h3>
                <button type="button" @click="createModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Category Name *</label>
                    <input type="text" name="name" required placeholder="e.g., Design Tools" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:border-cyan-400 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Slug (URL)</label>
                    <input type="text" name="slug" placeholder="Auto-generated if blank" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white font-mono focus:border-cyan-400 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Icon Keyword</label>
                        <input type="text" name="icon" value="sparkles" placeholder="palette, key, sparkles" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:border-cyan-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Display Order</label>
                        <input type="number" name="display_order" value="1" min="0" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white font-mono focus:border-cyan-400 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Short description for SEO and category page" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:border-cyan-400 outline-none"></textarea>
                </div>

                <div class="flex items-center space-x-2 pt-1">
                    <input type="checkbox" name="is_active" id="create_is_active" value="1" checked class="rounded bg-slate-900 border-slate-700 text-cyan-400 focus:ring-0">
                    <label for="create_is_active" class="text-xs text-slate-300">Category is active and visible in store</label>
                </div>

                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-800">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white">Cancel</button>
                    <button type="submit" class="btn-primary-glow px-5 py-2 text-xs font-bold uppercase tracking-wider">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div @click.away="editModal = false" class="bg-[#0B101B] border border-cyan-500/30 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Edit Category</h3>
                <button type="button" @click="editModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" :action="'{{ url('admin/categories') }}/' + editData.id" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Category Name *</label>
                    <input type="text" name="name" x-model="editData.name" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:border-cyan-400 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Slug *</label>
                    <input type="text" name="slug" x-model="editData.slug" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white font-mono focus:border-cyan-400 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Icon Keyword</label>
                        <input type="text" name="icon" x-model="editData.icon" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:border-cyan-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Display Order</label>
                        <input type="number" name="display_order" x-model="editData.display_order" min="0" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white font-mono focus:border-cyan-400 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Description</label>
                    <textarea name="description" x-model="editData.description" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:border-cyan-400 outline-none"></textarea>
                </div>

                <div class="flex items-center space-x-2 pt-1">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1" :checked="editData.is_active" class="rounded bg-slate-900 border-slate-700 text-cyan-400 focus:ring-0">
                    <label for="edit_is_active" class="text-xs text-slate-300">Category is active</label>
                </div>

                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-800">
                    <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white">Cancel</button>
                    <button type="submit" class="btn-primary-glow px-5 py-2 text-xs font-bold uppercase tracking-wider">Update Category</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
