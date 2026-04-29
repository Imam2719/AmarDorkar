<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SalesHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // ── Allowed status flow (same as PHP original) ────────────────────
    private array $validTransitions = [
        'pending'    => ['confirmed', 'cancelled'],
        'confirmed'  => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped'    => ['delivered', 'cancelled'],
        'delivered'  => [],
        'cancelled'  => [],
    ];

    // ── LIST ─────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $search       = $request->input('search', '');
        $statusFilter = $request->input('status', '');

        $query = Order::with(['items.product.images', 'user'])->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name',  'like', "%{$search}%")
                  ->orWhere('customer_email','like', "%{$search}%")
                  ->orWhere('customer_mobile','like', "%{$search}%")
                  ->orWhere('order_number',  'like', "%{$search}%");
            });
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $orders = $query->paginate(20)->withQueryString();

        $stats = [
            'total_orders'    => Order::count(),
            'total_revenue'   => Order::whereNotIn('status', ['cancelled'])->sum('total_amount'),
            'pending_orders'  => Order::where('status', 'pending')->count(),
            'delivered_orders'=> Order::where('status', 'delivered')->count(),
            'today_orders'    => Order::whereDate('created_at', today())->count(),
        ];

        $success = session()->pull('success');
        $error   = session()->pull('error');

        return view('admin.orders.index', compact(
            'orders', 'stats', 'search', 'statusFilter', 'success', 'error'
        ));
    }

    // ── SHOW (AJAX + full detail page) ────────────────────────────────
    public function show(Request $request, Order $order)
    {
        $order->load('items.product.images', 'user');

        if ($request->ajax() || $request->wantsJson()) {
            $orderData = $order->toArray();
            // Map items with product_image (accessor not included in toArray)
            $orderData['items'] = $order->items->map(function ($item) {
                $arr = $item->toArray();
                $arr['product_image']  = $item->product ? ($item->product->image_url ?? null) : null;
                $arr['selected_size']  = $item->selected_size ?? null;
                $arr['subtotal']       = $item->price * $item->quantity;
                return $arr;
            })->values()->toArray();

            return response()->json([
                'success' => true,
                'order'   => $orderData,
            ]);
        }

        return view('admin.orders.show', compact('order'));
    }

    // ── UPDATE STATUS ─────────────────────────────────────────────────
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status'         => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        $newOrderStatus   = $request->status;
        $newPaymentStatus = $request->payment_status;
        $oldOrderStatus   = $order->status;

        // Validate transition
        $error = $this->validateTransition($oldOrderStatus, $newOrderStatus, $newPaymentStatus);
        if ($error) {
            return back()->with('error', $error);
        }

        DB::transaction(function () use ($order, $newOrderStatus, $newPaymentStatus, $request) {
            $order->update([
                'status'         => $newOrderStatus,
                'payment_status' => $newPaymentStatus,
            ]);

            // When delivered → move to sales_history
            if ($newOrderStatus === 'delivered' && $order->getOriginal('status') !== 'delivered') {
                $this->moveToSalesHistory($order, $request->user());
            }

            // FIX 2: When cancelled → restore flash sale sold_quantity and stock
            if ($newOrderStatus === 'cancelled' && $order->getOriginal('status') !== 'cancelled') {
                $this->restoreOnCancel($order);
            }
        });

        $redirectTo = $request->input('redirect_to') === 'view'
            ? route('admin.orders.show', $order)
            : route('admin.orders.index');

        return redirect($redirectTo)->with('success', __('messages.order_status_updated'));
    }

    // ── SELL (mark as sold → move to sales history) ───────────────────
    // This mirrors the PHP "sell_order" POST action
    public function sell(Request $request, Order $order)
    {
        if (SalesHistory::where('order_id', $order->id)->exists()) {
            return back()->with('error', 'এই অর্ডার ইতিমধ্যে বিক্রয় ইতিহাসে রয়েছে।');
        }

        DB::transaction(function () use ($order, $request) {
            $this->moveToSalesHistory($order, $request->user());

            $order->update([
                'status'         => 'delivered',
                'payment_status' => 'paid',
            ]);
        });

        return redirect()->route('admin.orders.index')
                         ->with('success', 'অর্ডারটি সফলভাবে বিক্রয় ইতিহাসে স্থানান্তরিত হয়েছে।');
    }

    // ── DELETE ────────────────────────────────────────────────────────
    public function destroy(Order $order)
    {
        $order->items()->delete();
        $order->delete();

        return redirect()->route('admin.orders.index')
                         ->with('success', __('messages.order_deleted'));
    }

    // ── BULK STATUS UPDATE ────────────────────────────────────────────
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'order_ids'      => 'required|array',
            'order_ids.*'    => 'integer|exists:orders,id',
            'status'         => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        $updated = 0;
        $failed  = 0;

        foreach ($request->order_ids as $orderId) {
            $order = Order::find($orderId);
            if (!$order) { $failed++; continue; }

            $err = $this->validateTransition($order->status, $request->status, $request->payment_status);
            if ($err) { $failed++; continue; }

            DB::transaction(function () use ($order, $request) {
                $order->update([
                    'status'         => $request->status,
                    'payment_status' => $request->payment_status,
                ]);
                if ($request->status === 'delivered' && $order->getOriginal('status') !== 'delivered') {
                    $this->moveToSalesHistory($order, $request->user());
                }
                // FIX 2: restore on cancel
                if ($request->status === 'cancelled' && $order->getOriginal('status') !== 'cancelled') {
                    $this->restoreOnCancel($order);
                }
            });

            $updated++;
        }

        $msg = "{$updated} অর্ডার আপডেট হয়েছে" . ($failed ? ", {$failed}টি ব্যর্থ" : '');
        return back()->with($failed ? 'warning' : 'success', $msg);
    }

    // ── PRIVATE: transition validator ─────────────────────────────────
    private function validateTransition(string $old, string $new, string $paymentStatus): ?string
    {
        if ($old !== $new) {
            $allowed = $this->validTransitions[$old] ?? [];
            if (!in_array($new, $allowed)) {
                return "অবৈধ স্ট্যাটাস পরিবর্তন: {$old} থেকে {$new}";
            }
        }

        if ($new === 'delivered' && $paymentStatus !== 'paid') {
            return 'ডেলিভার্ড অর্ডারের পেমেন্ট স্ট্যাটাস অবশ্যই "পরিশোধিত" হতে হবে।';
        }

        if ($new === 'cancelled' && $paymentStatus === 'paid') {
            return 'বাতিল অর্ডারের পেমেন্ট স্ট্যাটাস "পরিশোধিত" হতে পারে না।';
        }

        return null;
    }

    // ── PRIVATE: restore stock & flash sale quota on cancellation ─────
    private function restoreOnCancel(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            // Restore product stock
            \App\Models\Product::where('id', $item->product_id)
                ->increment('stock_quantity', $item->quantity);

            // FIX: Use stored flash_sale_product_id so we can restore quota even after
            // the flash sale has expired (the old code only checked currently active sales)
            if ($item->flash_sale_product_id) {
                $flashSaleProduct = \App\Models\FlashSaleProduct::find($item->flash_sale_product_id);
            } else {
                // Fallback for older orders without flash_sale_product_id
                $flashSaleProduct = \App\Models\FlashSaleProduct::where('product_id', $item->product_id)
                    ->whereHas('flashSale', fn($q) => $q->where('is_active', true))
                    ->first();
            }

            if ($flashSaleProduct && $flashSaleProduct->sold_quantity >= $item->quantity) {
                $flashSaleProduct->decrement('sold_quantity', $item->quantity);
            }
        }
    }

    // ── PRIVATE: move order data to sales_history ─────────────────────
    private function moveToSalesHistory(Order $order, $admin): void
    {
        foreach ($order->items as $item) {
            SalesHistory::create([
                'order_id'            => $order->id,
                'product_id'          => $item->product_id,
                'product_name'        => $item->product_name,
                'product_type'        => $item->product_type ?? null,
                'product_size'        => $item->product_size ?? null,
                'customer_name'       => $order->customer_name,
                'customer_email'      => $order->customer_email,
                'customer_mobile'     => $order->customer_mobile,
                'customer_address'    => $order->customer_address,
                'shipping_location'   => $order->shipping_location,
                'quantity'            => $item->quantity,
                'unit_price'          => $item->price,
                // FIX: redundant columns removed — subtotal is canonical (was total_product_amount)
                'subtotal'            => $item->subtotal,
                'is_discounted'       => $item->is_discounted ?? false,
                'original_price'      => $item->original_price ?? $item->price,
                'shipping_charge'     => $order->shipping_charge,
                // FIX: final_amount is canonical (was also total_amount)
                'final_amount'        => $order->total_amount,
                'discount_amount'     => $item->discount_amount ?? 0,
                'original_unit_price' => $item->original_price ?? $item->price,
                'order_status'        => 'delivered',
                'payment_method'      => $order->payment_method,
                'payment_status'      => 'completed',
                'sold_by'             => $admin?->name ?? 'system',
                'delivery_status'     => 'delivered',
                'notes'               => 'Completed via orders panel',
                'order_time'          => $order->created_at,
                'selling_time'        => now(),
            ]);
        }

        // FIX: নতুন sales data এলে customer dashboard cache clear করো
        Cache::forget('admin_customer_all_stats');
        Cache::forget('admin_customer_top5');
        Cache::forget('admin_customer_recent');
        Cache::forget('admin_category_stats_en');
        Cache::forget('admin_category_stats_bn');
        Cache::forget('admin_top_products_by_cat_en');
        Cache::forget('admin_top_products_by_cat_bn');
    }
}
