<?php

namespace App\Services\Coupon;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;

class CouponService
{
    public function validateAndCalculate(string $code, float $subtotal, ?string $email = null): array
    {
        $code = strtoupper(trim($code));

        if (empty($code)) {
            return [
                'valid' => false,
                'discount' => 0.00,
                'total' => $subtotal,
                'coupon' => null,
                'message' => 'Please enter a coupon code.',
            ];
        }

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return [
                'valid' => false,
                'discount' => 0.00,
                'total' => $subtotal,
                'coupon' => null,
                'message' => 'Invalid coupon code.',
            ];
        }

        $errorMessage = $coupon->getValidationErrorMessage($subtotal, $email);

        if ($errorMessage !== null) {
            return [
                'valid' => false,
                'discount' => 0.00,
                'total' => $subtotal,
                'coupon' => null,
                'message' => $errorMessage,
            ];
        }

        $discount = $coupon->calculateDiscount($subtotal);
        $total = max(0.00, round($subtotal - $discount, 2));

        return [
            'valid' => true,
            'discount' => $discount,
            'total' => $total,
            'coupon' => $coupon,
            'message' => 'Coupon applied successfully! You saved ₹' . number_format($discount, 2),
        ];
    }

    public function recordRedemption(Coupon $coupon, Order $order, string $email, float $discount): void
    {
        CouponRedemption::create([
            'coupon_id' => $coupon->id,
            'order_id' => $order->id,
            'customer_email' => strtolower($email),
            'discount_amount' => $discount,
        ]);

        $coupon->increment('used_count');
    }
}
