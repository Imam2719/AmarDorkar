<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class FlashSaleController extends Controller
{
    private int $perPage = 10;

    // ══════════════════════════════════════════════════
    //  INDEX — supports Infinite Scroll via AJAX
    // ══════════════════════════════════════════════════
    public function index(Request $request)
    {
        $search  = $request->input('search', '');
        $status  = $request->input('status', '');
        $perPage = $this->perPage;

        // ── AJAX / Infinite Scroll ────────────────────
        if ($request->ajax() || $request->wantsJson()) {
            $page  = max(1, (int) $request->input('page', 1));
            $query = $this->buildQuery($search, $status);
            $total = (clone $query)->count();
            $items = (clone $query)
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->load('products');

            $hasMore = ($page * $perPage) < $total;
            $bn      = app()->getLocale() === 'bn';
            $now     = now();

            $rows = $items->map(function ($fs) use ($bn, $now) {
                $isLive    = $fs->is_active && $fs->start_time <= $now && $fs->end_time >= $now;
                $isExpired = $fs->end_time < $now;
                $upcoming  = $fs->start_time > $now;
                $progPct   = 0;
                $remaining = '';

                if ($isLive) {
                    $totalDur  = max(1, $fs->end_time->diffInSeconds($fs->start_time));
                    $elapsed   = $now->diffInSeconds($fs->start_time);
                    $progPct   = min(100, round(($elapsed / $totalDur) * 100));
                    $remaining = $fs->end_time->diffForHumans(['parts' => 2]);
                }

                // Status badge HTML
                if ($isLive) {
                    $statusBadge = '<span class="status-badge flash-live"><i class="fas fa-bolt"></i>' . ($bn ? 'লাইভ' : 'LIVE') . '</span>';
                } elseif ($fs->is_active && $upcoming) {
                    $statusBadge = '<span class="status-badge flash-upcoming"><i class="fas fa-clock"></i>' . ($bn ? 'আসছে' : 'Upcoming') . '</span>';
                } elseif ($fs->is_active) {
                    $statusBadge = '<span class="status-badge status-pending"><i class="fas fa-check-circle"></i>' . ($bn ? 'সক্রিয়' : 'Active') . '</span>';
                } elseif ($isExpired) {
                    $statusBadge = '<span class="status-badge status-cancelled"><i class="fas fa-history"></i>' . ($bn ? 'মেয়াদ শেষ' : 'Expired') . '</span>';
                } else {
                    $statusBadge = '<span class="status-badge status-inactive"><i class="fas fa-pause-circle"></i>' . ($bn ? 'নিষ্ক্রিয়' : 'Inactive') . '</span>';
                }

                $totalSold = $fs->products->sum('sold_quantity');
                $totalMax  = $fs->products->sum('max_quantity');
                $stockPct  = $totalMax > 0 ? min(100, round(($totalSold / $totalMax) * 100)) : 0;

                return [
                    'id'            => $fs->id,
                    'title_en'      => $fs->title_en,
                    'title_bn'      => $fs->title_bn,
                    'start_fmt'     => $fs->start_time->format('d M Y, h:i A'),
                    'end_fmt'       => $fs->end_time->format('d M Y, h:i A'),
                    'product_count' => $fs->products->count(),
                    'status_badge'  => $statusBadge,
                    'is_live'       => $isLive,
                    'is_active'     => $fs->is_active,
                    'is_expired'    => $isExpired,
                    'prog_pct'      => $progPct,
                    'remaining'     => $remaining,
                    'total_sold'    => $totalSold,
                    'total_max'     => $totalMax,
                    'stock_pct'     => $stockPct,
                    'show_url'      => route('admin.flash-sales.show',    $fs->id),
                    'toggle_url'    => route('admin.flash-sales.toggle',  $fs->id),
                    'delete_url'    => route('admin.flash-sales.destroy', $fs->id),
                ];
            });

            return response()->json([
                'rows'    => $rows,
                'hasMore' => $hasMore,
                'total'   => $total,
                'page'    => $page,
            ]);
        }

        // ── Full page load ────────────────────────────
        $query   = $this->buildQuery($search, $status);
        $total   = (clone $query)->count();
        $initial = (clone $query)
            ->skip(0)->take($perPage)
            ->get()
            ->load('products');

        $products = Product::where('status', true)->orderBy('name_en')->get();

        $now        = now();
        $allFlash   = FlashSale::withCount('products')->get();
        $statLive   = $allFlash->filter(fn($f) => $f->is_active && $f->start_time <= $now && $f->end_time >= $now)->count();
        $statActive = $allFlash->filter(fn($f) => $f->is_active)->count();
        $statExpired= $allFlash->filter(fn($f) => $f->end_time < $now)->count();
        $statTotal  = $allFlash->count();

        return view('admin.flash_sales.index', compact(
            'initial', 'total', 'products',
            'search', 'status', 'perPage',
            'statLive', 'statActive', 'statExpired', 'statTotal'
        ));
    }

    // ── Build base query ──────────────────────────────
    private function buildQuery(string $search, string $status)
    {
        $q   = FlashSale::latest();
        $now = now();

        if ($search) {
            $q->where(function ($sq) use ($search) {
                $sq->where('title_en', 'like', "%{$search}%")
                   ->orWhere('title_bn', 'like', "%{$search}%");
            });
        }

        switch ($status) {
            case 'live':
                $q->where('is_active', true)
                  ->where('start_time', '<=', $now)
                  ->where('end_time',   '>=', $now);
                break;
            case 'active':
                $q->where('is_active', true);
                break;
            case 'inactive':
                $q->where('is_active', false);
                break;
            case 'expired':
                $q->where('end_time', '<', $now);
                break;
            case 'upcoming':
                $q->where('start_time', '>', $now)->where('is_active', true);
                break;
        }

        return $q;
    }

    // ══════════════════════════════════════════════════
    //  STORE
    // ══════════════════════════════════════════════════
    public function store(Request $request)
    {
        $request->validate([
            'title_en'   => 'required|string|max:255',
            'title_bn'   => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time'   => 'required|date|after:start_time',
        ]);

        $flashSale = FlashSale::create([
            'title_en'   => $request->title_en,
            'title_bn'   => $request->title_bn,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'is_active'  => false,
        ]);

        $isBn = app()->getLocale() === 'bn';

        return redirect()->route('admin.flash-sales.show', $flashSale)
            ->with('success', $isBn
                ? 'ফ্ল্যাশ সেল তৈরি হয়েছে! এখন পণ্য যোগ করুন।'
                : 'Flash Sale created! Now add products.');
    }

    // ══════════════════════════════════════════════════
    //  SHOW
    // ══════════════════════════════════════════════════
    public function show(FlashSale $flashSale)
    {
        $flashSale->load('products.product.primaryImage');

        $products = Product::where('status', true)
            ->whereNotIn('id', $flashSale->products->pluck('product_id'))
            ->orderBy('name_en')
            ->get();

        return view('admin.flash_sales.show', compact('flashSale', 'products'));
    }

    // ══════════════════════════════════════════════════
    //  UPDATE
    // ══════════════════════════════════════════════════
    public function update(Request $request, FlashSale $flashSale)
    {
        $request->validate([
            'title_en'   => 'required|string|max:255',
            'title_bn'   => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time'   => 'required|date|after:start_time',
        ]);

        $flashSale->update([
            'title_en'   => $request->title_en,
            'title_bn'   => $request->title_bn,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
        ]);

        FlashSale::clearActiveCache();

        $isBn = app()->getLocale() === 'bn';

        return redirect()->route('admin.flash-sales.show', $flashSale)
            ->with('success', $isBn
                ? 'ফ্ল্যাশ সেল আপডেট হয়েছে!'
                : 'Flash Sale updated successfully!');
    }

    // ══════════════════════════════════════════════════
    //  ADD PRODUCT
    // ══════════════════════════════════════════════════
    public function addProduct(Request $request, FlashSale $flashSale)
    {
        $isBn = app()->getLocale() === 'bn';

        $request->validate([
            'product_id'   => 'required|exists:products,id',
            'flash_price'  => 'required|numeric|min:0.01',
            'max_quantity' => 'required|integer|min:1',
        ]);

        // Validate flash price is less than product original price
        $product = Product::findOrFail($request->product_id);
        if ($request->flash_price >= $product->price) {
            return back()->withInput()->with('error', $isBn
                ? "ফ্ল্যাশ মূল্য মূল মূল্যের (৳{$product->price}) চেয়ে কম হতে হবে।"
                : "Flash price must be less than the original price (৳{$product->price}).");
        }

        // Prevent duplicate product in same flash sale
        if ($flashSale->products()->where('product_id', $request->product_id)->exists()) {
            return back()->withInput()->with('error', $isBn
                ? 'এই পণ্যটি ইতিমধ্যে এই ফ্ল্যাশ সেলে রয়েছে।'
                : 'This product is already in this flash sale.');
        }

        // ── Stock validation ──────────────────────────────────────────
        // অন্য active/upcoming flash sale-এ এই product-এর কতটা quota reserved আছে
        $reservedElsewhere = FlashSaleProduct::where('product_id', $product->id)
            ->where('flash_sale_id', '!=', $flashSale->id)
            ->whereHas('flashSale', fn($q) => $q->where('is_active', true)
                ->where('end_time', '>=', now()))
            ->sum('max_quantity');

        // এই flash sale-এর জন্য কতটা available
        $available = $product->stock_quantity - $reservedElsewhere;

        if ($request->max_quantity > $available) {
            $msg = $reservedElsewhere > 0
                ? ($isBn
                    ? "শুধুমাত্র {$available}টি পাওয়া যাবে (অন্য ফ্ল্যাশ সেলে {$reservedElsewhere}টি reserved আছে, মোট স্টক: {$product->stock_quantity})।"
                    : "Only {$available} unit(s) available ({$reservedElsewhere} reserved by other flash sales, total stock: {$product->stock_quantity}).")
                : ($isBn
                    ? "সর্বোচ্চ পরিমাণ বর্তমান স্টক ({$product->stock_quantity}টি)-এর বেশি হতে পারবে না।"
                    : "Max quantity cannot exceed current stock ({$product->stock_quantity}).");

            return back()->withInput()->with('error', $msg);
        }

        $flashSale->products()->create([
            'product_id'    => $request->product_id,
            'flash_price'   => $request->flash_price,
            'max_quantity'  => $request->max_quantity,
            'sold_quantity' => 0,
        ]);

        FlashSale::clearActiveCache();

        return back()->with('success', $isBn
            ? 'পণ্য ফ্ল্যাশ সেলে যোগ হয়েছে!'
            : 'Product added to Flash Sale!');
    }

    // ══════════════════════════════════════════════════
    //  REMOVE PRODUCT
    // ══════════════════════════════════════════════════
    public function removeProduct(FlashSale $flashSale, FlashSaleProduct $flashSaleProduct)
    {
        $flashSaleProduct->delete();
        FlashSale::clearActiveCache();

        $isBn = app()->getLocale() === 'bn';

        return back()->with('success', $isBn
            ? 'পণ্য সরানো হয়েছে।'
            : 'Product removed from Flash Sale.');
    }

    // ══════════════════════════════════════════════════
    //  TOGGLE
    // ══════════════════════════════════════════════════
    public function toggle(Request $request, FlashSale $flashSale)
    {
        $flashSale->update(['is_active' => !$flashSale->is_active]);
        FlashSale::clearActiveCache();

        $isBn = app()->getLocale() === 'bn';
        $msg  = $flashSale->is_active
            ? ($isBn ? 'ফ্ল্যাশ সেল চালু হয়েছে!' : 'Flash Sale activated!')
            : ($isBn ? 'ফ্ল্যাশ সেল বন্ধ হয়েছে।' : 'Flash Sale deactivated.');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'is_active' => $flashSale->is_active,
                'message'   => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    // ══════════════════════════════════════════════════
    //  DESTROY
    // ══════════════════════════════════════════════════
    public function destroy(Request $request, FlashSale $flashSale)
    {
        $isBn = app()->getLocale() === 'bn';

        try {
            // Check column existence OUTSIDE transaction (Schema queries can break transactions)
            $hasFlashSaleProductIdCol = Schema::hasColumn('order_items', 'flash_sale_product_id');

            DB::transaction(function () use ($flashSale, $hasFlashSaleProductIdCol) {
                // Collect product IDs before deletion
                $productIds = $flashSale->products()->pluck('id')->toArray();

                if (!empty($productIds) && $hasFlashSaleProductIdCol) {
                    // Nullify order_items references to avoid FK constraint errors
                    DB::table('order_items')
                        ->whereIn('flash_sale_product_id', $productIds)
                        ->update(['flash_sale_product_id' => null]);
                }

                // Delete products then flash sale
                $flashSale->products()->delete();
                $flashSale->delete();
            });

            FlashSale::clearActiveCache();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $isBn ? 'ফ্ল্যাশ সেল মুছে ফেলা হয়েছে।' : 'Flash Sale deleted successfully.',
                ]);
            }

            return redirect()->route('admin.flash-sales.index')
                ->with('success', $isBn
                    ? 'ফ্ল্যাশ সেল মুছে ফেলা হয়েছে।'
                    : 'Flash Sale deleted.');

        } catch (\Exception $e) {
            Log::error('FlashSale delete failed #' . $flashSale->id . ': ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $isBn
                        ? 'মুছতে ব্যর্থ হয়েছে। আবার চেষ্টা করুন।'
                        : 'Delete failed. Please try again.',
                ], 500);
            }

            return back()->with('error', $isBn
                ? 'মুছতে ব্যর্থ হয়েছে।'
                : 'Delete failed. Please try again.');
        }
    }
}