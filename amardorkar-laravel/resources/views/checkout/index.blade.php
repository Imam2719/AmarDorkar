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

                    <div class="mb-3">
                        <label class="form-label bengali-text small fw-500">
                            {{ __('messages.full_name') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror"
                               value="{{ old('customer_name', auth()->user()->name ?? '') }}"
                               placeholder="{{ app()->getLocale()==='bn'?'আপনার পূর্ণ নাম লিখুন':'Enter your full name' }}"
                               required>
                        @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label bengali-text small fw-500">
                            {{ __('messages.phone_number') }} <span class="text-danger">*</span>
                        </label>
                        <input type="tel" name="customer_mobile" class="form-control @error('customer_mobile') is-invalid @enderror"
                               value="{{ old('customer_mobile') }}"
                               placeholder="{{ app()->getLocale()==='bn'?'01XXXXXXXXX':'01XXXXXXXXX' }}"
                               required>
                        @error('customer_mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-500">
                            {{ __('messages.email_address') }}
                            <span class="text-muted" style="font-size:11px;">({{ app()->getLocale()==='bn'?'ঐচ্ছিক':'optional' }})</span>
                        </label>
                        <input type="email" name="customer_email" class="form-control @error('customer_email') is-invalid @enderror"
                               value="{{ old('customer_email', auth()->user()->email ?? '') }}"
                               placeholder="example@email.com">
                        {{-- FIX 2: Show why email is useful so users understand the value --}}
                        <div class="form-text" style="font-size:11px;color:var(--muted);">
                            {{ app()->getLocale()==='bn'
                                ? 'ইমেইল দিলে অর্ডার কনফার্মেশন পাঠানো হবে।'
                                : 'Provide email to receive order confirmation.' }}
                        </div>
                        @error('customer_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label bengali-text small fw-500">
                            {{ __('messages.address') }} <span class="text-danger">*</span>
                        </label>
                        <textarea name="customer_address" class="form-control @error('customer_address') is-invalid @enderror"
                                  rows="3"
                                  placeholder="{{ app()->getLocale()==='bn'?'বাড়ি নং, রাস্তা, এলাকা, শহর':'House no, Road, Area, City' }}"
                                  required>{{ old('customer_address') }}</textarea>
                        @error('customer_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label bengali-text small fw-500">
                            {{ __('messages.shipping_location') }} <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="d-block" style="cursor:pointer;">
                                    <input type="radio" name="shipping_location" value="inside_dhaka"
                                           {{ old('shipping_location','inside_dhaka')==='inside_dhaka'?'checked':'' }}
                                           onchange="updateShipping('inside_dhaka', 60)"
                                           style="display:none;" id="r_inside">
                                    <div class="shipping-option {{ old('shipping_location','inside_dhaka')==='inside_dhaka'?'active':'' }}"
                                         id="opt_inside" onclick="selectShipping('inside_dhaka',60)">
                                        <div style="font-weight:600;font-size:13px;" class="bengali-text">{{ __('messages.inside_dhaka') }}</div>
                                        <div style="color:var(--success);font-weight:700;">৳60</div>
                                        <div style="font-size:11px;color:var(--muted);">১-২ ঘন্টা</div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-6">
                                <label class="d-block" style="cursor:pointer;">
                                    <input type="radio" name="shipping_location" value="outside_dhaka"
                                           {{ old('shipping_location')==='outside_dhaka'?'checked':'' }}
                                           onchange="updateShipping('outside_dhaka', 120)"
                                           style="display:none;" id="r_outside">
                                    <div class="shipping-option {{ old('shipping_location')==='outside_dhaka'?'active':'' }}"
                                         id="opt_outside" onclick="selectShipping('outside_dhaka',120)">
                                        <div style="font-weight:600;font-size:13px;" class="bengali-text">{{ __('messages.outside_dhaka') }}</div>
                                        <div style="color:var(--success);font-weight:700;">৳120</div>
                                        <div style="font-size:11px;color:var(--muted);">১-৩ কার্যদিবস</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label bengali-text small fw-500">{{ __('messages.payment_method') }}</label>
                        <div style="background:var(--surface-2);border-radius:8px;padding:14px 16px;border:1px solid var(--border);display:flex;align-items:center;gap:10px;">
                            <i class="fas fa-money-bill-wave" style="color:var(--success);font-size:20px;"></i>
                            <div>
                                <div style="font-weight:600;font-size:14px;" class="bengali-text">{{ __('messages.cash_on_delivery') }}</div>
                                <div style="font-size:12px;color:var(--muted);">{{ __('messages.pay_on_delivery') }}</div>
                            </div>
                        </div>
                        <input type="hidden" name="payment_method" value="cod">
                    </div>
                </div>
            </div>

            {{-- RIGHT: Order Summary --}}
            <div class="col-lg-5">
                <div style="background:var(--surface);border-radius:14px;padding:24px;border:1px solid var(--border);position:sticky;top:100px;">
                    <h5 class="bengali-text mb-3" style="font-weight:600;">{{ __('messages.order_summary') }}</h5>

                    <div id="orderItemsList">
                        @foreach($cartItems as $item)
                        <div style="display:flex;gap:10px;padding:10px 0;border-bottom:1px solid var(--border);align-items:center;">
                            <div style="width:52px;height:52px;border-radius:7px;overflow:hidden;background:var(--surface-2);flex-shrink:0;border:1px solid var(--border);">
                                @if($item->product && $item->product->image_url)
                                    <img src="{{ $item->product->image_url }}"
                                         style="width:100%;height:100%;object-fit:contain;">
                                @else
                                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;"><i class="fas fa-image text-muted"></i></div>
                                @endif
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div class="bengali-text" style="font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $item->product ? $item->product->name : $item->product_name }}
                                </div>
                                <div style="font-size:12px;color:var(--muted);">{{ __('messages.quantity') }}: {{ $item->quantity }}</div>
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
                            {{-- FIX 1: Initial display now uses server-calculated shippingCharge (honest value) --}}
                            <span style="font-weight:500;">৳<span id="shippingDisplay">{{ number_format($shippingCharge, 0) }}</span></span>
                        </div>
                        <hr style="border-color:var(--border);">
                        <div style="display:flex;justify-content:space-between;font-size:17px;font-weight:700;">
                            <span class="bengali-text">{{ __('messages.total') }}</span>
                            {{-- FIX 1: Use server-calculated total (honest) --}}
                            <span style="color:var(--success);">৳<span id="totalDisplay">{{ number_format($total, 0) }}</span></span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 mt-4" style="padding:14px;font-size:15px;font-weight:600;" id="placeOrderBtn">
                        <i class="fas fa-shopping-bag me-2"></i>{{ __('messages.place_order') }}
                    </button>

                    <div style="text-align:center;margin-top:12px;font-size:12px;color:var(--muted);">
                        <i class="fas fa-lock me-1"></i>
                        {{ app()->getLocale()==='bn'?'আপনার তথ্য সম্পূর্ণ সুরক্ষিত':'Your information is completely secure' }}
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
.shipping-option{border:2px solid var(--border);border-radius:10px;padding:12px 14px;text-align:center;transition:all .2s;background:var(--surface-2);}
.shipping-option.active{border-color:var(--primary);background:var(--primary-light, #e8f5e9);}
.shipping-option:hover{border-color:var(--primary);}
</style>
@endpush

@push('scripts')
<script>
const subtotal = {{ $subtotal }};

function selectShipping(location, charge){
    document.getElementById('r_' + (location==='inside_dhaka'?'inside':'outside')).checked = true;
    document.getElementById('opt_inside').classList.toggle('active', location==='inside_dhaka');
    document.getElementById('opt_outside').classList.toggle('active', location==='outside_dhaka');
    updateShipping(location, charge);
}

// FIX 1: Mirror server logic — inside Dhaka is free above ৳1000, outside Dhaka always ৳120
function updateShipping(location, rawCharge){
    let actualCharge;
    if (location === 'outside_dhaka') {
        actualCharge = 120;
    } else {
        // inside_dhaka: free if subtotal >= 1000
        actualCharge = subtotal >= 1000 ? 0 : 60;
    }
    document.getElementById('shippingDisplay').textContent = actualCharge.toLocaleString();
    document.getElementById('totalDisplay').textContent = (subtotal + actualCharge).toLocaleString();
}

document.getElementById('checkoutForm').addEventListener('submit', function(){
    const btn = document.getElementById('placeOrderBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>{{ app()->getLocale()==="bn"?"প্রসেস করা হচ্ছে...":"Processing..." }}';
});
</script>
@endpush