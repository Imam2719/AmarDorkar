@extends('layouts.app')
@section('title', __('messages.checkout') . ' — ' . __('messages.site_name'))

@section('content')
<div class="fw-wrap" style="margin-top:24px;margin-bottom:48px;">
    <h2 class="bengali-text mb-4">{{ __('messages.order_details') }}</h2>

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('checkout.store') }}" method="POST" id="checkoutForm">
        @csrf
        <div class="row g-4">

            {{-- LEFT: Customer Info --}}
            <div class="col-lg-7">
                <div style="background:var(--surface);border-radius:14px;padding:24px;border:1px solid var(--border);">
                    <h5 class="bengali-text mb-4" style="font-weight:600;">{{ __('messages.billing_details') }}</h5>

                    {{-- Full Name --}}
                    <div class="mb-3">
                        <label class="form-label bengali-text small fw-500">
                            {{ __('messages.full_name') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="customer_name"
                               class="form-control @error('customer_name') is-invalid @enderror"
                               value="{{ old('customer_name', auth()->user()->name ?? '') }}"
                               placeholder="{{ app()->getLocale()==='bn' ? 'আপনার পূর্ণ নাম লিখুন' : 'Enter your full name' }}"
                               required>
                        @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Phone --}}
                    <div class="mb-3">
                        <label class="form-label bengali-text small fw-500">
                            {{ __('messages.phone_number') }} <span class="text-danger">*</span>
                        </label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:16px;line-height:1;pointer-events:none;">📱</span>
                            <input type="tel" name="customer_mobile"
                                   class="form-control @error('customer_mobile') is-invalid @enderror"
                                   value="{{ old('customer_mobile') }}"
                                   placeholder="{{ app()->getLocale()==='bn' ? '01XXXXXXXXX (হোয়াটসঅ্যাপ নম্বর দিন)' : '01XXXXXXXXX (Preferably WhatsApp number)' }}"
                                   style="padding-left:36px;"
                                   required>
                        </div>
                        <div class="form-text" style="font-size:11px;color:var(--muted);display:flex;align-items:center;gap:4px;margin-top:4px;">
                            <span style="color:#25D366;font-size:13px;">●</span>
                            {{ app()->getLocale()==='bn'
                                ? 'অর্ডার আপডেটের জন্য হোয়াটসঅ্যাপ নম্বর দেওয়া ভালো।'
                                : 'Prefer giving your WhatsApp number for order updates.' }}
                        </div>
                        @error('customer_mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-3">
                        <label class="form-label small fw-500">
                            {{ __('messages.email_address') }} <span class="text-danger">*</span>
                        </label>
                        <div style="position:relative;">
                            <input type="email" name="customer_email" id="customerEmail"
                                   class="form-control @error('customer_email') is-invalid @enderror"
                                   value="{{ old('customer_email', auth()->user()->email ?? '') }}"
                                   placeholder="example@email.com"
                                   {{ auth()->check() ? 'readonly' : '' }}
                                   required>
                            <span id="emailCheckSpinner"
                                  style="display:none;position:absolute;right:12px;top:50%;transform:translateY(-50%);font-size:14px;">⏳</span>
                        </div>
                        <div class="form-text" style="font-size:11px;color:var(--muted);">
                            {{ app()->getLocale()==='bn'
                                ? 'অর্ডার কনফার্মেশন ইমেইলে পাঠানো হবে।'
                                : 'Order confirmation will be sent to this email.' }}
                        </div>
                        @error('customer_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- ── PASSWORD SECTION (existing account) ─────────────── --}}
                    @if(!auth()->check())
                    <div id="passwordSection" style="display:none;" class="mb-3">
                        <div style="background:var(--surface-2);border:1.5px solid var(--primary);border-radius:10px;padding:16px;">

                            {{-- Header --}}
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                                <span style="font-size:20px;">🔒</span>
                                <div>
                                    <div style="font-weight:600;font-size:14px;">
                                        {{ __('messages.account_exists_title') }}
                                    </div>
                                    <div style="font-size:12px;color:var(--muted);">
                                        {{ __('messages.account_exists_desc') }}
                                    </div>
                                </div>
                            </div>

                            {{-- Password input --}}
                            <label class="form-label small fw-500">
                                {{ __('messages.account_password_label') }} <span class="text-danger">*</span>
                            </label>
                            <div style="position:relative;">
                                <input type="password" name="account_password" id="accountPassword"
                                       class="form-control @error('account_password') is-invalid @enderror"
                                       placeholder="{{ __('messages.account_password_placeholder') }}"
                                       autocomplete="current-password">
                                <button type="button" onclick="togglePassword()"
                                        style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:15px;color:var(--muted);"
                                        title="{{ app()->getLocale()==='bn' ? 'পাসওয়ার্ড দেখুন' : 'Show password' }}">
                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                </button>
                            </div>

                            @error('account_password')
                            <div class="text-danger" style="font-size:12px;margin-top:4px;">
                                ⚠️ {{ $message }}
                            </div>
                            @enderror

                            {{-- Forgot password --}}
                            <div style="margin-top:8px;font-size:12px;">
                                <a href="{{ route('user.password.forgot') }}"
                                   target="_blank"
                                   style="color:var(--primary);text-decoration:none;">
                                    {{ __('messages.forgot_password_link') }}
                                </a>
                                <span style="color:var(--muted);margin-left:4px;">
                                    {{ __('messages.forgot_password_new_tab') }}
                                </span>
                            </div>

                        </div>
                    </div>
                    @endif
                    {{-- ──────────────────────────────────────────────────────── --}}

                    {{-- Delivery Address --}}
                    <div class="mb-3">
                        <label class="form-label bengali-text small fw-500">
                            {{ __('messages.address') }} <span class="text-danger">*</span>
                        </label>
                        <textarea name="customer_address"
                                  class="form-control @error('customer_address') is-invalid @enderror"
                                  rows="3"
                                  placeholder="{{ app()->getLocale()==='bn' ? 'বাড়ি নং, রাস্তা, এলাকা, শহর' : 'House no, Road, Area, City' }}"
                                  required>{{ old('customer_address') }}</textarea>
                        @error('customer_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Shipping Location --}}
                    <div class="mb-3">
                        <label class="form-label bengali-text small fw-500">
                            {{ __('messages.shipping_location') }} <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="d-block" style="cursor:pointer;">
                                    <input type="radio" name="shipping_location" value="inside_dhaka"
                                           {{ old('shipping_location','inside_dhaka')==='inside_dhaka' ? 'checked' : '' }}
                                           style="display:none;" id="r_inside">
                                    <div class="shipping-option {{ old('shipping_location','inside_dhaka')==='inside_dhaka' ? 'active' : '' }}"
                                         id="opt_inside" onclick="selectShipping('inside_dhaka',60)">
                                        <div style="font-weight:600;font-size:13px;" class="bengali-text">
                                            {{ __('messages.inside_dhaka') }}
                                        </div>
                                        <div style="color:var(--success);font-weight:700;">৳60</div>
                                        <div style="font-size:11px;color:var(--muted);">
                                            {{ app()->getLocale()==='bn' ? '১-৩ কার্যদিবস' : '1-3 business days' }}
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-6">
                                <label class="d-block" style="cursor:pointer;">
                                    <input type="radio" name="shipping_location" value="outside_dhaka"
                                           {{ old('shipping_location')==='outside_dhaka' ? 'checked' : '' }}
                                           style="display:none;" id="r_outside">
                                    <div class="shipping-option {{ old('shipping_location')==='outside_dhaka' ? 'active' : '' }}"
                                         id="opt_outside" onclick="selectShipping('outside_dhaka',120)">
                                        <div style="font-weight:600;font-size:13px;" class="bengali-text">
                                            {{ __('messages.outside_dhaka') }}
                                        </div>
                                        <div style="color:var(--success);font-weight:700;">৳120</div>
                                        <div style="font-size:11px;color:var(--muted);">
                                            {{ app()->getLocale()==='bn' ? '৩-৫ কার্যদিবস' : '3-5 business days' }}
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Method --}}
                    <div class="mb-3">
                        <label class="form-label bengali-text small fw-500">{{ __('messages.payment_method') }}</label>
                        <div style="background:var(--surface-2);border-radius:8px;padding:14px 16px;border:1px solid var(--border);display:flex;align-items:center;gap:10px;">
                            <i class="fas fa-money-bill-wave" style="color:var(--success);font-size:20px;"></i>
                            <div>
                                <div style="font-weight:600;font-size:14px;" class="bengali-text">
                                    {{ __('messages.cash_on_delivery') }}
                                </div>
                                <div style="font-size:12px;color:var(--muted);">
                                    {{ __('messages.pay_on_delivery') }}
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="payment_method" value="cod">
                    </div>

                    {{-- Mobile Place Order Button --}}
                    <div class="d-block d-lg-none mt-4">
                        <button type="submit" class="btn btn-success w-100"
                                style="padding:14px;font-size:15px;font-weight:600;"
                                id="placeOrderBtnMobile">
                            <i class="fas fa-shopping-bag me-2"></i>{{ __('messages.place_order') }}
                        </button>
                        <div style="text-align:center;margin-top:10px;font-size:12px;color:var(--muted);">
                            <i class="fas fa-lock me-1"></i>
                            {{ app()->getLocale()==='bn' ? 'আপনার তথ্য সম্পূর্ণ সুরক্ষিত' : 'Your information is completely secure' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Order Summary --}}
            <div class="col-lg-5 checkout-summary-col">
                <div style="background:var(--surface);border-radius:14px;padding:24px;border:1px solid var(--border);position:sticky;top:calc(var(--header-total, 90px) + 16px);">
                    <h5 class="bengali-text mb-3" style="font-weight:600;">{{ __('messages.order_summary') }}</h5>

                    <div id="orderItemsList">
                        @foreach($cartItems as $item)
                        <div style="display:flex;gap:10px;padding:10px 0;border-bottom:1px solid var(--border);align-items:center;">
                            <div style="width:52px;height:52px;border-radius:7px;overflow:hidden;background:var(--surface-2);flex-shrink:0;border:1px solid var(--border);">
                                @if($item->product && $item->product->image_url)
                                    <img src="{{ $item->product->image_url }}"
                                         style="width:100%;height:100%;object-fit:contain;">
                                @else
                                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div class="bengali-text"
                                     style="font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $item->product ? $item->product->name : $item->product_name }}
                                </div>
                                <div style="font-size:12px;color:var(--muted);">
                                    {{ __('messages.quantity') }}: {{ $item->quantity }}
                                </div>
                            </div>
                            <div style="font-weight:600;font-size:14px;color:var(--success);flex-shrink:0;">
                                ৳{{ number_format($item->price * $item->quantity, 0) }}
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div style="padding-top:14px;">
                        <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:14px;">
                            <span style="color:var(--muted);" class="bengali-text">{{ __('messages.subtotal') }}</span>
                            <span style="font-weight:500;">৳<span id="subtotalDisplay">{{ number_format($subtotal, 0) }}</span></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:14px;">
                            <span style="color:var(--muted);" class="bengali-text">{{ __('messages.shipping_cost') }}</span>
                            <span style="font-weight:500;">৳<span id="shippingDisplay">{{ number_format($shippingCharge, 0) }}</span></span>
                        </div>

                        {{-- Coupon discount row --}}
                        <div id="couponDiscountRow" style="display:none;justify-content:space-between;margin-bottom:8px;font-size:14px;">
                            <span style="color:var(--success);" class="bengali-text">{{ __('messages.coupon_discount_label') }}</span>
                            <span style="font-weight:600;color:var(--success);">-৳<span id="couponDiscountDisplay">0</span></span>
                        </div>

                        <hr style="border-color:var(--border);">
                        <div style="display:flex;justify-content:space-between;font-size:17px;font-weight:700;">
                            <span class="bengali-text">{{ __('messages.total') }}</span>
                            <span style="color:var(--success);">৳<span id="totalDisplay">{{ number_format($total, 0) }}</span></span>
                        </div>
                    </div>

                    {{-- Coupon input --}}
                    <div style="margin-top:14px;margin-bottom:4px;">
                        <div style="font-size:13px;font-weight:600;margin-bottom:6px;" class="bengali-text">
                            {{ __('messages.coupon_label') }}
                        </div>
                        <div style="display:flex;gap:6px;">
                            <input type="text" id="couponInput" placeholder="XXXXX" maxlength="10"
                                   style="flex:1;min-width:0;padding:8px 10px;border:1.5px solid var(--border);border-radius:8px;background:var(--surface-2);color:var(--text);font-family:monospace;font-size:13px;letter-spacing:2px;text-transform:uppercase;font-weight:700;">
                            <button type="button" id="applyCouponBtn" onclick="applyCoupon()"
                                    style="flex-shrink:0;padding:8px 12px;background:var(--primary);color:#fff;border:none;border-radius:8px;font-weight:600;font-size:12px;cursor:pointer;white-space:nowrap;">
                                {{ __('messages.coupon_apply_btn') }}
                            </button>
                        </div>
                        <div id="couponMessage" style="font-size:12px;margin-top:5px;"></div>
                        <input type="hidden" name="coupon_code" id="couponCodeHidden">
                    </div>

                    {{-- Desktop Place Order Button --}}
                    <div id="placeOrderWrapDesktop" class="d-none d-lg-block">
                        <button type="submit" class="btn btn-success w-100 mt-4"
                                style="padding:14px;font-size:15px;font-weight:600;"
                                id="placeOrderBtn">
                            <i class="fas fa-shopping-bag me-2"></i>{{ __('messages.place_order') }}
                        </button>
                        <div style="text-align:center;margin-top:12px;font-size:12px;color:var(--muted);">
                            <i class="fas fa-lock me-1"></i>
                            {{ app()->getLocale()==='bn' ? 'আপনার তথ্য সম্পূর্ণ সুরক্ষিত' : 'Your information is completely secure' }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
.shipping-option {
    border: 2px solid var(--border);
    border-radius: 10px;
    padding: 12px 14px;
    text-align: center;
    transition: all .2s;
    background: var(--surface-2);
    cursor: pointer;
}
.shipping-option.active  { border-color: var(--primary); background: var(--primary-light, #e8f5e9); }
.shipping-option:hover   { border-color: var(--primary); }

/* Password box slide-in */
#passwordSection { animation: slideDown .25s ease; }
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Mobile: summary on top */
@media (max-width: 991px) {
    .checkout-summary-col         { order: -1; }
    .checkout-summary-col > div   { position: static !important; top: auto !important; }
}
@media (max-width: 575px) {
    #placeOrderBtn { font-size: 14px; padding: 12px; }
}
</style>
@endpush

@push('scripts')
<script>
const subtotal      = {{ $subtotal }};
let currentShipping = {{ $shippingCharge }};
let couponDiscount  = 0;
const IS_LOGGED_IN  = {{ auth()->check() ? 'true' : 'false' }};

// ── Locale strings passed from Blade (bilingual JS labels) ───────────
const LANG = {
    processing:       '{{ app()->getLocale()==="bn" ? "প্রসেস করা হচ্ছে..." : "Processing..." }}',
    couponApplyLabel: '{{ __("messages.coupon_apply_btn") }}',
    couponRemoveLabel:'{{ app()->getLocale()==="bn" ? "✕ সরান" : "✕ Remove" }}',
    showPassword:     '{{ app()->getLocale()==="bn" ? "পাসওয়ার্ড দেখুন" : "Show password" }}',
    hidePassword:     '{{ app()->getLocale()==="bn" ? "পাসওয়ার্ড লুকান" : "Hide password" }}',
};

// ── Number formatter ─────────────────────────────────────────────────
function fmtNum(n) {
    return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function recalcTotal() {
    document.getElementById('totalDisplay').textContent =
        fmtNum(Math.max(0, subtotal + currentShipping - couponDiscount));
}

// ── Shipping selection ────────────────────────────────────────────────
function selectShipping(location, charge) {
    document.getElementById('r_' + (location === 'inside_dhaka' ? 'inside' : 'outside')).checked = true;
    document.getElementById('opt_inside').classList.toggle('active',  location === 'inside_dhaka');
    document.getElementById('opt_outside').classList.toggle('active', location === 'outside_dhaka');
    updateShipping(location, charge);
}

function updateShipping(location) {
    currentShipping = location === 'outside_dhaka' ? 120 : (subtotal >= 1000 ? 0 : 60);
    document.getElementById('shippingDisplay').textContent = fmtNum(currentShipping);
    recalcTotal();
}

// ── Password show / hide ──────────────────────────────────────────────
function togglePassword() {
    const inp  = document.getElementById('accountPassword');
    const icon = document.getElementById('eyeIcon');
    if (!inp) return;
    const showing = inp.type === 'text';
    inp.type = showing ? 'password' : 'text';
    icon.classList.toggle('fa-eye',      showing);
    icon.classList.toggle('fa-eye-slash', !showing);
}

// ── Email → account check ─────────────────────────────────────────────
let emailTimer = null;

function checkEmailExists(email) {
    if (IS_LOGGED_IN) return;

    const section = document.getElementById('passwordSection');
    const spinner = document.getElementById('emailCheckSpinner');
    if (!section) return;

    clearTimeout(emailTimer);
    emailTimer = setTimeout(function () {
        spinner.style.display = 'inline';

        fetch('{{ route('checkout.check-email') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ email: email })
        })
        .then(r => r.json())
        .then(data => {
            spinner.style.display = 'none';
            const pwdInput = document.getElementById('accountPassword');

            if (data.exists) {
                section.style.display = 'block';
                if (pwdInput) pwdInput.required = true;
            } else {
                section.style.display = 'none';
                if (pwdInput) { pwdInput.required = false; pwdInput.value = ''; }
            }
        })
        .catch(() => { spinner.style.display = 'none'; });

    }, 600); // 600ms debounce
}

// Attach email field events
const emailField = document.getElementById('customerEmail');
if (emailField && !IS_LOGGED_IN) {
    // Restore state after validation error (old value present)
    if (emailField.value.trim()) checkEmailExists(emailField.value.trim());

    emailField.addEventListener('input', function () {
        const val = this.value.trim();
        if (val && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
            checkEmailExists(val);
        } else {
            const s = document.getElementById('passwordSection');
            if (s) s.style.display = 'none';
        }
    });

    emailField.addEventListener('blur', function () {
        const val = this.value.trim();
        if (val && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) checkEmailExists(val);
    });
}

// ── Coupon ────────────────────────────────────────────────────────────
function applyCoupon() {
    const code = document.getElementById('couponInput').value.trim().toUpperCase();
    if (!code) return;

    const btn = document.getElementById('applyCouponBtn');
    btn.disabled = true;
    btn.textContent = '...';

    fetch('{{ route('checkout.coupon.apply') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ code, subtotal })
    })
    .then(r => r.json())
    .then(data => {
        const msg = document.getElementById('couponMessage');
        if (data.valid) {
            couponDiscount = data.discount;
            document.getElementById('couponDiscountDisplay').textContent = fmtNum(data.discount);
            document.getElementById('couponDiscountRow').style.display  = 'flex';
            document.getElementById('couponCodeHidden').value           = data.coupon_code;
            msg.style.color   = 'var(--success)';
            msg.textContent   = '✓ ' + data.message;
            btn.textContent   = LANG.couponRemoveLabel;
            btn.style.background = '#dc3545';
            btn.disabled      = false;
            document.getElementById('couponInput').disabled = true;
            btn.onclick       = removeCoupon;
        } else {
            couponDiscount = 0;
            document.getElementById('couponDiscountRow').style.display = 'none';
            document.getElementById('couponCodeHidden').value          = '';
            msg.style.color  = '#dc3545';
            msg.textContent  = '✗ ' + data.message;
            btn.disabled     = false;
            btn.textContent  = LANG.couponApplyLabel;
        }
        recalcTotal();
    })
    .catch(() => { btn.disabled = false; btn.textContent = LANG.couponApplyLabel; });
}

function removeCoupon() {
    couponDiscount = 0;
    document.getElementById('couponDiscountRow').style.display = 'none';
    document.getElementById('couponCodeHidden').value          = '';
    document.getElementById('couponMessage').textContent       = '';
    document.getElementById('couponInput').value               = '';
    document.getElementById('couponInput').disabled            = false;
    const btn = document.getElementById('applyCouponBtn');
    btn.textContent      = LANG.couponApplyLabel;
    btn.style.background = 'var(--primary)';
    btn.onclick          = applyCoupon;
    recalcTotal();
}

document.getElementById('couponInput').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); applyCoupon(); }
});

// ── Form submit loading state ─────────────────────────────────────────
document.getElementById('checkoutForm').addEventListener('submit', function () {
    const html = '<i class="fas fa-spinner fa-spin me-2"></i>' + LANG.processing;
    ['placeOrderBtn', 'placeOrderBtnMobile'].forEach(id => {
        const btn = document.getElementById(id);
        if (btn) { btn.disabled = true; btn.innerHTML = html; }
    });
});
</script>
@endpush