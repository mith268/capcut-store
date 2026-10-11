<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentAndWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_payment_verification_rejects_tampered_signature(): void
    {
        $product = Product::first();
        $order = Order::create([
            'customer_name' => 'Signature Victim',
            'customer_email' => 'victim@example.com',
            'customer_phone' => '9876543210',
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->effective_price,
            'discount_amount' => 0.00,
            'total_amount' => $product->effective_price,
            'payment_status' => 'pending',
            'delivery_status' => 'pending',
        ]);

        $response = $this->postJson('/payment/verify', [
            'order_id' => $order->id,
            'razorpay_order_id' => 'order_valid_123',
            'razorpay_payment_id' => 'pay_valid_456',
            'razorpay_signature' => 'FORGED_INVALID_SIGNATURE',
        ]);

        $response->assertStatus(400);
        $order->refresh();
        $this->assertEquals('failed', $order->payment_status);
    }

    public function test_payment_verification_accepts_valid_signature_and_marks_paid(): void
    {
        $product = Product::first();
        $order = Order::create([
            'customer_name' => 'Valid Payer',
            'customer_email' => 'payer@example.com',
            'customer_phone' => '9876543210',
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->effective_price,
            'discount_amount' => 0.00,
            'total_amount' => $product->effective_price,
            'payment_status' => 'pending',
            'delivery_status' => 'pending',
        ]);

        $response = $this->postJson('/payment/verify', [
            'order_id' => $order->id,
            'razorpay_order_id' => 'order_mock_test123',
            'razorpay_payment_id' => 'pay_mock_test456',
            'razorpay_signature' => 'mock_valid_signature',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $order->refresh();
        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->paid_at);
        $this->assertNotNull($order->credential);
    }

    public function test_webhook_idempotency_prevents_duplicate_processing(): void
    {
        $product = Product::first();
        $order = Order::create([
            'order_number' => 'CAP-IDEMP-001',
            'customer_name' => 'Idempotent Buyer',
            'customer_email' => 'idemp@example.com',
            'customer_phone' => '9876543210',
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->effective_price,
            'discount_amount' => 0.00,
            'total_amount' => $product->effective_price,
            'payment_status' => 'pending',
            'delivery_status' => 'pending',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'gateway' => 'razorpay',
            'gateway_order_id' => 'order_rzp_idemp_999',
            'amount' => $order->total_amount,
            'status' => 'created',
        ]);

        $webhookPayload = [
            'id' => 'evt_unique_12345678',
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_rzp_999',
                        'order_id' => 'order_rzp_idemp_999',
                        'amount' => $order->total_amount * 100,
                        'status' => 'captured',
                    ]
                ]
            ]
        ];

        // 1. First Webhook Dispatch
        $response1 = $this->postJson('/webhooks/razorpay', $webhookPayload);
        $response1->assertStatus(200);

        $order->refresh();
        $this->assertTrue($order->isPaid());

        // 2. Duplicate Webhook Dispatch (e.g. network retry from gateway)
        $response2 = $this->postJson('/webhooks/razorpay', $webhookPayload);
        $response2->assertStatus(200);
        $response2->assertJson(['status' => 'already_processed']);

        // Assert exactly 1 event recorded
        $this->assertEquals(1, PaymentEvent::where('event_id', 'evt_unique_12345678')->count());
    }

    public function test_canva_pro_bonus_claim_saves_email_and_marks_requested(): void
    {
        $product = Product::first();
        $order = Order::create([
            'customer_name' => 'Canva Requester',
            'customer_email' => 'requester@example.com',
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->effective_price,
            'discount_amount' => 0.00,
            'total_amount' => $product->effective_price,
            'payment_status' => 'paid',
            'delivery_status' => 'delivered',
        ]);

        $response = $this->postJson('/checkout/canva-bonus', [
            'order_id' => $order->id,
            'order_token' => $order->access_token,
            'canva_email' => 'canva_user@gmail.com',
            'skip' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals('canva_user@gmail.com', $order->canva_email);
        $this->assertEquals('requested', $order->canva_bonus_status);
        $this->assertTrue($order->hasCanvaBonusRequested());
    }

    public function test_canva_pro_bonus_skip_marks_skipped(): void
    {
        $product = Product::first();
        $order = Order::create([
            'customer_name' => 'Canva Skipper',
            'customer_email' => 'skipper@example.com',
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->effective_price,
            'discount_amount' => 0.00,
            'total_amount' => $product->effective_price,
            'payment_status' => 'paid',
            'delivery_status' => 'delivered',
        ]);

        $response = $this->postJson('/checkout/canva-bonus', [
            'order_id' => $order->id,
            'order_token' => $order->access_token,
            'skip' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals('skipped', $order->canva_bonus_status);
    }
}

