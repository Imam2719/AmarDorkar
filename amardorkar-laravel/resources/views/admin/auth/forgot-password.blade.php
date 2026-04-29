@extends('layouts.guest')
@section('title', 'Admin - Forgot Password')
@section('content')
<div class="min-h-screen d-flex align-items-center justify-content-center" style="background:#f0fdf4;">
  <div class="card shadow" style="max-width:420px;width:100%;border-radius:16px;">
    <div class="card-body p-4">
      <div class="text-center mb-4">
        <div style="width:60px;height:60px;background:#1a9e4a;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
          <i class="fas fa-lock" style="color:#fff;font-size:22px;"></i>
        </div>
        <h4 style="font-weight:700;color:#1a9e4a;">Admin Password Reset</h4>
        <p class="text-muted small">Enter your admin email to receive a 6-digit OTP</p>
      </div>

      @if(session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
      @endif

      @if($errors->any())
        <div class="alert alert-danger small">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('admin.password.send-otp') }}">
        @csrf
        <div class="mb-3">
          <label class="form-label small fw-semibold">Admin Email</label>
          <input type="email" name="email" value="{{ old('email') }}" required
            class="form-control @error('email') is-invalid @enderror"
            placeholder="admin@example.com">
          @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn w-100" style="background:#1a9e4a;color:#fff;font-weight:600;">
          <i class="fas fa-paper-plane me-2"></i>Send OTP
        </button>
      </form>

      <div class="text-center mt-3">
        <a href="{{ route('admin.login') }}" class="small text-muted">
          <i class="fas fa-arrow-left me-1"></i>Back to login
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
