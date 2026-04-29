<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name_bn', 'name_en',
        'description_bn', 'description_en', 'sku',
        'price', 'discount_price', 'stock_quantity',
        'product_type', 'product_size', 'image_path',
        'is_featured', 'is_new_arrival', 'status'
    ];

    protected $casts = [
        'is_featured'    => 'boolean',
        'is_new_arrival' => 'boolean',
        'status'         => 'boolean',
        'price'          => 'decimal:2',
    ];

    // ── Relationships ───────────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function discounts()
    {
        return $this->hasMany(DiscountProduct::class);
    }

    public function activeDiscount()
    {
        return $this->hasOne(DiscountProduct::class)
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    // All images ordered by sort_order (primary first)
    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    // Only the primary/thumbnail image
    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class)->where('is_approved', true)->latest();
    }

    /**
     * FIX N+1: Active flash sale product relation (eager-loadable).
     * Use with('activeFlashSaleProduct') in listing queries instead of
     * running a fresh query per product.
     */
    public function activeFlashSaleProduct()
    {
        return $this->hasOne(FlashSaleProduct::class)
            ->whereHas('flashSale', function ($q) {
                $q->where('is_active', true)
                  ->where('start_time', '<=', now())
                  ->where('end_time', '>=', now());
            });
    }

    public function getAvgRatingAttribute()
    {
        if (array_key_exists('avg_rating', $this->attributes)) {
            return (float) $this->attributes['avg_rating'];
        }
        return $this->reviews()->avg('rating') ?? 0;
    }

    // ── Accessors ───────────────────────────────────────────────────

    public function getNameAttribute()
    {
        return app()->getLocale() === 'bn' ? $this->name_bn : $this->name_en;
    }

    public function getDescriptionAttribute()
    {
        return app()->getLocale() === 'bn' ? $this->description_bn : $this->description_en;
    }

    /**
     * FIX N+1: getFinalPriceAttribute checks eager-loaded relation first.
     * If 'activeFlashSaleProduct' was eager-loaded (with()), no extra query runs.
     * Falls back to a single DB query only when not eager-loaded (e.g. single product page).
     */
    public function getFinalPriceAttribute()
    {
        // Use eager-loaded relation if available — zero extra queries
        if ($this->relationLoaded('activeFlashSaleProduct')) {
            $fsp = $this->activeFlashSaleProduct;
        } else {
            // Single product page fallback — one query is acceptable
            $fsp = FlashSaleProduct::whereHas('flashSale', function ($q) {
                    $q->where('is_active', true)
                      ->where('start_time', '<=', now())
                      ->where('end_time', '>=', now());
                })
                ->where('product_id', $this->id)
                ->first();
        }

        if ($fsp && $fsp->flash_price > 0) {
            return (float) $fsp->flash_price;
        }

        return $this->discount_price ?? $this->price;
    }

    /**
     * Returns the primary image URL.
     * Priority: storage path → placeholder
     */
    public function getImageUrlAttribute(): string
    {
        $primary = $this->primaryImage;
        if ($primary) {
            return $primary->url;
        }

        $first = $this->images->first();
        if ($first) {
            return $first->url;
        }

        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }

        return asset('images/no-image.png');
    }
}
