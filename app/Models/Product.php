<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'primary_image',
        'tags',
        'description',
        'short_description',
        'price',
        'sale_price',
        'currency',
        'duration',
        'credits',
        'warranty_days',
        'stock_mode',
        'status',
        'show_in_store',
        'visibility',
        'purchase_mode',
        'fulfillment_mode',
        'inventory_type',
        'low_stock_threshold',
        'max_order_quantity',
        'active',
        'featured',
        'hero_title',
        'hero_subtitle',
        'cta_text',
        'video_provider',
        'video_id',
        'video_poster',
        'badge_text',
        'whatsapp_message',
        'activation_instructions',
        'exclusions',
        'warranty_terms',
        'meta_title',
        'meta_description',
        'social_image',
        'bonus_title',
        'bonus_description',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'active' => 'boolean',
            'featured' => 'boolean',
            'show_in_store' => 'boolean',
            'credits' => 'integer',
            'warranty_days' => 'integer',
            'low_stock_threshold' => 'integer',
            'max_order_quantity' => 'integer',
            'display_order' => 'integer',
            'tags' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('display_order');
    }

    public function activeVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true)->orderBy('display_order');
    }

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->orderBy('display_order');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProductFile::class);
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
        if ($this->inventory_type === 'unlimited') {
            return 999;
        }

        // If product has variants, calculate total stock across variants
        if ($this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
            $sum = 0;
            foreach ($this->variants as $variant) {
                $sum += $variant->available_stock;
            }
            return $sum;
        }

        return $this->availableCredentials()->count();
    }

    public function getInStockAttribute(): bool
    {
        return $this->available_stock > 0;
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

    public function getImageUrlAttribute(): string
    {
        if (!empty($this->primary_image)) {
            if (str_starts_with($this->primary_image, 'http://') || str_starts_with($this->primary_image, 'https://')) {
                return $this->primary_image;
            }
            if (str_starts_with($this->primary_image, '/')) {
                return $this->primary_image;
            }
            return asset('storage/' . $this->primary_image);
        }

        // Fallback to first media
        if ($this->relationLoaded('media') && $this->media->isNotEmpty()) {
            return $this->media->first()->url;
        }

        return asset('assets/images/capcut-pro-sticker.png');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')->where('active', true);
    }

    public function scopeStoreCatalog(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where('active', true)
            ->where('show_in_store', true)
            ->where('visibility', 'public')
            ->orderBy('display_order');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        $clean = trim($term);
        return $query->where(function (Builder $q) use ($clean) {
            $q->where('name', 'like', "%{$clean}%")
                ->orWhere('short_description', 'like', "%{$clean}%")
                ->orWhere('description', 'like', "%{$clean}%")
                ->orWhere('sku', 'like', "%{$clean}%");
        });
    }
}
