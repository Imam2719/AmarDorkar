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

    /**
     * BUG FIX: getActive() এখন Cache::remember ব্যবহার করে।
     * আগে সরাসরি DB query চলত — navbar সহ প্রতিটা page load এ fresh query হত।
     * এখন 60 সেকেন্ড cache করা হবে। Toggle/addProduct/removeProduct/destroy এ
     * Cache::forget('active_flash_sale') call হয়, তাই stale data হবে না।
     */
    /**
     * Returns all currently-live flash sales (active + within time window).
     * Multiple flash sales can now be active simultaneously.
     */
    public static function getActive()
    {
        return \Illuminate\Support\Facades\Cache::remember('active_flash_sale', 60, function () {
            return static::where('is_active', true)
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->with(['products.product.primaryImage'])
                ->get();
        });
    }

    /**
     * Convenience: get the first live flash sale.
     * Use this in views/controllers that still expect a single item.
     */
    public static function getActiveFirst()
    {
        return static::getActive()->first();
    }

    /**
     * Cache clear করার central method।
     */
    public static function clearActiveCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget('active_flash_sale');
    }
}
