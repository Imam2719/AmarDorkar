@extends('layouts.app')
@section('title', __('messages.order_success') . ' — ' . __('messages.site_name'))

@section('content')
<div class="fw-wrap" style="margin-top:32px;margin-bottom:56px;">
    <div style="max-width:680px;margin:0 auto;">

        {{-- Success Card --}}
        <div style="background:var(--surface);border-radius:16px;padding:36px 32px;border:1px solid var(--border);text-align:center;margin-bottom:24px;">
            <div style="width:80px;height:80px;background:#e8f5e9;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                <i class="fas fa-check" style="font-size:36px;color:#1a9e4a;"></i>
            </div>
            <h2 class="bengali-text" style="font-size:22px;font-weight:700;color:var(--text);margin-bottom:8px;">
                {{ __('messages.order_success') }}
            </h2>
            <p class="bengali-text" style="font-size:16px;color:var(--primary);font-weight:600;margin-bottom:4px;">
                {{ app()->getLocale()==='bn'
                    ? $order->customer_name . ' ভাই/আপু, আপনার অর্ডারটি সফলভাবে গ্রহণ করা হয়েছে! 🎉'
                    : 'Hi ' . $order->customer_name . ', your order has been placed successfully! 🎉' }}
            </p>
            <p class="bengali-text text-muted" style="font-size:13px;margin:0;">
                {{ __('messages.order_placed_successfully') }}
            </p>
        </div>

        {{-- Order Details --}}
        <div style="background:var(--surface);border-radius:14px;padding:24px;border:1px solid var(--border);margin-bottom:20px;">
            <h5 class="bengali-text mb-3" style="font-weight:600;">{{ __('messages.order_summary') }}</h5>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:14px;">
                <div style="background:var(--surface-2);border-radius:8px;padding:12px 14px;">
                    <div style="color:var(--muted);font-size:12px;margin-bottom:4px;">{{ __('messages.order_number') }}</div>
                    <div style="font-weight:700;color:var(--success);">{{ $order->order_number }}</div>
                </div>
                <div style="background:var(--surface-2);border-radius:8px;padding:12px 14px;">
                    <div style="color:var(--muted);font-size:12px;margin-bottom:4px;">{{ __('messages.total') }}</div>
                    <div style="font-weight:700;font-size:16px;color:var(--success);">৳{{ number_format($order->total_amount, 0) }}</div>
                </div>
                <div style="background:var(--surface-2);border-radius:8px;padding:12px 14px;">
                    <div style="color:var(--muted);font-size:12px;margin-bottom:4px;">{{ __('messages.order_date') }}</div>
                    <div style="font-weight:500;">{{ $order->created_at->format('d M Y') }}</div>
                </div>
                <div style="background:var(--surface-2);border-radius:8px;padding:12px 14px;">
                    <div style="color:var(--muted);font-size:12px;margin-bottom:4px;">{{ __('messages.payment_method') }}</div>
                    <div style="font-weight:500;" class="bengali-text">{{ __('messages.cash_on_delivery') }}</div>
                </div>
            </div>
        </div>

        {{-- Ordered Items --}}
        @if($order->items->count())
        <div style="background:var(--surface);border-radius:14px;padding:24px;border:1px solid var(--border);margin-bottom:20px;">
            <h5 class="bengali-text mb-3" style="font-weight:600;">
                <i class="fas fa-box me-2" style="color:var(--primary);"></i>
                {{ app()->getLocale()==='bn' ? 'অর্ডারকৃত পণ্যসমূহ' : 'Ordered Items' }}
            </h5>
            @foreach($order->items as $item)
            <div style="display:flex;gap:12px;padding:12px 0;border-bottom:1px solid var(--border);align-items:center;">
                <div style="width:52px;height:52px;border-radius:8px;overflow:hidden;background:var(--surface-2);flex-shrink:0;border:1px solid var(--border);">
                    @if($item->product && $item->product->image_url)
                        <img src="{{ $item->product->image_url }}" style="width:100%;height:100%;object-fit:contain;" alt="{{ $item->product_name }}">
                    @else
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;"><i class="fas fa-image text-muted"></i></div>
                    @endif
                </div>
                <div style="flex:1;">
                    <div class="bengali-text" style="font-size:13px;font-weight:600;">{{ $item->product_name }}</div>
                    <div style="font-size:12px;color:var(--muted);">
                        {{ __('messages.quantity') }}: {{ $item->quantity }} × ৳{{ number_format($item->price, 0) }}
                    </div>
                </div>
                <div style="font-weight:700;color:var(--success);">৳{{ number_format($item->subtotal, 0) }}</div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Customer Info --}}
        <div style="background:var(--surface);border-radius:14px;padding:24px;border:1px solid var(--border);margin-bottom:20px;">
            <h5 class="bengali-text mb-3" style="font-weight:600;">{{ __('messages.billing_details') }}</h5>
            <div style="font-size:14px;line-height:2;color:var(--muted);">
                <div><i class="fas fa-user me-2" style="color:var(--primary);width:16px;"></i>{{ $order->customer_name }}</div>
                <div><i class="fas fa-phone me-2" style="color:var(--primary);width:16px;"></i>{{ $order->customer_mobile }}</div>
                @if($order->customer_email)
                <div><i class="fas fa-envelope me-2" style="color:var(--primary);width:16px;"></i>{{ $order->customer_email }}</div>
                @endif
                <div><i class="fas fa-map-marker-alt me-2" style="color:var(--primary);width:16px;"></i>{{ $order->customer_address }}</div>
                <div>
                    <i class="fas fa-truck me-2" style="color:var(--primary);width:16px;"></i>
                    <span class="bengali-text">
                        {{ $order->shipping_location === 'outside_dhaka' ? __('messages.outside_dhaka') : __('messages.inside_dhaka') }}
                    </span>
                    — ৳{{ number_format($order->shipping_charge, 0) }}
                </div>
            </div>
        </div>

        {{-- What's next --}}
        <div style="background:linear-gradient(135deg,#1a9e4a 0%,#0a5a2a 100%);border-radius:14px;padding:20px 24px;color:#fff;margin-bottom:20px;">
            <h6 style="font-weight:600;margin-bottom:12px;">
                {{ app()->getLocale()==='bn'?'পরবর্তী পদক্ষেপ':'What happens next?' }}
            </h6>
            <div style="font-size:13px;line-height:1.8;opacity:.9;" class="bengali-text">
                @if(app()->getLocale()==='bn')
                <div><i class="fas fa-check-circle me-2"></i>আমরা শীঘ্রই আপনার অর্ডার কনফার্ম করব</div>
                <div><i class="fas fa-box me-2"></i>পণ্য প্যাক করে ডেলিভারির জন্য প্রস্তুত করা হবে</div>
                <div><i class="fas fa-truck me-2"></i>{{ $order->shipping_location === 'outside_dhaka' ? 'কুরিয়ারে ১-৩ কার্যদিবসে' : 'ঢাকায় ১-২ ঘন্টার মধ্যে' }} পৌঁছে দেওয়া হবে</div>
                <div><i class="fas fa-money-bill-wave me-2"></i>পণ্য পেয়ে পেমেন্ট করুন</div>
                @else
                <div><i class="fas fa-check-circle me-2"></i>We will confirm your order shortly</div>
                <div><i class="fas fa-box me-2"></i>Your order will be packed and prepared for delivery</div>
                <div><i class="fas fa-truck me-2"></i>{{ $order->shipping_location === 'outside_dhaka' ? 'Delivered within 1–3 business days' : 'Delivered within 1–2 hours in Dhaka' }}</div>
                <div><i class="fas fa-money-bill-wave me-2"></i>Pay in cash when you receive your order</div>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <a href="{{ route('home') }}" class="btn btn-success flex-grow-1" style="padding:12px;font-size:14px;">
                <i class="fas fa-shopping-bag me-2"></i>{{ __('messages.continue_shopping_btn') }}
            </a>
            @auth
            <a href="{{ route('dashboard') }}" class="btn btn-outline-success flex-grow-1" style="padding:12px;font-size:14px;">
                <i class="fas fa-tachometer-alt me-2"></i>{{ app()->getLocale()==='bn'?'ড্যাশবোর্ড':'Dashboard' }}
            </a>
            @endauth
        </div>

        {{-- Contact --}}
        <div style="text-align:center;margin-top:20px;font-size:13px;color:var(--muted);">
            <i class="fas fa-headset me-1"></i>
            {{ app()->getLocale()==='bn'?'সাহায্যের জন্য কল করুন:':'For support call:' }}
            <a href="tel:01720981682" style="color:var(--success);text-decoration:none;font-weight:600;">01720981682</a>
        </div>
    </div>
</div>
@endsection