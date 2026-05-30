<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscountProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DiscountController extends Controller
{
    // ── LIST ─────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $query = DiscountProduct::with('product')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', fn($p) =>
                    $p->where('name_en', 'like', "%{$search}%")
                      ->orWhere('name_bn', 'like', "%{$search}%")
                );
            });
        }

        if ($request->ajax() || $request->wantsJson()) {
            $page      = (int) $request->input('page', 1);
            $perPage   = 20;
            $total     = $query->count();
            $discounts = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

            return response()->json([
                'discounts' => $discounts->map(fn($d) => [
                    'id'                  => $d->id,
                    'product_name'        => $d->product?->name_en ?? 'N/A',
                    'product_name_bn'     => $d->product?->name_bn ?? $d->product?->name_en ?? 'N/A',
                    'product_type'        => $d->product?->product_type ?? '',
                    'image_url'           => $d->product?->image_url ?? null,
                    'original_price'      => $d->original_price,
                    'discount_price'      => $d->discount_price,
                    'discount_percentage' => number_format($d->discount_percentage, 1),
                    'is_active'           => $d->is_active,
                    'start_date'          => $d->start_date->format('Y-m-d\TH:i'),
                    'end_date_formatted'  => $d->end_date->format('M d, Y'),
                    'end_date_input'      => $d->end_date->format('Y-m-d\TH:i'),
                    'toggle_url'          => route('admin.discounts.toggle', $d),
                    'update_url'          => route('admin.discounts.update', $d),
                    'delete_url'          => route('admin.discounts.destroy', $d),
                ]),
                'total'     => $total,
                'has_more'  => ($page * $perPage) < $total,
                'next_page' => $page + 1,
            ]);
        }

        $total     = $query->count();
        $discounts = $query->take(20)->get();
        $hasMore   = $total > 20;

        $products = Product::where('status', true)
            ->orderBy('name_en')
            ->get();

        $success = session()->pull('success');
        $error   = session()->pull('error');

        return view('admin.discounts.index', compact(
            'discounts', 'products', 'success', 'error',
            'search', 'total', 'hasMore'
        ));
    }

    // ── STORE ─────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'product_id'     => 'required|exists:products,id',
            'original_price' => 'required|numeric|min:0.01',
            'discount_price' => 'required|numeric|min:0.01|lt:original_price',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after:start_date',
        ]);

        // Delete any existing discount for this product
        $existing = DiscountProduct::where('product_id', $request->product_id)->first();
        if ($existing) {
            // ── BUG 3 FIX: পুরনো discount সরানোর সময় product sync ──
            $existing->product?->update(['discount_price' => null]);
            $existing->delete();
        }

        DiscountProduct::create([
            'product_id'     => $request->product_id,
            'original_price' => $request->original_price,
            'discount_price' => $request->discount_price,
            'start_date'     => $request->start_date,
            'end_date'       => $request->end_date,
            'is_active'      => true,
        ]);

        // Sync discount_price on product (active হচ্ছে তাই set করো)
        Product::find($request->product_id)
               ->update(['discount_price' => $request->discount_price]);

        return redirect()->route('admin.discounts.index')
                         ->with('success', __('messages.discount_added'));
    }

    // ── SHOW ──────────────────────────────────────────────────────────
    public function show(DiscountProduct $discount)
    {
        $discount->load('product');
        return view('admin.discounts.show', compact('discount'));
    }

    // ── UPDATE ────────────────────────────────────────────────────────
    public function update(Request $request, DiscountProduct $discount)
    {
        $request->validate([
            'original_price' => 'required|numeric|min:0.01',
            'discount_price' => 'required|numeric|min:0.01|lt:original_price',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after:start_date',
        ]);

        $isActive = $request->boolean('is_active', $discount->is_active);

        $discount->update([
            'original_price' => $request->original_price,
            'discount_price' => $request->discount_price,
            'start_date'     => $request->start_date,
            'end_date'       => $request->end_date,
            'is_active'      => $isActive,
        ]);

        // ── BUG 3 FIX: is_active অনুযায়ী product sync ──────────────
        // active হলে discount_price set, inactive হলে null
        $discount->product?->update([
            'discount_price' => $isActive ? $request->discount_price : null,
        ]);

        return redirect()->route('admin.discounts.index')
                         ->with('success', __('messages.discount_updated'));
    }

    // ── TOGGLE ACTIVE ─────────────────────────────────────────────────
    public function toggle(DiscountProduct $discount)
    {
        $newActiveState = !$discount->is_active;
        $discount->update(['is_active' => $newActiveState]);

        // ── BUG 3 FIX: toggle করলে products.discount_price sync করো ──
        // আগে শুধু is_active বদলাতো, products.discount_price আগের value-ই
        // থেকে যেতো। ফলে discount inactive থাকলেও discounted price-এ
        // product বিক্রি হতো। এখন:
        // → active করলে: discount_price set করো
        // → inactive করলে: discount_price null করো
        if ($discount->product) {
            $discount->product->update([
                'discount_price' => $newActiveState ? $discount->discount_price : null,
            ]);
        }

        $isBn = app()->getLocale() === 'bn';
        $msg  = $newActiveState
            ? ($isBn ? 'ছাড় সক্রিয় করা হয়েছে।' : 'Discount activated.')
            : ($isBn ? 'ছাড় নিষ্ক্রিয় করা হয়েছে।' : 'Discount deactivated.');

        return back()->with('success', $msg);
    }

    // ── DELETE ────────────────────────────────────────────────────────
    public function destroy(DiscountProduct $discount)
    {
        // ── BUG 3 FIX: delete করলেও discount_price null করো (আগে থেকেই ছিল) ──
        $discount->product?->update(['discount_price' => null]);
        $discount->delete();

        return redirect()->route('admin.discounts.index')
                         ->with('success', __('messages.discount_deleted'));
    }

    // ── BUG 3 FIX: Stale discount_price cleanup ───────────────────────
    // যেসব product-এর discount_price set আছে কিন্তু active discount নেই
    // সেগুলো automatically fix করো।
    // Usage: Admin panel-এ একটা button দিয়ে বা console command দিয়ে call করো।
    // Route: POST /admin/discounts/cleanup-stale
    public function cleanupStale()
    {
        // Active discount নেই কিন্তু discount_price set আছে এমন products খোঁজো
        $staleProducts = Product::whereNotNull('discount_price')
            ->whereDoesntHave('activeDiscount')
            ->get();

        $count = $staleProducts->count();

        foreach ($staleProducts as $product) {
            $product->update(['discount_price' => null]);
        }

        $isBn = app()->getLocale() === 'bn';
        $msg  = $isBn
            ? "{$count}টি পণ্যের পুরনো discount_price পরিষ্কার করা হয়েছে।"
            : "{$count} product(s) had stale discount_price cleared.";

        return back()->with('success', $msg);
    }
}