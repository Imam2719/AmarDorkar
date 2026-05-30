<?php
// PATH: app/Http/Controllers/Admin/CouponController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendCouponEmail;
use App\Jobs\SendCouponWhatsApp;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CouponController extends Controller
{
    // ── List ──────────────────────────────────────────────────────────
    public function index()
    {
        Coupon::autoExpireToHistory();

        $activeCoupons = Coupon::withCount(['usages', 'allowedUsers'])
            ->active()->latest()->get();

        $historyCoupons = Coupon::withCount(['usages', 'allowedUsers'])
            ->history()
            ->orderByRaw('COALESCE(expired_at, expires_at) ASC')
            ->get();

        return view('admin.coupons.index', compact('activeCoupons', 'historyCoupons'));
    }

    // ── Create ────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $isBn = app()->getLocale() === 'bn';

        $request->validate([
            'code'                => 'nullable|string|max:10|unique:coupons,code',
            'type'                => 'required|in:percentage,fixed',
            'discount_value'      => 'required|numeric|min:0.01',
            'min_order_amount'    => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'expires_at'          => 'nullable|date|after:now',
            'description'         => 'nullable|string|max:255',

            // Preset-based limits
            'preset'              => 'required|in:one_per_user,unlimited,first_n,advanced',
            'global_limit'        => 'required_if:preset,first_n|nullable|integer|min:1',
            'per_user_limit'      => 'nullable|integer|min:1',
            'adv_global_limit'    => 'nullable|integer|min:1',
        ], [
            'code.unique'             => $isBn ? 'এই কোড আগে থেকেই বিদ্যমান।' : 'This coupon code already exists.',
            'discount_value.required' => $isBn ? 'ছাড়ের মান দিতে হবে।'        : 'Discount value is required.',
            'global_limit.required_if'=> $isBn ? 'কতজন পাবে সেটা দিতে হবে।'  : 'Please enter the usage limit.',
            'preset.required'         => $isBn ? 'কুপনের ধরন নির্বাচন করুন।'  : 'Please select a coupon preset.',
        ]);

        $code = $request->filled('code')
            ? strtoupper(trim($request->code))
            : Coupon::generateCode();

        // Preset → per_user_limit + global_limit resolve
        [$perUser, $global] = match ($request->preset) {
            'one_per_user' => [1,    null],
            'unlimited'    => [null, null],
            'first_n'      => [null, (int) $request->global_limit],
            'advanced'     => [
                $request->filled('per_user_limit')   ? (int) $request->per_user_limit   : null,
                $request->filled('adv_global_limit') ? (int) $request->adv_global_limit : null,
            ],
        };

        Coupon::create([
            'code'                => $code,
            'type'                => $request->type,
            'discount_value'      => $request->discount_value,
            'min_order_amount'    => $request->min_order_amount ?? 0,
            'max_discount_amount' => $request->max_discount_amount,
            'per_user_limit'      => $perUser,
            'global_limit'        => $global,
            'expires_at'          => $request->expires_at,
            'description'         => $request->description,
            'is_active'           => true,
        ]);

        $msg = $isBn
            ? "✅ কুপন {$code} সফলভাবে তৈরি হয়েছে!"
            : "✅ Coupon {$code} created successfully!";

        return back()->with('success', $msg);
    }

    // ── Toggle ────────────────────────────────────────────────────────
    public function toggle(Coupon $coupon)
    {
        $newActive = !$coupon->is_active;

        if (!$newActive && $coupon->expired_at === null) {
            $coupon->update(['is_active' => false, 'expired_at' => now()]);
        } else {
            $coupon->update([
                'is_active'  => $newActive,
                'expired_at' => $newActive ? null : $coupon->expired_at,
            ]);
        }

        $isBn = app()->getLocale() === 'bn';
        $msg  = $coupon->fresh()->is_active
            ? ($isBn ? 'কুপন সক্রিয় করা হয়েছে।'                          : 'Coupon activated.')
            : ($isBn ? 'কুপন নিষ্ক্রিয় করা হয়েছে এবং ইতিহাসে চলে গেছে।' : 'Coupon deactivated and moved to history.');

        return back()->with('success', $msg);
    }

    // ── Delete ────────────────────────────────────────────────────────
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        $msg = app()->getLocale() === 'bn' ? 'কুপন মুছে ফেলা হয়েছে।' : 'Coupon deleted.';
        return back()->with('success', $msg);
    }

    // ── Bulk delete history ───────────────────────────────────────────
    public function bulkDeleteExpired()
    {
        $isBn  = app()->getLocale() === 'bn';
        $count = Coupon::history()->count();

        if ($count === 0) {
            return back()->with('error', $isBn
                ? 'মুছার জন্য কোনো পুরনো কুপন নেই।'
                : 'No expired coupons to delete.');
        }

        Coupon::history()->delete();

        return back()->with('success', $isBn
            ? "✅ {$count}টি মেয়াদোত্তীর্ণ কুপন মুছে ফেলা হয়েছে।"
            : "✅ {$count} expired coupon(s) deleted.");
    }

    // ── Send form ─────────────────────────────────────────────────────
    public function sendForm()
    {
        $coupons      = Coupon::active()->latest()->get();
        $allCustomers = User::select('id', 'name', 'email', 'mobile')->get();
        $monthlyTop   = $this->topBuyersQuery(30, 50);
        $weeklyTop    = $this->topBuyersQuery(7, 50);
        $yearlyTop    = $this->topBuyersQuery(365, 50);

        $highestPurchase = User::select(
                'users.id', 'users.name', 'users.email', 'users.mobile',
                DB::raw('SUM(orders.total_amount) as total_spent'),
                DB::raw('COUNT(orders.id) as order_count')
            )
            ->join('orders', 'orders.user_id', '=', 'users.id')
            ->whereIn('orders.status', ['delivered', 'processing', 'shipped'])
            ->groupBy('users.id', 'users.name', 'users.email', 'users.mobile')
            ->orderByDesc('total_spent')
            ->limit(20)->get();

        return view('admin.coupons.send', compact(
            'coupons', 'allCustomers', 'monthlyTop', 'weeklyTop', 'yearlyTop', 'highestPurchase'
        ));
    }

    // ── Send ──────────────────────────────────────────────────────────
    public function send(Request $request)
    {
        $isBn = app()->getLocale() === 'bn';

        $request->validate([
            'coupon_id'      => 'required|exists:coupons,id',
            'channels'       => 'required|array|min:1',
            'channels.*'     => 'in:email,whatsapp',
            'template'       => 'required|in:1,2,3,4,5',
            'recipient_type' => 'required|in:all,selected,monthly_top,weekly_top,yearly_top,highest_purchase',
            'user_ids'       => 'required_if:recipient_type,selected|array',
            // send_unique এখন preset থেকে auto-decide — তবুও override রাখা হলো
            'send_unique'    => 'nullable|boolean',
        ]);

        $templateCoupon = Coupon::findOrFail($request->coupon_id);
        $users          = $this->resolveRecipients($request);

        if ($users->isEmpty()) {
            return back()->with('error', $isBn ? 'কোনো গ্রাহক পাওয়া যায়নি।' : 'No customers found.');
        }

        /**
         * Auto-decide unique code:
         * per_user_limit=1 হলে → unique code না দিলে ২য় user same code use করতে গেলে
         * "আপনি আগেই use করেছেন" error দেখাবে — সেটা confusing।
         * তাই per_user_limit=1 হলে সবসময় unique।
         * Admin override করতে চাইলে send_unique explicit পাঠাতে পারে।
         */
        $sendUnique = $request->has('send_unique')
            ? (bool) $request->send_unique
            : ($templateCoupon->per_user_limit === 1);

        $channels = $request->channels;
        $template = (int) $request->template;

        $emailOk = $emailFail = $waOk = $waFail = $noEmail = $noPhone = 0;

        foreach ($users as $user) {
            $coupon = $sendUnique
                ? $this->cloneCouponForUser($templateCoupon, $user->id)
                : $templateCoupon;

            if (in_array('email', $channels)) {
                if ($user->email) {
                    try   { SendCouponEmail::dispatch($coupon, $user, $template, app()->getLocale()); $emailOk++; }
                    catch (\Exception $e) { Log::error("Coupon email [{$user->email}]: " . $e->getMessage()); $emailFail++; }
                } else { $noEmail++; }
            }

            if (in_array('whatsapp', $channels)) {
                if ($user->mobile) {
                    try   { SendCouponWhatsApp::dispatch($coupon, $user, $template); $waOk++; }
                    catch (\Exception $e) { Log::error("Coupon WA [{$user->mobile}]: " . $e->getMessage()); $waFail++; }
                } else { $noPhone++; }
            }
        }

        $parts = array_filter([
            $emailOk   ? ($isBn ? "{$emailOk}টি ইমেইল পাঠানো হয়েছে"  : "{$emailOk} email(s) sent")    : null,
            $emailFail ? ($isBn ? "{$emailFail}টি ইমেইল ব্যর্থ"        : "{$emailFail} email(s) failed") : null,
            $noEmail   ? ($isBn ? "{$noEmail} জনের ইমেইল নেই"          : "{$noEmail} without email")     : null,
            $waOk      ? ($isBn ? "{$waOk}টি WhatsApp পাঠানো হয়েছে"   : "{$waOk} WhatsApp sent")        : null,
            $waFail    ? ($isBn ? "{$waFail}টি WhatsApp ব্যর্থ"         : "{$waFail} WhatsApp failed")    : null,
            $noPhone   ? ($isBn ? "{$noPhone} জনের মোবাইল নেই"         : "{$noPhone} without mobile")    : null,
        ]);

        $uniqueNote = $sendUnique
            ? ($isBn ? ' (প্রতিজনের জন্য আলাদা কোড তৈরি)' : ' (unique code per customer)')
            : '';

        $msg = ($isBn ? $users->count() . ' জন — ' : $users->count() . ' customer(s) — ')
             . implode(', ', $parts) . $uniqueNote . '.';

        return back()->with('success', $msg);
    }

    // ── Private helpers ───────────────────────────────────────────────

    /** Template coupon clone করে user-এর সাথে attach করো */
    private function cloneCouponForUser(Coupon $template, int $userId): Coupon
    {
        $coupon = Coupon::create([
            'code'                => Coupon::generateCode(),
            'type'                => $template->type,
            'discount_value'      => $template->discount_value,
            'min_order_amount'    => $template->min_order_amount,
            'max_discount_amount' => $template->max_discount_amount,
            'per_user_limit'      => $template->per_user_limit,
            'global_limit'        => $template->global_limit,
            'expires_at'          => $template->expires_at,
            'description'         => $template->description,
            'is_active'           => true,
        ]);
        $coupon->allowedUsers()->attach($userId);

        return $coupon;
    }

    private function topBuyersQuery(int $days, int $limit)
    {
        return User::select(
                'users.id', 'users.name', 'users.email', 'users.mobile',
                DB::raw('SUM(orders.total_amount) as total_spent')
            )
            ->join('orders', 'orders.user_id', '=', 'users.id')
            ->whereIn('orders.status', ['delivered', 'processing', 'shipped'])
            ->where('orders.created_at', '>=', now()->subDays($days))
            ->groupBy('users.id', 'users.name', 'users.email', 'users.mobile')
            ->orderByDesc('total_spent')
            ->limit($limit)->get();
    }

    private function resolveRecipients(Request $request)
    {
        return match ($request->recipient_type) {
            'all'      => User::where(fn($q) => $q->whereNotNull('email')->orWhereNotNull('mobile'))->get(),
            'selected' => User::whereIn('id', $request->user_ids ?? [])->get(),
            'monthly_top'      => $this->topBuyersQuery(30, 50),
            'weekly_top'       => $this->topBuyersQuery(7, 50),
            'yearly_top'       => $this->topBuyersQuery(365, 50),
            'highest_purchase' => User::select('users.*')
                ->join('orders', 'orders.user_id', '=', 'users.id')
                ->whereIn('orders.status', ['delivered', 'processing', 'shipped'])
                ->groupBy('users.id')->orderByRaw('SUM(orders.total_amount) DESC')
                ->limit(20)->get(),
            default => collect(),
        };
    }
}
