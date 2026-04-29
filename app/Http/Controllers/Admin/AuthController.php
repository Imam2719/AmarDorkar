<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Old admin login page → redirect to home with popup flag.
     */
    public function showLogin()
    {
        if (auth('admin')->check()) return redirect()->route('admin.dashboard');
        if (auth('web')->check())   return redirect()->route('dashboard');
        return redirect('/')->with('open_login_modal', true);
    }

    /**
     * Admin-only login (POST from the old admin form, kept for compatibility).
     * The main login is now handled by Auth\AuthenticatedSessionController.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        return back()
            ->withErrors(['email' => app()->getLocale() === 'bn' ? 'ইমেইল বা পাসওয়ার্ড ভুল।' : 'Invalid credentials.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}