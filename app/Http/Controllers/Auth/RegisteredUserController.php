<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return redirect('/')->with('open_login_modal', true);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'mobile'   => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            // Bangla custom validation messages
            'name.required'        => 'নাম দেওয়া আবশ্যক।',
            'email.required'       => 'ইমেইল দেওয়া আবশ্যক।',
            'email.email'          => 'সঠিক ইমেইল ঠিকানা দিন।',
            'email.unique'         => 'এই ইমেইল দিয়ে আগেই অ্যাকাউন্ট আছে। অন্য ইমেইল ব্যবহার করুন অথবা লগইন করুন।',
            'password.required'    => 'পাসওয়ার্ড দেওয়া আবশ্যক।',
            'password.confirmed'   => 'পাসওয়ার্ড দুটো মিলছে না।',
            'password.min'         => 'পাসওয়ার্ড কমপক্ষে ৮ অক্ষর হতে হবে।',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'mobile'   => $request->mobile,
            'password' => Hash::make($request->password),
        ]);

        // FIX: try-catch so mail/queue failure does NOT block registration
        try {
            event(new Registered($user));
        } catch (\Exception $e) {
            Log::error('Registration event failed: ' . $e->getMessage());
        }

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}