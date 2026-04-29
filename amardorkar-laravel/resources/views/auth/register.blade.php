<x-guest-layout>
    <h2>রেজিস্ট্রেশন করুন</h2>
    <p class="subtitle">নতুন অ্যাকাউন্ট তৈরি করুন</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">নাম / Name</label>
            <input id="name" type="text" name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name') }}" required autofocus autocomplete="name"
                   placeholder="আপনার পূর্ণ নাম">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">ইমেইল / Email</label>
            <input id="email" type="email" name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" required autocomplete="username"
                   placeholder="your@email.com">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">পাসওয়ার্ড / Password</label>
            <input id="password" type="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   required autocomplete="new-password" placeholder="কমপক্ষে ৮ অক্ষর">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">পাসওয়ার্ড নিশ্চিত করুন</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   class="form-control" required autocomplete="new-password" placeholder="••••••••">
        </div>

        <button type="submit" class="btn-auth">
            <i class="fas fa-user-plus me-2"></i>রেজিস্ট্রেশন করুন
        </button>
    </form>

    <div class="auth-links">
        ইতিমধ্যে অ্যাকাউন্ট আছে? <a href="{{ route('login') }}">লগইন করুন</a>
    </div>
</x-guest-layout>
