<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'customer_name', 'customer_email',
        'customer_mobile', 'customer_address', 'shipping_location',
        'shipping_charge', 'subtotal', 'total_amount', 'status',
        'payment_method', 'payment_status', 'admin_notified',
        'admin_notification_time', 'assigned_admin_id',
        'coupon_id', 'coupon_discount', 'coupon_code',
        'delivered_at', // ← নতুন
    ];

    protected $casts = [
        'admin_notified'           => 'boolean',
        'admin_notification_time'  => 'datetime',
        'shipping_charge'          => 'decimal:2',
        'subtotal'                 => 'decimal:2',
        'total_amount'             => 'decimal:2',
        'coupon_discount'          => 'decimal:2',
        'delivered_at'             => 'datetime', // ← নতুন
    ];

    // ── Relationships ─────────────────────────────────────────────────
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(Admin::class, 'assigned_admin_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function couponUsage()
    {
        return $this->hasOne(CouponUsage::class);
    }

    public function returnRequests()
    {
        return $this->hasMany(ReturnRequest::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────

    public static function generateOrderNumber(): string
    {
        return 'DOL-' . date('Ymd') . '-' . strtoupper(Str::random(8));
    }

    /**
     * Return window deadline (7 দিন delivered_at থেকে)।
     * delivered_at না থাকলে null return করে — সেক্ষেত্রে
     * ReturnRequestController return allow করবে না।
     */
    public function returnDeadline(int $days = 7): ?Carbon
    {
        if (!$this->delivered_at) {
            return null;
        }
        return $this->delivered_at->copy()->addDays($days);
    }

    /**
     * এখন return request করা যাবে কিনা তা check করে।
     * Condition: delivered + delivered_at আছে + window শেষ হয়নি
     */
    public function canRequestReturn(int $days = 7): bool
    {
        if ($this->status !== 'delivered') {
            return false;
        }
        $deadline = $this->returnDeadline($days);
        if (!$deadline) {
            return false;
        }
        return Carbon::now()->lte($deadline);
    }

    // Maps status to Bootstrap badge class (used in views)
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'warning',
            'confirmed'  => 'success',
            'processing' => 'info',
            'shipped'    => 'primary',
            'delivered'  => 'success',
            'cancelled'  => 'danger',
            default      => 'secondary',
        };
    }

    // Maps payment_status to Bootstrap badge class
    public function getPaymentBadgeAttribute(): string
    {
        return match ($this->payment_status) {
            'paid'     => 'success',
            'failed'   => 'danger',
            'refunded' => 'warning',
            default    => 'secondary',
        };
    }

    // Localised Bengali status label
    public function getStatusLabelBnAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'মুলতবি',
            'confirmed'  => 'নিশ্চিত',
            'processing' => 'প্রক্রিয়াকরণ',
            'shipped'    => 'পাঠানো',
            'delivered'  => 'ডেলিভার্ড',
            'cancelled'  => 'বাতিল',
            default      => ucfirst($this->status),
        };
    }
}
