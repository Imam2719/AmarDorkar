<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlashSaleProduct extends Model
{
    protected $fillable = [
        'flash_sale_id', 'product_id', 'flash_price', 'max_quantity', 'sold_quantity'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function flashSale()
    {
        return $this->belongsTo(FlashSale::class);
    }

    public function getRemainingAttribute()
    {
        return max(0, $this->max_quantity - $this->sold_quantity);
    }

    public function getSoldPercentAttribute()
    {
        if ($this->max_quantity <= 0) return 0;
        return min(100, round(($this->sold_quantity / $this->max_quantity) * 100));
    }
}
