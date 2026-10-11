<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerLeadAutosaveTest extends TestCase
{
    use RefreshDatabase;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::create([
            'name' => 'CapCut Pro — 1 Month (1200 AI Credits)',
            'slug' => 'capcut-pro-1-month',
            'hero_title' => 'CapCut Pro — 1 Month',
            'description' => '1200 AI Credits + Canva Pro 1 Year Bonus',
            'price' => 1999,
            'effective_price' => 399,
            'active' => true,
            'currency' => 'INR',
        ]);
    }

    public function test_autosave_creates_draft_lead_with_only_phone(): void
    {
        $sessionId = (string) Str::uuid();

        $response = $this->postJson('/checkout/autosave', [
            'checkout_session_id' => $sessionId,
            'customer_phone' => '9876543210',
            'checkout_status' => 'draft',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'checkout_session_id' => $sessionId,
        ]);

        $this->assertDatabaseHas('orders', [
            'checkout_session_id' => $sessionId,
            'customer_phone' => '9876543210',
            'checkout_status' => 'draft',
        ]);
        $this->assertEquals(1, Order::where('checkout_session_id', $sessionId)->count());
    }

    public function test_autosave_updates_same_lead_when_email_and_name_are_typed_later(): void
    {
        $sessionId = (string) Str::uuid();

        // 1. Visitor enters phone
        $this->postJson('/checkout/autosave', [
            'checkout_session_id' => $sessionId,
            'customer_phone' => '9876543210',
            'checkout_status' => 'draft',
        ])->assertStatus(200);

        // 2. Visitor adds email
        $this->postJson('/checkout/autosave', [
            'checkout_session_id' => $sessionId,
            'customer_phone' => '9876543210',
            'customer_email' => 'lead@example.com',
            'checkout_status' => 'draft',
        ])->assertStatus(200);

        // 3. Visitor adds name
        $this->postJson('/checkout/autosave', [
            'checkout_session_id' => $sessionId,
            'customer_phone' => '9876543210',
            'customer_email' => 'lead@example.com',
            'customer_name' => 'Rahul Sharma',
            'checkout_status' => 'draft',
        ])->assertStatus(200);

        // Crucial requirement: No duplicate records created!
        $this->assertEquals(1, Order::where('checkout_session_id', $sessionId)->count());

        $order = Order::where('checkout_session_id', $sessionId)->first();
        $this->assertEquals('9876543210', $order->customer_phone);
        $this->assertEquals('lead@example.com', $order->customer_email);
        $this->assertEquals('Rahul Sharma', $order->customer_name);
        $this->assertEquals('draft', $order->checkout_status);
    }

    public function test_modal_close_marks_lead_as_abandoned(): void
    {
        $sessionId = (string) Str::uuid();

        // Lead enters phone
        $this->postJson('/checkout/autosave', [
            'checkout_session_id' => $sessionId,
            'customer_phone' => '9876543210',
            'checkout_status' => 'draft',
        ])->assertStatus(200);

        // Modal closed -> marks abandoned
        $this->postJson('/checkout/autosave', [
            'checkout_session_id' => $sessionId,
            'customer_phone' => '9876543210',
            'checkout_status' => 'abandoned',
        ])->assertStatus(200);

        $order = Order::where('checkout_session_id', $sessionId)->first();
        $this->assertEquals('abandoned', $order->checkout_status);
        $this->assertNotNull($order->abandoned_at);
        $this->assertEquals(1, Order::where('checkout_session_id', $sessionId)->count());
    }

    public function test_razorpay_create_order_reuses_existing_draft_lead(): void
    {
        $sessionId = (string) Str::uuid();

        // 1. Autosave draft lead
        $this->postJson('/checkout/autosave', [
            'checkout_session_id' => $sessionId,
            'customer_phone' => '9876543210',
            'customer_email' => 'buyer@example.com',
            'customer_name' => 'Amit Kumar',
            'checkout_status' => 'draft',
        ])->assertStatus(200);

        $this->assertEquals(1, Order::count());

        // 2. User clicks Pay with Razorpay -> calls /api/create-order
        $response = $this->postJson('/api/create-order', [
            'amount' => 39900,
            'currency' => 'INR',
            'checkout_session_id' => $sessionId,
            'customer_phone' => '9876543210',
            'customer_email' => 'buyer@example.com',
            'customer_name' => 'Amit Kumar',
            'create_order_record' => true,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, Order::count(), 'Should update existing order rather than creating a duplicate row');

        $order = Order::where('checkout_session_id', $sessionId)->first();
        $this->assertEquals('payment_pending', $order->checkout_status);
        $this->assertNotNull($order->payment_attempted_at);
        $this->assertNotNull($order->gateway_order_id);
    }

    public function test_admin_can_filter_and_search_draft_and_abandoned_leads(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@capcutstore.local',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $sessionId1 = (string) Str::uuid();
        $sessionId2 = (string) Str::uuid();

        // Create 1 draft lead and 1 abandoned lead
        $this->postJson('/checkout/autosave', [
            'checkout_session_id' => $sessionId1,
            'customer_phone' => '9111111111',
            'customer_name' => 'Draft Lead Person',
            'checkout_status' => 'draft',
        ]);

        $this->postJson('/checkout/autosave', [
            'checkout_session_id' => $sessionId2,
            'customer_phone' => '9222222222',
            'customer_email' => 'abandoned@test.local',
            'checkout_status' => 'abandoned',
        ]);

        // Admin checks 'draft' tab
        $response = $this->actingAs($admin)->get('/admin/orders?status=draft');
        $response->assertStatus(200);
        $response->assertSee('9111111111');
        $response->assertSee('Draft Lead Person');

        // Admin checks 'abandoned' tab
        $response = $this->actingAs($admin)->get('/admin/orders?status=abandoned');
        $response->assertStatus(200);
        $response->assertSee('9222222222');
        $response->assertSee('abandoned@test.local');

        // Admin searches by phone
        $response = $this->actingAs($admin)->get('/admin/orders?q=9111111111');
        $response->assertStatus(200);
        $response->assertSee('9111111111');
        $response->assertDontSee('9222222222');
    }
}
