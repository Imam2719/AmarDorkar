<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'order_id', 'rating', 'comment',
        'review_image_data', 'review_mime_type', 'is_approved'
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'rating'      => 'integer',
    ];

    // Exclude binary data from JSON/serialization
    protected $hidden = ['review_image_data'];

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

    /**
     * URL to serve review image from the database.
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->review_image_data) {
            return route('image.review', $this->id);
        }

        return null;
    }

    // ── Static helpers ────────────────────────────────────────
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

    public static function canUserReview(int $userId, int $productId): bool
    {
        $orderId = self::getDeliveredOrderId($userId, $productId);
        if (!$orderId) return false;

        return !self::where('user_id', $userId)
                    ->where('product_id', $productId)
                    ->exists();
    }
}
