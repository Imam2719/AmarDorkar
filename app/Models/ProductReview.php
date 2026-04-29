<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'order_id', 'rating', 'comment', 'review_image', 'is_approved'
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'rating'      => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // ── FIX 6: Image URL accessor ─────────────────────────────────────
    // Provides a consistent way to get the review image URL in views
    // instead of manually building the path each time.
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->review_image) {
            return null;
        }

        // Support both full URLs (e.g. from old data) and storage-relative paths
        if (str_starts_with($this->review_image, 'http')) {
            return $this->review_image;
        }

        return asset('storage/' . $this->review_image);
    }

    // ── Static helper: delivered order id for this user+product ──
    // Returns the order_id if user has a delivered order containing this product,
    // otherwise returns null (meaning user cannot review).
    public static function getDeliveredOrderId(int $userId, int $productId): ?int
    {
        $orderItem = \App\Models\OrderItem::whereHas('order', function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->where('status', 'delivered');
            })
            ->where('product_id', $productId)
            ->first();

        return $orderItem?->order_id;
    }

    // ── Static helper: can this user review this product? ────────
    // true  → user has a delivered order with this product AND hasn't reviewed yet
    // false → either not purchased/delivered, or already reviewed
    public static function canUserReview(int $userId, int $productId): bool
    {
        $orderId = self::getDeliveredOrderId($userId, $productId);
        if (!$orderId) return false;

        return !self::where('user_id', $userId)
                    ->where('product_id', $productId)
                    ->exists();
    }
}
