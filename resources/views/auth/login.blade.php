<x-guest-layout>
    <h2>লগইন করুন</h2>
    <p class="subtitle">আপনার অ্যাকাউন্টে প্রবেশ করুন</p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-3" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div class="mb-3">
            <label for="email" class="form-label">ইমেইল / Email</label>
            <input id="email" type="email" name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" required autofocus autocomplete="username"
                   placeholder="your@email.com">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label">পাসওয়ার্ড / Password</label>
            <input id="password" type="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   required autocomplete="current-password"
                   placeholder="••••••••">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember & Forgot -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <label class="d-flex align-items-center gap-2" style="font-size:13px;cursor:pointer;">
                <input type="checkbox" name="remember" id="remember_me" class="form-check-input" style="margin:0;">
                <span style="color:#64748b;">মনে রাখুন</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" style="font-size:13px;color:#1a9e4a;text-decoration:none;">
                    পাসওয়ার্ড ভুলে গেছেন?
                </a>
            @endif
        </div>

        <button type="submit" class="btn-auth">
            <i class="fas fa-sign-in-alt me-2"></i>লগইন করুন
        </button>
    </form>

    @if (Route::has('register'))
    <div class="auth-links">
        নতুন অ্যাকাউন্ট নেই? <a href="{{ route('register') }}">রেজিস্ট্রেশন করুন</a>
    </div>
    @endif
</x-guest-layout>
