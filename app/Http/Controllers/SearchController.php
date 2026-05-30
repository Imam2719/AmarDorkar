<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query       = $request->get('q', '');
        $categoryId  = $request->get('category');
        $priceMin    = $request->get('price_min');
        $priceMax    = $request->get('price_max');
        $productType = $request->get('product_type');
        $minRating   = $request->get('rating');
        $inStock     = $request->get('in_stock');
        $sortBy      = $request->get('sort', 'newest');
        $perPage     = 16;

        $now = now();

        $productQuery = Product::where('status', true)
            ->with(['activeDiscount', 'primaryImage', 'activeFlashSaleProduct'])
            ->withAvg(['reviews as avg_rating' => fn($q) => $q->where('is_approved', true)], 'rating')
            ->withCount(['reviews as review_count' => fn($q) => $q->where('is_approved', true)])
            // ── Compute effective (discounted) price as a sub-select ──────────────
            // Priority: flash sale price > discount_products price > products.discount_price > products.price
            ->selectRaw("products.*, (
                COALESCE(
                    -- Flash sale price (active flash sale)
                    (SELECT fsp.flash_price
                     FROM flash_sale_products fsp
                     INNER JOIN flash_sales fs ON fs.id = fsp.flash_sale_id
                     WHERE fsp.product_id = products.id
                       AND fs.is_active = 1
                       AND fs.start_time <= ?
                       AND fs.end_time   >= ?
                     ORDER BY fsp.flash_price ASC
                     LIMIT 1),
                    -- Discount-products table price (active discount)
                    (SELECT dp.discount_price
                     FROM discount_products dp
                     WHERE dp.product_id = products.id
                       AND dp.is_active = 1
                       AND dp.start_date <= ?
                       AND dp.end_date   >= ?
                     ORDER BY dp.discount_price ASC
                     LIMIT 1),
                    -- products.discount_price column (static discount)
                    NULLIF(products.discount_price, 0),
                    -- Fallback: base price
                    products.price
                )
            ) as effective_price", [$now, $now, $now, $now]);

        // ── Text search ───────────────────────────────────────────────────────
        if ($query) {
            if (DB::getDriverName() === 'mysql') {
                $productQuery->whereRaw(
                    'MATCH(name_bn, name_en, description_bn, description_en, sku) AGAINST(? IN BOOLEAN MODE)',
                    ['"' . str_replace('"', '', $query) . '*"']
                );
            } else {
                $productQuery->where(function ($q) use ($query) {
                    $q->where('name_bn',        'like', "%{$query}%")
                      ->orWhere('name_en',        'like', "%{$query}%")
                      ->orWhere('description_bn', 'like', "%{$query}%")
                      ->orWhere('description_en', 'like', "%{$query}%")
                      ->orWhere('sku',            'like', "%{$query}%");
                });
            }
        }

        // ── Category filter ───────────────────────────────────────────────────
        if ($categoryId) {
            $productQuery->where('category_id', $categoryId);
        }

        // ── Price filter — uses effective_price (discounted), not base price ──
        if ($priceMin !== null && $priceMin !== '') {
            $productQuery->havingRaw('effective_price >= ?', [(float) $priceMin]);
        }
        if ($priceMax !== null && $priceMax !== '') {
            $productQuery->havingRaw('effective_price <= ?', [(float) $priceMax]);
        }

        // ── Other filters ─────────────────────────────────────────────────────
        if ($productType) {
            $productQuery->where('product_type', $productType);
        }
        if ($inStock) {
            $productQuery->where('stock_quantity', '>', 0);
        }
        if ($minRating) {
            $productQuery->having('avg_rating', '>=', (float) $minRating);
        }

        // ── Sort ──────────────────────────────────────────────────────────────
        // price_low / price_high sort by effective_price so discounted items rank correctly
        switch ($sortBy) {
            case 'price_low':
                $productQuery->orderByRaw('effective_price ASC');
                break;
            case 'price_high':
                $productQuery->orderByRaw('effective_price DESC');
                break;
            case 'rating':
                $productQuery->orderByDesc('avg_rating');
                break;
            case 'popular':
                $productQuery->orderByDesc('review_count');
                break;
            default:
                $productQuery->latest();
                break;
        }

        // ── AJAX Infinite Scroll ──────────────────────────────────────────────
        if ($request->ajax() || $request->wantsJson()) {
            $page   = (int) $request->input('page', 1);
            $total  = $productQuery->count();
            $items  = $productQuery->skip(($page - 1) * $perPage)->take($perPage)->get();
            $locale = app()->getLocale();
            $isBn   = $locale === 'bn';

            return response()->json([
                'products' => $items->map(fn($p) => [
                    'id'               => $p->id,
                    'name'             => $p->name,
                    'img'              => $p->image_url,
                    'price'            => number_format($p->price, 0),
                    // Show effective (discounted) price
                    'effective_price'  => number_format($p->effective_price, 0),
                    'has_discount'     => (float) $p->effective_price < (float) $p->price,
                    'discount_price'   => $p->activeDiscount
                                            ? number_format($p->activeDiscount->discount_price, 0)
                                            : null,
                    'discount_pct'     => $p->activeDiscount
                                            ? number_format($p->activeDiscount->discount_percentage, 0)
                                            : null,
                    'avg_rating'       => $p->avg_rating ?? 0,
                    'review_count'     => $p->review_count ?? 0,
                    'in_stock'         => $p->stock_quantity > 0,
                    'product_url'      => route('product.show', $p->id),
                    'add_to_cart_label'     => $isBn ? 'কার্টে যোগ করুন' : 'Add to Cart',
                    'out_of_stock_label'    => $isBn ? 'স্টকে নেই' : 'Out of Stock',
                ]),
                'has_more'  => ($page * $perPage) < $total,
                'next_page' => $page + 1,
                'total'     => $total,
            ]);
        }

        // ── Normal page load ──────────────────────────────────────────────────
        $total    = $productQuery->count();
        $products = $productQuery->take($perPage)->get();
        $hasMore  = $total > $perPage;

        $categories      = Category::where('status', true)->orderBy('sort_order')->get();
        $currentCategory = $categoryId ? Category::find($categoryId) : null;

        // ── Global price range — use effective (min discounted) price ─────────
        // We need the global min/max of effective prices so the price slider
        // reflects what users actually see in the UI, not raw base prices.
        $priceStats = DB::select("
            SELECT
                MIN(
                    COALESCE(
                        (SELECT fsp.flash_price
                         FROM flash_sale_products fsp
                         INNER JOIN flash_sales fs ON fs.id = fsp.flash_sale_id
                         WHERE fsp.product_id = p.id
                           AND fs.is_active = 1
                           AND fs.start_time <= ?
                           AND fs.end_time   >= ?
                         ORDER BY fsp.flash_price ASC
                         LIMIT 1),
                        (SELECT dp.discount_price
                         FROM discount_products dp
                         WHERE dp.product_id = p.id
                           AND dp.is_active = 1
                           AND dp.start_date <= ?
                           AND dp.end_date   >= ?
                         ORDER BY dp.discount_price ASC
                         LIMIT 1),
                        NULLIF(p.discount_price, 0),
                        p.price
                    )
                ) as min_price,
                MAX(p.price) as max_price
            FROM products p
            WHERE p.status = 1
        ", [$now, $now, $now, $now]);

        $globalMin = (int) ($priceStats[0]->min_price ?? 0);
        $globalMax = (int) ($priceStats[0]->max_price ?? 10000);

        $productTypes = Product::where('status', true)
            ->whereNotNull('product_type')
            ->where('product_type', '!=', '')
            ->distinct()
            ->pluck('product_type');

        $hasFilters = $priceMin || $priceMax || $categoryId || $productType || $minRating || $inStock;

        return view('search.index', compact(
            'products', 'query', 'categories', 'categoryId', 'currentCategory',
            'priceMin', 'priceMax', 'globalMin', 'globalMax',
            'productType', 'productTypes', 'minRating', 'inStock', 'sortBy',
            'hasMore', 'total', 'hasFilters'
        ));
    }

    public function autocomplete(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $now = now();

        $products = Product::where('status', true)
            ->where(function ($query) use ($q) {
                $query->where('name_bn', 'like', "%{$q}%")
                      ->orWhere('name_en', 'like', "%{$q}%")
                      ->orWhere('sku',     'like', "%{$q}%");
            })
            ->with(['primaryImage', 'activeDiscount', 'activeFlashSaleProduct'])
            ->select('id', 'name_bn', 'name_en', 'price', 'discount_price', 'sku', 'category_id')
            ->limit(8)
            ->get();

        $locale = app()->getLocale();

        $suggestions = $products->map(fn($p) => [
            'id'              => $p->id,
            'name'            => $locale === 'bn' ? $p->name_bn : $p->name_en,
            // Show discounted price in autocomplete as well
            'price'           => number_format($p->price, 0),
            'effective_price' => number_format($p->final_price, 0),
            'has_discount'    => $p->final_price < $p->price,
            'url'             => route('product.show', $p->id),
            'img'             => $p->image_url,
        ]);

        $categories = Category::where('status', true)
            ->where(function ($q2) use ($q) {
                $q2->where('name_bn', 'like', "%{$q}%")
                   ->orWhere('name_en', 'like', "%{$q}%");
            })
            ->limit(3)
            ->get();

        $catSuggestions = $categories->map(fn($c) => [
            'type'  => 'category',
            'id'    => $c->id,
            'name'  => $locale === 'bn' ? $c->name_bn : $c->name_en,
            'url'   => route('search', ['category' => $c->id]),
            'icon'  => $c->icon ?? 'fas fa-tag',
            'color' => $c->color ?? 'var(--primary)',
        ]);

        return response()->json([
            'products'   => $suggestions,
            'categories' => $catSuggestions,
        ]);
    }
}
