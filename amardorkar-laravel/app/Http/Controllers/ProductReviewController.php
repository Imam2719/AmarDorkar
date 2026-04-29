<?php
namespace App\Http\Controllers;

use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id'   => 'required|exists:products,id',
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'required|string|min:3|max:1000',
            'review_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:9072',
        ], [
            'comment.required' => app()->getLocale() === 'bn' ? 'মন্তব্য লিখুন।' : 'Comment is required.',
            'comment.min'      => app()->getLocale() === 'bn' ? 'মন্তব্য কমপক্ষে ৩ অক্ষর হতে হবে।' : 'Comment must be at least 3 characters.',
            'rating.required'  => app()->getLocale() === 'bn' ? 'রেটিং দিন।' : 'Please select a rating.',
            'product_id.required' => app()->getLocale() === 'bn' ? 'পণ্য নির্বাচন করুন।' : 'Product is required.',
        ]);

        $userId    = auth()->id();
        $productId = (int) $request->product_id;

        // ── 1. Must have a delivered order containing this product ──
        $orderId = ProductReview::getDeliveredOrderId($userId, $productId);

        if (!$orderId) {
            return back()->with('error',
                app()->getLocale() === 'bn'
                    ? 'শুধুমাত্র ডেলিভারি হওয়া পণ্যে রিভিউ দেওয়া যাবে।'
                    : 'You can only review products from a delivered order.'
            );
        }

        // ── 2. Already reviewed? ───────────────────────────────────
        $alreadyReviewed = ProductReview::where('user_id', $userId)
                                        ->where('product_id', $productId)
                                        ->exists();
        if ($alreadyReviewed) {
            return back()->with('error',
                app()->getLocale() === 'bn'
                    ? 'আপনি ইতিমধ্যে এই পণ্যে রিভিউ দিয়েছেন।'
                    : 'You have already reviewed this product.'
            );
        }

        // ── 3. Save image if provided ──────────────────────────────
        $imagePath = null;
        if ($request->hasFile('review_image')) {
            $imagePath = $request->file('review_image')->store('reviews', 'public');
        }

        // ── 4. Create review — attach the verified order_id ────────
        ProductReview::create([
            'product_id'   => $productId,
            'user_id'      => $userId,
            'order_id'     => $orderId,          // proven delivered order
            'rating'       => $request->rating,
            'comment'      => $request->comment,
            'review_image' => $imagePath,
            'is_approved'  => true,
        ]);

        return back()->with('success',
            app()->getLocale() === 'bn'
                ? 'আপনার রিভিউ সফলভাবে যোগ করা হয়েছে!'
                : 'Your review has been submitted successfully!'
        );
    }

    public function destroy(ProductReview $review)
    {
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }
        if ($review->review_image) {
            Storage::disk('public')->delete($review->review_image);
        }
        $review->delete();

        return back()->with('success',
            app()->getLocale() === 'bn' ? 'রিভিউ মুছে ফেলা হয়েছে।' : 'Review deleted.'
        );
    }
}
