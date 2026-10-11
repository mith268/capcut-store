<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('display_order')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:categories,slug',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'display_order' => 'integer|min:0',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['icon'] = $validated['icon'] ?? 'sparkles';

        $category = Category::create($validated);

        AuditLog::logAction('category_created', 'Category', (string) $category->id, [
            'name' => $category->name,
            'slug' => $category->slug,
        ]);

        return back()->with('success', "Category '{$category->name}' created successfully.");
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:categories,slug,' . $category->id,
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'display_order' => 'integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['icon'] = $validated['icon'] ?? 'sparkles';

        $category->update($validated);

        AuditLog::logAction('category_updated', 'Category', (string) $category->id, [
            'name' => $category->name,
        ]);

        return back()->with('success', "Category '{$category->name}' updated successfully.");
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', "Cannot delete category '{$category->name}' because it contains {$category->products()->count()} products. Reassign or delete those products first.");
        }

        $name = $category->name;
        $id = $category->id;
        $category->delete();

        AuditLog::logAction('category_deleted', 'Category', (string) $id, ['name' => $name]);

        return back()->with('success', "Category '{$name}' deleted.");
    }
}
