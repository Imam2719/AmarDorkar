<?php
namespace App\Http\Controllers;

use App\Jobs\SendOrderConfirmationEmail;
use App\Jobs\SendOrderConfirmationWhatsApp;
use App\Jobs\SendAdminOrderNotification;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        $userId    = auth()->id();
        $sessionId = session()->getId();

        $cartItems = $this->getCartItems($userId, $sessionId);

        if ($cartItems->isEmpty()) {
            return redirect()->route('home')->with('error', __('messages.cart_empty'));
        }

        $subtotal       = $cartItems->sum('subtotal');
        $shippingCharge = $this->calculateShipping($cartItems, 'inside_dhaka');
        $total          = $subtotal + $shippingCharge;

        return view('checkout.index', compact('cartItems', 'subtotal', 'shippingCharge', 'total'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // NEW: AJAX — Email দিয়ে check করো account আছে কিনা
    // Returns: { exists: bool }
    // ──────────────────────────────────────────────────────────────────────────
    public function checkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $exists = User::where('email', $request->email)->exists();

        return response()->json(['exists' => $exists]);
    }

    // AJAX coupon apply
    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string|max:10']);

        $coupon = Coupon::where('code', strtoupper(trim($request->code)))->first();

        if (!$coupon) {
            return response()->json(['valid' => false, 'message' => 'কুপন কোডটি সঠিক নয়।']);
        }

        $userId   = auth()->id();
        $subtotal = (float) $request->input('subtotal', 0);
        $result   = $coupon->validate($userId, $subtotal);

        if (!$result['valid']) {
            return response()->json($result);
        }

        $discount = $coupon->calculateDiscount($subtotal);

        return response()->json([
            'valid'         => true,
            'message'       => $result['message'],
            'discount'      => $discount,
            'discount_text' => number_format($discount, 0),
            'coupon_code'   => $coupon->code,
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'customer_name'     => 'required|string|max:255',
            'customer_email'    => 'required|email|max:255',
            'customer_mobile'   => 'required|string|max:20',
            'customer_address'  => 'required|string',
            'shipping_location' => 'required|string|max:255',
            'payment_method'    => 'required|in:cod,online',
            'coupon_code'       => 'nullable|string|max:10',
        ];

        // যদি guest হয় এবং existing account-এর email দেয়
        // তাহলে password field টাও validate করতে হবে
        $userId = auth()->id();
        if (!$userId) {
            $existingUser = User::where('email', $request->customer_email)->first();
            if ($existingUser) {
                $rules['account_password'] = 'required|string';
            }
        }

        $request->validate($rules);

        $sessionId = session()->getId();

        $newAccountCreated = false;
        $existingAccount   = false;
        $tempPassword      = null;

        if (!$userId) {
            $existingUser = User::where('email', $request->customer_email)->first();

            if ($existingUser) {
                // ── SECURE: Password verify করো, তারপর login ────────────
                if (!Hash::check($request->account_password, $existingUser->password)) {
                    return back()
                        ->withInput()
                        ->withErrors(['account_password' => 'পাসওয়ার্ডটি সঠিক নয়। আবার চেষ্টা করুন।']);
                }

                Auth::login($existingUser);
                $userId          = $existingUser->id;
                $existingAccount = true;

            } else {
                // নতুন user — account তৈরি করো
                $tempPassword = Str::random(10);
                $newUser = User::create([
                    'name'     => $request->customer_name,
                    'email'    => $request->customer_email,
                    'mobile'   => $request->customer_mobile,
                    'password' => Hash::make($tempPassword),
                ]);
                Auth::login($newUser);
                $userId            = $newUser->id;
                $newAccountCreated = true;
            }

            // Login হওয়ার পরে session cart → user cart migrate
            $this->mergeSessionCartToUser($sessionId, $userId);
        }

        $cartItems = $this->getCartItems($userId, $sessionId);

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

        $subtotal       = $cartItems->sum(fn($i) => $i->price * $i->quantity);
        $shippingCharge = $this->calculateShipping($cartItems, $request->shipping_location);

        // Coupon
        $coupon         = null;
        $couponDiscount = 0;

        if ($request->filled('coupon_code')) {
            $coupon = Coupon::where('code', strtoupper(trim($request->coupon_code)))->first();
            if ($coupon) {
                $validation = $coupon->validate($userId, $subtotal);
                if ($validation['valid']) {
                    $couponDiscount = $coupon->calculateDiscount($subtotal);
                } else {
                    return back()->with('error', 'কুপন: ' . $validation['message'])->withInput();
                }
            } else {
                return back()->with('error', 'কুপন কোডটি সঠিক নয়।')->withInput();
            }
        }

        $total = max(0, $subtotal + $shippingCharge - $couponDiscount);

        try {
            $order = DB::transaction(function () use (
                $request, $cartItems, $subtotal, $shippingCharge, $total,
                $userId, $coupon, $couponDiscount
            ) {
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
                    'coupon_id'         => $coupon?->id,
                    'coupon_code'       => $coupon?->code,
                    'coupon_discount'   => $couponDiscount,
                    'status'            => 'pending',
                    'payment_status'    => 'pending',
                    'payment_method'    => $request->payment_method,
                ]);

                foreach ($cartItems as $item) {
                    $product = \App\Models\Product::lockForUpdate()->find($item->product_id);
                    if (!$product || $product->stock_quantity < $item->quantity) {
                        throw new \Exception(__('messages.out_of_stock', ['product' => $item->product->name ?? 'product']));
                    }

                    $flashSaleProduct = \App\Models\FlashSaleProduct::lockForUpdate()
                        ->whereHas('flashSale', fn($q) => $q->where('is_active', true)
                            ->where('start_time', '<=', now())
                            ->where('end_time', '>=', now()))
                        ->where('product_id', $item->product_id)->first();

                    if ($flashSaleProduct) {
                        $remaining = $flashSaleProduct->max_quantity - $flashSaleProduct->sold_quantity;
                        if ($remaining < $item->quantity) {
                            throw new \Exception('Flash sale quota exceeded for: ' . ($item->product->name_en ?? 'product') . ". Remaining: {$remaining}");
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
                        'selected_size'         => $item->selected_size ?? null,
                        'flash_sale_product_id' => $flashSaleProduct ? $flashSaleProduct->id : null,
                    ]);

                    $product->decrement('stock_quantity', $item->quantity);
                }

                if ($coupon) {
                    CouponUsage::create([
                        'coupon_id'       => $coupon->id,
                        'order_id'        => $order->id,
                        'user_id'         => $userId,
                        'discount_amount' => $couponDiscount,
                    ]);
                    $coupon->increment('used_count');
                }

                CartItem::where('user_id', $order->user_id)->delete();
                CartItem::where('session_id', session()->getId())->delete();

                return $order;
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        try { SendOrderConfirmationEmail::dispatch($order, $newAccountCreated ? $tempPassword : null, app()->getLocale()); }
        catch (\Exception $e) { \Illuminate\Support\Facades\Log::error('Customer email failed: ' . $e->getMessage()); }
        try { SendOrderConfirmationWhatsApp::dispatch($order); }
        catch (\Exception $e) { \Illuminate\Support\Facades\Log::error('Customer WhatsApp failed: ' . $e->getMessage()); }
        try { SendAdminOrderNotification::dispatch($order, app()->getLocale()); }
        catch (\Exception $e) { \Illuminate\Support\Facades\Log::error('Admin email failed: ' . $e->getMessage()); }

        session(['last_order_id' => $order->id]);

        return redirect()->route('order.success', $order)
            ->with('success', __('messages.order_placed'))
            ->with('new_account_created', $newAccountCreated)
            ->with('existing_account_login', $existingAccount)
            ->with('account_email', $request->customer_email)
            ->with('account_temp_password', $tempPassword);
    }

    public function success(Order $order)
    {
        if (is_null($order->user_id)) {
            if (session('last_order_id') !== $order->id) {
                abort(403);
            }
        } elseif (auth()->check() && $order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items.product');
        return view('checkout.success', compact('order'));
    }

    private function getCartItems(mixed $userId, string $sessionId)
    {
        return CartItem::where(function ($q) use ($userId, $sessionId) {
            $userId
                ? $q->where('user_id', $userId)
                : $q->where('session_id', $sessionId);
        })->with('product')->get();
    }

    private function mergeSessionCartToUser(string $sessionId, int $userId): void
    {
        $sessionItems = CartItem::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->get();

        if ($sessionItems->isEmpty()) {
            return;
        }

        foreach ($sessionItems as $sessionItem) {
            $existingUserItem = CartItem::where('user_id', $userId)
                ->where('product_id', $sessionItem->product_id)
                ->where('selected_size', $sessionItem->selected_size)
                ->first();

            if ($existingUserItem) {
                $existingUserItem->delete();
            }

            $sessionItem->update([
                'user_id'    => $userId,
                'session_id' => null,
            ]);
        }

        CartItem::where('session_id', $sessionId)->whereNull('user_id')->delete();
    }

    private function calculateShipping($cartItems, string $location = 'inside_dhaka'): float
    {
        $subtotal = $cartItems->sum('subtotal');
        if ($location === 'outside_dhaka') return 120.0;
        return $subtotal >= 1000 ? 0.0 : 60.0;
    }
}