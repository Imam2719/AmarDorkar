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
        $query      = $request->get('q', '');
        $categoryId = $request->get('category');

        $productQuery = Product::where('status', true)
            ->with(['activeDiscount', 'primaryImage', 'activeFlashSaleProduct']);

        // Text search
        if ($query) {
            // FIX: MySQL-এ FULLTEXT MATCH() use করো — full table scan এড়াতে।
            // SQLite/other driver-এ LIKE fallback।
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

        // Category filter
        if ($categoryId) {
            $productQuery->where('category_id', $categoryId);
        }

        $products   = $productQuery->latest()->paginate(16);
        $categories = Category::where('status', true)->orderBy('sort_order')->get();
        $currentCategory = $categoryId ? Category::find($categoryId) : null;

        return view('search.index', compact('products', 'query', 'categories', 'categoryId', 'currentCategory'));
    }
}
