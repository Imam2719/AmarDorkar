<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\Request;

/**
 * FIX 7: Admin Review Management Panel
 * Allows admins to list, approve/reject, and delete product reviews.
 */
class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $search   = $request->input('search', '');
        $filter   = $request->input('filter', ''); // 'approved', 'pending'

        $query = ProductReview::with(['product', 'user'])->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                  ->orWhereHas('product', fn($p) => $p->where('name_en', 'like', "%{$search}%"))
                  ->orWhereHas('user',    fn($u) => $u->where('name',    'like', "%{$search}%"));
            });
        }

        if ($filter === 'approved') {
            $query->where('is_approved', true);
        } elseif ($filter === 'pending') {
            $query->where('is_approved', false);
        }

        $reviews = $query->paginate(20)->withQueryString();

        $stats = [
            'total'    => ProductReview::count(),
            'approved' => ProductReview::where('is_approved', true)->count(),
            'pending'  => ProductReview::where('is_approved', false)->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'stats', 'search', 'filter'));
    }

    public function approve(ProductReview $review)
    {
        $review->update(['is_approved' => true]);
        return back()->with('success', 'রিভিউটি অনুমোদন করা হয়েছে।');
    }

    public function reject(ProductReview $review)
    {
        $review->update(['is_approved' => false]);
        return back()->with('success', 'রিভিউটি প্রত্যাখ্যান করা হয়েছে।');
    }

    public function destroy(ProductReview $review)
    {
        // Image is stored in DB — just delete the record
        $review->delete();
        return back()->with('success', 'রিভিউটি মুছে ফেলা হয়েছে।');
    }
}
