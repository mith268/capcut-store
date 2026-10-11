<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\WarrantyPolicy;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsappService
    ) {}

    public function index()
    {
        $product = Product::with(['features' => function ($q) {
            $q->where('is_active', true)->orderBy('display_order');
        }])->where('active', true)->first();

        if (!$product) {
            abort(404, 'Product configuration not found.');
        }

        $faqs = Faq::active()->get();
        $testimonials = Testimonial::published()->get();
        $warranty = WarrantyPolicy::where('is_active', true)->first();
        $settings = Setting::getAllPublic();

        $availableStock = $product->available_stock;
        $whatsappUrl = $this->whatsappService->generateCheckoutUrl($product);
        $whatsappSupportUrl = $this->whatsappService->generateSupportUrl();

        // Calculate verified orders completed (genuine count from DB or admin-configured)
        $dbCompletedOrders = Order::whereIn('payment_status', ['paid', 'delivered'])->count();
        $configuredCompleted = (int) ($settings['orders_completed_count'] ?? 0);
        $showCompletedOrders = ($settings['show_completed_orders'] ?? '0') === '1';
        $completedOrdersCount = max($dbCompletedOrders, $configuredCompleted);

        // Build Schema.org structured data (Product, Offer, FAQPage, Organization)
        $schemaData = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => url('/') . '#organization',
                    'name' => $settings['store_name'] ?? 'CapCut Pro Digital Store',
                    'url' => url('/'),
                    'description' => 'Independent digital service provider for video editing software accounts and digital subscriptions.',
                ],
                [
                    '@type' => 'Product',
                    '@id' => url('/') . '#product',
                    'name' => $product->name,
                    'description' => $product->description,
                    'sku' => $product->slug,
                    'brand' => [
                        '@type' => 'Brand',
                        'name' => 'Independent Digital Service',
                    ],
                    'offers' => [
                        '@type' => 'Offer',
                        'url' => route('checkout.show'),
                        'priceCurrency' => $product->currency,
                        'price' => number_format($product->effective_price, 2, '.', ''),
                        'priceValidUntil' => now()->addDays(30)->toDateString(),
                        'itemCondition' => 'https://schema.org/NewCondition',
                        'availability' => $availableStock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/LimitedAvailability',
                    ],
                ],
            ],
        ];

        if ($faqs->isNotEmpty()) {
            $faqItems = [];
            foreach ($faqs as $faq) {
                $faqItems[] = [
                    '@type' => 'Question',
                    'name' => $faq->question,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => strip_tags($faq->answer),
                    ],
                ];
            }
            $schemaData['@graph'][] = [
                '@type' => 'FAQPage',
                'mainEntity' => $faqItems,
            ];
        }

        return view('home.index', compact(
            'product',
            'faqs',
            'testimonials',
            'warranty',
            'settings',
            'availableStock',
            'whatsappUrl',
            'whatsappSupportUrl',
            'completedOrdersCount',
            'showCompletedOrders',
            'schemaData'
        ));
    }
}
