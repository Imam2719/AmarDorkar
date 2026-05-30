<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\DiscountProduct;
use App\Models\Order;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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

        // Infinite scroll: AJAX request হলে JSON return করো
        if ($request->ajax() || $request->wantsJson()) {
            $page     = (int) $request->input('page', 1);
            $perPage  = 20;
            $total    = $query->count();
            $products = $query->skip(($page - 1) * $perPage)->take($perPage)->get();
            return response()->json([
                'products' => $products->map(fn($p) => [
                    'id'           => $p->id,
                    'name'         => $p->name,
                    'name_en'      => $p->name_en,
                    'description'  => \Str::limit($p->description, 50),
                    'product_type' => $p->product_type,
                    'product_size' => $p->product_size,
                    'price'        => number_format($p->price, 2),
                    'status'       => $p->status,
                    'image_url'    => $p->image_url,
                    'discount'     => $p->activeDiscount ? [
                        'pct'   => number_format($p->activeDiscount->discount_percentage, 1),
                        'price' => number_format($p->activeDiscount->discount_price),
                    ] : null,
                    'view_url'   => route('admin.products.show', $p),
                    'edit_url'   => route('admin.products.edit', $p),
                    'delete_url' => route('admin.products.destroy', $p),
                ]),
                'total'    => $total,
                'has_more' => ($page * $perPage) < $total,
                'next_page'=> $page + 1,
            ]);
        }

        // Normal page load: প্রথম 20টা দাও (infinite scroll JS বাকিটা করবে)
        $total       = $query->count();
        $products    = $query->take(20)->get();
        $hasMore     = $total > 20;

        $productTypes = Product::select('product_type')->distinct()->whereNotNull('product_type')->pluck('product_type');
        $categories   = Category::where('status', true)->get();
        $success = session()->pull('success');
        $error   = session()->pull('error');
        return view('admin.products.index', compact(
            'products', 'productTypes', 'categories',
            'search', 'typeFilter', 'success', 'error',
            'total', 'hasMore'
        ));
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

        if ($request->hasFile('product_images')) {
            $primaryIndex = (int) $request->input('primary_image_index', 0);
            foreach ($request->file('product_images') as $index => $file) {
                try {
                    $product->images()->create([
                        'image_data' => base64_encode(file_get_contents($file->getRealPath())),
                        'mime_type'  => $file->getMimeType(),
                        'sort_order' => $index,
                        'is_primary' => ($index === $primaryIndex),
                    ]);
                } catch (\Exception $e) {
                    logger()->error('Product image DB insert failed: ' . $e->getMessage());
                }
            }
            if ($product->images()->where('is_primary', true)->count() === 0) {
                $first = $product->images()->orderBy('sort_order')->first();
                if ($first) $first->update(['is_primary' => true]);
            }
        }

        // Discount section removed from create form — no discount logic here
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

        if ($request->filled('delete_images')) {
            foreach ($request->input('delete_images') as $imgId) {
                $img = $product->images()->find($imgId);
                if ($img) $img->delete();
            }
        }

        if ($request->filled('primary_image_id')) {
            $product->images()->update(['is_primary' => false]);
            $product->images()->where('id', $request->primary_image_id)->update(['is_primary' => true]);
        }

        $existingCount  = $product->images()->count();
        $primaryNewIdx  = (int) $request->input('primary_image_index', -1);
        $newImagesAdded = 0;

        if ($request->hasFile('product_images')) {
            foreach ($request->file('product_images') as $index => $file) {
                $isPrimary = ($primaryNewIdx >= 0 && $index === $primaryNewIdx);
                if ($isPrimary) $product->images()->update(['is_primary' => false]);
                try {
                    $product->images()->create([
                        'image_data' => base64_encode(file_get_contents($file->getRealPath())),
                        'mime_type'  => $file->getMimeType(),
                        'sort_order' => $existingCount + $newImagesAdded,
                        'is_primary' => $isPrimary,
                    ]);
                    $newImagesAdded++;
                } catch (\Exception $e) {
                    logger()->error('Product image upload failed: ' . $e->getMessage());
                }
            }
        }

        $b64Images  = $request->input('new_image_b64', []);
        $mimeImages = $request->input('new_image_mime', []);
        if (!empty($b64Images) && is_array($b64Images)) {
            foreach ($b64Images as $index => $b64) {
                if (empty($b64)) continue;
                $isPrimary = ($primaryNewIdx >= 0 && $index === $primaryNewIdx);
                if ($isPrimary) $product->images()->update(['is_primary' => false]);
                try {
                    $product->images()->create([
                        'image_data' => $b64,
                        'mime_type'  => $mimeImages[$index] ?? 'image/jpeg',
                        'sort_order' => $existingCount + $newImagesAdded,
                        'is_primary' => $isPrimary,
                    ]);
                    $newImagesAdded++;
                } catch (\Exception $e) {
                    logger()->error('Product image (b64) DB insert failed: ' . $e->getMessage());
                }
            }
        }

        if ($product->images()->where('is_primary', true)->count() === 0) {
            $first = $product->images()->orderBy('sort_order')->first();
            if ($first) $first->update(['is_primary' => true]);
        }

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
        $product->images()->delete();
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', __('messages.product_deleted'));
    }

    // ── NOTIFY CUSTOMERS ─────────────────────────────────────────────
    public function notifyForm(Product $product)
    {
        $product->load('primaryImage');
        $totalCustomers = User::count();
        $topCustomers   = User::withCount('orders')
                              ->having('orders_count', '>=', 2)
                              ->count();
        return view('admin.products.notify', compact('product', 'totalCustomers', 'topCustomers'));
    }

    public function notifySend(Request $request, Product $product)
    {
        $request->validate([
            'audience'  => 'required|in:all,top',
            'channels'  => 'required|array|min:1',
            'channels.*'=> 'in:email,whatsapp',
            'subject'   => 'required_if:channels.*,email|nullable|string|max:255',
            'message'   => 'required|string|max:1000',
        ]);

        $product->load('primaryImage');

        // Customer audience নির্ধারণ
        if ($request->audience === 'top') {
            $customers = User::withCount('orders')
                             ->having('orders_count', '>=', 2)
                             ->get();
        } else {
            $customers = User::all();
        }

        $emailSent     = 0;
        $whatsappSent  = 0;
        $whatsappFailed= 0;

        foreach ($customers as $customer) {
            // ── EMAIL ───────────────────────────────────────────────
            if (in_array('email', $request->channels) && $customer->email) {
                try {
                    $prodData = $product;
                    $msgText  = $request->message;
                    $subj     = $request->subject ?: 'AmardoKar — New Product Alert';
                    Mail::send([], [], function ($mail) use ($customer, $prodData, $msgText, $subj) {
                        $productUrl = url('/product/' . $prodData->id);
                        $imageUrl   = $prodData->image_url ?? '';
                        $html = "
                        <div style='font-family:DM Sans,sans-serif;max-width:580px;margin:0 auto;background:#0f172a;border-radius:16px;overflow:hidden;'>
                          <div style='background:linear-gradient(135deg,#2563eb,#7c3aed);padding:32px 24px;text-align:center;'>
                            <h1 style='color:#fff;font-size:22px;margin:0;'>🛍️ AmardoKar</h1>
                            <p style='color:rgba(255,255,255,.8);margin:6px 0 0;font-size:13px;'>New Product Alert</p>
                          </div>
                          " . ($imageUrl ? "<div style='text-align:center;padding:24px 24px 0;'><img src='{$imageUrl}' style='max-width:220px;border-radius:12px;border:2px solid #334155;' alt='{$prodData->name}'></div>" : "") . "
                          <div style='padding:24px;'>
                            <h2 style='color:#f1f5f9;font-size:18px;margin:0 0 8px;'>{$prodData->name}</h2>
                            <p style='color:#2563eb;font-size:20px;font-weight:700;margin:0 0 16px;'>৳" . number_format($prodData->price, 2) . "</p>
                            <p style='color:#94a3b8;font-size:14px;line-height:1.6;margin:0 0 24px;'>{$msgText}</p>
                            <div style='text-align:center;'>
                              <a href='{$productUrl}' style='display:inline-block;background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;text-decoration:none;padding:13px 32px;border-radius:8px;font-weight:600;font-size:14px;'>পণ্যটি দেখুন →</a>
                            </div>
                          </div>
                          <div style='padding:16px 24px;border-top:1px solid #334155;text-align:center;'>
                            <p style='color:#475569;font-size:11px;margin:0;'>AmardoKar · Unsubscribe anytime</p>
                          </div>
                        </div>";
                        $mail->to($customer->email, $customer->name)
                             ->subject($subj)
                             ->html($html);
                    });
                    $emailSent++;
                } catch (\Exception $e) {
                    logger()->error('Product notify email failed: ' . $e->getMessage());
                }
            }

            // ── WHATSAPP ────────────────────────────────────────────
            if (in_array('whatsapp', $request->channels) && $customer->mobile) {
                try {
                    $this->sendWhatsApp($customer->mobile, $product, $request->message);
                    $whatsappSent++;
                } catch (\Exception $e) {
                    logger()->error('WhatsApp send failed for ' . $customer->mobile . ': ' . $e->getMessage());
                    $whatsappFailed++;
                }
            }
        }

        $summary = [];
        if ($emailSent)     $summary[] = "{$emailSent}টি email পাঠানো হয়েছে";
        if ($whatsappSent)  $summary[] = "{$whatsappSent}টি WhatsApp পাঠানো হয়েছে";
        if ($whatsappFailed)$summary[] = "{$whatsappFailed}টি WhatsApp ব্যর্থ হয়েছে";

        return redirect()->route('admin.products.index')
                         ->with('success', implode(' | ', $summary) ?: 'Notification পাঠানো হয়েছে');
    }

    // ── WhatsApp Helper — Meta Cloud API ────────────────────────────
    private function sendWhatsApp(string $mobile, Product $product, string $message): void
    {
        $text = "🛍️ *AmardoKar — নতুন পণ্য*\n\n"
              . "*{$product->name}*\n"
              . "💰 মূল্য: ৳" . number_format($product->price, 2) . "\n\n"
              . $message . "\n\n"
              . "🔗 " . url('/product/' . $product->id);

        $whatsapp = app(WhatsAppService::class);
        $result   = $whatsapp->sendText($mobile, $text);

        if (!$result['success']) {
            throw new \RuntimeException('WhatsApp send failed: ' . ($result['error'] ?? 'unknown'));
        }
    }
}