<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price',
        'sale_price',
        'duration',
        'device_limit',
        'credits',
        'warranty_days',
        'fulfillment_mode',
        'is_default',
        'is_active',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'device_limit' => 'integer',
            'credits' => 'integer',
            'warranty_days' => 'integer',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(InventoryCredential::class, 'variant_id');
    }

    public function availableCredentials(): HasMany
    {
        return $this->hasMany(InventoryCredential::class, 'variant_id')->where('status', 'available');
    }

    public function getAvailableStockAttribute(): int
    {
        if ($this->product && $this->product->inventory_type === 'unlimited') {
            return 999;
        }
        return $this->availableCredentials()->count();
    }

    public function hasDiscount(): bool
    {
        return !is_null($this->sale_price) && $this->sale_price > 0 && $this->sale_price < $this->price;
    }

    public function getEffectivePriceAttribute(): float
    {
        return $this->hasDiscount() ? (float) $this->sale_price : (float) $this->price;
    }

    public function getSavingsAttribute(): float
    {
        return $this->hasDiscount() ? (float) ($this->price - $this->sale_price) : 0.00;
    }

    public function getSavingsPercentageAttribute(): int
    {
        if (!$this->hasDiscount() || $this->price <= 0) {
            return 0;
        }
        return (int) round((($this->price - $this->sale_price) / $this->price) * 100);
    }
}
