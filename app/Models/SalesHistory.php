<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesHistory extends Model
{
    protected $table = 'sales_history';

    protected $fillable = [
        'order_id', 'product_id', 'product_name', 'product_type', 'product_size',
        'product_details', 'customer_name', 'customer_email', 'customer_mobile',
        'customer_address', 'shipping_location', 'quantity', 'unit_price',
        // FIX: redundant columns removed:
        // - 'total_product_amount' dropped (same as subtotal)
        // - 'discount_applied' dropped (same as is_discounted)
        // - 'total_amount' dropped (same as final_amount)
        'subtotal', 'is_discounted', 'original_price', 'shipping_charge',
        'final_amount', 'discount_amount', 'original_unit_price',
        'order_status', 'payment_method', 'payment_status',
        'sold_by', 'delivery_status', 'notes', 'order_time', 'selling_time',
    ];

    protected $casts = [
        'is_discounted'  => 'boolean',
        'selling_time'   => 'datetime',
        'order_time'     => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
