<?php
namespace App\Http\Controllers;

use App\Models\ProductReview;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id'   => 'required|exists:products,id',
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'required|string|min:3|max:1000',
            'review_images'   => 'nullable|array|max:5',
            'review_images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ], [
            'comment.required' => app()->getLocale() === 'bn' ? 'মন্তব্য লিখুন।' : 'Comment is required.',
            'comment.min'      => app()->getLocale() === 'bn' ? 'মন্তব্য কমপক্ষে ৩ অক্ষর হতে হবে।' : 'Comment must be at least 3 characters.',
            'rating.required'  => app()->getLocale() === 'bn' ? 'রেটিং দিন।' : 'Please select a rating.',
            'product_id.required' => app()->getLocale() === 'bn' ? 'পণ্য নির্বাচন করুন।' : 'Product is required.',
        ]);

        $userId    = auth()->id();
        $productId = (int) $request->product_id;

        // Must have a delivered order containing this product
        $orderId = ProductReview::getDeliveredOrderId($userId, $productId);
        if (!$orderId) {
            return back()->with('error',
                app()->getLocale() === 'bn'
                    ? 'শুধুমাত্র ডেলিভারি হওয়া পণ্যে রিভিউ দেওয়া যাবে।'
                    : 'You can only review products from a delivered order.'
            );
        }

        // Already reviewed?
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

        // ✅ Review images — সব images JSON array হিসেবে store করা হচ্ছে
        // format: [{"data":"base64...","mime":"image/jpeg"}, ...]
        $imageData = null;
        $mimeType  = null;
        $files = $request->file('review_images') ?? [];
        if (!is_array($files)) $files = [$files];
        $files = array_values(array_filter($files));

        if (!empty($files)) {
            $allImages = [];
            foreach (array_slice($files, 0, 5) as $file) {
                $allImages[] = [
                    'data' => base64_encode(file_get_contents($file->getRealPath())),
                    'mime' => $file->getMimeType(),
                ];
            }
            // প্রথমটা আলাদাভাবেও রাখি backward compat এর জন্য
            $mimeType  = $allImages[0]['mime'];
            // সব images JSON হিসেবে review_image_data তে store
            $imageData = json_encode($allImages);
        }

        ProductReview::create([
            'product_id'        => $productId,
            'user_id'           => $userId,
            'order_id'          => $orderId,
            'rating'            => $request->rating,
            'comment'           => $request->comment,
            'review_image_data' => $imageData,
            'review_mime_type'  => $mimeType,
            'is_approved'       => true,
        ]);

        return back()->with('success',
            app()->getLocale() === 'bn'
                ? 'আপনার রিভিউ সফলভাবে যোগ করা হয়েছে!'
                : 'Your review has been submitted successfully!'
        );
    }

    public function destroy(\App\Models\ProductReview $review)
    {
        if (auth()->id() !== $review->user_id) {
            abort(403);
        }
        $review->delete();
        return back()->with('success',
            app()->getLocale() === 'bn' ? 'রিভিউ মুছে ফেলা হয়েছে।' : 'Review deleted.'
        );
    }
}