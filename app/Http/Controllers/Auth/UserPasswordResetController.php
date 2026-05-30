<?php
// PATH: app/Http/Controllers/Auth/UserPasswordResetController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserPasswordResetController extends Controller
{
    private const OTP_EXPIRE_MINS  = 10;
    private const RESET_TOKEN_MINS = 15;

    // ── Step 1: Show email form ───────────────────────────────────────
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    // ── Step 1: Send OTP — Admin ও User দুটোই check করে ─────────────
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = $request->email;

        $admin = Admin::where('email', $email)->first();

        if ($admin) {
            $this->sendAdminOtp($admin);

            return redirect()
                ->route('user.password.otp-form', ['email' => $email, 'type' => 'admin'])
                ->with('status', app()->getLocale() === 'bn'
                    ? 'যদি এই ইমেইল নিবন্ধিত থাকে, একটি OTP পাঠানো হয়েছে।'
                    : 'If this email is registered, an OTP has been sent.');
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $this->sendUserOtp($user);
        }

        return redirect()
            ->route('user.password.otp-form', ['email' => $email, 'type' => 'user'])
            ->with('status', app()->getLocale() === 'bn'
                ? 'যদি এই ইমেইল নিবন্ধিত থাকে, একটি OTP পাঠানো হয়েছে।'
                : 'If this email is registered, an OTP has been sent.');
    }

    // ── Step 2: Show OTP form ─────────────────────────────────────────
    public function showOtpForm(Request $request)
    {
        return view('auth.verify-otp', [
            'email' => $request->query('email', ''),
            'type'  => $request->query('type', 'user'),
        ]);
    }

    // ── Step 2: Verify OTP ────────────────────────────────────────────
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
            'type'  => 'nullable|in:admin,user',
        ]);

        $type  = $request->input('type', 'user');
        $table = $type === 'admin' ? 'admin_password_resets' : 'user_password_resets';

        $record = DB::table($table)
            ->where('email', $request->email)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->first();

        if (!$record || !Hash::check($request->otp, $record->otp)) {
            return back()
                ->withErrors(['otp' => app()->getLocale() === 'bn'
                    ? 'OTP কোডটি ভুল অথবা মেয়াদ শেষ হয়ে গেছে।'
                    : 'The OTP code is incorrect or has expired.'])
                ->withInput();
        }

        DB::table($table)->where('id', $record->id)->update(['used' => true]);

        $resetToken = encrypt($request->email . '|' . now()->addMinutes(self::RESET_TOKEN_MINS)->timestamp . '|' . $type);
        session([
            'user_reset_token' => $resetToken,
            'user_reset_email' => $request->email,
            'user_reset_type'  => $type,
        ]);

        return redirect()->route('user.password.reset-form');
    }

    // ── Step 3: Show new password form ───────────────────────────────
    public function showResetForm()
    {
        if (!session('user_reset_token') || !session('user_reset_email')) {
            return redirect()->route('user.password.forgot')
                ->withErrors(['email' => app()->getLocale() === 'bn'
                    ? 'অনুগ্রহ করে আবার শুরু করুন।'
                    : 'Please start again.']);
        }

        try {
            $parts = explode('|', decrypt(session('user_reset_token')));
            $exp   = (int) ($parts[1] ?? 0);
            if (now()->timestamp > $exp) {
                session()->forget(['user_reset_token', 'user_reset_email', 'user_reset_type']);
                return redirect()->route('user.password.forgot')
                    ->withErrors(['email' => app()->getLocale() === 'bn'
                        ? 'Session মেয়াদ শেষ। আবার চেষ্টা করুন।'
                        : 'Session expired. Please try again.']);
            }
        } catch (\Exception $e) {
            return redirect()->route('user.password.forgot');
        }

        return view('auth.reset-password-otp');
    }

    // ── Step 3: Save new password ────────────────────────────────────
    public function resetPassword(Request $request)
    {
        $request->validate([
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $email = session('user_reset_email');
        $type  = session('user_reset_type', 'user');

        if (!$email || !session('user_reset_token')) {
            return redirect()->route('user.password.forgot')
                ->withErrors(['email' => app()->getLocale() === 'bn'
                    ? 'Session মেয়াদ শেষ। আবার চেষ্টা করুন।'
                    : 'Session expired. Please try again.']);
        }

        try {
            $parts      = explode('|', decrypt(session('user_reset_token')));
            $tokenEmail = $parts[0] ?? '';
            $exp        = (int) ($parts[1] ?? 0);

            if ($tokenEmail !== $email || now()->timestamp > $exp) {
                session()->forget(['user_reset_token', 'user_reset_email', 'user_reset_type']);
                return redirect()->route('user.password.forgot')
                    ->withErrors(['email' => app()->getLocale() === 'bn'
                        ? 'Session মেয়াদ শেষ।'
                        : 'Session expired.']);
            }
        } catch (\Exception $e) {
            return redirect()->route('user.password.forgot');
        }

        // ── Admin password update ─────────────────────────────────────
        if ($type === 'admin') {
            $admin = Admin::where('email', $email)->first();
            if (!$admin) return redirect()->route('user.password.forgot');
            $admin->update(['password' => Hash::make($request->password)]);
            session()->forget(['user_reset_token', 'user_reset_email', 'user_reset_type']);

            return redirect()->route('home')
                ->with('status', app()->getLocale() === 'bn'
                    ? 'পাসওয়ার্ড সফলভাবে পরিবর্তন হয়েছে! এখন লগইন করুন।'
                    : 'Password changed successfully! Please login now.');
        }

        // ── User password update ──────────────────────────────────────
        $user = User::where('email', $email)->first();
        if (!$user) return redirect()->route('user.password.forgot');

        $user->update(['password' => Hash::make($request->password)]);
        session()->forget(['user_reset_token', 'user_reset_email', 'user_reset_type']);

        return redirect()->route('login')
            ->with('status', app()->getLocale() === 'bn'
                ? 'পাসওয়ার্ড সফলভাবে পরিবর্তন হয়েছে! এখন লগইন করুন।'
                : 'Password changed successfully! Please login now.');
    }

    // ── Private: Admin OTP পাঠাও (সবসময় English — admin panel) ──────
    private function sendAdminOtp(Admin $admin): void
    {
        $otp       = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = Carbon::now()->addMinutes(self::OTP_EXPIRE_MINS);

        // ── Dhaka timezone, 12-hour ──────────────────────────────────
        $expiresAtDhaka = Carbon::now()
            ->timezone('Asia/Dhaka')
            ->addMinutes(self::OTP_EXPIRE_MINS)
            ->format('g:i A');

        try {
            DB::table('admin_password_resets')->where('email', $admin->email)->delete();
            DB::table('admin_password_resets')->insert([
                'email'      => $admin->email,
                'otp'        => Hash::make($otp),
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Admin OTP DB save failed', ['error' => $e->getMessage()]);
        }

        try {
            Mail::send([], [], function ($msg) use ($admin, $otp, $expiresAtDhaka) {
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
                        . 'Expires at: ' . $expiresAtDhaka . ' (' . self::OTP_EXPIRE_MINS . ' minutes)</p>'
                        . '</div>'
                        . '<p style="color:#94a3b8;font-size:12px;text-align:center;margin:0;">'
                        . 'If you did not request this, please ignore this email.</p>'
                        . '</div>'
                    );
            });
            Log::info('Admin OTP email sent', ['email' => $admin->email]);
        } catch (\Exception $e) {
            Log::error('Admin OTP email failed', ['error' => $e->getMessage()]);
        }
    }

    // ── Private: User OTP পাঠাও (language-based) ─────────────────────
    private function sendUserOtp(User $user): void
    {
        $otp       = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = Carbon::now()->addMinutes(self::OTP_EXPIRE_MINS);
        $isBn      = app()->getLocale() !== 'en';   // controller context এ session available

        // ── Dhaka timezone, 12-hour ──────────────────────────────────
        $expiresAtDhaka = Carbon::now()
            ->timezone('Asia/Dhaka')
            ->addMinutes(self::OTP_EXPIRE_MINS)
            ->format('g:i A');

        try {
            DB::table('user_password_resets')->where('email', $user->email)->delete();
            DB::table('user_password_resets')->insert([
                'email'      => $user->email,
                'otp'        => Hash::make($otp),
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('User OTP DB save failed', ['error' => $e->getMessage()]);
        }

        try {
            Mail::send([], [], function ($msg) use ($user, $otp, $expiresAtDhaka, $isBn) {

                if ($isBn) {
                    $subject     = '🔑 পাসওয়ার্ড রিসেট OTP – AmardoKar';
                    $brandSub    = 'পাসওয়ার্ড রিসেট অনুরোধ';
                    $greeting    = 'হ্যালো <strong style="color:#1a9e4a;">' . e($user->name) . '</strong>,';
                    $body        = 'আপনার পাসওয়ার্ড রিসেটের জন্য নিচের OTP কোডটি ব্যবহার করুন।';
                    $otpLabel    = 'আপনার OTP কোড';
                    $expiryText  = '⏱ মেয়াদ শেষ: ' . $expiresAtDhaka . ' (১০ মিনিট)';
                    $warning     = '⚠️ এই কোডটি কারো সাথে শেয়ার করবেন না।';
                    $footer      = 'আপনি যদি এই অনুরোধ না করে থাকেন, এই ইমেইলটি উপেক্ষা করুন।';
                    $langAttr    = 'bn';
                } else {
                    $subject     = '🔑 Password Reset OTP – AmardoKar';
                    $brandSub    = 'Password Reset Request';
                    $greeting    = 'Hello <strong style="color:#1a9e4a;">' . e($user->name) . '</strong>,';
                    $body        = 'Use the OTP code below to reset your password.';
                    $otpLabel    = 'Your OTP Code';
                    $expiryText  = '⏱ Expires at: ' . $expiresAtDhaka . ' (10 minutes)';
                    $warning     = '⚠️ Never share this code with anyone.';
                    $footer      = 'If you did not request this, please ignore this email.';
                    $langAttr    = 'en';
                }

                $html = '<!DOCTYPE html><html lang="' . $langAttr . '"><head><meta charset="UTF-8"></head>'
                    . '<body style="margin:0;padding:0;background:#f1f5f9;font-family:\'Segoe UI\',Arial,sans-serif;">'
                    . '<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:32px 16px;">'
                    . '<tr><td align="center">'
                    . '<table width="480" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:18px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.10);max-width:480px;width:100%;">'
                    . '<tr><td style="background:linear-gradient(135deg,#1a9e4a 0%,#15803d 100%);padding:28px;text-align:center;">'
                    . '<div style="font-size:24px;font-weight:900;color:#fff;letter-spacing:-1px;">🛒 AmardoKar</div>'
                    . '<div style="color:rgba(255,255,255,0.85);font-size:13px;margin-top:5px;">' . $brandSub . '</div>'
                    . '</td></tr>'
                    . '<tr><td style="padding:28px 32px;">'
                    . '<p style="font-size:15px;color:#1e293b;margin:0 0 6px;">' . $greeting . '</p>'
                    . '<p style="font-size:13px;color:#64748b;margin:0 0 24px;">' . $body . '</p>'
                    . '<div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:2px solid #86efac;border-radius:14px;padding:24px;text-align:center;margin-bottom:22px;">'
                    . '<div style="font-size:11px;color:#166534;font-weight:700;text-transform:uppercase;letter-spacing:2px;margin-bottom:10px;">' . $otpLabel . '</div>'
                    . '<div style="font-size:44px;font-weight:900;color:#15803d;letter-spacing:14px;font-family:monospace;">' . $otp . '</div>'
                    . '<div style="font-size:12px;color:#64748b;margin-top:10px;">' . $expiryText . '</div>'
                    . '</div>'
                    . '<div style="background:#fef9c3;border:1px solid #fde68a;border-radius:10px;padding:14px 16px;font-size:12px;color:#92400e;">' . $warning . '</div>'
                    . '</td></tr>'
                    . '<tr><td style="padding:18px 32px;border-top:1px solid #f1f5f9;text-align:center;">'
                    . '<p style="font-size:12px;color:#94a3b8;margin:0;">' . $footer . '</p>'
                    . '</td></tr>'
                    . '</table></td></tr></table></body></html>';

                $msg->to($user->email, $user->name)
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject($subject)
                    ->html($html);
            });
        } catch (\Exception $e) {
            Log::error('User OTP email failed', ['error' => $e->getMessage()]);
        }
    }
}
