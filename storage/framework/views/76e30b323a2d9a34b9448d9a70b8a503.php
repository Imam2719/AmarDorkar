<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(config('app.name', 'amardorkar')); ?></title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <style>
            * { margin:0; padding:0; box-sizing:border-box; }
            body {
                font-family: 'Inter', 'Hind Siliguri', sans-serif;
                background: linear-gradient(135deg, #e8f5e9 0%, #f0f4f8 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .auth-wrapper {
                width: 100%;
                max-width: 420px;
                padding: 20px;
            }
            .auth-logo {
                text-align: center;
                margin-bottom: 28px;
            }
            .auth-logo a {
                display: inline-block;
                text-decoration: none;
            }
            .auth-logo img {
                height: 60px;
                width: auto;
            }
            .auth-card {
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.10);
                padding: 36px 32px 32px;
            }
            .auth-card h2 {
                font-size: 20px;
                font-weight: 700;
                color: #1e293b;
                margin-bottom: 6px;
            }
            .auth-card .subtitle {
                font-size: 13px;
                color: #64748b;
                margin-bottom: 24px;
            }
            .form-label {
                font-size: 13px;
                font-weight: 500;
                color: #374151;
                margin-bottom: 5px;
            }
            .form-control {
                border: 1.5px solid #e2e8f0;
                border-radius: 8px;
                padding: 10px 14px;
                font-size: 14px;
                color: #1e293b;
                transition: border-color .2s, box-shadow .2s;
            }
            .form-control:focus {
                border-color: #1a9e4a;
                box-shadow: 0 0 0 3px rgba(26,158,74,.12);
                outline: none;
            }
            .btn-auth {
                background: #1a9e4a;
                color: #fff;
                border: none;
                border-radius: 8px;
                padding: 11px 0;
                font-size: 15px;
                font-weight: 600;
                width: 100%;
                cursor: pointer;
                transition: background .2s, transform .15s;
                margin-top: 6px;
            }
            .btn-auth:hover {
                background: #157a39;
                transform: translateY(-1px);
                color: #fff;
            }
            .auth-links {
                text-align: center;
                margin-top: 18px;
                font-size: 13px;
                color: #64748b;
            }
            .auth-links a {
                color: #1a9e4a;
                text-decoration: none;
                font-weight: 500;
            }
            .auth-links a:hover {
                text-decoration: underline;
            }
            .invalid-feedback { font-size: 12px; }
            .alert { border-radius: 8px; font-size: 13px; }
        </style>
    </head>
    <body>
        <div class="auth-wrapper">
            <!-- Logo -->
            <div class="auth-logo">
                <a href="<?php echo e(route('home')); ?>">
                    <img src="<?php echo e(asset('logo.png')); ?>" alt="amardorkar" onerror="this.style.display='none'">
                </a>
            </div>

            <!-- Card -->
            <div class="auth-card">
                <?php echo e($slot); ?>

            </div>

            <div class="auth-links" style="margin-top:16px;">
                <a href="<?php echo e(route('home')); ?>">
                    <i class="fas fa-home me-1"></i> হোমে ফিরুন / Back to Home
                </a>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
<?php /**PATH E:\amardorkar-laravel\resources\views/layouts/guest.blade.php ENDPATH**/ ?>