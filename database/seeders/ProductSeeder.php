<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\InventoryCredential;
use App\Models\Product;
use App\Models\ProductFeature;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all()->keyBy('slug');

        $subscriptionsCatId = $categories['subscriptions']->id ?? null;
        $designCatId = $categories['design-tools']->id ?? null;
        $softwareCatId = $categories['software-licenses']->id ?? null;

        // 1. CapCut Pro
        $capcut = Product::updateOrCreate(
            ['slug' => 'capcut-pro-team-1-month'],
            [
                'category_id' => $designCatId,
                'name' => 'CapCut Pro — 1 Month',
                'sku' => 'CP-PRO-1M',
                'description' => 'CapCut Pro 1 Month access with 1200 AI Credits, plus a Canva Pro 1 Year bonus. Supports mobile, Windows and Mac, with access for up to 2 devices. Backed by 30-day warranty.',
                'short_description' => 'CapCut Pro — 1 Month with 1200 AI Credits + Canva Pro 1 Year Bonus.',
                'price' => 1999.00,
                'sale_price' => 399.00,
                'currency' => 'INR',
                'duration' => '1 Month',
                'credits' => 1200,
                'warranty_days' => 30,
                'stock_mode' => 'digital_credentials',
                'status' => 'active',
                'active' => true,
                'featured' => true,
                'show_in_store' => true,
                'visibility' => 'public',
                'purchase_mode' => 'both',
                'fulfillment_mode' => 'key_inventory',
                'inventory_type' => 'finite',
                'primary_image' => 'assets/images/capcut-pro-sticker.png',
                'hero_title' => 'CapCut Pro — 1 Month',
                'hero_subtitle' => '1200 AI Credits + Canva Pro 1 Year Bonus',
                'cta_text' => 'GET CAPCUT PRO NOW',
                'badge_text' => 'HOT DEAL',
                'bonus_title' => '🎁 BONUS: CANVA PRO 1 YEAR FREE',
                'bonus_description' => 'Complimentary 1-year Canva Pro access after confirmed CapCut purchase.',
                'tags' => ['video editing', 'capcut', 'ai credits', 'canva pro'],
                'display_order' => 1,
            ]
        );

        // CapCut Variants
        $capcutVariants = [
            [
                'name' => '1 Month • 1200 AI Credits',
                'sku' => 'CP-1M',
                'price' => 1999.00,
                'sale_price' => 399.00,
                'duration' => '1 Month',
                'device_limit' => 2,
                'credits' => 1200,
                'warranty_days' => 30,
                'is_default' => true,
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'name' => '3 Months • 3600 AI Credits',
                'sku' => 'CP-3M',
                'price' => 4999.00,
                'sale_price' => 999.00,
                'duration' => '3 Months',
                'device_limit' => 2,
                'credits' => 3600,
                'warranty_days' => 90,
                'is_default' => false,
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'name' => '1 Year • 14400 AI Credits',
                'sku' => 'CP-1Y',
                'price' => 9999.00,
                'sale_price' => 2999.00,
                'duration' => '12 Months',
                'device_limit' => 2,
                'credits' => 14400,
                'warranty_days' => 365,
                'is_default' => false,
                'is_active' => true,
                'display_order' => 3,
            ],
        ];

        foreach ($capcutVariants as $v) {
            ProductVariant::updateOrCreate(
                ['product_id' => $capcut->id, 'sku' => $v['sku']],
                array_merge($v, ['product_id' => $capcut->id])
            );
        }

        $defaultCapcutVariant = ProductVariant::where('product_id', $capcut->id)->where('is_default', true)->first();

        // 2. ChatGPT Plus
        $chatgpt = Product::updateOrCreate(
            ['slug' => 'chatgpt-plus-shared'],
            [
                'category_id' => $subscriptionsCatId,
                'name' => 'ChatGPT Plus (Shared Account)',
                'sku' => 'CGPT-PLUS-1M',
                'description' => 'Experience GPT-4o, DALL-E 3, Advanced Data Analysis, and voice mode with 30 days of ChatGPT Plus membership.',
                'short_description' => 'Shared ChatGPT Plus account with GPT-4o, DALL-E, and Custom GPTs.',
                'price' => 1999.00,
                'sale_price' => 499.00,
                'currency' => 'INR',
                'duration' => '1 Month',
                'credits' => 0,
                'warranty_days' => 30,
                'stock_mode' => 'digital_credentials',
                'status' => 'active',
                'active' => true,
                'featured' => true,
                'show_in_store' => true,
                'visibility' => 'public',
                'purchase_mode' => 'both',
                'fulfillment_mode' => 'key_inventory',
                'inventory_type' => 'finite',
                'primary_image' => 'products/BRglp5EU7xatsxv0fBw6awvGvlnrKhbCf2vgoh1b.png',
                'cta_text' => 'GET CHATGPT PLUS',
                'badge_text' => 'POPULAR',
                'tags' => ['ai', 'chatgpt', 'openai', 'gpt-4o'],
                'display_order' => 2,
            ]
        );

        $chatgptVariant = ProductVariant::updateOrCreate(
            ['product_id' => $chatgpt->id, 'sku' => 'CGPT-1M'],
            [
                'product_id' => $chatgpt->id,
                'name' => '1 Month Access',
                'sku' => 'CGPT-1M',
                'price' => 1999.00,
                'sale_price' => 499.00,
                'duration' => '1 Month',
                'device_limit' => 1,
                'warranty_days' => 30,
                'is_default' => true,
                'is_active' => true,
                'display_order' => 1,
            ]
        );

        // 3. Canva Pro
        $canva = Product::updateOrCreate(
            ['slug' => 'canva-pro'],
            [
                'category_id' => $designCatId,
                'name' => 'Canva Pro (1 Year License)',
                'sku' => 'CANVA-PRO-1Y',
                'description' => 'Unlock unlimited premium templates, 100M+ stock photos, videos, Brand Kit, and background remover with Canva Pro for 1 Year.',
                'short_description' => 'Canva Pro 1 Year official access on your personal email.',
                'price' => 1499.00,
                'sale_price' => 299.00,
                'currency' => 'INR',
                'duration' => '12 Months',
                'credits' => 0,
                'warranty_days' => 365,
                'stock_mode' => 'digital_credentials',
                'status' => 'active',
                'active' => true,
                'featured' => true,
                'show_in_store' => true,
                'visibility' => 'public',
                'purchase_mode' => 'both',
                'fulfillment_mode' => 'account_access',
                'inventory_type' => 'unlimited',
                'primary_image' => 'products/6dZU2epLFxttSeuu3iBrXZuZxvKIixC179vkSEKd.png',
                'cta_text' => 'GET CANVA PRO',
                'badge_text' => 'BESTSELLER',
                'tags' => ['design', 'canva', 'templates', 'graphics'],
                'display_order' => 3,
            ]
        );

        ProductVariant::updateOrCreate(
            ['product_id' => $canva->id, 'sku' => 'CNV-1Y'],
            [
                'product_id' => $canva->id,
                'name' => '1 Year Personal Activation',
                'sku' => 'CNV-1Y',
                'price' => 1499.00,
                'sale_price' => 299.00,
                'duration' => '12 Months',
                'warranty_days' => 365,
                'is_default' => true,
                'is_active' => true,
                'display_order' => 1,
            ]
        );

        // 4. Windows 11 Pro
        $win11 = Product::updateOrCreate(
            ['slug' => 'windows-11-pro-retail-key'],
            [
                'category_id' => $softwareCatId,
                'name' => 'Windows 11 Professional Retail Key',
                'sku' => 'WIN11-PRO-KEY',
                'description' => '100% Genuine retail license key for Windows 11 Pro. Permanent activation, transferable, supports all language packs with direct Microsoft updates.',
                'short_description' => 'Genuine Lifetime Windows 11 Pro Retail activation key for 1 PC.',
                'price' => 2499.00,
                'sale_price' => 399.00,
                'currency' => 'INR',
                'duration' => 'Lifetime',
                'credits' => 0,
                'warranty_days' => 365,
                'stock_mode' => 'digital_credentials',
                'status' => 'active',
                'active' => true,
                'featured' => true,
                'show_in_store' => true,
                'visibility' => 'public',
                'purchase_mode' => 'both',
                'fulfillment_mode' => 'key_inventory',
                'inventory_type' => 'finite',
                'primary_image' => 'products/HLpRbVEFBEMNvDDr3Knv4h17KJkjG75jJEhbClHh.png',
                'cta_text' => 'BUY LICENSE KEY',
                'badge_text' => 'OFFICIAL LICENSE',
                'tags' => ['windows', 'microsoft', 'software license', 'os'],
                'display_order' => 4,
            ]
        );

        $win11Variant = ProductVariant::updateOrCreate(
            ['product_id' => $win11->id, 'sku' => 'WIN11-1PC'],
            [
                'product_id' => $win11->id,
                'name' => '1 PC Retail License',
                'sku' => 'WIN11-1PC',
                'price' => 2499.00,
                'sale_price' => 399.00,
                'duration' => 'Lifetime',
                'warranty_days' => 365,
                'is_default' => true,
                'is_active' => true,
                'display_order' => 1,
            ]
        );

        // 5. LinkedIn Premium Career
        $linkedin = Product::updateOrCreate(
            ['slug' => 'linkedin-premium-career'],
            [
                'category_id' => $subscriptionsCatId,
                'name' => 'LinkedIn Premium Career (6 Months)',
                'sku' => 'LNKD-CAR-6M',
                'description' => 'Get noticed by recruiters, access salary insights, 5 InMail credits per month, who viewed your profile, and full access to LinkedIn Learning courses.',
                'short_description' => '6 Months LinkedIn Premium Career activation on your personal account.',
                'price' => 4999.00,
                'sale_price' => 999.00,
                'currency' => 'INR',
                'duration' => '6 Months',
                'credits' => 0,
                'warranty_days' => 180,
                'stock_mode' => 'digital_credentials',
                'status' => 'active',
                'active' => true,
                'featured' => false,
                'show_in_store' => true,
                'visibility' => 'public',
                'purchase_mode' => 'both',
                'fulfillment_mode' => 'manual',
                'inventory_type' => 'unlimited',
                'primary_image' => 'products/vtDHfOzkDFGmZtkyMnI7f7hPIo6nnxaP8Y9FWlIi.png',
                'cta_text' => 'GET LINKEDIN CAREER',
                'badge_text' => 'CAREER BOOST',
                'tags' => ['career', 'linkedin', 'learning', 'inmail'],
                'display_order' => 5,
            ]
        );

        ProductVariant::updateOrCreate(
            ['product_id' => $linkedin->id, 'sku' => 'LNKD-6M'],
            [
                'product_id' => $linkedin->id,
                'name' => '6 Months Personal Invite',
                'sku' => 'LNKD-6M',
                'price' => 4999.00,
                'sale_price' => 999.00,
                'duration' => '6 Months',
                'warranty_days' => 180,
                'is_default' => true,
                'is_active' => true,
                'display_order' => 1,
            ]
        );

        // Demo inventory credentials for finite products
        $demoStock = [
            [
                'product_id' => $capcut->id,
                'variant_id' => $defaultCapcutVariant->id ?? null,
                'email' => 'pro.editor.alpha@convora.store',
                'password' => 'CapCutConvora#2026!a',
            ],
            [
                'product_id' => $capcut->id,
                'variant_id' => $defaultCapcutVariant->id ?? null,
                'email' => 'pro.editor.beta@convora.store',
                'password' => 'CapCutConvora#2026!b',
            ],
            [
                'product_id' => $chatgpt->id,
                'variant_id' => $chatgptVariant->id ?? null,
                'email' => 'chatgpt.shared.alpha@convora.store',
                'password' => 'GptPlusConvora#2026!a',
            ],
            [
                'product_id' => $win11->id,
                'variant_id' => $win11Variant->id ?? null,
                'email' => 'retail-key',
                'password' => 'VK7JG-NPHTM-C97JM-9MPGT-3V66T',
            ],
        ];

        foreach ($demoStock as $stock) {
            $existing = InventoryCredential::where('product_id', $stock['product_id'])
                ->where('email_encrypted', '!=', null)
                ->get()
                ->first(fn ($c) => $c->email === $stock['email']);

            if (!$existing) {
                $cred = new InventoryCredential();
                $cred->product_id = $stock['product_id'];
                $cred->variant_id = $stock['variant_id'];
                $cred->email = $stock['email'];
                $cred->password = $stock['password'];
                $cred->status = 'available';
                $cred->save();
            }
        }
    }
}
