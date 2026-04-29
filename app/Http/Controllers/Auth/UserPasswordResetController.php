<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UserPasswordResetController extends Controller
{
    // ── Step 1: Show email form ───────────────────────────────────────
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    // ── Step 1: Send OTP ──────────────────────────────────────────────
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        // Security: always show same message whether email exists or not
        if ($user) {
            $otp       = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = Carbon::now()->addMinutes(10);

            // Delete old OTPs for this email
            DB::table('user_password_resets')
                ->where('email', $request->email)
                ->delete();

            // Store new hashed OTP
            DB::table('user_password_resets')->insert([
                'email'      => $request->email,
                'otp'        => Hash::make($otp),
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Send OTP email
            try {
                Mail::send([], [], function ($msg) use ($user, $otp, $expiresAt) {
                    $html = '<!DOCTYPE html>
<html lang="bn">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:\'Segoe UI\',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:32px 16px;">
<tr><td align="center">
<table width="480" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:18px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.10);max-width:480px;width:100%;">

  <!-- Header -->
  <tr>
    <td style="background:linear-gradient(135deg,#1a9e4a 0%,#15803d 100%);padding:28px;text-align:center;">
      <div style="font-size:24px;font-weight:900;color:#fff;letter-spacing:-1px;">🛒 AmardoKar</div>
      <div style="color:rgba(255,255,255,0.85);font-size:13px;margin-top:5px;">পাসওয়ার্ড রিসেট অনুরোধ</div>
    </td>
  </tr>

  <!-- Body -->
  <tr>
    <td style="padding:28px 32px;">
      <p style="font-size:15px;color:#1e293b;margin:0 0 6px;">হ্যালো <strong style="color:#1a9e4a;">' . e($user->name) . '</strong>,</p>
      <p style="font-size:13px;color:#64748b;margin:0 0 24px;">আপনার পাসওয়ার্ড রিসেটের জন্য নিচের OTP কোডটি ব্যবহার করুন।</p>

      <!-- OTP Box -->
      <div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:2px solid #86efac;border-radius:14px;padding:24px;text-align:center;margin-bottom:22px;">
        <div style="font-size:11px;color:#166534;font-weight:700;text-transform:uppercase;letter-spacing:2px;margin-bottom:10px;">আপনার OTP কোড</div>
        <div style="font-size:44px;font-weight:900;color:#15803d;letter-spacing:14px;font-family:monospace;">' . $otp . '</div>
        <div style="font-size:12px;color:#64748b;margin-top:10px;">⏱ মেয়াদ শেষ: ' . $expiresAt->format('h:i A') . ' (১০ মিনিট)</div>
      </div>

      <div style="background:#fef9c3;border:1px solid #fde68a;border-radius:10px;padding:14px 16px;font-size:12px;color:#92400e;">
        ⚠️ এই কোডটি কারো সাথে শেয়ার করবেন না। AmardoKar কখনো OTP চায় না।
      </div>
    </td>
  </tr>

  <!-- Footer -->
  <tr>
    <td style="padding:18px 32px;border-top:1px solid #f1f5f9;text-align:center;">
      <p style="font-size:12px;color:#94a3b8;margin:0;">আপনি যদি এই অনুরোধ না করে থাকেন, এই ইমেইলটি উপেক্ষা করুন।</p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>';

                    $msg->to($user->email, $user->name)
                        ->subject('🔑 পাসওয়ার্ড রিসেট OTP – AmardoKar')
                        ->html($html);
                });
            } catch (\Exception $e) {
                Log::error('User OTP email failed: ' . $e->getMessage());
            }
        }

        return redirect()
            ->route('user.password.otp-form', ['email' => $request->email])
            ->with('status', 'যদি এই ইমেইল নিবন্ধিত থাকে, একটি OTP পাঠানো হয়েছে।');
    }

    // ── Step 2: Show OTP form ─────────────────────────────────────────
    public function showOtpForm(Request $request)
    {
        return view('auth.verify-otp', [
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

        $record = DB::table('user_password_resets')
            ->where('email', $request->email)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->first();

        if (!$record || !Hash::check($request->otp, $record->otp)) {
            return back()
                ->withErrors(['otp' => 'OTP কোডটি ভুল অথবা মেয়াদ শেষ হয়ে গেছে।'])
                ->withInput();
        }

        // Mark OTP as used
        DB::table('user_password_resets')
            ->where('id', $record->id)
            ->update(['used' => true]);

        // Store signed session token for next step
        $resetToken = encrypt($request->email . '|' . now()->addMinutes(15)->timestamp);
        session([
            'user_reset_token' => $resetToken,
            'user_reset_email' => $request->email,
        ]);

        return redirect()->route('user.password.reset-form');
    }

    // ── Step 3: Show new password form ───────────────────────────────
    public function showResetForm()
    {
        if (!session('user_reset_token') || !session('user_reset_email')) {
            return redirect()->route('user.password.forgot')
                ->withErrors(['email' => 'অনুগ্রহ করে আবার শুরু করুন।']);
        }

        try {
            [$email, $exp] = explode('|', decrypt(session('user_reset_token')));
            if (now()->timestamp > (int)$exp) {
                session()->forget(['user_reset_token', 'user_reset_email']);
                return redirect()->route('user.password.forgot')
                    ->withErrors(['email' => 'Session মেয়াদ শেষ। আবার চেষ্টা করুন।']);
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
        if (!$email || !session('user_reset_token')) {
            return redirect()->route('user.password.forgot')
                ->withErrors(['email' => 'Session মেয়াদ শেষ। আবার চেষ্টা করুন।']);
        }

        try {
            [$tokenEmail, $exp] = explode('|', decrypt(session('user_reset_token')));
            if ($tokenEmail !== $email || now()->timestamp > (int)$exp) {
                session()->forget(['user_reset_token', 'user_reset_email']);
                return redirect()->route('user.password.forgot')
                    ->withErrors(['email' => 'Session মেয়াদ শেষ।']);
            }
        } catch (\Exception $e) {
            return redirect()->route('user.password.forgot');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('user.password.forgot');
        }

        $user->update(['password' => Hash::make($request->password)]);
        session()->forget(['user_reset_token', 'user_reset_email']);

        return redirect()->route('login')
            ->with('status', 'পাসওয়ার্ড সফলভাবে পরিবর্তন হয়েছে! এখন লগইন করুন।');
    }
}
