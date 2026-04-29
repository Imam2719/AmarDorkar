<?php
namespace App\Http\Controllers;

use App\Jobs\SendOrderConfirmationEmail;
use App\Jobs\SendAdminOrderNotification;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    // ── Checkout page ────────────────────────────────────────────────
    public function index()
    {
        $userId    = auth()->id();
        $sessionId = session()->getId();

        $cartItems = CartItem::where(function ($q) use ($userId, $sessionId) {
            $userId
                ? $q->where('user_id', $userId)
                : $q->where('session_id', $sessionId);
        })->with('product')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('home')
                             ->with('error', __('messages.cart_empty'));
        }

        $subtotal      = $cartItems->sum('subtotal');
        $shippingCharge = $this->calculateShipping($cartItems, 'inside_dhaka');
        $total         = $subtotal + $shippingCharge;

        return view('checkout.index', compact('cartItems', 'subtotal', 'shippingCharge', 'total'));
    }

    // ── Place order ──────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'customer_name'    => 'required|string|max:255',
            'customer_email'   => 'nullable|email|max:255',
            'customer_mobile'  => 'required|string|max:20',
            'customer_address' => 'required|string',
            'shipping_location'=> 'required|string|max:255',
            'payment_method'   => 'required|in:cod,online',
        ]);

        $userId    = auth()->id();
        $sessionId = session()->getId();

        $cartItems = CartItem::where(function ($q) use ($userId, $sessionId) {
            $userId
                ? $q->where('user_id', $userId)
                : $q->where('session_id', $sessionId);
        })->with('product')->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', __('messages.cart_empty'));
        }

        foreach ($cartItems as $item) {
            $currentPrice = $item->product->final_price;
            if ($item->price != $currentPrice) {
                $item->update(['price' => $currentPrice]);
                $item->price = $currentPrice;
            }
        }

        $subtotal      = $cartItems->sum('subtotal');
        $shippingCharge = $this->calculateShipping($cartItems, $request->shipping_location);
        $total         = $subtotal + $shippingCharge;

        try {
            $order = DB::transaction(function () use ($request, $cartItems, $subtotal, $shippingCharge, $total, $userId) {
                $order = Order::create([
                    'order_number'      => Order::generateOrderNumber(),
                    'user_id'           => $userId,
                    'customer_name'     => $request->customer_name,
                    'customer_email'    => $request->customer_email,
                    'customer_mobile'   => $request->customer_mobile,
                    'customer_address'  => $request->customer_address,
                    'shipping_location' => $request->shipping_location,
                    'shipping_charge'   => $shippingCharge,
                    'subtotal'          => $subtotal,
                    'total_amount'      => $total,
                    'status'            => 'pending',
                    'payment_status'    => 'pending',
                    'payment_method'    => $request->payment_method,
                ]);

                foreach ($cartItems as $item) {
                    $product = \App\Models\Product::lockForUpdate()->find($item->product_id);

                    if (!$product || $product->stock_quantity < $item->quantity) {
                        throw new \Exception(
                            __('messages.out_of_stock', ['product' => $item->product->name ?? 'product'])
                        );
                    }

                    $flashSaleProduct = \App\Models\FlashSaleProduct::lockForUpdate()
                        ->whereHas('flashSale', fn($q) => $q->where('is_active', true)
                            ->where('start_time', '<=', now())
                            ->where('end_time', '>=', now()))
                        ->where('product_id', $item->product_id)
                        ->first();

                    if ($flashSaleProduct) {
                        $remaining = $flashSaleProduct->max_quantity - $flashSaleProduct->sold_quantity;
                        if ($remaining < $item->quantity) {
                            throw new \Exception(
                                'Flash sale quota exceeded for: ' . ($item->product->name_en ?? 'product')
                                . ". Remaining: {$remaining}"
                            );
                        }
                        $flashSaleProduct->increment('sold_quantity', $item->quantity);
                    }

                    OrderItem::create([
                        'order_id'              => $order->id,
                        'product_id'            => $item->product_id,
                        'product_name'          => $product->name_en,
                        'price'                 => $item->price,
                        'original_price'        => $product->price,
                        'is_discounted'         => $item->price < $product->price,
                        'discount_amount'       => max(0, $product->price - $item->price) * $item->quantity,
                        'quantity'              => $item->quantity,
                        'subtotal'              => $item->subtotal,
                        'product_type'          => $product->product_type,
                        'product_size'          => $product->product_size,
                        // FIX: selected_size cart থেকে order_items-এ copy করা হচ্ছে
                        'selected_size'         => $item->selected_size ?? null,
                        'flash_sale_product_id' => $flashSaleProduct ? $flashSaleProduct->id : null,
                    ]);

                    $product->decrement('stock_quantity', $item->quantity);
                }

                CartItem::where(function ($q) use ($order) {
                    $order->user_id
                        ? $q->where('user_id', $order->user_id)
                        : $q->where('session_id', session()->getId());
                })->delete();

                return $order;
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        try {
            SendOrderConfirmationEmail::dispatch($order);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Customer email failed: ' . $e->getMessage());
        }
        try {
            SendAdminOrderNotification::dispatch($order);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Admin email failed: ' . $e->getMessage());
        }

        return redirect()->route('order.success', $order)
                         ->with('success', __('messages.order_placed'));
    }

    // ── Order success page ───────────────────────────────────────────
    public function success(Order $order)
    {
        if ($order->user_id && $order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items.product');

        return view('checkout.success', compact('order'));
    }

    // ── Helper: calculate shipping ───────────────────────────────────
    private function calculateShipping($cartItems, string $location = 'inside_dhaka'): float
    {
        $subtotal = $cartItems->sum('subtotal');

        if ($location === 'outside_dhaka') {
            return 120.0;
        }

        return $subtotal >= 1000 ? 0.0 : 60.0;
    }
}
