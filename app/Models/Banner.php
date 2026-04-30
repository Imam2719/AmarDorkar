<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'button_text',
        'button_link',
        'image_data',
        'mime_type',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // image_data hidden রাখলে getImageUrlAttribute এ আসে না — তাই removed
    protected $hidden = [];

    public static function getActive()
    {
        // image_data বাদ দিয়ে শুধু metadata নিই।
        // image_data cache এ store করলে cache.value column overflow হয়।
        return static::select(['id', 'title', 'subtitle', 'button_text', 'button_link', 'sort_order', 'is_active'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function getImageUrlAttribute(): string
    {
        // data URI বানানো বন্ধ — route দিয়ে image serve হবে।
        // এতে cache এ শুধু metadata থাকবে, image data থাকবে না।
        return route('image.banner', $this->id);
    }
}