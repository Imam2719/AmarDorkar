<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\UserPasswordResetController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // Registration সরিয়ে দেওয়া হয়েছে — checkout এ automatic account তৈরি হয়
    Route::get('register', fn() => redirect('/')->with('info', 'অর্ডার দিলে স্বয়ংক্রিয়ভাবে অ্যাকাউন্ট তৈরি হয়।'))->name('register');
    Route::post('register', fn() => redirect('/'));

    // Unified login — popup-based
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // ── OTP-based password reset for users ──────────────────────────
    Route::get('forgot-password',  [UserPasswordResetController::class, 'showForgotForm'])->name('user.password.forgot');
    Route::post('forgot-password', [UserPasswordResetController::class, 'sendOtp'])->name('user.password.send-otp')->middleware('throttle:20,5');
    Route::get('verify-otp',       [UserPasswordResetController::class, 'showOtpForm'])->name('user.password.otp-form');
    Route::post('verify-otp',      [UserPasswordResetController::class, 'verifyOtp'])->name('user.password.verify-otp')->middleware('throttle:20,1');
    Route::get('reset-password',   [UserPasswordResetController::class, 'showResetForm'])->name('user.password.reset-form');
    Route::post('reset-password',  [UserPasswordResetController::class, 'resetPassword'])->name('user.password.reset');

    // Keep old named routes as aliases so existing links/emails don't break
    Route::get('password/request', fn() => redirect()->route('user.password.forgot'))->name('password.request');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed','throttle:20,1'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:20,1')->name('verification.send');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});