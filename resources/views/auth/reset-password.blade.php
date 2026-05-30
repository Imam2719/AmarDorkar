<x-guest-layout>
    {{-- ── Header ────────────────────────────────────────────────── --}}
    <div style="text-align:center;margin-bottom:24px;">
        <div class="auth-header-icon">
            <i class="fas fa-key"></i>
        </div>
        <h2 class="auth-page-title">{{ __('messages.reset_password_title') }}</h2>
        <p class="auth-page-subtitle">{{ __('messages.new_pwd_subtitle') }}</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger small mb-3">
            <i class="fas fa-exclamation-circle me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email -->
        <div class="mb-3">
            <label class="form-label">{{ __('messages.email_label') }}</label>
            <div class="auth-input-wrap">
                <span class="auth-input-icon"><i class="fas fa-envelope"></i></span>
                <input type="email" id="email" name="email"
                    value="{{ old('email', $request->email) }}"
                    required autofocus autocomplete="username"
                    class="form-control auth-input-icon-input @error('email') is-invalid @enderror"
                    placeholder="example@gmail.com">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <!-- New Password -->
        <div class="mb-3">
            <label class="form-label">{{ __('messages.new_password') }}</label>
            <div class="auth-input-wrap">
                <span class="auth-input-icon"><i class="fas fa-lock"></i></span>
                <input type="password" id="password" name="password"
                    required autocomplete="new-password"
                    placeholder="{{ __('messages.min_8_placeholder') }}"
                    class="form-control auth-input-icon-input @error('password') is-invalid @enderror"
                    style="padding-right:40px;">
                <button type="button" class="auth-eye-btn" onclick="togglePwd('password','eye1')">
                    <i id="eye1" class="fas fa-eye"></i>
                </button>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label class="form-label">{{ __('messages.confirm_password') }}</label>
            <div class="auth-input-wrap">
                <span class="auth-input-icon"><i class="fas fa-lock"></i></span>
                <input type="password" id="password_confirmation" name="password_confirmation"
                    required autocomplete="new-password"
                    placeholder="{{ __('messages.repeat_password_ph') }}"
                    class="form-control auth-input-icon-input"
                    style="padding-right:40px;">
                <button type="button" class="auth-eye-btn" onclick="togglePwd('password_confirmation','eye2')">
                    <i id="eye2" class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-auth">
            <i class="fas fa-save me-2"></i>{{ __('messages.reset_password_btn') }}
        </button>
    </form>

    <script>
    function togglePwd(inputId, iconId) {
        const inp = document.getElementById(inputId);
        const ico = document.getElementById(iconId);
        if (inp.type === 'password') {
            inp.type = 'text';
            ico.classList.replace('fa-eye','fa-eye-slash');
        } else {
            inp.type = 'password';
            ico.classList.replace('fa-eye-slash','fa-eye');
        }
    }
    </script>
</x-guest-layout>
