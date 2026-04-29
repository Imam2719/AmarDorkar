<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;

class ProductController extends Controller
{
    public function show($id)
    {
        $product = Product::where('status', true)
            ->with(['category', 'images', 'primaryImage', 'activeDiscount', 'activeFlashSaleProduct', 'reviews.user'])
            ->withAvg(['reviews as avg_rating' => fn($q) => $q->where('is_approved', true)], 'rating')
            ->findOrFail($id);

        $related = Product::where('status', true)
            ->where('id', '!=', $id)
            ->where('category_id', $product->category_id)
            ->with(['primaryImage', 'activeDiscount', 'activeFlashSaleProduct'])
            ->withAvg(['reviews as avg_rating' => fn($q) => $q->where('is_approved', true)], 'rating')
            ->take(4)
            ->get();

        // ── Rating stats ───────────────────────────────────────────
        $avgRating   = $product->reviews->avg('rating') ?? 0;
        $reviewCount = $product->reviews->count();
        $ratingDist  = [];
        for ($i = 5; $i >= 1; $i--) {
            $ratingDist[$i] = $product->reviews->where('rating', $i)->count();
        }

        // ── Can the logged-in user write a review? ─────────────────
        // true  → has a delivered order with this product AND hasn't reviewed yet
        // false → not logged in, not purchased, not delivered, or already reviewed
        $canReview = false;
        if (auth()->check()) {
            $canReview = ProductReview::canUserReview(auth()->id(), $product->id);
        }

        return view('product.show', compact(
            'product', 'related',
            'avgRating', 'reviewCount', 'ratingDist',
            'canReview'
        ));
    }
}
