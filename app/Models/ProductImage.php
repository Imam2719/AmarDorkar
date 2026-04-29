<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'image_path',
        'sort_order',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];


    /**
     * FIX: Enforce only one primary image per product.
     * When a new primary is saved, all others for the same product are unset.
     * This prevents race conditions that could leave multiple primaries.
     */
    protected static function booted(): void
    {
        static::saving(function (self $image) {
            if ($image->is_primary) {
                static::where('product_id', $image->product_id)
                    ->where('id', '!=', $image->id ?? 0)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Image এর URL। Storage থেকে serve করা হয়।
     */
    public function getUrlAttribute(): string
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }

        return asset('images/no-image.png');
    }
}