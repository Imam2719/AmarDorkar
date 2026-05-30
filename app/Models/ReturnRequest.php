<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReturnRequest extends Model
{
    protected $fillable = [
        'return_number', 'order_id', 'user_id', 'reason', 'description',
        'status', 'admin_notes', 'refund_amount', 'refund_method', 'resolved_at',
    ];

    protected $casts = [
        'resolved_at'   => 'datetime',
        'refund_amount' => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────────

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(ReturnRequestImage::class)->orderBy('sort_order');
    }

    // ── Helpers ───────────────────────────────────────────────────────

    public static function generateReturnNumber(): string
    {
        return 'RET-' . date('Ymd') . '-' . strtoupper(Str::random(6));
    }

    // English reason labels
    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason) {
            'defective_product'    => 'Defective Product',
            'wrong_item'           => 'Wrong Item Received',
            'not_as_described'     => 'Not as Described',
            'damaged_in_shipping'  => 'Damaged in Shipping',
            'changed_mind'         => 'Changed Mind',
            'missing_parts'        => 'Missing Parts/Accessories',
            'other'                => 'Other',
            default                => ucfirst($this->reason),
        };
    }

    // Bengali reason labels
    public function getReasonLabelBnAttribute(): string
    {
        return match ($this->reason) {
            'defective_product'    => 'ত্রুটিপূর্ণ পণ্য',
            'wrong_item'           => 'ভুল পণ্য পেয়েছি',
            'not_as_described'     => 'বিবরণ অনুযায়ী নয়',
            'damaged_in_shipping'  => 'ডেলিভারিতে ক্ষতিগ্রস্ত',
            'changed_mind'         => 'মন পরিবর্তন',
            'missing_parts'        => 'যন্ত্রাংশ/আনুষঙ্গিক অনুপস্থিত',
            'other'                => 'অন্যান্য',
            default                => ucfirst($this->reason),
        };
    }

    // Bootstrap badge color per status
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending'          => 'warning',
            'under_review'     => 'info',
            'approved'         => 'success',
            'rejected'         => 'danger',
            'refund_initiated' => 'primary',
            'refunded'         => 'success',
            default            => 'secondary',
        };
    }

    // English status label
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'          => 'Pending',
            'under_review'     => 'Under Review',
            'approved'         => 'Approved',
            'rejected'         => 'Rejected',
            'refund_initiated' => 'Refund Initiated',
            'refunded'         => 'Refunded',
            default            => ucfirst($this->status),
        };
    }

    // Bengali status label
    public function getStatusLabelBnAttribute(): string
    {
        return match ($this->status) {
            'pending'          => 'মুলতবি',
            'under_review'     => 'পর্যালোচনাধীন',
            'approved'         => 'অনুমোদিত',
            'rejected'         => 'প্রত্যাখ্যাত',
            'refund_initiated' => 'রিফান্ড শুরু',
            'refunded'         => 'রিফান্ড সম্পন্ন',
            default            => ucfirst($this->status),
        };
    }
}
