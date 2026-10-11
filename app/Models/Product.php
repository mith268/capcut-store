<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'sale_price',
        'currency',
        'duration',
        'credits',
        'warranty_days',
        'stock_mode',
        'active',
        'featured',
        'hero_title',
        'hero_subtitle',
        'cta_text',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'active' => 'boolean',
            'featured' => 'boolean',
            'credits' => 'integer',
            'warranty_days' => 'integer',
        ];
    }

    public function features(): HasMany
    {
        return $this->hasMany(ProductFeature::class)->orderBy('display_order');
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(InventoryCredential::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function availableCredentials(): HasMany
    {
        return $this->hasMany(InventoryCredential::class)->where('status', 'available');
    }

    public function getAvailableStockAttribute(): int
    {
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
