<?php
// PATH: app/Jobs/SendCouponEmail.php

namespace App\Jobs;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCouponEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries  = 2;
    public int $backoff = 30;

    public function __construct(
        public Coupon $coupon,
        public User   $user,
        public int    $template = 1,
        public string $locale   = 'bn'          // ← dispatch করার সময় locale পাঠাও
    ) {}

    public function handle(): void
    {
        if (!$this->user->email) return;

        $subject = $this->getSubject($this->coupon, $this->template, $this->locale);
        $html    = $this->buildHtml($this->coupon, $this->user, $this->template, $this->locale);
        $to      = $this->user->email;
        $name    = $this->user->name;

        try {
            Mail::send([], [], function ($msg) use ($to, $name, $subject, $html) {
                $msg->to($to, $name)
                    ->subject($subject)
                    ->html($html);
            });

            Log::info("Coupon email sent → {$to} | code: {$this->coupon->code}");

        } catch (\Exception $e) {
            Log::error("Coupon email FAILED → {$to} | {$e->getMessage()}");
        }
    }

    // ── Subject lines ──────────────────────────────────────────────────
    private function getSubject(Coupon $coupon, int $template, string $locale): string
    {
        $isBn = $locale !== 'en';

        if ($isBn) {
            return match ($template) {
                1 => '🎁 বিশেষ উপহার! আপনার কুপন কোড: ' . $coupon->code,
                2 => '⚡ এক্সক্লুসিভ অফার! ' . $coupon->getTypeLabel() . ' ছাড় পান',
                3 => '🌟 আপনাকে ধন্যবাদ! বিশেষ পুরস্কার পাঠানো হয়েছে',
                4 => '🛒 কেনাকাটা করুন, সাশ্রয় করুন! কুপন কোড পেলেন',
                5 => '🔥 সীমিত সময়ের অফার! এখনই ব্যবহার করুন',
                default => '🎁 আপনার কুপন কোড: ' . $coupon->code,
            };
        }

        return match ($template) {
            1 => '🎁 Special Gift! Your coupon code: ' . $coupon->code,
            2 => '⚡ Exclusive Offer! Get ' . $coupon->getTypeLabel() . ' discount',
            3 => '🌟 Thank You! A special reward has been sent to you',
            4 => '🛒 Shop & Save! You have received a coupon code',
            5 => '🔥 Limited Time Offer! Use it now',
            default => '🎁 Your coupon code: ' . $coupon->code,
        };
    }

    // ── Build HTML email ───────────────────────────────────────────────
    private function buildHtml(Coupon $coupon, User $user, int $template, string $locale): string
    {
        $isBn = $locale !== 'en';

        if ($isBn) {
            $discountText = $coupon->type === 'percentage'
                ? $coupon->discount_value . '% ছাড়'
                : '৳' . number_format($coupon->discount_value, 0) . ' ছাড়';

            $expiry = $coupon->expires_at
                ? 'মেয়াদ শেষ: ' . $coupon->expires_at->format('d M Y')
                : 'কোনো মেয়াদ সীমা নেই';

            $minOrder = $coupon->min_order_amount > 0
                ? 'সর্বনিম্ন অর্ডার: ৳' . number_format($coupon->min_order_amount, 0)
                : '';

            $shopNow     = 'এখনই কেনাকাটা করুন';
            $footerText  = 'চেকআউটে কোড লিখুন এবং ছাড় উপভোগ করুন · AmarDorkar.com';
            $couponLabel = 'আপনার কুপন কোড';
        } else {
            $discountText = $coupon->type === 'percentage'
                ? $coupon->discount_value . '% off'
                : '৳' . number_format($coupon->discount_value, 0) . ' off';

            $expiry = $coupon->expires_at
                ? 'Expires: ' . $coupon->expires_at->format('d M Y')
                : 'No expiry date';

            $minOrder = $coupon->min_order_amount > 0
                ? 'Minimum order: ৳' . number_format($coupon->min_order_amount, 0)
                : '';

            $shopNow     = 'Shop Now';
            $footerText  = 'Enter the code at checkout to enjoy your discount · AmarDorkar.com';
            $couponLabel = 'Your Coupon Code';
        }

        $shopUrl   = config('app.url', 'http://localhost');
        $logoStyle = 'background:#1a9e4a;color:#fff;padding:10px 24px;border-radius:8px;'
                   . 'font-size:22px;font-weight:800;display:inline-block;';

        return match ($template) {
            2       => $this->templateFlash($user, $coupon, $discountText, $expiry, $minOrder, $shopUrl, $isBn, $shopNow, $footerText, $couponLabel),
            3       => $this->templateThankYou($user, $coupon, $discountText, $expiry, $minOrder, $shopUrl, $isBn, $shopNow, $footerText, $couponLabel),
            4       => $this->templateShopping($user, $coupon, $discountText, $expiry, $minOrder, $shopUrl, $isBn, $shopNow, $footerText, $couponLabel),
            5       => $this->templateLimitedTime($user, $coupon, $discountText, $expiry, $minOrder, $shopUrl, $isBn, $shopNow, $footerText, $couponLabel),
            default => $this->templateGift($user, $coupon, $discountText, $expiry, $minOrder, $shopUrl, $isBn, $shopNow, $footerText, $couponLabel, $logoStyle),
        };
    }

    // ── Template 1: Gift (green) ───────────────────────────────────────
    private function templateGift(User $user, Coupon $coupon, string $discountText, string $expiry, string $minOrder, string $shopUrl, bool $isBn, string $shopNow, string $footerText, string $couponLabel, string $logoStyle): string
    {
        $headline = $isBn ? 'বিশেষ উপহার!' : 'Special Gift!';
        $subtitle = $isBn
            ? 'প্রিয় ' . e($user->name) . ', আপনার জন্য একটি বিশেষ কুপন পাঠানো হলো'
            : 'Dear ' . e($user->name) . ', a special coupon has been sent just for you';
        $bodyText = $isBn
            ? 'আপনার পরবর্তী অর্ডারে <strong style="color:#1a9e4a;">' . $discountText . '</strong> পান!'
            : 'Get <strong style="color:#1a9e4a;">' . $discountText . '</strong> on your next order!';

        return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
        <body style="font-family:Arial,sans-serif;background:#f5f5f5;margin:0;padding:20px;">
        <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);">
          <div style="background:linear-gradient(135deg,#1a9e4a,#0d7a38);padding:40px 30px;text-align:center;">
            <div style="' . $logoStyle . '">AmarDorkar</div>
            <div style="font-size:48px;margin:20px 0;">🎁</div>
            <h1 style="color:#fff;margin:0;font-size:26px;">' . $headline . '</h1>
            <p style="color:rgba(255,255,255,.85);margin:8px 0 0;">' . $subtitle . '</p>
          </div>
          <div style="padding:30px;text-align:center;">
            <p style="color:#555;font-size:15px;margin-bottom:20px;">' . $bodyText . '</p>
            <div style="background:#f0faf5;border:2px dashed #1a9e4a;border-radius:12px;padding:20px;margin:20px 0;">
              <p style="margin:0 0 8px;color:#888;font-size:13px;">' . $couponLabel . '</p>
              <div style="font-size:32px;font-weight:800;letter-spacing:6px;color:#1a9e4a;">' . $coupon->code . '</div>
            </div>
            ' . ($minOrder ? '<p style="color:#888;font-size:13px;margin:5px 0;">📦 ' . $minOrder . '</p>' : '') . '
            <p style="color:#888;font-size:13px;margin:5px 0;">📅 ' . $expiry . '</p>
            ' . ($coupon->description ? '<p style="color:#666;font-size:14px;margin:12px 0;">' . e($coupon->description) . '</p>' : '') . '
            <a href="' . $shopUrl . '" style="display:inline-block;background:#1a9e4a;color:#fff;padding:14px 36px;border-radius:30px;text-decoration:none;font-weight:700;font-size:16px;margin-top:20px;">' . $shopNow . '</a>
          </div>
          <div style="background:#f5f5f5;padding:16px;text-align:center;font-size:12px;color:#aaa;">' . $footerText . '</div>
        </div></body></html>';
    }

    // ── Template 2: Flash / Orange ─────────────────────────────────────
    private function templateFlash(User $user, Coupon $coupon, string $discountText, string $expiry, string $minOrder, string $shopUrl, bool $isBn, string $shopNow, string $footerText, string $couponLabel): string
    {
        $headline   = $isBn ? 'এক্সক্লুসিভ অফার!' : 'EXCLUSIVE OFFER!';
        $bodyText   = $isBn
            ? 'প্রিয় <strong>' . e($user->name) . '</strong>, আপনার জন্য এক্সক্লুসিভ ছাড়!'
            : 'Dear <strong>' . e($user->name) . '</strong>, an exclusive discount just for you!';
        $btnText    = $isBn ? 'এখনই সুযোগ নিন →' : 'Grab the Deal →';

        return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
        <body style="font-family:Arial,sans-serif;background:#fff8e1;margin:0;padding:20px;">
        <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);">
          <div style="background:linear-gradient(135deg,#ff6f00,#e65100);padding:36px 30px;text-align:center;">
            <div style="font-size:40px;">⚡</div>
            <h1 style="color:#fff;margin:8px 0;font-size:24px;">' . $headline . '</h1>
            <div style="background:rgba(255,255,255,.15);border-radius:8px;padding:8px 20px;display:inline-block;margin-top:8px;">
              <span style="color:#fff;font-size:28px;font-weight:800;">' . $discountText . '</span>
            </div>
          </div>
          <div style="padding:30px;text-align:center;">
            <p style="color:#555;font-size:15px;">' . $bodyText . '</p>
            <div style="background:#fff3e0;border:2px solid #ff6f00;border-radius:12px;padding:20px;margin:20px 0;">
              <p style="margin:0 0 8px;color:#888;font-size:13px;">' . $couponLabel . '</p>
              <div style="font-size:32px;font-weight:800;letter-spacing:6px;color:#e65100;">' . $coupon->code . '</div>
            </div>
            ' . ($minOrder ? '<p style="color:#888;font-size:13px;">📦 ' . $minOrder . '</p>' : '') . '
            <p style="color:#888;font-size:13px;">📅 ' . $expiry . '</p>
            <a href="' . $shopUrl . '" style="display:inline-block;background:linear-gradient(135deg,#ff6f00,#e65100);color:#fff;padding:14px 36px;border-radius:30px;text-decoration:none;font-weight:700;font-size:16px;margin-top:20px;">' . $btnText . '</a>
          </div>
          <div style="background:#f5f5f5;padding:16px;text-align:center;font-size:12px;color:#aaa;">' . $footerText . '</div>
        </div></body></html>';
    }

    // ── Template 3: Thank You / Purple ─────────────────────────────────
    private function templateThankYou(User $user, Coupon $coupon, string $discountText, string $expiry, string $minOrder, string $shopUrl, bool $isBn, string $shopNow, string $footerText, string $couponLabel): string
    {
        $headline  = $isBn ? 'আপনাকে অসংখ্য ধন্যবাদ!' : 'Thank You So Much!';
        $subtitle  = $isBn ? 'আপনার বিশ্বস্ততার পুরস্কার' : 'A reward for your loyalty';
        $bodyText  = $isBn
            ? 'প্রিয় <strong>' . e($user->name) . '</strong>,<br>আমাদের সাথে কেনাকাটা করার জন্য আন্তরিক ধন্যবাদ। আপনার বিশ্বস্ততার পুরস্কার হিসেবে <strong style="color:#6a1b9a;">' . $discountText . '</strong> কুপন পাঠানো হলো।'
            : 'Dear <strong>' . e($user->name) . '</strong>,<br>Thank you sincerely for shopping with us. As a reward for your loyalty, here is a <strong style="color:#6a1b9a;">' . $discountText . '</strong> coupon.';
        $couponLbl = $isBn ? 'বিশেষ পুরস্কার কুপন' : 'Loyalty Reward Coupon';
        $btnText   = $isBn ? 'কেনাকাটা শুরু করুন 🛒' : 'Start Shopping 🛒';

        return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
        <body style="font-family:Arial,sans-serif;background:#f3e5f5;margin:0;padding:20px;">
        <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);">
          <div style="background:linear-gradient(135deg,#6a1b9a,#4a148c);padding:36px 30px;text-align:center;">
            <div style="font-size:40px;">🌟</div>
            <h1 style="color:#fff;margin:8px 0;font-size:24px;">' . $headline . '</h1>
            <p style="color:rgba(255,255,255,.8);margin:0;">' . $subtitle . '</p>
          </div>
          <div style="padding:30px;text-align:center;">
            <p style="color:#555;font-size:15px;line-height:1.6;">' . $bodyText . '</p>
            <div style="background:#f3e5f5;border:2px dashed #6a1b9a;border-radius:12px;padding:20px;margin:20px 0;">
              <p style="margin:0 0 8px;color:#888;font-size:13px;">' . $couponLbl . '</p>
              <div style="font-size:32px;font-weight:800;letter-spacing:6px;color:#6a1b9a;">' . $coupon->code . '</div>
            </div>
            ' . ($minOrder ? '<p style="color:#888;font-size:13px;">📦 ' . $minOrder . '</p>' : '') . '
            <p style="color:#888;font-size:13px;">📅 ' . $expiry . '</p>
            <a href="' . $shopUrl . '" style="display:inline-block;background:linear-gradient(135deg,#6a1b9a,#4a148c);color:#fff;padding:14px 36px;border-radius:30px;text-decoration:none;font-weight:700;font-size:16px;margin-top:20px;">' . $btnText . '</a>
          </div>
          <div style="background:#f5f5f5;padding:16px;text-align:center;font-size:12px;color:#aaa;">' . $footerText . '</div>
        </div></body></html>';
    }

    // ── Template 4: Shopping / Blue ────────────────────────────────────
    private function templateShopping(User $user, Coupon $coupon, string $discountText, string $expiry, string $minOrder, string $shopUrl, bool $isBn, string $shopNow, string $footerText, string $couponLabel): string
    {
        $headline  = $isBn ? 'কেনাকাটা করুন, সাশ্রয় করুন!' : 'Shop & Save!';
        $subtitle  = $isBn ? $discountText . ' পেলেন আপনি!' : 'You have got ' . $discountText . '!';
        $bodyText  = $isBn
            ? 'হ্যালো <strong>' . e($user->name) . '</strong>! আপনার পরবর্তী কেনাকাটায় সাশ্রয় করুন।'
            : 'Hello <strong>' . e($user->name) . '</strong>! Save big on your next purchase.';
        $discLbl   = $isBn ? 'ছাড়' : 'Discount';
        $codeLbl   = $isBn ? 'কুপন কোড' : 'Coupon Code';

        return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
        <body style="font-family:Arial,sans-serif;background:#e3f2fd;margin:0;padding:20px;">
        <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);">
          <div style="background:linear-gradient(135deg,#0d47a1,#1565c0);padding:36px 30px;text-align:center;">
            <div style="font-size:40px;">🛒</div>
            <h1 style="color:#fff;margin:8px 0;font-size:24px;">' . $headline . '</h1>
            <p style="color:rgba(255,255,255,.8);margin:0;">' . $subtitle . '</p>
          </div>
          <div style="padding:30px;text-align:center;">
            <p style="color:#555;font-size:15px;">' . $bodyText . '</p>
            <div style="display:flex;gap:16px;margin:20px 0;justify-content:center;flex-wrap:wrap;">
              <div style="background:#e3f2fd;border-radius:12px;padding:16px 20px;flex:1;min-width:120px;">
                <div style="font-size:13px;color:#888;margin-bottom:4px;">' . $discLbl . '</div>
                <div style="font-size:22px;font-weight:800;color:#0d47a1;">' . $discountText . '</div>
              </div>
              <div style="background:#e8f5e9;border-radius:12px;padding:16px 20px;flex:1;min-width:120px;">
                <div style="font-size:13px;color:#888;margin-bottom:4px;">' . $codeLbl . '</div>
                <div style="font-size:22px;font-weight:800;color:#1a9e4a;letter-spacing:3px;">' . $coupon->code . '</div>
              </div>
            </div>
            ' . ($minOrder ? '<p style="color:#888;font-size:13px;">📦 ' . $minOrder . '</p>' : '') . '
            <p style="color:#888;font-size:13px;">📅 ' . $expiry . '</p>
            <a href="' . $shopUrl . '" style="display:inline-block;background:linear-gradient(135deg,#0d47a1,#1565c0);color:#fff;padding:14px 36px;border-radius:30px;text-decoration:none;font-weight:700;font-size:16px;margin-top:20px;">' . $shopNow . '</a>
          </div>
          <div style="background:#f5f5f5;padding:16px;text-align:center;font-size:12px;color:#aaa;">' . $footerText . '</div>
        </div></body></html>';
    }

    // ── Template 5: Limited Time / Red ────────────────────────────────
    private function templateLimitedTime(User $user, Coupon $coupon, string $discountText, string $expiry, string $minOrder, string $shopUrl, bool $isBn, string $shopNow, string $footerText, string $couponLabel): string
    {
        $headline  = $isBn ? 'সীমিত সময়ের অফার!' : 'Limited Time Offer!';
        $badge     = $isBn ? 'মাত্র সীমিত সময়ের জন্য' : 'For a limited time only';
        $bodyText  = $isBn
            ? 'প্রিয় <strong>' . e($user->name) . '</strong>, এই অফার মিস করবেন না!'
            : 'Dear <strong>' . e($user->name) . '</strong>, don\'t miss this offer!';
        $btnText   = $isBn ? '⚡ এখনই ব্যবহার করুন' : '⚡ Use It Now';

        return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
        <body style="font-family:Arial,sans-serif;background:#ffebee;margin:0;padding:20px;">
        <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);">
          <div style="background:linear-gradient(135deg,#c62828,#b71c1c);padding:36px 30px;text-align:center;">
            <div style="font-size:40px;">🔥</div>
            <h1 style="color:#fff;margin:8px 0;font-size:24px;">' . $headline . '</h1>
            <div style="background:rgba(255,255,255,.2);border-radius:50px;padding:6px 20px;display:inline-block;margin-top:8px;">
              <span style="color:#fff;font-weight:700;">' . $badge . '</span>
            </div>
          </div>
          <div style="padding:30px;text-align:center;">
            <p style="color:#555;font-size:15px;">' . $bodyText . '</p>
            <div style="background:linear-gradient(135deg,#ffebee,#fff8f8);border:2px solid #c62828;border-radius:12px;padding:24px;margin:20px 0;">
              <div style="font-size:36px;font-weight:800;color:#c62828;">' . $discountText . '</div>
              <div style="border-top:1px dashed #c62828;margin:12px 0;"></div>
              <p style="margin:0 0 8px;color:#888;font-size:13px;">' . $couponLabel . '</p>
              <div style="font-size:30px;font-weight:800;letter-spacing:5px;color:#b71c1c;">' . $coupon->code . '</div>
            </div>
            ' . ($minOrder ? '<p style="color:#888;font-size:13px;">📦 ' . $minOrder . '</p>' : '') . '
            <p style="color:#c62828;font-size:13px;font-weight:700;">⏰ ' . $expiry . '</p>
            <a href="' . $shopUrl . '" style="display:inline-block;background:linear-gradient(135deg,#c62828,#b71c1c);color:#fff;padding:14px 36px;border-radius:30px;text-decoration:none;font-weight:700;font-size:16px;margin-top:20px;">' . $btnText . '</a>
          </div>
          <div style="background:#f5f5f5;padding:16px;text-align:center;font-size:12px;color:#aaa;">' . $footerText . '</div>
        </div></body></html>';
    }
}
