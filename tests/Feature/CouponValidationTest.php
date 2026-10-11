<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_valid_coupon_calculates_discount_correctly(): void
    {
        $response = $this->postJson('/checkout/calculate', [
            'coupon_code' => 'PRO50',
            'customer_email' => 'user@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'coupon_applied' => true,
            'discount_amount' => 50.00,
        ]);
    }

    public function test_invalid_coupon_returns_error(): void
    {
        $response = $this->postJson('/checkout/calculate', [
            'coupon_code' => 'NONEXISTENT_CODE',
            'customer_email' => 'user@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'coupon_applied' => false,
            'discount_amount' => 0.00,
        ]);
    }

    public function test_expired_coupon_is_rejected(): void
    {
        Coupon::create([
            'code' => 'EXPIRED99',
            'type' => 'fixed',
            'value' => 99.00,
            'minimum_amount' => 100.00,
            'starts_at' => now()->subMonths(2),
            'expires_at' => now()->subDay(),
            'is_active' => true,
        ]);

        $response = $this->postJson('/checkout/calculate', [
            'coupon_code' => 'EXPIRED99',
            'customer_email' => 'user@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'coupon_applied' => false,
        ]);
        $this->assertStringContainsString('expired', strtolower($response->json('coupon_message')));
    }
}
