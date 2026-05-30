<?php

namespace App\Jobs;

use App\Models\Coupon;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCouponWhatsApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries  = 3;
    public int $backoff = 60;

    public function __construct(
        public Coupon $coupon,
        public User $user,
        public int $template = 1
    ) {}

    public function handle(WhatsAppService $whatsapp): void
    {
        $coupon = $this->coupon;
        $user   = $this->user;

        if (!$user->mobile) return;

        $message = $this->buildMessage($coupon, $user);

        $result = $whatsapp->sendText($user->mobile, $message);

        if (!$result['success']) {
            Log::warning('SendCouponWhatsApp failed', [
                'user_id'   => $user->id,
                'coupon_id' => $coupon->id,
                'error'     => $result['error'] ?? 'unknown',
            ]);

            // Retry হবে — exception throw করলে Laravel queue retry করবে
            throw new \RuntimeException('WhatsApp send failed: ' . ($result['error'] ?? 'unknown'));
        }
    }

    private function buildMessage(Coupon $coupon, User $user): string
    {
        $discountText = $coupon->type === 'percentage'
            ? $coupon->discount_value . '% ছাড়'
            : '৳' . number_format($coupon->discount_value, 0) . ' ছাড়';

        $expiry = $coupon->expires_at
            ? $coupon->expires_at->format('d M Y')
            : 'সীমাহীন';

        $minOrder = $coupon->min_order_amount > 0
            ? "\n📦 সর্বনিম্ন অর্ডার: ৳" . number_format($coupon->min_order_amount, 0)
            : '';

        $description = $coupon->description ? "\n💬 " . $coupon->description : '';

        $shopUrl = config('app.url', 'http://localhost');

        return match ($this->template) {
            2 => "⚡ *EXCLUSIVE OFFER!*\n\nআস্সালামু আলাইকুম {$user->name} ভাই/আপু!\n\nআপনার জন্য এক্সক্লুসিভ অফার:\n\n🎯 ছাড়: *{$discountText}*\n🔑 কুপন কোড: *{$coupon->code}*{$minOrder}\n📅 মেয়াদ: {$expiry}{$description}\n\n🛒 এখনই কেনাকাটা করুন:\n{$shopUrl}\n\nচেকআউটে কোড লিখুন এবং ছাড় পান! 🎉",

            3 => "🌟 *আপনাকে ধন্যবাদ!*\n\nপ্রিয় {$user->name},\n\nআমাদের সাথে থাকার জন্য আন্তরিক ধন্যবাদ 💚\n\nআপনার বিশেষ পুরস্কার:\n\n🎁 ছাড়: *{$discountText}*\n🔑 কুপন কোড: *{$coupon->code}*{$minOrder}\n📅 মেয়াদ: {$expiry}{$description}\n\n{$shopUrl}",

            4 => "🛒 *কেনাকাটা করুন, সাশ্রয় করুন!*\n\nহ্যালো {$user->name}!\n\nআপনার কুপন তৈরি:\n\n💰 ছাড়: *{$discountText}*\n🔑 কোড: *{$coupon->code}*{$minOrder}\n⏰ মেয়াদ: {$expiry}{$description}\n\nকেনাকাটা করুন 👉 {$shopUrl}",

            5 => "🔥 *সীমিত সময়ের অফার!*\n\nদ্রুত করুন {$user->name}!\n\n⚡ ছাড়: *{$discountText}*\n🔑 কুপন কোড: *{$coupon->code}*{$minOrder}\n⏰ মেয়াদ: {$expiry}\n\n*এখনই ব্যবহার করুন:* {$shopUrl}\n\nঅফার সীমিত সময়ের জন্য! 🚨",

            default => "🎁 *বিশেষ উপহার!*\n\nআস্সালামু আলাইকুম {$user->name}!\n\nআপনার জন্য বিশেষ কুপন পাঠানো হলো:\n\n✨ ছাড়: *{$discountText}*\n🔑 কুপন কোড: *{$coupon->code}*{$minOrder}\n📅 মেয়াদ: {$expiry}{$description}\n\n🛒 কেনাকাটা করুন: {$shopUrl}\n\nচেকআউটে এই কোড দিন এবং ছাড় উপভোগ করুন! 🎉",
        };
    }
}
