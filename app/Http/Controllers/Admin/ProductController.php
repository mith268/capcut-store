<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\ProductFeature;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::withCount(['credentials as available_stock' => function ($q) {
            $q->where('status', 'available');
        }])->with('features')->get();

        return view('admin.products.index', compact('products'));
    }

    public function edit(Product $product)
    {
        $product->load('features');
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:10',
            'duration' => 'required|string|max:50',
            'credits' => 'required|integer|min:0',
            'warranty_days' => 'required|integer|min:0',
            'active' => 'boolean',
            'featured' => 'boolean',
            'hero_title' => 'nullable|string|max:200',
            'hero_subtitle' => 'nullable|string|max:500',
            'cta_text' => 'nullable|string|max:100',
        ]);

        $oldPrice = $product->price;
        $oldSalePrice = $product->sale_price;

        $validated['active'] = $request->boolean('active');
        $validated['featured'] = $request->boolean('featured');

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

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

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
