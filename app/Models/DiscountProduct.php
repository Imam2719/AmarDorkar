<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountProduct extends Model
{
    protected $fillable = [
        'product_id', 'original_price', 'discount_price',
        'discount_percentage', 'start_date', 'end_date', 'is_active'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
        'is_active'  => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Auto-calculate percentage before saving
    protected static function booted()
    {
        static::saving(function ($discount) {
            if ($discount->original_price > 0) {
                $discount->discount_percentage = round(
                    (($discount->original_price - $discount->discount_price) / $discount->original_price) * 100,
                    2
                );
            }
        });
    }
}