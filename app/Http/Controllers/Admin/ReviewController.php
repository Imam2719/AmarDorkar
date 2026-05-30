<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $filter = $request->input('filter', '');

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

        $stats = [
            'total'    => ProductReview::count(),
            'approved' => ProductReview::where('is_approved', true)->count(),
            'pending'  => ProductReview::where('is_approved', false)->count(),
        ];

        // AJAX infinite scroll request
        if ($request->ajax() || $request->wantsJson()) {
            $page    = (int) $request->input('page', 1);
            $perPage = 20;
            $total   = $query->count();
            $reviews = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

            return response()->json([
                'reviews'   => $reviews->map(fn($r) => [
                    'id'          => $r->id,
                    'product'     => $r->product?->name_en ?? 'N/A',
                    'user'        => $r->user?->name ?? 'N/A',
                    'rating'      => $r->rating,
                    'comment'     => \Str::limit($r->comment, 60),
                    'image_url'   => $r->image_url ?? null,
                    'is_approved' => $r->is_approved,
                    'date'        => $r->created_at->format('d M Y'),
                    'approve_url' => route('admin.reviews.approve', $r),
                    'reject_url'  => route('admin.reviews.reject', $r),
                    'delete_url'  => route('admin.reviews.destroy', $r),
                ]),
                'total'    => $total,
                'has_more' => ($page * $perPage) < $total,
                'next_page'=> $page + 1,
            ]);
        }

        // Normal page load — first 20
        $total   = $query->count();
        $reviews = $query->take(20)->get();
        $hasMore = $total > 20;

        return view('admin.reviews.index', compact(
            'reviews', 'stats', 'search', 'filter', 'total', 'hasMore'
        ));
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
        $review->delete();
        return back()->with('success', 'রিভিউটি মুছে ফেলা হয়েছে।');
    }
}