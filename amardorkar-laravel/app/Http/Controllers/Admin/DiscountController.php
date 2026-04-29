<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscountProduct;
use App\Models\Product;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    // ── LIST ─────────────────────────────────────────────────────────
    public function index()
    {
        $discounts = DiscountProduct::with('product')
            ->latest()
            ->paginate(10);

        $products = Product::where('status', true)
            ->orderBy('name_en')
            ->get();

        $success = session()->pull('success');
        $error   = session()->pull('error');

        return view('admin.discounts.index', compact('discounts', 'products', 'success', 'error'));
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
            // max_quantity removed — column does not exist in discount_products table
        ]);

        // FIX: Delete any existing discount for this product first (unique constraint on product_id)
        DiscountProduct::where('product_id', $request->product_id)->delete();

        DiscountProduct::create([
            'product_id'      => $request->product_id,
            'original_price'  => $request->original_price,
            'discount_price'  => $request->discount_price,
            'start_date'      => $request->start_date,
            'end_date'        => $request->end_date,
            'is_active'       => true,
        ]);

        // Also sync discount_price on product
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
            // max_quantity removed — column does not exist in discount_products table
        ]);

        $discount->update([
            'original_price' => $request->original_price,
            'discount_price' => $request->discount_price,
            'start_date'     => $request->start_date,
            'end_date'       => $request->end_date,
            'is_active'      => $request->boolean('is_active', $discount->is_active),
        ]);

        // Sync discount_price on product
        $discount->product->update(['discount_price' => $request->discount_price]);

        return redirect()->route('admin.discounts.index')
                         ->with('success', __('messages.discount_updated'));
    }

    // ── TOGGLE ACTIVE ─────────────────────────────────────────────────
    public function toggle(DiscountProduct $discount)
    {
        $discount->update(['is_active' => !$discount->is_active]);

        $msg = $discount->is_active
            ? 'ছাড় সক্রিয় করা হয়েছে।'
            : 'ছাড় নিষ্ক্রিয় করা হয়েছে।';

        return back()->with('success', $msg);
    }

    // ── DELETE ────────────────────────────────────────────────────────
    public function destroy(DiscountProduct $discount)
    {
        // Clear discount_price from product
        $discount->product?->update(['discount_price' => null]);
        $discount->delete();

        return redirect()->route('admin.discounts.index')
                         ->with('success', __('messages.discount_deleted'));
    }
}