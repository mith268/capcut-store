<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_homepage_loads_successfully_with_product_information(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('CapCut Pro');
        $response->assertSee('1200 AI Credits');
        $response->assertSee('30-Day Warranty');
        $response->assertSee('Independent digital service provider');
        $response->assertSee('Canva Pro');
    }

    public function test_homepage_displays_database_calculated_price(): void
    {
        $product = Product::first();
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('₹' . number_format($product->effective_price, 0));
    }

    public function test_schema_markup_is_injected(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('"@type": "Product"', false);
        $response->assertSee('"@type": "Organization"', false);
    }
}
