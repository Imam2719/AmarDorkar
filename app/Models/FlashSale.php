<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlashSale extends Model
{
    protected $fillable = [
        'title_en', 'title_bn', 'start_time', 'end_time', 'is_active'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
        'is_active'  => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(FlashSaleProduct::class);
    }

    public function getTitleAttribute()
    {
        return app()->getLocale() === 'bn' ? $this->title_bn : $this->title_en;
    }

    public static function getActive()
    {
        return static::where('is_active', true)
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->with(['products.product.primaryImage'])
            ->first();
    }
}
