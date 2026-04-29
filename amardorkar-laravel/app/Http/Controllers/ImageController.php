<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use App\Models\Banner;
use Illuminate\Http\Response;

class ImageController extends Controller
{
    /**
     * Serve a product image from the database.
     */
    public function product(int $id): Response
    {
        $image = ProductImage::select('image_data', 'mime_type')->findOrFail($id);

        if (!$image->image_data) {
            abort(404);
        }

        return response(base64_decode($image->image_data), 200)
            ->header('Content-Type', $image->mime_type ?? 'image/jpeg')
            ->header('Cache-Control', 'public, max-age=86400'); // 1 day cache
    }

    /**
     * Serve a banner image from the database.
     */
    public function banner(int $id): Response
    {
        $banner = Banner::select('image_data', 'mime_type')->findOrFail($id);

        if (!$banner->image_data) {
            abort(404);
        }

        return response(base64_decode($banner->image_data), 200)
            ->header('Content-Type', $banner->mime_type ?? 'image/jpeg')
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
