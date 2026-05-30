<x-guest-layout>
    {{-- ── Header ────────────────────────────────────────────────── --}}
    <div style="text-align:center;margin-bottom:24px;">
        <div class="auth-header-icon">
            <i class="fas fa-lock"></i>
        </div>
        <h2 class="auth-page-title">{{ __('messages.pwd_reset_title') }}</h2>
        <p class="auth-page-subtitle">{{ __('messages.pwd_reset_subtitle') }}</p>
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

    <form method="POST" action="{{ route('user.password.send-otp') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">{{ __('messages.email_address') }}</label>
            <div class="auth-input-wrap">
                <span class="auth-input-icon"><i class="fas fa-envelope"></i></span>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    placeholder="example@gmail.com"
                    class="form-control auth-input-icon-input @error('email') is-invalid @enderror">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <button type="submit" class="btn-auth mt-2">
            <i class="fas fa-paper-plane me-2"></i>{{ __('messages.send_otp') }}
        </button>
    </form>

    <div class="auth-footer-link">
        {{ __('messages.remembered_password') }}
        <a href="{{ route('login') }}">{{ __('messages.login') }}</a>
    </div>
</x-guest-layout>
