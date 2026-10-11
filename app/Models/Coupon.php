<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'minimum_amount',
        'maximum_discount',
        'usage_limit',
        'used_count',
        'per_customer_limit',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'minimum_amount' => 'decimal:2',
            'maximum_discount' => 'decimal:2',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'per_customer_limit' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function isValidFor(float $subtotal, ?string $customerEmail = null): bool
    {
        return is_null($this->getValidationErrorMessage($subtotal, $customerEmail));
    }

    public function getValidationErrorMessage(float $subtotal, ?string $customerEmail = null): ?string
    {
        if (!$this->is_active) {
            return 'This coupon is no longer active.';
        }

        $now = Carbon::now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return 'This coupon is not yet valid.';
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return 'This coupon has expired.';
        }

        if (!is_null($this->usage_limit) && $this->used_count >= $this->usage_limit) {
            return 'This coupon has reached its maximum usage limit.';
        }

        if ($this->minimum_amount > 0 && $subtotal < $this->minimum_amount) {
            return 'Order subtotal must be at least ₹' . number_format($this->minimum_amount, 2) . ' to use this coupon.';
        }

        if ($customerEmail && $this->per_customer_limit > 0) {
            $customerUsedCount = $this->redemptions()->where('customer_email', strtolower($customerEmail))->count();
            if ($customerUsedCount >= $this->per_customer_limit) {
                return 'You have already reached the maximum usage limit for this coupon.';
            }
        }

        return null;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'percentage') {
            $discount = ($subtotal * $this->value) / 100;
        } else {
            $discount = (float) $this->value;
        }

        if (!is_null($this->maximum_discount) && $this->maximum_discount > 0) {
            $discount = min($discount, (float) $this->maximum_discount);
        }

        return min(round($discount, 2), $subtotal);
    }
}
