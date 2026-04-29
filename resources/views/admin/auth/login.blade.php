<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - দোয়েল ই-কমার্স</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Kalpurush&display=swap" rel="stylesheet">
<style>
body{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);font-family:'Inter','Kalpurush',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;}
.login-container{background:#fff;border-radius:20px;box-shadow:0 15px 35px rgba(0,0,0,.1);overflow:hidden;width:100%;max-width:420px;}
.login-header{background:linear-gradient(135deg,#2c3e50,#34495e);color:#fff;padding:2rem;text-align:center;}
.login-body{padding:2rem;}
.form-control{border-radius:10px;border:2px solid #e9ecef;padding:12px 15px;transition:all .3s ease;}
.form-control:focus{border-color:#667eea;box-shadow:0 0 0 .2rem rgba(102,126,234,.25);}
.btn-login{background:linear-gradient(135deg,#667eea,#764ba2);border:none;border-radius:10px;padding:12px;font-weight:600;text-transform:uppercase;letter-spacing:1px;transition:all .3s ease;}
.btn-login:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(102,126,234,.3);}
.bengali-text{font-family:'Kalpurush',sans-serif;}
.alert{border-radius:10px;border:none;}
</style>
</head>
<body>
<div class="login-container">
  <div class="login-header">
    <h3 class="mb-1"><i class="fas fa-shield-alt me-2"></i>Admin Login</h3>
    <p class="mb-0 bengali-text opacity-75">অ্যাডমিন প্যানেল প্রবেশ</p>
  </div>
  <div class="login-body">
    @if($errors->any())
    <div class="alert alert-danger mb-3">
      <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.login.post') }}">
      @csrf
      <div class="mb-3">
        <label class="form-label"><i class="fas fa-envelope me-2"></i>Email Address</label>
        <input type="email" name="email" class="form-control"
          value="{{ old('email','admin@amardorkar.com') }}" required autofocus>
      </div>
      <div class="mb-4">
        <label class="form-label"><i class="fas fa-lock me-2"></i>Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="remember" id="remember">
        <label class="form-check-label bengali-text" for="remember">
          {{ app()->getLocale()==='bn' ? 'মনে রাখুন' : 'Remember me' }}
        </label>
      </div>
      <button type="submit" class="btn btn-primary btn-login w-100 text-white">
        <i class="fas fa-sign-in-alt me-2"></i>Login to Dashboard
      </button>
    </form>

    <div class="text-center mt-3 mb-2"><a href="{{ route('admin.password.forgot') }}" class="small text-muted"><i class="fas fa-key me-1"></i>Forgot Password?</a></div>
    <hr class="my-4">
    <div class="text-center">
      <a href="{{ route('home') }}" class="text-decoration-none text-muted">
        <i class="fas fa-arrow-left me-2"></i>
        <span class="bengali-text">ওয়েবসাইটে ফিরে যান</span>
      </a>
    </div>
  </div>
</div>
</body>
</html>
