<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'customer_name', 'customer_email',
        'customer_mobile', 'customer_address', 'shipping_location',
        'shipping_charge', 'subtotal', 'total_amount', 'status',
        'payment_method', 'payment_status', 'admin_notified',
        'admin_notification_time', 'assigned_admin_id',
    ];

    protected $casts = [
        'admin_notified'           => 'boolean',
        'admin_notification_time'  => 'datetime',
        'shipping_charge'          => 'decimal:2',
        'subtotal'                 => 'decimal:2',
        'total_amount'             => 'decimal:2',
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

    // ── Helpers ───────────────────────────────────────────────────────

    /**
     * FIX: Replace uniqid() (microsecond-based, race-condition-prone) with
     * Str::random(8) — collision-safe and does not depend on timing.
     */
    public static function generateOrderNumber(): string
    {
        return 'DOL-' . date('Ymd') . '-' . strtoupper(Str::random(8));
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
            default    => 'secondary',  // pending
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
