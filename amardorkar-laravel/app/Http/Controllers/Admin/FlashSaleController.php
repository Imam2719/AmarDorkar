<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FlashSaleController extends Controller
{
    public function index()
    {
        $flashSales = FlashSale::with(['products.product.primaryImage'])->latest()->paginate(10);
        $products   = Product::where('status', true)->orderBy('name_en')->get();
        return view('admin.flash_sales.index', compact('flashSales', 'products'));
    }

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

        return redirect()->route('admin.flash-sales.show', $flashSale)
                         ->with('success', 'Flash Sale created! Now add products.');
    }

    public function show(FlashSale $flashSale)
    {
        $flashSale->load('products.product.primaryImage');
        $products = Product::where('status', true)
            ->whereNotIn('id', $flashSale->products->pluck('product_id'))
            ->orderBy('name_en')->get();
        return view('admin.flash_sales.show', compact('flashSale', 'products'));
    }

    /**
     * Flash Sale-এর title ও time আপডেট করো।
     * Previously missing — resource route existed but no update() method.
     */
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

        Cache::forget('active_flash_sale');

        return redirect()->route('admin.flash-sales.show', $flashSale)
                         ->with('success', 'Flash Sale updated successfully!');
    }

    public function addProduct(Request $request, FlashSale $flashSale)
    {
        $request->validate([
            'product_id'   => 'required|exists:products,id',
            'flash_price'  => 'required|numeric|min:0.01',
            'max_quantity' => 'required|integer|min:1',
        ]);

        $flashSale->products()->create([
            'product_id'   => $request->product_id,
            'flash_price'  => $request->flash_price,
            'max_quantity' => $request->max_quantity,
            'sold_quantity'=> 0,
        ]);

        return back()->with('success', 'Product added to Flash Sale!');
    }

    public function removeProduct(FlashSale $flashSale, FlashSaleProduct $flashSaleProduct)
    {
        $flashSaleProduct->delete();
        return back()->with('success', 'Product removed from Flash Sale.');
    }

    public function toggle(FlashSale $flashSale)
    {
        if (!$flashSale->is_active) {
            FlashSale::where('id', '!=', $flashSale->id)->update(['is_active' => false]);
        }
        $flashSale->update(['is_active' => !$flashSale->is_active]);
        Cache::forget('active_flash_sale');
        $msg = $flashSale->is_active ? 'Flash Sale activated!' : 'Flash Sale deactivated.';
        return back()->with('success', $msg);
    }

    public function destroy(FlashSale $flashSale)
    {
        $flashSale->products()->delete();
        $flashSale->delete();
        Cache::forget('active_flash_sale');
        return redirect()->route('admin.flash-sales.index')->with('success', 'Flash Sale deleted.');
    }
}
