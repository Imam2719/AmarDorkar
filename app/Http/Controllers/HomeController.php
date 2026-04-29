<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Banner;
use App\Models\DiscountProduct;
use App\Models\FlashSale;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        // FIX N+1: 'activeFlashSaleProduct' eager load করা হয়েছে,
        // যাতে getFinalPriceAttribute() প্রতিটি product-এ আলাদা query না চালায়।
        $products = Product::where('status', true)
            ->with(['activeDiscount', 'primaryImage', 'activeFlashSaleProduct'])
            ->withAvg('reviews as avg_rating', 'rating')
            ->latest()
            ->get();

        $newArrivals = Product::where('status', true)
            ->where('is_new_arrival', true)
            ->with(['activeDiscount', 'primaryImage', 'activeFlashSaleProduct'])
            ->withAvg('reviews as avg_rating', 'rating')
            ->latest()
            ->take(12)
            ->get();

        $categories = Cache::remember('active_categories', 300, function () {
            return Category::where('status', true)
                ->orderBy('sort_order')
                ->get();
        });

        $activeFlashSale = Cache::remember('active_flash_sale', 60, function () {
            return FlashSale::getActive();
        });

        $banners = Cache::remember('active_banners', 300, function () {
            return Banner::getActive();
        });

        return view('home.index', compact('products', 'newArrivals', 'activeFlashSale', 'categories', 'banners'));
    }

    public function newArrivals()
    {
        $products = Product::where('status', true)
            ->where('is_new_arrival', true)
            ->with(['activeDiscount', 'primaryImage'])
            ->latest()
            ->paginate(24);

        return view('home.new-arrivals', compact('products'));
    }

    public function offers()
    {
        $offerProducts = DiscountProduct::where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->with(['product.primaryImage', 'product.activeDiscount'])
            ->orderByDesc('discount_percentage')
            ->paginate(24);

        return view('home.offers', compact('offerProducts'));
    }
}
