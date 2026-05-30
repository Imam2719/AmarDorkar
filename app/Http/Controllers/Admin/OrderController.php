<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendOrderStatusUpdateEmail;
use App\Jobs\SendOrderStatusWhatsApp;
use App\Models\FlashSaleProduct;
use App\Models\Order;
use App\Models\Product;
use App\Models\SalesHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    private const PER_PAGE = 10;

    // ── INDEX ─────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $perPage      = self::PER_PAGE;
        $search       = $request->input('search', '');
        $statusFilter = $request->input('status', '');

        // Admin sees ALL orders — no user_id filter
        $orderQuery = Order::with(['items.product.primaryImage', 'user'])
            ->latest();

        if ($search) {
            $orderQuery->where(function ($q) use ($search) {
                $q->where('order_number',     'like', "%{$search}%")
                  ->orWhere('customer_name',  'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_mobile','like', "%{$search}%");
            });
        }

        if ($statusFilter) {
            $orderQuery->where('status', $statusFilter);
        }

        // Stats always reflect all orders, not the current filter
        $stats = [
            'total_orders'    => Order::count(),
            'total_revenue'   => Order::whereNotIn('status', ['cancelled'])->sum('total_amount'),
            'pending_orders'  => Order::where('status', 'pending')->count(),
            'delivered_orders'=> Order::where('status', 'delivered')->count(),
            'today_orders'    => Order::whereDate('created_at', today())->count(),
        ];

        $total   = $orderQuery->count();
        $orders  = $orderQuery->take($perPage)->get();
        $hasMore = $total > $perPage;

        return view('admin.orders.index', compact(
            'orders', 'hasMore', 'total', 'stats', 'search', 'statusFilter'
        ));
    }

    // ── SHOW ──────────────────────────────────────────────────────────
    // AJAX (from index modal): returns JSON
    // Direct URL visit:        returns admin blade
    public function show(Request $request, Order $order)
    {
        $order->load('items.product.primaryImage', 'user');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'order'   => [
                    'id'               => $order->id,
                    'order_number'     => $order->order_number,
                    'created_at'       => $order->created_at->toISOString(),
                    'status'           => $order->status,
                    'payment_method'   => $order->payment_method,
                    'payment_status'   => $order->payment_status,
                    'customer_name'    => $order->customer_name,
                    'customer_email'   => $order->customer_email,
                    'customer_mobile'  => $order->customer_mobile,
                    'customer_address' => $order->customer_address,
                    'shipping_location'=> $order->shipping_location,
                    'subtotal'         => $order->subtotal,
                    'shipping_charge'  => $order->shipping_charge,
                    'coupon_code'      => $order->coupon_code,
                    'coupon_discount'  => $order->coupon_discount,
                    'total_amount'     => $order->total_amount,
                    'items'            => $order->items->map(fn($item) => [
                        'product_name'  => $item->product_name,
                        'selected_size' => $item->selected_size,
                        'quantity'      => $item->quantity,
                        'price'         => $item->price,
                        'subtotal'      => $item->subtotal,
                        'product_image' => $item->product?->image_url,
                    ]),
                ],
            ]);
        }

        return view('admin.orders.show', compact('order'));
    }

    // ── UPDATE STATUS + PAYMENT ───────────────────────────────────────
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status'         => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        $data = ['status' => $newStatus];

        if ($request->filled('payment_status')) {
            $data['payment_status'] = $request->payment_status;
        }

        // Track delivery timestamp
        if ($newStatus === 'delivered' && $oldStatus !== 'delivered') {
            $data['delivered_at'] = now();
        } elseif ($newStatus !== 'delivered' && $oldStatus === 'delivered') {
            $data['delivered_at'] = null;
        }

        DB::transaction(function () use ($order, $data, $newStatus, $oldStatus) {
            // Restore stock + flash sale qty when cancelling
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                $this->restoreStockForOrder($order);
            }

            $order->update($data);
        });

        // Email + WhatsApp customer about status change (non-blocking)
        if ($oldStatus !== $newStatus) {
            try {
                SendOrderStatusUpdateEmail::dispatch($order, $oldStatus, $newStatus, app()->getLocale());
            } catch (\Exception $e) {
                Log::error('Order status email failed: ' . $e->getMessage());
            }
            try {
                SendOrderStatusWhatsApp::dispatch($order, $oldStatus, $newStatus);
            } catch (\Exception $e) {
                Log::error('Order status WhatsApp failed: ' . $e->getMessage());
            }
        }

        // redirect_to=view comes from the show page's status form
        if ($request->input('redirect_to') === 'view') {
            return redirect()->route('admin.orders.show', $order)
                ->with('success', app()->getLocale() === 'bn'
                    ? 'অর্ডার স্ট্যাটাস আপডেট হয়েছে।'
                    : 'Order status updated.');
        }

        return redirect()->route('admin.orders.index')
            ->with('success', app()->getLocale() === 'bn'
                ? 'অর্ডার স্ট্যাটাস আপডেট হয়েছে।'
                : 'Order status updated.');
    }

    // ── SELL (mark delivered + write sales history) ───────────────────
    public function sell(Request $request, Order $order)
    {
        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return back()->with('error', app()->getLocale() === 'bn'
                ? 'এই অর্ডার ইতিমধ্যে সম্পন্ন বা বাতিল।'
                : 'Order is already completed or cancelled.');
        }

        $order->loadMissing('items.product');
        $oldStatus = $order->status;
        $adminName = auth('admin')->user()?->name ?? 'Admin';

        DB::transaction(function () use ($order, $adminName) {
            foreach ($order->items as $item) {
                SalesHistory::create([
                    'order_id'           => $order->id,
                    'product_id'         => $item->product_id,
                    'product_name'       => $item->product_name,
                    'product_type'       => $item->product_type,
                    'product_size'       => $item->selected_size ?? $item->product_size,
                    'customer_name'      => $order->customer_name,
                    'customer_email'     => $order->customer_email,
                    'customer_mobile'    => $order->customer_mobile,
                    'customer_address'   => $order->customer_address,
                    'shipping_location'  => $order->shipping_location,
                    'quantity'           => $item->quantity,
                    'unit_price'         => $item->price,
                    'subtotal'           => $item->subtotal,
                    'is_discounted'      => (bool) ($item->is_discounted ?? false),
                    'original_price'     => $item->original_price,
                    'original_unit_price'=> $item->original_price,
                    'discount_amount'    => $item->discount_amount ?? 0,
                    'shipping_charge'    => $order->shipping_charge,
                    'final_amount'       => $order->total_amount,
                    'order_status'       => 'delivered',
                    'payment_method'     => $order->payment_method,
                    'payment_status'     => $order->payment_status ?? 'pending',
                    'sold_by'            => $adminName,
                    'delivery_status'    => 'delivered',
                    'order_time'         => $order->created_at,
                    'selling_time'       => now(),
                ]);
            }

            $order->update([
                'status'       => 'delivered',
                'delivered_at' => now(),
            ]);
        });

        // Email + WhatsApp customer
        try {
            SendOrderStatusUpdateEmail::dispatch($order, $oldStatus, 'delivered', app()->getLocale());
        } catch (\Exception $e) {
            Log::error('Sell order email failed: ' . $e->getMessage());
        }
        try {
            SendOrderStatusWhatsApp::dispatch($order, $oldStatus, 'delivered');
        } catch (\Exception $e) {
            Log::error('Sell order WhatsApp failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.orders.index')
            ->with('success', app()->getLocale() === 'bn'
                ? 'অর্ডার #' . $order->order_number . ' বিক্রয় সম্পন্ন হয়েছে।'
                : 'Order #' . $order->order_number . ' marked as sold.');
    }

    // ── BULK UPDATE ───────────────────────────────────────────────────
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'order_ids'   => 'required|array|min:1',
            'order_ids.*' => 'integer|exists:orders,id',
            'status'      => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
        ]);

        $newStatus = $request->status;

        // items eager load করো — N+1 query এড়াতে
        $orders = Order::whereIn('id', $request->order_ids)
            ->with('items')
            ->get();

        DB::transaction(function () use ($orders, $newStatus) {
            foreach ($orders as $order) {
                $oldStatus = $order->status;

                // Already same status → skip (double-restore এড়াতে)
                if ($oldStatus === $newStatus) {
                    continue;
                }

                $data = ['status' => $newStatus];

                if ($newStatus === 'delivered' && $oldStatus !== 'delivered') {
                    $data['delivered_at'] = now();
                }

                // Cancel হলে stock + flash sale quota restore করো
                if ($newStatus === 'cancelled') {
                    $this->restoreStockForOrder($order);
                }

                $order->update($data);
            }
        });

        return response()->json([
            'success' => true,
            'message' => app()->getLocale() === 'bn'
                ? $orders->count() . 'টি অর্ডার আপডেট হয়েছে।'
                : $orders->count() . ' orders updated.',
        ]);
    }

    // ── PRIVATE: stock + flash sale quota restore ─────────────────────
    // update() এবং bulkUpdate() দুটোই এই method ব্যবহার করে।
    // শুধু তখনই call করো যখন oldStatus !== 'cancelled'।
    private function restoreStockForOrder(\App\Models\Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            if ($item->product_id) {
                Product::where('id', $item->product_id)
                    ->increment('stock_quantity', $item->quantity);
            }

            $flashSaleProduct = null;
            if ($item->flash_sale_product_id) {
                $flashSaleProduct = FlashSaleProduct::find($item->flash_sale_product_id);
            }
            if (!$flashSaleProduct && $item->product_id) {
                $flashSaleProduct = FlashSaleProduct::where('product_id', $item->product_id)
                    ->whereHas('flashSale', fn($q) => $q->where('is_active', true))
                    ->first();
            }
            if ($flashSaleProduct && $flashSaleProduct->sold_quantity >= $item->quantity) {
                $flashSaleProduct->decrement('sold_quantity', $item->quantity);
            }
        }
    }

    // ── DESTROY ───────────────────────────────────────────────────────
    public function destroy(Order $order)
    {
        // Restore stock for non-terminal orders
        if (!in_array($order->status, ['cancelled', 'delivered'])) {
            $order->loadMissing('items');
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    Product::where('id', $item->product_id)
                        ->increment('stock_quantity', $item->quantity);
                }
            }
        }

        $order->delete();

        return redirect()->route('admin.orders.index')
            ->with('success', app()->getLocale() === 'bn'
                ? 'অর্ডার মুছে ফেলা হয়েছে।'
                : 'Order deleted.');
    }
}