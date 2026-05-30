<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="{{ session('theme','light')==='dark' ? 'theme-dark' : 'theme-light' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'amardorkar') }}</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <style>
            /* ── CSS Variables (Light / Dark) ───────────────────────────── */
            .theme-light {
                --auth-bg:              linear-gradient(135deg, #e8f5e9 0%, #f0f4f8 100%);
                --auth-card-bg:         #ffffff;
                --auth-card-shadow:     0 8px 32px rgba(0,0,0,0.10);
                --auth-text:            #1e293b;
                --auth-muted:           #64748b;
                --auth-label:           #374151;
                --auth-input-bg:        #ffffff;
                --auth-input-border:    #e2e8f0;
                --auth-input-text:      #1e293b;
                --auth-link:            #1a9e4a;
                --auth-icon-color:      #94a3b8;
                --auth-logo-bg:         linear-gradient(135deg,#1a9e4a,#15803d);
                --auth-alert-succ-bg:   #f0fdf4;
                --auth-alert-succ-bd:   #bbf7d0;
                --auth-alert-succ-text: #166534;
                --auth-alert-err-bg:    #fef2f2;
                --auth-alert-err-bd:    #fecaca;
                --auth-alert-err-text:  #991b1b;
                --auth-strength-track:  #e2e8f0;
                --auth-footer-color:    #64748b;
            }
            .theme-dark {
                --auth-bg:              linear-gradient(135deg, #0f2027 0%, #111827 50%, #1a2432 100%);
                --auth-card-bg:         #1f2937;
                --auth-card-shadow:     0 8px 32px rgba(0,0,0,0.40);
                --auth-text:            #f9fafb;
                --auth-muted:           #9ca3af;
                --auth-label:           #d1d5db;
                --auth-input-bg:        #374151;
                --auth-input-border:    #4b5563;
                --auth-input-text:      #f9fafb;
                --auth-link:            #34d399;
                --auth-icon-color:      #6b7280;
                --auth-logo-bg:         linear-gradient(135deg,#059669,#047857);
                --auth-alert-succ-bg:   #064e3b;
                --auth-alert-succ-bd:   #065f46;
                --auth-alert-succ-text: #6ee7b7;
                --auth-alert-err-bg:    #450a0a;
                --auth-alert-err-bd:    #7f1d1d;
                --auth-alert-err-text:  #fca5a5;
                --auth-strength-track:  #374151;
                --auth-footer-color:    #9ca3af;
            }

            * { margin:0; padding:0; box-sizing:border-box; }
            body {
                font-family: 'Inter', 'Hind Siliguri', sans-serif;
                background: var(--auth-bg);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background .3s;
            }
            .auth-wrapper { width:100%; max-width:420px; padding:20px; }
            .auth-logo { text-align:center; margin-bottom:28px; }
            .auth-logo a { display:inline-block; text-decoration:none; }
            .auth-logo img { height:60px; width:auto; }

            .auth-card {
                background: var(--auth-card-bg);
                border-radius: 16px;
                box-shadow: var(--auth-card-shadow);
                padding: 36px 32px 32px;
                transition: background .3s, box-shadow .3s;
            }
            .auth-card h2 { font-size:20px; font-weight:700; color:var(--auth-text); margin-bottom:6px; }
            .auth-card .subtitle { font-size:13px; color:var(--auth-muted); margin-bottom:24px; }

            .form-label { font-size:13px; font-weight:500; color:var(--auth-label); margin-bottom:5px; }
            .form-control {
                border: 1.5px solid var(--auth-input-border);
                border-radius: 8px;
                padding: 10px 14px;
                font-size: 14px;
                color: var(--auth-input-text);
                background: var(--auth-input-bg);
                transition: border-color .2s, box-shadow .2s, background .3s, color .3s;
            }
            .form-control::placeholder { color: var(--auth-icon-color); }
            .form-control:focus {
                border-color: #1a9e4a;
                box-shadow: 0 0 0 3px rgba(26,158,74,.12);
                outline: none;
                background: var(--auth-input-bg);
                color: var(--auth-input-text);
            }
            .btn-auth {
                background: #1a9e4a; color: #fff; border: none;
                border-radius: 8px; padding: 11px 0;
                font-size: 15px; font-weight: 600; width: 100%;
                cursor: pointer; transition: background .2s, transform .15s;
                margin-top: 6px; display: block; text-align: center;
            }
            .btn-auth:hover { background:#157a39; transform:translateY(-1px); color:#fff; }

            .auth-links { text-align:center; margin-top:18px; font-size:13px; color:var(--auth-muted); }
            .auth-links a { color:var(--auth-link); text-decoration:none; font-weight:500; }
            .auth-links a:hover { text-decoration:underline; }

            .invalid-feedback { font-size:12px; }
            .alert { border-radius:8px; font-size:13px; }
            .alert-success {
                background: var(--auth-alert-succ-bg);
                border-color: var(--auth-alert-succ-bd);
                color: var(--auth-alert-succ-text);
            }
            .alert-danger {
                background: var(--auth-alert-err-bg);
                border-color: var(--auth-alert-err-bd);
                color: var(--auth-alert-err-text);
            }

            /* Shared helpers used by child pages */
            .auth-header-icon {
                width:56px; height:56px;
                background: var(--auth-logo-bg);
                border-radius:50%; display:flex; align-items:center; justify-content:center;
                margin:0 auto 12px;
            }
            .auth-header-icon i { color:#fff; font-size:20px; }
            .auth-page-title  { font-size:20px; font-weight:700; color:var(--auth-text); margin:0 0 4px; }
            .auth-page-subtitle { font-size:13px; color:var(--auth-muted); margin:0; }
            .auth-input-wrap  { position:relative; }
            .auth-input-icon  { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--auth-icon-color); font-size:13px; }
            .auth-input-icon-input { padding-left:36px !important; }
            .auth-eye-btn {
                position:absolute; right:12px; top:50%; transform:translateY(-50%);
                background:none; border:none; color:var(--auth-icon-color); cursor:pointer; font-size:14px;
            }
            .auth-footer-link { text-align:center; margin-top:20px; font-size:13px; color:var(--auth-footer-color); }
            .auth-footer-link a { color:var(--auth-link); font-weight:500; text-decoration:none; }
            .auth-footer-link a:hover { text-decoration:underline; }
            .auth-strength-track { height:4px; border-radius:4px; background:var(--auth-strength-track); margin-bottom:14px; overflow:hidden; }
            .auth-strength-fill  { height:100%; width:0; border-radius:4px; transition:width .3s, background .3s; }
            .auth-strength-text  { font-size:11px; color:var(--auth-muted); margin-bottom:10px; text-align:center; }
        </style>
    </head>
    <body>
        <div class="auth-wrapper">
            <!-- Logo -->
            <div class="auth-logo">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('logo.png') }}" alt="amardorkar" onerror="this.style.display='none'">
                </a>
            </div>

            @hasSection('content')
                {{-- Admin pages using @extends + @section('content') --}}
                @yield('content')
            @else
                {{-- User pages using <x-guest-layout> component --}}
                <div class="auth-card">
                    {{ $slot }}
                </div>

                <div class="auth-links" style="margin-top:16px;">
                    <a href="{{ route('home') }}">
                        <i class="fas fa-home me-1"></i>
                        {{ __('messages.back_to_home') }}
                    </a>
                </div>
            @endif
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
