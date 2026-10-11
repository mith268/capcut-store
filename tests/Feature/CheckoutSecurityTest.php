<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_checkout_page_redirects_to_inline_modal_on_home(): void
    {
        $response = $this->get('/checkout');
        $response->assertRedirect(route('home', ['checkout' => 1]));

        $homeResponse = $this->get('/?checkout=1');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('CapCut Pro');
    }

    public function test_checkout_initiation_supports_frictionless_flow_and_validates_email_format(): void
    {
        // Invalid email format is rejected
        $invalidResponse = $this->postJson('/checkout/initiate', [
            'customer_email' => 'invalid-email-address',
        ]);
        $invalidResponse->assertStatus(422);
        $invalidResponse->assertJsonValidationErrors(['customer_email']);

        // Frictionless empty payload succeeds with default guest attributes
        $response = $this->postJson('/checkout/initiate', []);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('orders', [
            'customer_email' => 'customer@guest.local',
            'payment_status' => 'pending',
        ]);
    }

    public function test_server_side_price_integrity_ignores_client_tampering(): void
    {
        $product = Product::first();

        // Attacker attempts to post price = 1.00
        $response = $this->postJson('/checkout/initiate', [
            'product_id' => $product->id,
            'customer_name' => 'Security Tester',
            'customer_email' => 'security@example.com',
            'customer_phone' => '9876543210',
            'terms' => true,
            'price' => 1.00, // MALICIOUS CLIENT INPUT
            'total_amount' => 1.00, // MALICIOUS CLIENT INPUT
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $createdOrder = Order::latest()->first();
        // Asserts total amount is the real database effective price, NOT ₹1.00!
        $this->assertEquals((float) $product->effective_price, (float) $createdOrder->total_amount);
        $this->assertNotEquals(1.00, (float) $createdOrder->total_amount);
    }

    public function test_order_generates_cryptographic_uuid_and_random_token(): void
    {
        $product = Product::first();

        $response = $this->postJson('/checkout/initiate', [
            'product_id' => $product->id,
            'customer_name' => 'Cryptographic Tester',
            'customer_email' => 'crypto@example.com',
            'customer_phone' => '9876543210',
            'terms' => true,
        ]);

        $response->assertStatus(200);
        $order = Order::latest()->first();

        $this->assertNotEmpty($order->uuid);
        $this->assertEquals(64, strlen($order->access_token));
        $this->assertStringStartsWith('CAP-', $order->order_number);
    }
}
