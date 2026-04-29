<x-guest-layout>
    {{-- ── Header ────────────────────────────────────────────────── --}}
    <div style="text-align:center;margin-bottom:24px;">
        <div style="width:56px;height:56px;background:linear-gradient(135deg,#1a9e4a,#15803d);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
            <i class="fas fa-shield-alt" style="color:#fff;font-size:20px;"></i>
        </div>
        <h2 style="font-size:20px;font-weight:700;color:#1e293b;margin:0 0 4px;">নতুন পাসওয়ার্ড দিন</h2>
        <p style="font-size:13px;color:#64748b;margin:0;">কমপক্ষে ৮ অক্ষরের পাসওয়ার্ড দিন</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger small mb-3" style="border-radius:8px;font-size:13px;">
            <i class="fas fa-exclamation-circle me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('user.password.reset') }}">
        @csrf

        {{-- New Password --}}
        <div class="mb-3">
            <label class="form-label" style="font-size:13px;font-weight:500;color:#374151;">নতুন পাসওয়ার্ড</label>
            <div style="position:relative;">
                <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;">
                    <i class="fas fa-lock" style="font-size:13px;"></i>
                </span>
                <input
                    type="password"
                    name="password"
                    id="password"
                    required
                    autofocus
                    autocomplete="new-password"
                    placeholder="কমপক্ষে ৮ অক্ষর"
                    class="form-control @error('password') is-invalid @enderror"
                    style="padding-left:36px;padding-right:40px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;">
                <button type="button" onclick="togglePwd('password','eye1')"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#94a3b8;cursor:pointer;">
                    <i id="eye1" class="fas fa-eye" style="font-size:14px;"></i>
                </button>
                @error('password')<div class="invalid-feedback" style="font-size:12px;">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Confirm Password --}}
        <div class="mb-3">
            <label class="form-label" style="font-size:13px;font-weight:500;color:#374151;">পাসওয়ার্ড নিশ্চিত করুন</label>
            <div style="position:relative;">
                <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;">
                    <i class="fas fa-lock" style="font-size:13px;"></i>
                </span>
                <input
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="আবার পাসওয়ার্ড দিন"
                    class="form-control @error('password_confirmation') is-invalid @enderror"
                    style="padding-left:36px;padding-right:40px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;">
                <button type="button" onclick="togglePwd('password_confirmation','eye2')"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#94a3b8;cursor:pointer;">
                    <i id="eye2" class="fas fa-eye" style="font-size:14px;"></i>
                </button>
                @error('password_confirmation')<div class="invalid-feedback" style="font-size:12px;">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Strength indicator --}}
        <div id="strength-bar" style="height:4px;border-radius:4px;background:#e2e8f0;margin-bottom:14px;overflow:hidden;">
            <div id="strength-fill" style="height:100%;width:0;border-radius:4px;transition:width .3s,background .3s;"></div>
        </div>
        <p id="strength-text" style="font-size:11px;color:#94a3b8;margin-bottom:10px;text-align:center;"></p>

        <button type="submit" class="btn-auth">
            <i class="fas fa-save me-2"></i>পাসওয়ার্ড পরিবর্তন করুন
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
    document.getElementById('password').addEventListener('input', function() {
        const val = this.value;
        let score = 0;
        if (val.length >= 8)  score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const fill   = document.getElementById('strength-fill');
        const text   = document.getElementById('strength-text');
        const colors = ['#ef4444','#f97316','#eab308','#22c55e'];
        const labels = ['দুর্বল','মোটামুটি','ভালো','শক্তিশালী'];
        fill.style.width   = (score * 25) + '%';
        fill.style.background = colors[score-1] || '#e2e8f0';
        text.textContent   = score > 0 ? 'পাসওয়ার্ড: ' + (labels[score-1]||'') : '';
        text.style.color   = colors[score-1] || '#94a3b8';
    });
    </script>
</x-guest-layout>
