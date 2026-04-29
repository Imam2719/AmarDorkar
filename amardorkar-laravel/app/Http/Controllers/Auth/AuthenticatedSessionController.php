<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view — now redirects to home (login is via popup).
     */
    public function create()
    {
        if (auth('web')->check())   return redirect()->route('dashboard');
        if (auth('admin')->check()) return redirect()->route('admin.dashboard');
        return redirect('/')->with('open_login_modal', true);
    }

    /**
     * Handle unified login — tries admin first, then user.
     */
    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        // Try Admin guard
        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // AJAX request হলে JSON return করো (login modal থেকে)
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'      => true,
                    'redirect'     => route('admin.dashboard'),
                    'csrf'         => csrf_token(),
                ]);
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        // Try User guard
        if (Auth::guard('web')->attempt($credentials, $remember)) {
            $sessionId = $request->session()->getId();
            $request->session()->regenerate();

            // FIX 3: Merge guest cart into user cart on login
            $userId = Auth::guard('web')->id();
            $this->mergeGuestCart($sessionId, $userId);

            // If request is AJAX (from login modal), return JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'   => true,
                    'user_name' => Auth::guard('web')->user()->name,
                    'csrf'      => csrf_token(),
                ]);
            }

            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => app()->getLocale() === 'bn'
                ? 'ইমেইল বা পাসওয়ার্ড ভুল।'
                : 'Invalid email or password.',
        ]);
    }

    /**
     * FIX 3: Merge guest (session-based) cart items into the authenticated user's cart.
     * If the user already has the same product in cart, quantities are combined.
     */
    private function mergeGuestCart(string $sessionId, int $userId): void
    {
        $guestItems = CartItem::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->get();

        foreach ($guestItems as $guestItem) {
            $existingItem = CartItem::where('user_id', $userId)
                ->where('product_id', $guestItem->product_id)
                ->first();

            if ($existingItem) {
                // Combine quantities; also sync to latest price
                $existingItem->increment('quantity', $guestItem->quantity);
            } else {
                // Transfer ownership to logged-in user
                $guestItem->update([
                    'user_id'    => $userId,
                    'session_id' => null,
                ]);
            }
        }

        // Delete any remaining guest items (already merged above)
        CartItem::where('session_id', $sessionId)->whereNull('user_id')->delete();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}