<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminPasswordResetController extends Controller
{
    /**
     * Step 1: Show form to enter email
     */
    public function showForgotForm()
    {
        return view('admin.auth.forgot-password');
    }

    /**
     * Step 1: Send OTP to admin email
     */
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $admin = Admin::where('email', $request->email)->first();

        // Always show same message for security (don't reveal if email exists)
        if ($admin) {
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = Carbon::now()->addMinutes(10);

            // Delete old OTPs for this email
            DB::table('admin_password_resets')->where('email', $request->email)->delete();

            // Store new OTP
            DB::table('admin_password_resets')->insert([
                'email'      => $request->email,
                'otp'        => Hash::make($otp),
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Send OTP email
            try {
                Mail::send([], [], function ($msg) use ($admin, $otp, $expiresAt) {
                    $msg->to($admin->email, $admin->name)
                        ->subject('AmardoKar Admin – Password Reset OTP')
                        ->html(
                            '<div style="font-family:Inter,sans-serif;max-width:520px;margin:auto;padding:32px;background:#fff;border-radius:14px;border:1px solid #e2e8f0;">'
                            . '<div style="text-align:center;margin-bottom:24px;">'
                            . '<h2 style="color:#1a9e4a;font-size:22px;margin:0;">AmardoKar Admin</h2>'
                            . '<p style="color:#64748b;font-size:13px;margin:4px 0 0;">Password Reset Request</p>'
                            . '</div>'
                            . '<div style="background:#f0fdf4;border:2px solid #1a9e4a;border-radius:12px;padding:24px;text-align:center;margin-bottom:24px;">'
                            . '<p style="color:#374151;font-size:14px;margin:0 0 12px;">Your OTP code:</p>'
                            . '<div style="font-size:42px;font-weight:800;color:#1a9e4a;letter-spacing:10px;font-family:monospace;">' . $otp . '</div>'
                            . '<p style="color:#64748b;font-size:12px;margin:12px 0 0;">Expires at: ' . $expiresAt->format('H:i') . ' (10 minutes)</p>'
                            . '</div>'
                            . '<p style="color:#94a3b8;font-size:12px;text-align:center;margin:0;">If you did not request this, please ignore this email.</p>'
                            . '</div>'
                        );
                });
            } catch (\Exception $e) {
                // Log but don't expose error
                \Log::error('Admin OTP email failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.password.otp-form', ['email' => $request->email])
            ->with('status', 'If this email is registered, an OTP has been sent.');
    }

    /**
     * Step 2: Show OTP verification form
     */
    public function showOtpForm(Request $request)
    {
        return view('admin.auth.verify-otp', ['email' => $request->query('email', '')]);
    }

    /**
     * Step 2: Verify OTP
     */
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
            return back()->withErrors(['otp' => 'Invalid or expired OTP.'])->withInput();
        }

        // Mark as used and issue a signed token for the next step
        DB::table('admin_password_resets')->where('id', $record->id)->update(['used' => true]);

        // Store a temporary reset token in session
        $resetToken = encrypt($request->email . '|' . now()->addMinutes(15)->timestamp);
        session(['admin_reset_token' => $resetToken, 'admin_reset_email' => $request->email]);

        return redirect()->route('admin.password.reset-form');
    }

    /**
     * Step 3: Show new password form
     */
    public function showResetForm()
    {
        if (!session('admin_reset_token') || !session('admin_reset_email')) {
            return redirect()->route('admin.password.forgot')
                ->withErrors(['email' => 'Please start the reset process again.']);
        }

        // Validate token hasn't expired
        try {
            [$email, $exp] = explode('|', decrypt(session('admin_reset_token')));
            if (now()->timestamp > (int)$exp) {
                session()->forget(['admin_reset_token', 'admin_reset_email']);
                return redirect()->route('admin.password.forgot')
                    ->withErrors(['email' => 'Reset session expired. Please try again.']);
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.password.forgot');
        }

        return view('admin.auth.reset-password');
    }

    /**
     * Step 3: Update password
     */
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

        // Validate token one more time
        try {
            [$tokenEmail, $exp] = explode('|', decrypt(session('admin_reset_token')));
            if ($tokenEmail !== $email || now()->timestamp > (int)$exp) {
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
