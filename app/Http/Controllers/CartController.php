<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function getSessionId(): string
    {
        return session()->getId();
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id'    => 'required|exists:products,id',
            'quantity'      => 'integer|min:1|max:99',
            'selected_size' => 'nullable|string|max:50',
        ]);

        $product      = Product::findOrFail($request->product_id);
        $quantity     = $request->quantity ?? 1;
        $selectedSize = $request->selected_size ?? null;
        $userId       = auth()->id();
        $sessionId    = $this->getSessionId();

        // BUG 3 FIX: Count total qty in cart for this product across ALL sizes.
        // Size-based products share one stock_quantity pool — S×2 + M×3 = 5 total consumed.
        $totalQtyInCart = CartItem::where(function ($q) use ($userId, $sessionId) {
            $userId ? $q->where('user_id', $userId) : $q->where('session_id', $sessionId);
        })->where('product_id', $product->id)->sum('quantity');

        if (($totalQtyInCart + $quantity) > $product->stock_quantity) {
            $available = max(0, $product->stock_quantity - $totalQtyInCart);
            return response()->json([
                'success' => false,
                'message' => $available > 0
                    ? "স্টকে মাত্র {$available}টি পণ্য বাকি আছে।"
                    : "এই পণ্যটি স্টকে নেই।",
            ], 422);
        }

        // Same size = increment existing row; new size = new row
        $cartItem = CartItem::where(function ($q) use ($userId, $sessionId) {
            $userId ? $q->where('user_id', $userId) : $q->where('session_id', $sessionId);
        })->where('product_id', $product->id)
          ->where('selected_size', $selectedSize)
          ->first();

        if ($cartItem) {
            $cartItem->increment('quantity', $quantity);
        } else {
            CartItem::create([
                'user_id'       => $userId,
                'session_id'    => $sessionId,
                'product_id'    => $product->id,
                'quantity'      => $quantity,
                'price'         => $product->final_price,
                'selected_size' => $selectedSize,
            ]);
        }

        return response()->json([
            'success'    => true,
            'cart_count' => $this->getCartCount(),
            'message'    => __('messages.added_to_cart'),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'item_id'  => 'required|integer',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $item = CartItem::findOrFail($request->item_id);
        $this->authorizeCartItem($item);

        $newQty  = max(1, (int) $request->quantity);
        $product = $item->product;

        // BUG 1 FIX: Validate stock on update.
        // Sum qty of all OTHER cart rows for this product (e.g. other sizes),
        // then check new qty fits within remaining stock.
        if ($product) {
            $userId    = auth()->id();
            $sessionId = $this->getSessionId();

            $otherSizesQty = CartItem::where(function ($q) use ($userId, $sessionId) {
                $userId ? $q->where('user_id', $userId) : $q->where('session_id', $sessionId);
            })->where('product_id', $product->id)
              ->where('id', '!=', $item->id)
              ->sum('quantity');

            if (($otherSizesQty + $newQty) > $product->stock_quantity) {
                $available = max(0, $product->stock_quantity - $otherSizesQty);
                return response()->json([
                    'success' => false,
                    'message' => $available > 0
                        ? "স্টকে মাত্র {$available}টি পণ্য বাকি আছে।"
                        : "এই পণ্যটি স্টকে নেই।",
                ], 422);
            }
        }

        $item->update(['quantity' => $newQty]);
        $item->refresh();

        return response()->json([
            'success'    => true,
            'cart_count' => $this->getCartCount(),
            'subtotal'   => number_format($item->subtotal, 0),
        ]);
    }

    public function remove(Request $request)
    {
        $item = CartItem::findOrFail($request->item_id);
        $this->authorizeCartItem($item);
        $item->delete();

        return response()->json([
            'success'    => true,
            'cart_count' => $this->getCartCount(),
        ]);
    }

    public function items()
    {
        $items = $this->getCartItems();
        $total = $items->sum('subtotal');

        return response()->json([
            'items' => $items->map(fn($i) => [
                'id'            => $i->id,
                'name'          => $i->product->name,
                'price'         => $i->price,
                'quantity'      => $i->quantity,
                'subtotal'      => $i->subtotal,
                'image'         => $i->product->image_url,
                'selected_size' => $i->selected_size,
            ]),
            'total' => $total,
        ]);
    }

    private function authorizeCartItem(CartItem $item): void
    {
        $userId    = auth()->id();
        $sessionId = $this->getSessionId();

        $owned = $userId
            ? $item->user_id === $userId
            : $item->session_id === $sessionId;

        if (!$owned) {
            abort(403, 'Unauthorized cart action.');
        }
    }

    private function getCartItems()
    {
        $userId    = auth()->id();
        $sessionId = $this->getSessionId();

        return CartItem::where(function ($q) use ($userId, $sessionId) {
            $userId ? $q->where('user_id', $userId) : $q->where('session_id', $sessionId);
        })->with('product')->get();
    }

    private function getCartCount(): int
    {
        return $this->getCartItems()->sum('quantity');
    }
}