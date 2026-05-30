<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminPasswordResetController extends Controller
{
    // ── Config ────────────────────────────────────────────────────────
    private const MAX_OTP_ATTEMPTS  = 10;   // ১০ বার try করতে পারবে
    private const BLOCK_MINUTES     = 30;   // তারপর ৩০ মিনিট block
    private const OTP_EXPIRE_MINS   = 10;   // OTP মেয়াদ ১০ মিনিট
    private const RESET_TOKEN_MINS  = 15;   // reset token মেয়াদ ১৫ মিনিট

    // ── Helper: cache key per IP ──────────────────────────────────────
    private function attemptCacheKey(Request $request): string
    {
        return 'admin_otp_attempts_' . $request->ip();
    }

    private function blockCacheKey(Request $request): string
    {
        return 'admin_otp_blocked_' . $request->ip();
    }

    // ── Step 1: Show email form ───────────────────────────────────────
    public function showForgotForm()
    {
        return view('admin.auth.forgot-password');
    }

    // ── Step 1: Send OTP ─────────────────────────────────────────────
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // ── Block check ───────────────────────────────────────────────
        if (Cache::has($this->blockCacheKey($request))) {
            $remaining = (int) ceil(Cache::get($this->blockCacheKey($request) . '_exp', now()->timestamp) - now()->timestamp);
            $minutes   = max(1, (int) ceil($remaining / 60));

            return back()
                ->withInput()
                ->withErrors(['email' => "অনেকবার চেষ্টা করা হয়েছে। {$minutes} মিনিট পর আবার try করুন।"]);
        }

        // ── Attempt counter ───────────────────────────────────────────
        $attempts = (int) Cache::get($this->attemptCacheKey($request), 0);
        $attempts++;
        Cache::put($this->attemptCacheKey($request), $attempts, now()->addMinutes(self::BLOCK_MINUTES));

        // ── Block করো যদি ১০ এর বেশি ────────────────────────────────
        if ($attempts > self::MAX_OTP_ATTEMPTS) {
            Cache::put($this->blockCacheKey($request), true, now()->addMinutes(self::BLOCK_MINUTES));
            Cache::put($this->blockCacheKey($request) . '_exp', now()->addMinutes(self::BLOCK_MINUTES)->timestamp, now()->addMinutes(self::BLOCK_MINUTES));
            Cache::forget($this->attemptCacheKey($request));

            Log::warning('Admin OTP brute-force blocked', [
                'ip'    => $request->ip(),
                'email' => $request->email,
            ]);

            return back()
                ->withInput()
                ->withErrors(['email' => 'অনেকবার চেষ্টা করা হয়েছে। ' . self::BLOCK_MINUTES . ' মিনিট পর আবার try করুন।']);
        }

        // ── Admin খোঁজো ──────────────────────────────────────────────
        $admin = Admin::where('email', $request->email)->first();

        if ($admin) {
            $otp       = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = Carbon::now()->addMinutes(self::OTP_EXPIRE_MINS);

            // ── Mail পাঠাও ───────────────────────────────────────────
            try {
                Mail::send([], [], function ($msg) use ($admin, $otp, $expiresAt) {
                    $msg->to($admin->email, $admin->name)
                        ->from(config('mail.from.address'), config('mail.from.name'))
                        ->subject('AmardoKar Admin – Password Reset OTP')
                        ->html(
                            '<div style="font-family:Inter,sans-serif;max-width:520px;margin:auto;padding:32px;'
                            . 'background:#fff;border-radius:14px;border:1px solid #e2e8f0;">'

                            . '<div style="text-align:center;margin-bottom:24px;">'
                            . '<h2 style="color:#1a9e4a;font-size:22px;margin:0;">AmardoKar Admin</h2>'
                            . '<p style="color:#64748b;font-size:13px;margin:4px 0 0;">Password Reset Request</p>'
                            . '</div>'

                            . '<div style="background:#f0fdf4;border:2px solid #1a9e4a;border-radius:12px;'
                            . 'padding:24px;text-align:center;margin-bottom:24px;">'
                            . '<p style="color:#374151;font-size:14px;margin:0 0 12px;">Your OTP code:</p>'
                            . '<div style="font-size:42px;font-weight:800;color:#1a9e4a;'
                            . 'letter-spacing:10px;font-family:monospace;">' . $otp . '</div>'
                            . '<p style="color:#64748b;font-size:12px;margin:12px 0 0;">'
                            . 'Expires at: ' . $expiresAt->format('H:i') . ' (' . self::OTP_EXPIRE_MINS . ' minutes)</p>'
                            . '</div>'

                            . '<p style="color:#94a3b8;font-size:12px;text-align:center;margin:0;">'
                            . 'If you did not request this, please ignore this email.</p>'
                            . '</div>'
                        );
                });

                Log::info('Admin OTP email sent', ['email' => $admin->email]);

            } catch (\Exception $e) {
                Log::error('Admin OTP email failed', [
                    'admin_email' => $admin->email,
                    'error'       => $e->getMessage(),
                ]);
            }

            // ── DB তে OTP save ────────────────────────────────────────
            try {
                DB::table('admin_password_resets')
                    ->where('email', $request->email)
                    ->delete();

                DB::table('admin_password_resets')->insert([
                    'email'      => $request->email,
                    'otp'        => Hash::make($otp),
                    'expires_at' => $expiresAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            } catch (\Exception $e) {
                Log::error('Admin OTP DB save failed', [
                    'admin_email' => $admin->email,
                    'error'       => $e->getMessage(),
                ]);
            }

            // সফল হলে attempt counter reset করো
            Cache::forget($this->attemptCacheKey($request));
        }

        return redirect()
            ->route('admin.password.otp-form', ['email' => $request->email])
            ->with('status', 'If this email is registered, an OTP has been sent.');
    }

    // ── Step 2: Show OTP form ─────────────────────────────────────────
    public function showOtpForm(Request $request)
    {
        return view('admin.auth.verify-otp', [
            'email' => $request->query('email', ''),
        ]);
    }

    // ── Step 2: Verify OTP ────────────────────────────────────────────
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
        ]);

        $record = DB::table('admin_password_resets')
            ->where('email', $request->email)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->first();

        if (!$record || !Hash::check($request->otp, $record->otp)) {
            return back()
                ->withErrors(['otp' => 'Invalid or expired OTP.'])
                ->withInput();
        }

        // OTP use করা mark করো
        DB::table('admin_password_resets')
            ->where('id', $record->id)
            ->update(['used' => true]);

        // Session এ signed token রাখো
        $resetToken = encrypt($request->email . '|' . now()->addMinutes(self::RESET_TOKEN_MINS)->timestamp);
        session([
            'admin_reset_token' => $resetToken,
            'admin_reset_email' => $request->email,
        ]);

        return redirect()->route('admin.password.reset-form');
    }

    // ── Step 3: Show new password form ───────────────────────────────
    public function showResetForm()
    {
        if (!session('admin_reset_token') || !session('admin_reset_email')) {
            return redirect()->route('admin.password.forgot')
                ->withErrors(['email' => 'Please start the reset process again.']);
        }

        try {
            [$email, $exp] = explode('|', decrypt(session('admin_reset_token')));
            if (now()->timestamp > (int) $exp) {
                session()->forget(['admin_reset_token', 'admin_reset_email']);
                return redirect()->route('admin.password.forgot')
                    ->withErrors(['email' => 'Reset session expired. Please try again.']);
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.password.forgot');
        }

        return view('admin.auth.reset-password');
    }

    // ── Step 3: Save new password ────────────────────────────────────
    public function resetPassword(Request $request)
    {
        $request->validate([
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $email = session('admin_reset_email');
        if (!$email || !session('admin_reset_token')) {
            return redirect()->route('admin.password.forgot')
                ->withErrors(['email' => 'Session expired. Please try again.']);
        }

        try {
            [$tokenEmail, $exp] = explode('|', decrypt(session('admin_reset_token')));
            if ($tokenEmail !== $email || now()->timestamp > (int) $exp) {
                session()->forget(['admin_reset_token', 'admin_reset_email']);
                return redirect()->route('admin.password.forgot')
                    ->withErrors(['email' => 'Reset session expired.']);
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.password.forgot');
        }

        $admin = Admin::where('email', $email)->first();
        if (!$admin) {
            return redirect()->route('admin.password.forgot');
        }

        $admin->update(['password' => Hash::make($request->password)]);
        session()->forget(['admin_reset_token', 'admin_reset_email']);

        return redirect()->route('admin.login')
            ->with('status', 'Password reset successfully. You can now log in.');
    }
}