<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'button_text',
        'button_link',
        'image_path',
        'image_data',   // nullable — আর use হবে না, backward compat এর জন্য রাখা
        'mime_type',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = ['image_data'];

    public static function getActive()
    {
        return static::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Banner image এর URL।
     * নতুন method: সরাসরি storage থেকে serve করা হয়।
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }

        // Legacy: পুরনো base64 data থাকলে route দিয়ে serve করুন
        if ($this->image_data) {
            return route('image.banner', $this->id);
        }

        return asset('images/no-image.png');
    }
}