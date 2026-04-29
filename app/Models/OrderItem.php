<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_name', 'price',
        'original_price', 'is_discounted', 'discount_amount',
        'quantity', 'subtotal', 'product_type', 'product_size',
        'selected_size', // FIX: added — was missing, causing $item->selected_size to be null
        'flash_sale_product_id',
    ];

    protected $casts = [
        'is_discounted'  => 'boolean',
        'price'          => 'decimal:2',
        'original_price' => 'decimal:2',
        'discount_amount'=> 'decimal:2',
        'subtotal'       => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Auto-compute subtotal accessor (price × quantity)
    public function getSubtotalAttribute(): float
    {
        return (float) ($this->attributes['subtotal'] ?? $this->price * $this->quantity);
    }
}
