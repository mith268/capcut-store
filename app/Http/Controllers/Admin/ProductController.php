<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductFeature;
use App\Models\ProductMedia;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'variants'])
            ->withCount(['credentials as available_stock' => function ($q) {
                $q->where('status', 'available');
            }])
            ->orderBy('display_order');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        $products = $query->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:200',
            'slug' => 'nullable|string|max:200|unique:products,slug',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:10',
            'duration' => 'required|string|max:50',
            'credits' => 'nullable|integer|min:0',
            'warranty_days' => 'nullable|integer|min:0',
            'status' => 'required|in:active,draft,inactive,archived',
            'fulfillment_mode' => 'required|in:key_inventory,account_access,file_download,manual',
            'inventory_type' => 'required|in:finite,unlimited',
            'purchase_mode' => 'required|in:both,checkout,whatsapp',
            'show_in_store' => 'boolean',
            'featured' => 'boolean',
            'badge_text' => 'nullable|string|max:50',
            'cta_text' => 'nullable|string|max:100',
            'primary_image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        if (empty($validated['sku'])) {
            $validated['sku'] = 'SKU-' . strtoupper(Str::random(6));
        }

        $validated['show_in_store'] = $request->boolean('show_in_store', true);
        $validated['featured'] = $request->boolean('featured', false);
        $validated['active'] = $validated['status'] === 'active';
        $validated['credits'] = $validated['credits'] ?? 0;
        $validated['warranty_days'] = $validated['warranty_days'] ?? 30;

        // Handle Image upload
        if ($request->hasFile('primary_image_file') && $request->file('primary_image_file')->isValid()) {
            $path = $request->file('primary_image_file')->store('products', 'public');
            $validated['primary_image'] = $path;
        }

        unset($validated['primary_image_file']);

        $product = Product::create($validated);

        // Auto-create default variant
        ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Default Plan (' . $product->duration . ')',
            'sku' => $product->sku,
            'price' => $product->price,
            'sale_price' => $product->sale_price,
            'duration' => $product->duration,
            'credits' => $product->credits,
            'warranty_days' => $product->warranty_days,
            'fulfillment_mode' => $product->fulfillment_mode,
            'is_default' => true,
            'is_active' => true,
            'display_order' => 1,
        ]);

        AuditLog::logAction('product_created', 'Product', (string) $product->id, [
            'name' => $product->name,
            'price' => $product->price,
        ]);

        return redirect()->route('admin.products.edit', $product)
            ->with('success', "Product '{$product->name}' created successfully.");
    }

    public function edit(Product $product)
    {
        $product->load(['category', 'variants', 'features', 'media']);
        $categories = Category::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:200',
            'slug' => 'required|string|max:200|unique:products,slug,' . $product->id,
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:10',
            'duration' => 'required|string|max:50',
            'credits' => 'nullable|integer|min:0',
            'warranty_days' => 'nullable|integer|min:0',
            'status' => 'required|in:active,draft,inactive,archived',
            'fulfillment_mode' => 'required|in:key_inventory,account_access,file_download,manual',
            'inventory_type' => 'required|in:finite,unlimited',
            'purchase_mode' => 'required|in:both,checkout,whatsapp',
            'show_in_store' => 'boolean',
            'featured' => 'boolean',
            'badge_text' => 'nullable|string|max:50',
            'hero_title' => 'nullable|string|max:200',
            'hero_subtitle' => 'nullable|string|max:500',
            'cta_text' => 'nullable|string|max:100',
            'bonus_title' => 'nullable|string|max:200',
            'bonus_description' => 'nullable|string|max:500',
            'video_provider' => 'nullable|string|max:30',
            'video_id' => 'nullable|string|max:100',
            'whatsapp_message' => 'nullable|string|max:500',
            'activation_instructions' => 'nullable|string',
            'exclusions' => 'nullable|string',
            'warranty_terms' => 'nullable|string',
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:300',
            'primary_image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
        ]);

        $oldPrice = $product->price;
        $oldSalePrice = $product->sale_price;

        $validated['show_in_store'] = $request->boolean('show_in_store');
        $validated['featured'] = $request->boolean('featured');
        $validated['active'] = $validated['status'] === 'active';
        $validated['credits'] = $validated['credits'] ?? 0;
        $validated['warranty_days'] = $validated['warranty_days'] ?? 30;

        // Image upload
        if ($request->hasFile('primary_image_file') && $request->file('primary_image_file')->isValid()) {
            $path = $request->file('primary_image_file')->store('products', 'public');
            $validated['primary_image'] = $path;
        }

        unset($validated['primary_image_file']);

        $product->update($validated);

        if ($oldPrice != $validated['price'] || $oldSalePrice != $validated['sale_price']) {
            AuditLog::logAction('product_price_changed', 'Product', (string) $product->id, [
                'old_price' => $oldPrice,
                'new_price' => $validated['price'],
                'old_sale_price' => $oldSalePrice,
                'new_sale_price' => $validated['sale_price'],
            ]);
        } else {
            AuditLog::logAction('product_updated', 'Product', (string) $product->id);
        }

        return redirect()->route('admin.products.edit', $product)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        // Safety: check if product has paid orders
        $orderCount = $product->orders()->count();

        if ($orderCount > 0) {
            // Soft archive instead of delete to maintain order history integrity
            $product->update(['status' => 'archived', 'active' => false, 'show_in_store' => false]);
            AuditLog::logAction('product_archived', 'Product', (string) $product->id, [
                'reason' => 'Has order history, safely archived.',
            ]);
            return back()->with('info', "Product '{$product->name}' has {$orderCount} orders on record. It has been archived and hidden from the store.");
        }

        $name = $product->name;
        $id = $product->id;
        $product->delete();

        AuditLog::logAction('product_deleted', 'Product', (string) $id, ['name' => $name]);

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$name}' deleted successfully.");
    }

    // Variant Management
    public function storeVariant(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'sku' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'duration' => 'nullable|string|max:50',
            'device_limit' => 'nullable|integer|min:1',
            'credits' => 'nullable|integer|min:0',
            'warranty_days' => 'nullable|integer|min:0',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'display_order' => 'integer|min:0',
        ]);

        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($validated['is_default']) {
            $product->variants()->update(['is_default' => false]);
        }

        $variant = $product->variants()->create($validated);

        AuditLog::logAction('product_variant_created', 'ProductVariant', (string) $variant->id, [
            'product_id' => $product->id,
            'name' => $variant->name,
        ]);

        return back()->with('success', "Variant '{$variant->name}' added.");
    }

    public function updateVariant(Request $request, ProductVariant $variant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'sku' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'duration' => 'nullable|string|max:50',
            'device_limit' => 'nullable|integer|min:1',
            'credits' => 'nullable|integer|min:0',
            'warranty_days' => 'nullable|integer|min:0',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'display_order' => 'integer|min:0',
        ]);

        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active');

        if ($validated['is_default']) {
            $variant->product->variants()->where('id', '!=', $variant->id)->update(['is_default' => false]);
        }

        $variant->update($validated);

        AuditLog::logAction('product_variant_updated', 'ProductVariant', (string) $variant->id);

        return back()->with('success', "Variant '{$variant->name}' updated.");
    }

    public function deleteVariant(ProductVariant $variant)
    {
        $productId = $variant->product_id;
        $name = $variant->name;

        $variant->delete();

        AuditLog::logAction('product_variant_deleted', 'ProductVariant', (string) $variant->id, [
            'name' => $name,
            'product_id' => $productId,
        ]);

        return back()->with('success', "Variant '{$name}' deleted.");
    }

    // Media Gallery Management
    public function storeMedia(Request $request, Product $product)
    {
        $request->validate([
            'media_file' => 'required|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'alt_text' => 'nullable|string|max:150',
        ]);

        $file = $request->file('media_file');
        $path = $file->store('products', 'public');

        $media = $product->media()->create([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'alt_text' => $request->input('alt_text', $product->name),
            'display_order' => $product->media()->count() + 1,
        ]);

        AuditLog::logAction('product_media_added', 'ProductMedia', (string) $media->id, [
            'product_id' => $product->id,
        ]);

        return back()->with('success', 'Image uploaded to gallery.');
    }

    public function deleteMedia(ProductMedia $media)
    {
        $productId = $media->product_id;
        $media->delete();

        AuditLog::logAction('product_media_deleted', 'ProductMedia', (string) $media->id, [
            'product_id' => $productId,
        ]);

        return back()->with('success', 'Image removed from gallery.');
    }

    // Features Management
    public function storeFeature(Request $request, Product $product)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string|max:300',
            'icon' => 'nullable|string|max:50',
            'display_order' => 'integer',
        ]);

        $product->features()->create($validated);

        AuditLog::logAction('product_feature_added', 'Product', (string) $product->id, $validated);

        return back()->with('success', 'Feature added successfully.');
    }

    public function deleteFeature(ProductFeature $feature)
    {
        $productId = $feature->product_id;
        $feature->delete();

        AuditLog::logAction('product_feature_deleted', 'Product', (string) $productId);

        return back()->with('success', 'Feature removed.');
    }
}
