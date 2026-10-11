<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreCatalogAndProductDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_store_catalog_loads_successfully_with_products_and_categories(): void
    {
        $response = $this->get('/store');

        $response->assertStatus(200);
        $response->assertSee('Complete Digital Catalog');
        $response->assertSee('Subscriptions');
        $response->assertSee('Design Tools');
        $response->assertSee('Software Licenses');
        $response->assertSee('Instant Delivery');
        $response->assertSee('ChatGPT Plus (Shared Account)');
        $response->assertSee('Windows 11 Professional Retail Key');
    }

    public function test_search_filters_products_correctly(): void
    {
        $response = $this->get('/store?q=Windows');

        $response->assertStatus(200);
        $response->assertSee('Windows 11 Professional Retail Key');
        $response->assertDontSee('ChatGPT Plus (Shared Account)');
    }

    public function test_category_page_filters_by_category(): void
    {
        $response = $this->get('/category/software-licenses');

        $response->assertStatus(200);
        $response->assertSee('Software Licenses');
        $response->assertSee('Windows 11 Professional Retail Key');
        $response->assertDontSee('ChatGPT Plus (Shared Account)');
    }

    public function test_product_detail_page_loads_with_variants_and_features(): void
    {
        $product = Product::where('slug', 'windows-11-pro-retail-key')->firstOrFail();

        $response = $this->get('/products/' . $product->slug);

        $response->assertStatus(200);
        $response->assertSee('Windows 11 Professional Retail Key');
        $response->assertSee('₹' . number_format($product->effective_price, 0));
        $response->assertSee('Buy Online (Instant)');
        $response->assertSee('Order on WhatsApp');
    }

    public function test_legacy_store_slug_redirects_301_to_products_slug(): void
    {
        $response = $this->get('/store/chatgpt-plus-shared');
        $response->assertRedirect('/products/chatgpt-plus-shared');
        $response->assertStatus(301);

        $responseLegacy = $this->get('/product/chatgpt-plus-shared');
        $responseLegacy->assertRedirect('/products/chatgpt-plus-shared');
        $responseLegacy->assertStatus(301);
    }
}
