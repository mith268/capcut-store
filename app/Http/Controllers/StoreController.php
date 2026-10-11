<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsappService
    ) {}

    public function index(Request $request)
    {
        $categories = Category::active()
            ->withCount(['products' => function ($q) {
                $q->storeCatalog();
            }])
            ->get();

        $selectedCategory = null;
        if ($request->filled('category')) {
            $selectedCategory = Category::where('slug', $request->input('category'))->first();
        }

        $query = Product::storeCatalog()->with(['category', 'variants']);

        if ($selectedCategory) {
            $query->where('category_id', $selectedCategory->id);
        }

        // Search
        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        // Price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        // Fulfillment mode filter
        if ($request->filled('fulfillment')) {
            $query->where('fulfillment_mode', $request->input('fulfillment'));
        }

        // In-stock only
        if ($request->boolean('in_stock')) {
            $query->where(function ($q) {
                $q->where('inventory_type', 'unlimited')
                    ->orWhereHas('credentials', function ($cq) {
                        $cq->where('status', 'available');
                    });
            });
        }

        // Sorting
        $sort = $request->input('sort', 'featured');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'featured':
            default:
                $query->orderBy('featured', 'desc')->orderBy('display_order', 'asc');
                break;
        }

        $products = $query->paginate(12)->withQueryString();

        // Featured deals (3 items on desktop) - shown when not filtering heavily
        $featuredDeals = collect();
        if (!$request->filled('q') && !$request->filled('category') && !$request->filled('sort')) {
            $featuredDeals = Product::storeCatalog()
                ->featured()
                ->with(['category', 'variants'])
                ->take(3)
                ->get();
        }

        $settings = Setting::getAllPublic();
        $storeTheme = $settings['store_theme'] ?? 'electric_lime';

        return view('store.index', compact(
            'products',
            'categories',
            'selectedCategory',
            'featuredDeals',
            'settings',
            'storeTheme'
        ));
    }

    public function category(string $slug, Request $request)
    {
        $category = Category::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $request->merge(['category' => $slug]);
        return $this->index($request);
    }

    public function product(string $slug)
    {
        $product = Product::with(['category', 'variants' => function ($q) {
            $q->where('is_active', true)->orderBy('display_order');
        }, 'features', 'media'])
            ->where('slug', $slug)
            ->where('status', '!=', 'archived')
            ->firstOrFail();

        $relatedProducts = Product::storeCatalog()
            ->where('id', '!=', $product->id)
            ->when($product->category_id, function ($q) use ($product) {
                $q->where('category_id', $product->category_id);
            })
            ->take(4)
            ->get();

        $whatsappUrl = $this->whatsappService->generateCheckoutUrl($product);
        $settings = Setting::getAllPublic();

        return view('store.product', compact(
            'product',
            'relatedProducts',
            'whatsappUrl',
            'settings'
        ));
    }
}
