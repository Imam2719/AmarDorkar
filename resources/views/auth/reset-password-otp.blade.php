<x-guest-layout>
    {{-- ── Header ────────────────────────────────────────────────── --}}
    <div style="text-align:center;margin-bottom:24px;">
        <div class="auth-header-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        <h2 class="auth-page-title">{{ __('messages.new_pwd_title') }}</h2>
        <p class="auth-page-subtitle">{{ __('messages.new_pwd_subtitle') }}</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger small mb-3">
            <i class="fas fa-exclamation-circle me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('user.password.reset') }}">
        @csrf

        {{-- New Password --}}
        <div class="mb-3">
            <label class="form-label">{{ __('messages.new_password') }}</label>
            <div class="auth-input-wrap">
                <span class="auth-input-icon"><i class="fas fa-lock"></i></span>
                <input
                    type="password"
                    name="password"
                    id="password"
                    required
                    autofocus
                    autocomplete="new-password"
                    placeholder="{{ __('messages.min_8_placeholder') }}"
                    class="form-control auth-input-icon-input @error('password') is-invalid @enderror"
                    style="padding-right:40px;">
                <button type="button" class="auth-eye-btn" onclick="togglePwd('password','eye1')">
                    <i id="eye1" class="fas fa-eye"></i>
                </button>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Confirm Password --}}
        <div class="mb-3">
            <label class="form-label">{{ __('messages.confirm_password') }}</label>
            <div class="auth-input-wrap">
                <span class="auth-input-icon"><i class="fas fa-lock"></i></span>
                <input
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="{{ __('messages.repeat_password_ph') }}"
                    class="form-control auth-input-icon-input @error('password_confirmation') is-invalid @enderror"
                    style="padding-right:40px;">
                <button type="button" class="auth-eye-btn" onclick="togglePwd('password_confirmation','eye2')">
                    <i id="eye2" class="fas fa-eye"></i>
                </button>
                @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Strength indicator --}}
        <div class="auth-strength-track">
            <div id="strength-fill" class="auth-strength-fill"></div>
        </div>
        <p id="strength-text" class="auth-strength-text"></p>

        <button type="submit" class="btn-auth">
            <i class="fas fa-save me-2"></i>{{ __('messages.change_password') }}
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

    // Password strength
    const labels = [
        '{{ __("messages.pwd_weak") }}',
        '{{ __("messages.pwd_fair") }}',
        '{{ __("messages.pwd_good") }}',
        '{{ __("messages.pwd_strong") }}'
    ];
    const strengthPrefix = '{{ __("messages.pwd_strength_label") }}';

    document.getElementById('password').addEventListener('input', function() {
        const val = this.value;
        let score = 0;
        if (val.length >= 8)       score++;
        if (/[A-Z]/.test(val))     score++;
        if (/[0-9]/.test(val))     score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const fill   = document.getElementById('strength-fill');
        const text   = document.getElementById('strength-text');
        const colors = ['#ef4444','#f97316','#eab308','#22c55e'];
        fill.style.width      = (score * 25) + '%';
        fill.style.background = colors[score-1] || 'var(--auth-strength-track)';
        text.textContent      = score > 0 ? strengthPrefix + ' ' + (labels[score-1] || '') : '';
        text.style.color      = colors[score-1] || 'var(--auth-muted)';
    });
    </script>
</x-guest-layout>
