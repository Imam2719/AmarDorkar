@extends('layouts.guest')
@section('title', 'Admin - Reset Password')
@section('content')
<div class="min-h-screen d-flex align-items-center justify-content-center" style="background:#f0fdf4;">
  <div class="card shadow" style="max-width:420px;width:100%;border-radius:16px;">
    <div class="card-body p-4">
      <div class="text-center mb-4">
        <div style="width:60px;height:60px;background:#1a9e4a;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
          <i class="fas fa-shield-alt" style="color:#fff;font-size:22px;"></i>
        </div>
        <h4 style="font-weight:700;color:#1a9e4a;">Set New Password</h4>
        <p class="text-muted small">OTP verified. Choose a strong password.</p>
      </div>

      @if($errors->any())
        <div class="alert alert-danger small">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('admin.password.reset') }}">
        @csrf
        <div class="mb-3">
          <label class="form-label small fw-semibold">New Password</label>
          <input type="password" name="password" required minlength="8"
            class="form-control @error('password') is-invalid @enderror"
            placeholder="Minimum 8 characters">
          @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
          <label class="form-label small fw-semibold">Confirm Password</label>
          <input type="password" name="password_confirmation" required
            class="form-control" placeholder="Repeat new password">
        </div>
        <button type="submit" class="btn w-100" style="background:#1a9e4a;color:#fff;font-weight:600;">
          <i class="fas fa-save me-2"></i>Save New Password
        </button>
      </form>
    </div>
  </div>
</div>
@endsection
