<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\DiscountProduct;
use App\Models\Order;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search      = $request->input('search', '');
        $typeFilter  = $request->input('type', '');
        $query = Product::with('activeDiscount', 'category', 'primaryImage')->latest();
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('name_bn', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description_en', 'like', "%{$search}%");
            });
        }
        if ($typeFilter) { $query->where('product_type', $typeFilter); }
        $products     = $query->paginate(20)->withQueryString();
        $productTypes = Product::select('product_type')->distinct()->whereNotNull('product_type')->pluck('product_type');
        $categories   = Category::where('status', true)->get();
        $success = session()->pull('success');
        $error   = session()->pull('error');
        return view('admin.products.index', compact('products', 'productTypes', 'categories', 'search', 'typeFilter', 'success', 'error'));
    }

    public function create()
    {
        $categories = Category::where('status', true)->get();
        return view('admin.products.create', compact('categories'));
    }

    // ── STORE ─────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name_en'          => 'required|string|max:255',
            'name_bn'          => 'required|string|max:255',
            'price'            => 'required|numeric|min:0',
            'product_type'     => 'nullable|string|max:100',
            'product_size'     => 'nullable|string|max:100',
            'product_images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:51200',
        ]);

        $product = Product::create([
            'category_id'    => $request->category_id ?: null,
            'name_en'        => $request->name_en,
            'name_bn'        => $request->name_bn,
            'description_en' => $request->description_en,
            'description_bn' => $request->description_bn,
            'sku'            => $request->sku ?: null,
            'price'          => $request->price,
            'stock_quantity' => $request->stock_quantity ?? 0,
            'product_type'   => $request->product_type,
            'product_size'   => $request->product_size,
            'is_new_arrival' => $request->boolean('is_new_arrival'),
            'status'         => $request->boolean('status', true),
        ]);

        // ✅ Images সরাসরি Database এ store
        if ($request->hasFile('product_images')) {
            $primaryIndex = (int) $request->input('primary_image_index', 0);

            foreach ($request->file('product_images') as $index => $file) {
                try {
                    $product->images()->create([
                        'image_data' => base64_encode(file_get_contents($file->getRealPath())), // ✅ base64 encode
                        'mime_type'  => $file->getMimeType(),
                        'sort_order' => $index,
                        'is_primary' => ($index === $primaryIndex),
                    ]);
                } catch (\Exception $e) {
                    // Continue with other images if one fails
                    logger()->error('Product image DB insert failed: ' . $e->getMessage());
                }
            }

            // Fallback: কোনো primary না থাকলে প্রথমটাকে primary করুন
            if ($product->images()->where('is_primary', true)->count() === 0) {
                $first = $product->images()->orderBy('sort_order')->first();
                if ($first) {
                    $first->update(['is_primary' => true]);
                }
            }
        }

        if ($request->filled('discount_price') && $request->discount_price < $request->price) {
            DiscountProduct::create([
                'product_id'     => $product->id,
                'original_price' => $request->price,
                'discount_price' => $request->discount_price,
                'start_date'     => $request->discount_start ?? now(),
                'end_date'       => $request->discount_end   ?? now()->addDays(30),
                'is_active'      => true,
            ]);
            $product->update(['discount_price' => $request->discount_price]);
        }

        return redirect()->route('admin.products.index')->with('success', __('messages.product_added'));
    }

    public function show(Product $product)
    {
        $product->load('category', 'discounts', 'activeDiscount', 'images', 'primaryImage');
        $activeOrders = Order::whereHas('items', fn($q) => $q->where('product_id', $product->id))
                             ->whereNotIn('status', ['cancelled','delivered'])->count();
        return view('admin.products.view', compact('product', 'activeOrders'));
    }

    public function edit(Product $product)
    {
        $product->load('activeDiscount', 'category', 'images');
        $categories = Category::where('status', true)->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    // ── UPDATE ────────────────────────────────────────────────────────
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name_en'          => 'required|string|max:255',
            'name_bn'          => 'required|string|max:255',
            'price'            => 'required|numeric|min:0',
            'product_images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:51200',
        ]);

        $product->update([
            'category_id'    => $request->category_id ?: null,
            'name_en'        => $request->name_en,
            'name_bn'        => $request->name_bn,
            'description_en' => $request->description_en,
            'description_bn' => $request->description_bn,
            'sku'            => $request->sku ?: null,
            'price'          => $request->price,
            'stock_quantity' => $request->stock_quantity ?? 0,
            'product_type'   => $request->product_type,
            'product_size'   => $request->product_size,
            'is_new_arrival' => $request->boolean('is_new_arrival'),
            'status'         => $request->boolean('status', true),
        ]);

        // চিহ্নিত image delete করুন
        if ($request->filled('delete_images')) {
            foreach ($request->input('delete_images') as $imgId) {
                $img = $product->images()->find($imgId);
                if ($img) {
                    $img->delete();
                }
            }
        }

        // Primary image update করুন existing এর মধ্যে
        if ($request->filled('primary_image_id')) {
            $product->images()->update(['is_primary' => false]);
            $product->images()->where('id', $request->primary_image_id)->update(['is_primary' => true]);
        }

        // ✅ নতুন images Database এ store
        if ($request->hasFile('product_images')) {
            $existingCount = $product->images()->count();
            $primaryNewIdx = (int) $request->input('primary_image_index', -1);

            foreach ($request->file('product_images') as $index => $file) {
                $isPrimary = ($primaryNewIdx >= 0 && $index === $primaryNewIdx);

                if ($isPrimary) {
                    $product->images()->update(['is_primary' => false]);
                }

                $product->images()->create([
                    'image_data' => base64_encode(file_get_contents($file->getRealPath())), // ✅ base64 encode
                    'mime_type'  => $file->getMimeType(),
                    'sort_order' => $existingCount + $index,
                    'is_primary' => $isPrimary,
                ]);
            }
        }

        // কোনো primary না থাকলে প্রথমটাকে primary করুন
        if ($product->images()->where('is_primary', true)->count() === 0) {
            $first = $product->images()->orderBy('sort_order')->first();
            if ($first) {
                $first->update(['is_primary' => true]);
            }
        }

        // Discount logic
        if ($request->filled('discount_price') && $request->discount_price < $request->price) {
            $product->discounts()->update(['is_active' => false]);
            $discount = $product->discounts()->latest()->first();
            $data = [
                'original_price' => $request->price,
                'discount_price' => $request->discount_price,
                'start_date'     => $request->discount_start ?? now(),
                'end_date'       => $request->discount_end   ?? now()->addDays(30),
                'is_active'      => true,
            ];
            if ($discount) {
                $discount->update($data);
            } else {
                $product->discounts()->create(array_merge($data, ['product_id' => $product->id]));
            }
            $product->update(['discount_price' => $request->discount_price]);
        } else {
            $product->discounts()->update(['is_active' => false]);
            $product->update(['discount_price' => null]);
        }

        return redirect()->route('admin.products.index')->with('success', __('messages.product_updated'));
    }

    public function destroy(Product $product)
    {
        $activeOrders = Order::whereHas('items', fn($q) => $q->where('product_id', $product->id))
                             ->whereNotIn('status', ['cancelled','delivered'])->count();
        if ($activeOrders > 0) {
            return back()->with('error', "এই পণ্যের {$activeOrders}টি সক্রিয় অর্ডার রয়েছে। প্রথমে অর্ডারগুলো সম্পন্ন করুন।");
        }
        $product->discounts()->delete();
        // DB থেকে images delete (no file system cleanup needed)
        $product->images()->delete();
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', __('messages.product_deleted'));
    }
}