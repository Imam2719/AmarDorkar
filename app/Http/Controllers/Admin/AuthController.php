<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Admin login page।
     * Already logged in হলে dashboard-এ পাঠাও।
     */
    public function showLogin()
    {
        if (auth('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Admin login (POST)।
     * Success হলে intended URL-এ ফেরত যাও, না হলে dashboard।
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // redirect()->intended() — middleware যে URL save করেছিল সেখানে যাও।
            // সেটা না থাকলে dashboard-এ যাও।
            return redirect()->intended(route('admin.dashboard'));
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