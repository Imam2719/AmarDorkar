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
            <i class="fas fa-lock" style="color:#fff;font-size:20px;"></i>
        </div>
        <h2 style="font-size:20px;font-weight:700;color:#1e293b;margin:0 0 4px;">পাসওয়ার্ড রিসেট</h2>
        <p style="font-size:13px;color:#64748b;margin:0;">আপনার ইমেইলে একটি OTP কোড পাঠানো হবে</p>
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

    <form method="POST" action="<?php echo e(route('user.password.send-otp')); ?>">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
            <label class="form-label" style="font-size:13px;font-weight:500;color:#374151;">ইমেইল ঠিকানা</label>
            <div style="position:relative;">
                <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;">
                    <i class="fas fa-envelope" style="font-size:13px;"></i>
                </span>
                <input type="email" name="email" value="<?php echo e(old('email')); ?>" required autofocus
                    placeholder="example@gmail.com"
                    class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    style="padding-left:36px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;">
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback" style="font-size:12px;"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>
        <button type="submit" class="btn-auth mt-2">
            <i class="fas fa-paper-plane me-2"></i>OTP পাঠান
        </button>
    </form>

    <div style="text-align:center;margin-top:20px;font-size:13px;color:#64748b;">
        মনে পড়ে গেছে?
        <a href="<?php echo e(route('login')); ?>" style="color:#1a9e4a;font-weight:500;text-decoration:none;">লগইন করুন</a>
    </div>
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
<?php /**PATH E:\amardorkar-laravel\resources\views/auth/forgot-password.blade.php ENDPATH**/ ?>