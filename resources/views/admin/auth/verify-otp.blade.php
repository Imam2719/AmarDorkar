<x-guest-layout>
    {{-- ── Header ────────────────────────────────────────────────── --}}
    <div style="text-align:center;margin-bottom:24px;">
        <div class="auth-header-icon">
            <i class="fas fa-key"></i>
        </div>
        <h2 class="auth-page-title">{{ __('messages.otp_verify_title') }}</h2>
        <p class="auth-page-subtitle">
            {{ __('messages.otp_sent_to') }}
            <strong style="color:var(--auth-link);">{{ $email }}</strong>
        </p>
    </div>

    @if(session('status'))
        <div class="alert alert-success small mb-3">
            <i class="fas fa-check-circle me-1"></i> {{ session('status') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger small mb-3">
            <i class="fas fa-exclamation-circle me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.password.verify-otp') }}">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">

        <div class="mb-3">
            <label class="form-label">{{ __('messages.otp_6digit_label') }}</label>
            <input type="text" name="otp" maxlength="6" pattern="\d{6}" required
                autofocus autocomplete="off"
                placeholder="000000"
                class="form-control text-center @error('otp') is-invalid @enderror"
                style="font-size:32px;font-weight:800;letter-spacing:12px;font-family:monospace;border-radius:10px;padding:14px 10px;color:var(--auth-link);border:2px solid var(--auth-input-border);background:var(--auth-input-bg);">
            @error('otp')<div class="invalid-feedback text-center" style="font-size:12px;">{{ $message }}</div>@enderror
        </div>

        {{-- Countdown --}}
        <div style="text-align:center;font-size:12px;color:var(--auth-muted);margin-bottom:16px;">
            <i class="fas fa-clock me-1"></i>
            {{ __('messages.otp_expiry_label') }}
            <span id="countdown" style="font-weight:700;color:var(--auth-link);">10:00</span>
        </div>

        <button type="submit" class="btn-auth">
            <i class="fas fa-check-circle me-2"></i>{{ __('messages.otp_verify_btn') }}
        </button>
    </form>

    <div class="auth-footer-link" style="margin-top:18px;">
        {{ __('messages.otp_not_received') }}
        <a href="{{ route('admin.password.forgot') }}">
            <i class="fas fa-redo me-1"></i>{{ __('messages.resend_otp') }}
        </a>
        &nbsp;·&nbsp;
        <a href="{{ route('admin.login') }}">{{ __('messages.back_to_login') }}</a>
    </div>

    <script>
    let total = 600;
    const el  = document.getElementById('countdown');
    const expired = '{{ __("messages.otp_expired") }}';
    const t = setInterval(() => {
        if (total <= 0) {
            clearInterval(t);
            el.textContent = expired;
            el.style.color = '#ef4444';
            return;
        }
        total--;
        const m = String(Math.floor(total / 60)).padStart(2, '0');
        const s = String(total % 60).padStart(2, '0');
        el.textContent = m + ':' + s;
    }, 1000);
    </script>
</x-guest-layout>
