<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RazorpayStandardCheckoutApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_create_order_rejects_amount_less_than_100_paise(): void
    {
        $response = $this->postJson('/api/create-order', [
            'amount' => 99, // 99 paise < 100 paise minimum
            'currency' => 'INR',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'error' => 'Amount must be at least 100 paise (₹1.00).',
        ]);
    }

    public function test_create_order_rejects_non_numeric_amount(): void
    {
        $response = $this->postJson('/api/create-order', [
            'amount' => 'invalid_amount',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_create_order_calls_razorpay_api_and_returns_required_fields(): void
    {
        Config::set('services.razorpay.key_id', 'rzp_test_mock_dummy_key_123');
        Config::set('services.razorpay.key_secret', 'test_mock_secret_key_456');

        Http::fake([
            'https://api.razorpay.com/v1/orders' => Http::response([
                'id' => 'order_live_fake_12345',
                'entity' => 'order',
                'amount' => 29900,
                'amount_paid' => 0,
                'amount_due' => 29900,
                'currency' => 'INR',
                'receipt' => 'rcpt_custom_999',
                'status' => 'created',
                'attempts' => 0,
            ], 200),
        ]);

        $response = $this->postJson('/api/create-order', [
            'amount' => 29900,
            'currency' => 'INR',
            'receipt' => 'rcpt_custom_999',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '9876543210',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'order_id',
            'amount',
            'currency',
        ]);
        $response->assertJson([
            'success' => true,
            'order_id' => 'order_live_fake_12345',
            'amount' => 29900,
            'currency' => 'INR',
            'receipt' => 'rcpt_custom_999',
        ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.razorpay.com/v1/orders' &&
                   $request['amount'] === 29900 &&
                   $request['currency'] === 'INR' &&
                   $request['receipt'] === 'rcpt_custom_999';
        });
    }

    public function test_verify_payment_rejects_missing_required_fields(): void
    {
        // Missing signature
        $response1 = $this->postJson('/api/verify-payment', [
            'razorpay_order_id' => 'order_test_123',
            'razorpay_payment_id' => 'pay_test_456',
        ]);
        $response1->assertStatus(400);
        $response1->assertJson(['success' => false]);

        // Missing payment_id
        $response2 = $this->postJson('/api/verify-payment', [
            'razorpay_order_id' => 'order_test_123',
            'razorpay_signature' => 'some_signature',
        ]);
        $response2->assertStatus(400);
        $response2->assertJson(['success' => false]);

        // Missing order_id
        $response3 = $this->postJson('/api/verify-payment', [
            'razorpay_payment_id' => 'pay_test_456',
            'razorpay_signature' => 'some_signature',
        ]);
        $response3->assertStatus(400);
        $response3->assertJson(['success' => false]);
    }

    public function test_verify_payment_rejects_forged_signature_and_does_not_fulfill(): void
    {
        $testSecret = 'test_mock_secret_key_456';
        Config::set('services.razorpay.key_id', 'rzp_test_mock_dummy_key_123');
        Config::set('services.razorpay.key_secret', $testSecret);

        $product = Product::first();
        $order = Order::create([
            'order_number' => 'CAP-VERIFY-001',
            'customer_name' => 'Victim Client',
            'customer_email' => 'victim@example.com',
            'customer_phone' => '9876543210',
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->effective_price,
            'discount_amount' => 0.00,
            'total_amount' => $product->effective_price,
            'payment_status' => 'pending',
            'delivery_status' => 'pending',
            'gateway_order_id' => 'order_live_real_777',
        ]);

        $response = $this->postJson('/api/verify-payment', [
            'razorpay_order_id' => 'order_live_real_777',
            'razorpay_payment_id' => 'pay_live_real_888',
            'razorpay_signature' => 'FORGED_INVALID_HMAC_SIGNATURE',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'error' => 'Signature verification failed. Payment cannot be verified.',
        ]);

        $order->refresh();
        $this->assertEquals('failed', $order->payment_status);
        $this->assertNull($order->paid_at);
        $this->assertNull($order->credential);
    }

    public function test_verify_payment_verifies_valid_hmac_sha256_and_fulfills_order(): void
    {
        $testSecret = 'test_mock_secret_key_456';
        Config::set('services.razorpay.key_id', 'rzp_test_mock_dummy_key_123');
        Config::set('services.razorpay.key_secret', $testSecret);

        $orderId = 'order_live_real_999';
        $paymentId = 'pay_live_real_111';
        $validSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $testSecret);

        $product = Product::first();
        $order = Order::create([
            'order_number' => 'CAP-VERIFY-002',
            'customer_name' => 'Honest Buyer',
            'customer_email' => 'honest@example.com',
            'customer_phone' => '9876543210',
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->effective_price,
            'discount_amount' => 0.00,
            'total_amount' => $product->effective_price,
            'payment_status' => 'pending',
            'delivery_status' => 'pending',
            'gateway_order_id' => $orderId,
        ]);

        $response = $this->postJson('/api/verify-payment', [
            'razorpay_order_id' => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $validSignature,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Payment verified successfully.',
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);

        $order->refresh();
        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->paid_at);
        $this->assertEquals('delivered', $order->delivery_status);
        $this->assertNotNull($order->credential);
    }
}
