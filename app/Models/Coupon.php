<?php
// PATH: app/Models/Coupon.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'discount_value', 'min_order_amount',
        'max_discount_amount',
        'per_user_limit',   // null = unlimited per user
        'global_limit',     // null = unlimited globally
        'used_count',
        'is_active', 'expires_at', 'expired_at', 'description',
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'expires_at'          => 'datetime',
        'expired_at'          => 'datetime',
        'discount_value'      => 'decimal:2',
        'min_order_amount'    => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'per_user_limit'      => 'integer',
        'global_limit'        => 'integer',
        'used_count'          => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────

    public function allowedUsers()
    {
        return $this->belongsToMany(User::class, 'coupon_users');
    }

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->whereNull('expired_at')
                     ->where(function ($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>', now());
                     });
    }

    public function scopeHistory($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('expired_at')
              ->orWhere(function ($q2) {
                  $q2->whereNotNull('expires_at')
                     ->where('expires_at', '<=', now());
              });
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────

    public static function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(5));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public static function autoExpireToHistory(): int
    {
        return static::where('is_active', true)
                     ->whereNull('expired_at')
                     ->whereNotNull('expires_at')
                     ->where('expires_at', '<=', now())
                     ->update(['expired_at' => now(), 'is_active' => false]);
    }

    /**
     * Validate coupon for a given user + subtotal.
     * per_user_limit → কতবার একজন user use করতে পারবে
     * global_limit   → মোট কতবার সব মিলিয়ে use হতে পারবে
     */
    public function validate(?int $userId, float $subtotal): array
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'কুপনটি সক্রিয় নয়।'];
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return ['valid' => false, 'message' => 'কুপনের মেয়াদ শেষ হয়ে গেছে।'];
        }

        // Global limit check
        if ($this->global_limit !== null && $this->used_count >= $this->global_limit) {
            return ['valid' => false, 'message' => 'কুপনের সর্বোচ্চ ব্যবহার সীমা শেষ হয়ে গেছে।'];
        }

        // Per-user limit check
        if ($this->per_user_limit !== null && $userId) {
            $userUsedCount = $this->usages()->where('user_id', $userId)->count();
            if ($userUsedCount >= $this->per_user_limit) {
                $msg = $this->per_user_limit === 1
                    ? 'আপনি এই কুপন আগেই ব্যবহার করেছেন।'
                    : "আপনি এই কুপন সর্বোচ্চ {$this->per_user_limit} বার ব্যবহার করতে পারবেন।";
                return ['valid' => false, 'message' => $msg];
            }
        }

        // Min order check
        if ($subtotal < $this->min_order_amount) {
            return [
                'valid'   => false,
                'message' => 'কুপন প্রয়োগের জন্য সর্বনিম্ন অর্ডার পরিমাণ ৳'
                             . number_format($this->min_order_amount, 0) . '।',
            ];
        }

        // Allowed users check (VIP / targeted coupons)
        $allowedUserIds = $this->allowedUsers()->pluck('users.id');
        if ($allowedUserIds->isNotEmpty()) {
            if (!$userId || !$allowedUserIds->contains($userId)) {
                return ['valid' => false, 'message' => 'এই কুপনটি আপনার জন্য প্রযোজ্য নয়।'];
            }
        }

        return ['valid' => true, 'message' => 'কুপন সফলভাবে প্রয়োগ হয়েছে!'];
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'percentage') {
            $discount = $subtotal * ($this->discount_value / 100);
            if ($this->max_discount_amount) {
                $discount = min($discount, (float) $this->max_discount_amount);
            }
        } else {
            $discount = min((float) $this->discount_value, $subtotal);
        }

        return round($discount, 2);
    }

    public function getTypeLabel(): string
    {
        return $this->type === 'percentage'
            ? $this->discount_value . '%'
            : '৳' . number_format($this->discount_value, 0);
    }

    public function isPublic(): bool
    {
        return $this->allowedUsers()->count() === 0;
    }

    public function isExpired(): bool
    {
        return ($this->expires_at && $this->expires_at->isPast()) || $this->expired_at !== null;
    }

    /**
     * Preset detect করার জন্য — blade-এ badge দেখাতে কাজে লাগে।
     * একজন একবার  → per_user_limit=1, global_limit=null
     * সবাই যতবার  → per_user_limit=null, global_limit=null
     * প্রথম N জন  → global_limit=N (per_user_limit যাই হোক)
     * কাস্টম      → per_user_limit>1 বা দুটোই set
     */
    public function getPresetLabel(bool $bn = false): string
    {
        if ($this->per_user_limit === 1 && $this->global_limit === null) {
            return $bn ? '🎯 একজন একবার' : '🎯 Single Use';
        }

        if ($this->per_user_limit === null && $this->global_limit === null) {
            return $bn ? '📢 সবাই যতবার' : '📢 Unlimited';
        }

        if ($this->global_limit !== null && $this->per_user_limit === null) {
            return $bn
                ? "🏆 প্রথম {$this->global_limit} জন"
                : "🏆 First {$this->global_limit} uses";
        }

        // Custom combination
        $parts = [];
        if ($this->per_user_limit) {
            $parts[] = $bn
                ? "জনপ্রতি {$this->per_user_limit} বার"
                : "{$this->per_user_limit}/user";
        }
        if ($this->global_limit) {
            $parts[] = $bn
                ? "মোট {$this->global_limit} বার"
                : "{$this->global_limit} total";
        }

        return '⚙️ ' . implode(', ', $parts);
    }
}
