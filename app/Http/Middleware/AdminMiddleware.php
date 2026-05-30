<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth('admin')->check()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }

            // redirect()->guest() automatically saves the intended URL in session.
            // After login, redirect()->intended() will bring the admin back here.
            return redirect()->guest(route('admin.login'))
                ->with('error', app()->getLocale() === 'bn'
                    ? 'অনুগ্রহ করে এডমিন হিসেবে লগইন করুন।'
                    : 'Please login as admin to continue.');
        }

        return $next($request);
    }
}