@extends('layouts.guest')
@section('title', 'Admin - Verify OTP')
@section('content')
<div class="min-h-screen d-flex align-items-center justify-content-center" style="background:#f0fdf4;">
  <div class="card shadow" style="max-width:420px;width:100%;border-radius:16px;">
    <div class="card-body p-4">
      <div class="text-center mb-4">
        <div style="width:60px;height:60px;background:#1a9e4a;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
          <i class="fas fa-key" style="color:#fff;font-size:22px;"></i>
        </div>
        <h4 style="font-weight:700;color:#1a9e4a;">Enter OTP</h4>
        <p class="text-muted small">Enter the 6-digit OTP sent to <strong>{{ $email }}</strong></p>
      </div>

      @if(session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
      @endif
      @if($errors->any())
        <div class="alert alert-danger small">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('admin.password.verify-otp') }}">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        <div class="mb-3">
          <label class="form-label small fw-semibold">6-Digit OTP Code</label>
          <input type="text" name="otp" maxlength="6" pattern="\d{6}" required
            autofocus autocomplete="off"
            class="form-control text-center fw-bold @error('otp') is-invalid @enderror"
            style="font-size:28px;letter-spacing:10px;font-family:monospace;"
            placeholder="000000">
          @error('otp')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <p class="text-muted small mb-3"><i class="fas fa-clock me-1"></i>OTP expires in 10 minutes</p>
        <button type="submit" class="btn w-100" style="background:#1a9e4a;color:#fff;font-weight:600;">
          <i class="fas fa-check-circle me-2"></i>Verify OTP
        </button>
      </form>

      <div class="text-center mt-3">
        <a href="{{ route('admin.password.forgot') }}" class="small text-muted">
          <i class="fas fa-redo me-1"></i>Resend OTP
        </a>
        &nbsp;·&nbsp;
        <a href="{{ route('admin.login') }}" class="small text-muted">Back to login</a>
      </div>
    </div>
  </div>
</div>
@endsection
