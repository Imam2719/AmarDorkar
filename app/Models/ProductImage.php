<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'image_data',
        'mime_type',
        'sort_order',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    // Exclude binary data from serialization / JSON responses
    protected $hidden = ['image_data'];

    /**
     * Enforce only one primary image per product.
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
     * URL to serve this image from the database.
     */
    public function getUrlAttribute(): string
    {
        if ($this->image_data) {
            return route('image.product', $this->id);
        }

        return asset('images/no-image.png');
    }
}
