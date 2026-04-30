<?php if (isset($component)) { $__componentOriginal69dc84650370d1d4dc1b42d016d7226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b = $attributes; } ?>
<?php $component = App\View\Components\GuestLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\GuestLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    
    <div style="text-align:center;margin-bottom:24px;">
        <div style="width:56px;height:56px;background:linear-gradient(135deg,#1a9e4a,#15803d);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
            <i class="fas fa-key" style="color:#fff;font-size:20px;"></i>
        </div>
        <h2 style="font-size:20px;font-weight:700;color:#1e293b;margin:0 0 4px;">OTP যাচাই করুন</h2>
        <p style="font-size:13px;color:#64748b;margin:0;">
            <strong style="color:#1a9e4a;"><?php echo e($email); ?></strong> এ পাঠানো ৬-সংখ্যার কোড দিন
        </p>
    </div>

    <?php if(session('status')): ?>
        <div class="alert alert-success small mb-3" style="border-radius:8px;font-size:13px;">
            <i class="fas fa-check-circle me-1"></i> <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="alert alert-danger small mb-3" style="border-radius:8px;font-size:13px;">
            <i class="fas fa-exclamation-circle me-1"></i> <?php echo e($errors->first()); ?>

        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('user.password.verify-otp')); ?>">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="email" value="<?php echo e($email); ?>">

        <div class="mb-3">
            <label class="form-label" style="font-size:13px;font-weight:500;color:#374151;">৬-সংখ্যার OTP কোড</label>
            <input
                type="text"
                name="otp"
                maxlength="6"
                pattern="\d{6}"
                required
                autofocus
                autocomplete="off"
                placeholder="000000"
                class="form-control text-center <?php $__errorArgs = ['otp'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                style="font-size:32px;font-weight:800;letter-spacing:12px;font-family:monospace;border:2px solid #e2e8f0;border-radius:10px;padding:14px 10px;color:#1a9e4a;">
            <?php $__errorArgs = ['otp'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback text-center" style="font-size:12px;"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <div style="text-align:center;font-size:12px;color:#64748b;margin-bottom:16px;">
            <i class="fas fa-clock me-1"></i>
            OTP মেয়াদ: <span id="countdown" style="font-weight:700;color:#1a9e4a;">10:00</span>
        </div>

        <button type="submit" class="btn-auth">
            <i class="fas fa-check-circle me-2"></i>OTP যাচাই করুন
        </button>
    </form>

    <div style="text-align:center;margin-top:18px;font-size:13px;color:#64748b;">
        OTP পাননি?
        <a href="<?php echo e(route('user.password.forgot')); ?>" style="color:#1a9e4a;font-weight:500;text-decoration:none;">
            <i class="fas fa-redo me-1"></i>আবার পাঠান
        </a>
    </div>

    <script>
    // 10 min countdown
    let total = 600;
    const el = document.getElementById('countdown');
    const t = setInterval(() => {
        if (total <= 0) { clearInterval(t); el.textContent = 'মেয়াদ শেষ'; el.style.color='#ef4444'; return; }
        total--;
        const m = String(Math.floor(total/60)).padStart(2,'0');
        const s = String(total%60).padStart(2,'0');
        el.textContent = m+':'+s;
    }, 1000);
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php /**PATH E:\amardorkar-laravel\resources\views/auth/verify-otp.blade.php ENDPATH**/ ?>