<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'uuid',
        'access_token',
        'checkout_session_id',
        'checkout_status',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_country',
        'canva_email',
        'canva_bonus_status',
        'product_id',
        'quantity',
        'unit_price',
        'discount_amount',
        'coupon_id',
        'total_amount',
        'currency',
        'payment_method',
        'gateway',
        'gateway_order_id',
        'gateway_payment_id',
        'payment_status',
        'delivery_status',
        'fulfillment_status',
        'refund_status',
        'notes',
        'meta_data',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'fbp',
        'fbc',
        'ip_address',
        'user_agent',
        'paid_at',
        'delivered_at',
        'credentials_revealed_at',
        'last_activity_at',
        'payment_attempted_at',
        'abandoned_at',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'quantity' => 'integer',
            'paid_at' => 'datetime',
            'delivered_at' => 'datetime',
            'credentials_revealed_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'payment_attempted_at' => 'datetime',
            'abandoned_at' => 'datetime',
            'meta_data' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->uuid)) {
                $order->uuid = (string) Str::uuid();
            }
            if (empty($order->access_token)) {
                $order->access_token = Str::random(64);
            }
            if (empty($order->order_number)) {
                $order->order_number = 'CAP-' . strtoupper(Str::random(6)) . '-' . rand(100, 999);
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function credential(): HasOne
    {
        return $this->hasOne(InventoryCredential::class);
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class);
    }

    public function utmAttribution(): HasOne
    {
        return $this->hasOne(UtmAttribution::class);
    }

    public function isPaid(): bool
    {
        return in_array($this->payment_status, ['paid', 'processing', 'delivered']);
    }

    public function isDraft(): bool
    {
        return $this->checkout_status === 'draft';
    }

    public function isAbandoned(): bool
    {
        return $this->checkout_status === 'abandoned';
    }

    public function getCustomerDisplayNameAttribute(): string
    {
        return !empty($this->customer_name) && $this->customer_name !== 'Customer' ? $this->customer_name : 'Not provided';
    }

    public function getCustomerDisplayPhoneAttribute(): string
    {
        return !empty($this->customer_phone) ? $this->customer_phone : 'Not provided';
    }

    public function getCustomerDisplayEmailAttribute(): string
    {
        return !empty($this->customer_email) && $this->customer_email !== 'customer@guest.local' ? $this->customer_email : 'Not provided';
    }

    public function isDelivered(): bool
    {
        return $this->delivery_status === 'delivered';
    }

    public function canRevealCredentials(): bool
    {
        return $this->isPaid() && $this->credential()->exists();
    }

    public function getSecureUrl(): string
    {
        return route('order.show', [
            'uuid' => $this->uuid,
            'token' => $this->access_token,
        ]);
    }

    public function hasCanvaBonusRequested(): bool
    {
        return in_array($this->canva_bonus_status, ['requested', 'processing', 'activated']);
    }

    public function isCanvaBonusPending(): bool
    {
        return $this->canva_bonus_status === 'not_requested';
    }
}
