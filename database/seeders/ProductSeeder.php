<?php

namespace Database\Seeders;

use App\Models\InventoryCredential;
use App\Models\Product;
use App\Models\ProductFeature;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $price = (float) env('CAPCUT_PRODUCT_SALE_PRICE', 299.00);
        $originalPrice = (float) env('CAPCUT_PRODUCT_PRICE', 499.00);

        $product = Product::updateOrCreate(
            ['slug' => 'capcut-pro-team-1-month'],
            [
                'name' => 'CapCut Pro — 1 Month',
                'description' => 'CapCut Pro 1 Month access with 1200 AI Credits, plus a Canva Pro 1 Year bonus. Supports mobile, Windows and Mac, with access for up to 2 devices. Backed by 30-day warranty.',
                'short_description' => 'CapCut Pro — 1 Month with 1200 AI Credits + Canva Pro 1 Year Bonus.',
                'price' => $originalPrice,
                'sale_price' => $price,
                'currency' => 'INR',
                'duration' => '1 Month',
                'credits' => 1200,
                'warranty_days' => 30,
                'stock_mode' => 'digital_credentials',
                'active' => true,
                'featured' => true,
                'hero_title' => 'CapCut Pro — 1 Month',
                'hero_subtitle' => '1200 AI Credits + Canva Pro 1 Year Bonus',
                'cta_text' => '⚡ Get CapCut Pro Now',
            ]
        );

        $features = [
            [
                'title' => 'CapCut Pro Access',
                'description' => 'Premium CapCut Pro access for 1 month according to the selected product plan.',
                'icon' => 'zap',
                'display_order' => 1,
            ],
            [
                'title' => '1200 AI Credits',
                'description' => 'Includes 1200 AI credits for auto-captions, AI effects, and avatars.',
                'icon' => 'award',
                'display_order' => 2,
            ],
            [
                'title' => 'Premium Editing Tools',
                'description' => 'Access all Pro transitions, filters, speed curves, and high-resolution exports.',
                'icon' => 'sliders',
                'display_order' => 3,
            ],
            [
                'title' => 'Canva Pro 1 Year Bonus',
                'description' => 'Receive Canva Pro with 1-year validity as a complimentary bonus after completing your purchase.',
                'icon' => 'gift',
                'display_order' => 4,
            ],
            [
                'title' => '2 Devices Supported',
                'description' => 'Supports mobile, Windows PC / laptop, and MacBook / Mac (up to 2 devices).',
                'icon' => 'laptop',
                'display_order' => 5,
            ],
            [
                'title' => '30-Day Warranty',
                'description' => 'Product support/warranty for 30 days subject to the stated usage rules and applicable limitations.',
                'icon' => 'shield-check',
                'display_order' => 6,
            ],
            [
                'title' => 'Fast Digital Delivery',
                'description' => 'Digital delivery after successful purchase/payment confirmation.',
                'icon' => 'send',
                'display_order' => 7,
            ],
        ];

        ProductFeature::where('product_id', $product->id)->delete();

        foreach ($features as $feature) {
            ProductFeature::create(array_merge($feature, ['product_id' => $product->id]));
        }

        // Add 2 demo credentials for testing environment (can be replaced/cleared by admin)
        if (InventoryCredential::where('product_id', $product->id)->count() === 0) {
            $demoAccounts = [
                ['email' => 'pro.editor.alpha@digitalflow.io', 'password' => 'CapCutTeam#2026!a'],
                ['email' => 'pro.editor.beta@digitalflow.io', 'password' => 'CapCutTeam#2026!b'],
                ['email' => 'pro.editor.gamma@digitalflow.io', 'password' => 'CapCutTeam#2026!c'],
            ];

            foreach ($demoAccounts as $account) {
                $cred = new InventoryCredential();
                $cred->product_id = $product->id;
                $cred->email = $account['email'];
                $cred->password = $account['password'];
                $cred->status = 'available';
                $cred->save();
            }
        }
    }
}
