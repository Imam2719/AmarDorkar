<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnRequestImage extends Model
{
    protected $fillable = ['return_request_id', 'image_data', 'mime_type', 'sort_order'];

    public function returnRequest()
    {
        return $this->belongsTo(ReturnRequest::class);
    }

    public function getImageUrlAttribute(): string
    {
        return route('image.return', $this->id);
    }
}
