<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use App\Models\Banner;
use App\Models\ProductReview;
use App\Models\ReturnRequestImage;
use Illuminate\Http\Response;

class ImageController extends Controller
{
    public function product(int $id): Response
    {
        $image = ProductImage::select('image_data', 'mime_type')->findOrFail($id);

        if (!$image->image_data) {
            abort(404);
        }

        $binary = base64_decode($image->image_data);

        return response($binary, 200)
            ->header('Content-Type', $image->mime_type ?? 'image/jpeg')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    public function banner(int $id): Response
    {
        $banner = Banner::select('image_data', 'mime_type')->findOrFail($id);

        if (!$banner->image_data) {
            abort(404);
        }

        $binary = base64_decode($banner->image_data);

        return response($binary, 200)
            ->header('Content-Type', $banner->mime_type ?? 'image/jpeg')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    public function review(int $id, int $index = 0): Response
    {
        $review = ProductReview::select('review_image_data', 'review_mime_type')->findOrFail($id);

        if (!$review->review_image_data) {
            abort(404);
        }

        $raw = $review->review_image_data;

        // JSON array format (নতুন) নাকি single base64 (পুরনো)?
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $item = $decoded[$index] ?? $decoded[0];
            $binary = base64_decode($item['data']);
            $mime   = $item['mime'] ?? 'image/jpeg';
        } else {
            $binary = base64_decode($raw);
            $mime   = $review->review_mime_type ?? 'image/jpeg';
        }

        return response($binary, 200)
            ->header('Content-Type', $mime)
            ->header('Cache-Control', 'public, max-age=86400');
    }

    public function returnImage(int $id): Response
    {
        $image = ReturnRequestImage::select('image_data', 'mime_type')->findOrFail($id);

        if (!$image->image_data) {
            abort(404);
        }

        $binary = base64_decode($image->image_data);

        return response($binary, 200)
            ->header('Content-Type', $image->mime_type ?? 'image/jpeg')
            ->header('Cache-Control', 'public, max-age=86400');
    }
}