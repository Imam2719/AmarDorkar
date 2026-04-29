<x-guest-layout>
    {{-- ── Header ────────────────────────────────────────────────── --}}
    <div style="text-align:center;margin-bottom:24px;">
        <div style="width:56px;height:56px;background:linear-gradient(135deg,#1a9e4a,#15803d);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
            <i class="fas fa-key" style="color:#fff;font-size:20px;"></i>
        </div>
        <h2 style="font-size:20px;font-weight:700;color:#1e293b;margin:0 0 4px;">OTP যাচাই করুন</h2>
        <p style="font-size:13px;color:#64748b;margin:0;">
            <strong style="color:#1a9e4a;">{{ $email }}</strong> এ পাঠানো ৬-সংখ্যার কোড দিন
        </p>
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

    <form method="POST" action="{{ route('user.password.verify-otp') }}">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">

        <div class="mb-3">
            <label class="form-label" style="font-size:13px;font-weight:500;color:#374151;">৬-সংখ্যার OTP কোড</label>
            <input
                type="text"
                name="otp"
                maxlength="6"
                pattern="\d{6}"
                required
                autofocus
                autocomplete="off"
                placeholder="000000"
                class="form-control text-center @error('otp') is-invalid @enderror"
                style="font-size:32px;font-weight:800;letter-spacing:12px;font-family:monospace;border:2px solid #e2e8f0;border-radius:10px;padding:14px 10px;color:#1a9e4a;">
            @error('otp')<div class="invalid-feedback text-center" style="font-size:12px;">{{ $message }}</div>@enderror
        </div>

        {{-- Countdown timer --}}
        <div style="text-align:center;font-size:12px;color:#64748b;margin-bottom:16px;">
            <i class="fas fa-clock me-1"></i>
            OTP মেয়াদ: <span id="countdown" style="font-weight:700;color:#1a9e4a;">10:00</span>
        </div>

        <button type="submit" class="btn-auth">
            <i class="fas fa-check-circle me-2"></i>OTP যাচাই করুন
        </button>
    </form>

    <div style="text-align:center;margin-top:18px;font-size:13px;color:#64748b;">
        OTP পাননি?
        <a href="{{ route('user.password.forgot') }}" style="color:#1a9e4a;font-weight:500;text-decoration:none;">
            <i class="fas fa-redo me-1"></i>আবার পাঠান
        </a>
    </div>

    <script>
    // 10 min countdown
    let total = 600;
    const el = document.getElementById('countdown');
    const t = setInterval(() => {
        if (total <= 0) { clearInterval(t); el.textContent = 'মেয়াদ শেষ'; el.style.color='#ef4444'; return; }
        total--;
        const m = String(Math.floor(total/60)).padStart(2,'0');
        const s = String(total%60).padStart(2,'0');
        el.textContent = m+':'+s;
    }, 1000);
    </script>
</x-guest-layout>
