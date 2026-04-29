<x-guest-layout>
    {{-- ── Header ────────────────────────────────────────────────── --}}
    <div style="text-align:center;margin-bottom:24px;">
        <div style="width:56px;height:56px;background:linear-gradient(135deg,#1a9e4a,#15803d);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
            <i class="fas fa-lock" style="color:#fff;font-size:20px;"></i>
        </div>
        <h2 style="font-size:20px;font-weight:700;color:#1e293b;margin:0 0 4px;">পাসওয়ার্ড রিসেট</h2>
        <p style="font-size:13px;color:#64748b;margin:0;">আপনার ইমেইলে একটি OTP কোড পাঠানো হবে</p>
    </div>

    @if(session('status'))
        <div class="alert alert-success small mb-3" style="border-radius:8px;font-size:13px;">
            <i class="fas fa-check-circle me-1"></i> {{ session('status') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger small mb-3" style="border-radius:8px;font-size:13px;">
            <i class="fas fa-exclamation-circle me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('user.password.send-otp') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" style="font-size:13px;font-weight:500;color:#374151;">ইমেইল ঠিকানা</label>
            <div style="position:relative;">
                <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;">
                    <i class="fas fa-envelope" style="font-size:13px;"></i>
                </span>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    placeholder="example@gmail.com"
                    class="form-control @error('email') is-invalid @enderror"
                    style="padding-left:36px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;">
                @error('email')<div class="invalid-feedback" style="font-size:12px;">{{ $message }}</div>@enderror
            </div>
        </div>
        <button type="submit" class="btn-auth mt-2">
            <i class="fas fa-paper-plane me-2"></i>OTP পাঠান
        </button>
    </form>

    <div style="text-align:center;margin-top:20px;font-size:13px;color:#64748b;">
        মনে পড়ে গেছে?
        <a href="{{ route('login') }}" style="color:#1a9e4a;font-weight:500;text-decoration:none;">লগইন করুন</a>
    </div>
</x-guest-layout>
