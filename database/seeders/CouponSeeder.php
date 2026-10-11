<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::updateOrCreate(
            ['code' => 'PRO50'],
            [
                'type' => 'fixed',
                'value' => 50.00,
                'minimum_amount' => 250.00,
                'maximum_discount' => 50.00,
                'usage_limit' => 500,
                'used_count' => 0,
                'per_customer_limit' => 1,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ]
        );

        Coupon::updateOrCreate(
            ['code' => 'CAPVIP10'],
            [
                'type' => 'percentage',
                'value' => 10.00,
                'minimum_amount' => 200.00,
                'maximum_discount' => 100.00,
                'usage_limit' => 1000,
                'used_count' => 0,
                'per_customer_limit' => 1,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ]
        );

        // We create an unpublished draft review for admin reference only
        Testimonial::updateOrCreate(
            ['customer_name' => 'Demo Review (Draft)'],
            [
                'customer_initial' => 'DR',
                'review_text' => 'This is a sample draft testimonial. Add your real customer reviews through Admin Panel > Testimonials.',
                'rating' => 5,
                'verified' => true,
                'published' => false, // STRICTLY FALSE so no fake testimonials are presented to users
                'display_order' => 1,
            ]
        );
    }
}
