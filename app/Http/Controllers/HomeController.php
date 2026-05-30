<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Banner;
use App\Models\DiscountProduct;
use App\Models\FlashSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        // ── BUG 2 FIX: withAvg-এ is_approved filter যোগ করা হয়েছে ──────
        // আগে: ->withAvg('reviews as avg_rating', 'rating')
        // এতে unapproved/spam reviews-ও rating-এ ঢুকে যেতো।
        // SearchController-এ ঠিক ছিল, HomeController-এ ছিল না।
        // এখন দুই জায়গায় একই logic — শুধু approved reviews count হবে।
        $products = Product::where('status', true)
            ->with(['activeDiscount', 'primaryImage', 'activeFlashSaleProduct'])
            ->withAvg(
                ['reviews as avg_rating' => fn($q) => $q->where('is_approved', true)],
                'rating'
            )
            ->latest()
            ->get();

        $newArrivals = Product::where('status', true)
            ->where('is_new_arrival', true)
            ->with(['activeDiscount', 'primaryImage', 'activeFlashSaleProduct'])
            ->withAvg(
                ['reviews as avg_rating' => fn($q) => $q->where('is_approved', true)],
                'rating'
            )
            ->latest()
            ->take(12)
            ->get();

        $categories = Cache::remember('active_categories', 300, function () {
            return Category::where('status', true)
                ->orderBy('sort_order')
                ->get();
        });

        $activeFlashSales = FlashSale::getActive(); // all live flash sales
        $activeFlashSale  = $activeFlashSales->first(); // BC: kept for JS countdown (uses first)

        $banners = Cache::remember('active_banners', 300, function () {
            return Banner::getActive();
        });

        $newArrivalsCount = Product::where('status', true)->where('is_new_arrival', true)->count();
        $offersCount = \App\Models\DiscountProduct::where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->count();

        return view('home.index', compact(
            'products', 'newArrivals', 'activeFlashSale', 'activeFlashSales',
            'categories', 'banners', 'newArrivalsCount', 'offersCount'
        ));
    }

    public function newArrivals(Request $request)
    {
        $perPage = 24;
        $query = Product::where('status', true)
            ->where('is_new_arrival', true)
            ->with(['activeDiscount', 'primaryImage'])
            // ── BUG 2 FIX: এখানেও approved filter ──
            ->withAvg(
                ['reviews as avg_rating' => fn($q) => $q->where('is_approved', true)],
                'rating'
            )
            ->latest();

        if ($request->ajax() || $request->wantsJson()) {
            $page   = (int) $request->input('page', 1);
            $total  = $query->count();
            $items  = $query->skip(($page - 1) * $perPage)->take($perPage)->get();
            $locale = app()->getLocale();

            return response()->json([
                'products'  => $items->map(fn($p) => $this->productJson($p, $locale)),
                'has_more'  => ($page * $perPage) < $total,
                'next_page' => $page + 1,
                'total'     => $total,
            ]);
        }

        $total    = $query->count();
        $products = $query->take($perPage)->get();
        $hasMore  = $total > $perPage;

        return view('home.new-arrivals', compact('products', 'hasMore', 'total'));
    }

    public function offers(Request $request)
    {
        $perPage = 24;
        $offerQuery = DiscountProduct::where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->with(['product.primaryImage', 'product.activeDiscount'])
            ->orderByDesc('discount_percentage');

        if ($request->ajax() || $request->wantsJson()) {
            $page  = (int) $request->input('page', 1);
            $total = $offerQuery->count();
            $items = $offerQuery->skip(($page - 1) * $perPage)->take($perPage)->get();

            return response()->json([
                'offers'    => $items->filter(fn($o) => $o->product)->map(fn($o) => [
                    'product_id'          => $o->product_id,
                    'name'                => $o->product->name,
                    'img'                 => $o->product->primaryImage?->url ?? $o->product->image_url,
                    'original_price'      => number_format($o->original_price, 0),
                    'discount_price'      => number_format($o->discount_price, 0),
                    'discount_percentage' => number_format($o->discount_percentage, 0),
                    'end_date'            => $o->end_date->format('d M Y'),
                    'product_url'         => route('product.show', $o->product_id),
                ]),
                'has_more'  => ($page * $perPage) < $total,
                'next_page' => $page + 1,
                'total'     => $total,
            ]);
        }

        $total         = $offerQuery->count();
        $offerProducts = $offerQuery->take($perPage)->get();
        $hasMore       = $total > $perPage;

        return view('home.offers', compact('offerProducts', 'hasMore', 'total'));
    }

    private function productJson($p, $locale): array
    {
        return [
            'id'             => $p->id,
            'name'           => $p->name,
            'img'            => $p->primaryImage?->url ?? $p->image_url,
            'price'          => number_format($p->price, 0),
            'discount_price' => $p->activeDiscount
                                    ? number_format($p->activeDiscount->discount_price, 0)
                                    : null,
            'discount_pct'   => $p->activeDiscount
                                    ? number_format($p->activeDiscount->discount_percentage, 0)
                                    : null,
            // ── BUG 2 FIX: avg_rating এখন approved-only, null হলে 0 দাও ──
            'avg_rating'     => $p->avg_rating ?? 0,
            'description'    => $p->description,
            'product_url'    => route('product.show', $p->id),
        ];
    }
}