<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCategoryAndProductCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_view_category_and_product_indexes(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.categories.index'));
        $response->assertStatus(200);
        $response->assertSee('Product Categories');

        $response = $this->actingAs($this->admin)->get(route('admin.products.index'));
        $response->assertStatus(200);
        $response->assertSee('Products & Digital Offerings', false);
    }

    public function test_admin_can_create_and_update_category(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Cloud Subscriptions',
            'icon' => 'sparkles',
            'description' => 'AI and Cloud plans',
            'is_active' => true,
            'display_order' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'name' => 'Cloud Subscriptions',
            'slug' => 'cloud-subscriptions',
        ]);

        $category = Category::where('slug', 'cloud-subscriptions')->first();

        $updateResponse = $this->actingAs($this->admin)->put(route('admin.categories.update', $category), [
            'name' => 'AI & Cloud Subscriptions',
            'slug' => 'cloud-subscriptions',
            'icon' => 'zap',
            'description' => 'Updated description',
            'is_active' => true,
            'display_order' => 2,
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'AI & Cloud Subscriptions',
        ]);
    }

    public function test_cannot_delete_category_with_assigned_products(): void
    {
        $category = Category::create([
            'name' => 'Design Software',
            'slug' => 'design-software',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Figma Pro Team',
            'slug' => 'figma-pro-team',
            'price' => 1200,
            'currency' => 'INR',
            'duration' => '1 Year',
            'status' => 'active',
            'fulfillment_mode' => 'account_access',
            'inventory_type' => 'unlimited',
            'purchase_mode' => 'both',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_admin_can_create_new_product_with_default_variant(): void
    {
        Storage::fake('public');

        $category = Category::create([
            'name' => 'Developer Tools',
            'slug' => 'developer-tools',
            'is_active' => true,
        ]);

        $image = UploadedFile::fake()->image('copilot.png', 400, 400);

        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'GitHub Copilot Pro',
            'slug' => 'github-copilot-pro',
            'sku' => 'COPILOT-1Y',
            'price' => 2999.00,
            'sale_price' => 799.00,
            'currency' => 'INR',
            'duration' => '1 Year',
            'short_description' => 'AI pair programmer for developers',
            'status' => 'active',
            'fulfillment_mode' => 'account_access',
            'inventory_type' => 'unlimited',
            'purchase_mode' => 'both',
            'primary_image_file' => $image,
        ]);

        $product = Product::where('slug', 'github-copilot-pro')->first();
        $this->assertNotNull($product);
        $response->assertRedirect(route('admin.products.edit', $product));

        $this->assertDatabaseHas('products', [
            'name' => 'GitHub Copilot Pro',
            'sku' => 'COPILOT-1Y',
            'category_id' => $category->id,
        ]);

        // Default variant auto-created
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'is_default' => true,
            'price' => 2999.00,
        ]);
    }

    public function test_admin_can_add_and_update_product_variants(): void
    {
        $product = Product::create([
            'name' => 'Midjourney Pro',
            'slug' => 'midjourney-pro',
            'price' => 1999,
            'currency' => 'INR',
            'duration' => '1 Month',
            'status' => 'active',
            'fulfillment_mode' => 'key_inventory',
            'inventory_type' => 'finite',
            'purchase_mode' => 'both',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.products.variants.store', $product), [
            'name' => '3 Months Fast Hours',
            'sku' => 'MJ-3M',
            'price' => 4999.00,
            'sale_price' => 1299.00,
            'duration' => '3 Months',
            'is_default' => false,
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'sku' => 'MJ-3M',
            'sale_price' => 1299.00,
        ]);

        $variant = ProductVariant::where('sku', 'MJ-3M')->first();

        $updateResponse = $this->actingAs($this->admin)->put(route('admin.products.variants.update', $variant), [
            'name' => '3 Months Unlimited Fast',
            'sku' => 'MJ-3M-UNL',
            'price' => 5499.00,
            'sale_price' => 1499.00,
            'duration' => '3 Months',
            'is_default' => true,
            'is_active' => true,
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'name' => '3 Months Unlimited Fast',
            'is_default' => true,
        ]);
    }
}
