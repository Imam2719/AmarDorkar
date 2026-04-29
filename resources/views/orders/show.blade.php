@extends('layouts.app')
@section('title', 'অর্ডার #' . $order->order_number)

@section('content')
<div class="container py-4" style="max-width:860px;">

  {{-- Flash messages --}}
  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius:10px;">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif
  @if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:10px;">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  {{-- Back + Header --}}
  <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
      <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> ফিরে যান
      </a>
      <div>
        <h5 class="fw-bold mb-0">অর্ডার #{{ $order->order_number }}</h5>
        <small class="text-muted">{{ $order->created_at->format('d M Y, h:i A') }}</small>
      </div>
    </div>
    {{-- User cancel button — শুধু pending অর্ডারে দেখাবে --}}
    @if($order->status === 'pending')
    <form method="POST" action="{{ route('orders.cancel', $order) }}"
          onsubmit="return confirm('অর্ডার #{{ $order->order_number }} বাতিল করতে চান? এই কাজটি পূর্বাবস্থায় ফেরানো যাবে না।')">
      @csrf
      <button type="submit" class="btn btn-danger btn-sm">
        <i class="fas fa-times-circle me-1"></i> অর্ডার বাতিল করুন
      </button>
    </form>
    @endif
  </div>

  @if($order->status !== 'cancelled')
  {{-- ─── TRACKING TIMELINE ─── --}}
  <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
    <div class="card-header bg-white fw-semibold py-3 border-bottom">
      <i class="fas fa-map-marker-alt me-2 text-success"></i> ট্র্যাকিং স্ট্যাটাস
    </div>
    <div class="card-body py-4 px-4">
      <div class="d-flex align-items-start justify-content-between position-relative">
        {{-- connector line --}}
        <div style="position:absolute;top:20px;left:20px;right:20px;height:3px;background:#e2e8f0;z-index:0;"></div>
        <div style="position:absolute;top:20px;left:20px;height:3px;
                    width:{{ $currentIdx === false ? '0' : round(($currentIdx / (count($trackingSteps)-1)) * 100) }}%;
                    background:#1a9e4a;z-index:1;transition:width .5s;"></div>

        @foreach($trackingSteps as $i => $step)
        @php
          $done    = $currentIdx !== false && $i <= $currentIdx;
          $active  = $currentIdx !== false && $i === $currentIdx;
        @endphp
        <div class="d-flex flex-column align-items-center" style="flex:1;z-index:2;">
          <div style="
            width:42px;height:42px;border-radius:50%;
            background:{{ $done ? '#1a9e4a' : '#f1f5f9' }};
            border:3px solid {{ $done ? '#1a9e4a' : '#e2e8f0' }};
            display:flex;align-items:center;justify-content:center;
            box-shadow:{{ $active ? '0 0 0 4px #dcfce7' : 'none' }};
            transition:all .3s;
          ">
            <i class="{{ $step['icon'] }}" style="color:{{ $done ? '#fff' : '#94a3b8' }};font-size:15px;"></i>
          </div>
          <div class="mt-2 text-center" style="max-width:72px;">
            <div style="font-size:10px;font-weight:{{ $active ? '700' : '500' }};color:{{ $done ? '#1a9e4a' : '#94a3b8' }};">
              {{ $step['label'] }}
            </div>
          </div>
        </div>
        @endforeach
      </div>

      @if($order->status === 'delivered')
      <div class="text-center mt-3">
        <span class="badge bg-success px-3 py-2" style="font-size:13px;">
          <i class="fas fa-check-circle me-1"></i> অর্ডার ডেলিভার হয়েছে
        </span>
      </div>
      @endif
    </div>
  </div>
  @else
  <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" style="border-radius:12px;">
    <i class="fas fa-times-circle fa-lg"></i>
    <span>এই অর্ডারটি বাতিল করা হয়েছে।</span>
  </div>
  @endif

  <div class="row g-3">
    {{-- Order Items --}}
    <div class="col-md-7">
      <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
        <div class="card-header bg-white fw-semibold py-3 border-bottom">
          <i class="fas fa-box me-2 text-success"></i> অর্ডারের পণ্য
        </div>
        <div class="card-body p-0">
          @foreach($order->items as $item)
          <div class="d-flex align-items-center gap-3 px-4 py-3"
               style="{{ !$loop->last ? 'border-bottom:1px solid #f1f5f9;' : '' }}">
            @php
              $itemImgUrl = null;
              if ($item->product) {
                  $itemImgUrl = $item->product->image_url ?? null;
              }
            @endphp
            @if($itemImgUrl && !str_ends_with($itemImgUrl, 'no-image.png'))
              <img src="{{ $itemImgUrl }}"
                   style="width:52px;height:52px;object-fit:cover;border-radius:10px;border:1px solid #e2e8f0;"
                   alt="{{ $item->product_name }}">
            @else
              <div style="width:52px;height:52px;background:#f0fdf4;border-radius:10px;border:1px solid #e2e8f0;
                          display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-box" style="color:#16a34a;opacity:.5;"></i>
              </div>
            @endif
            <div class="flex-grow-1">
              <div class="fw-semibold" style="font-size:14px;">{{ $item->product_name }}</div>
              <div class="text-muted small">{{ $item->quantity }} × ৳{{ number_format($item->unit_price, 0) }}</div>
            </div>
            <div class="fw-bold text-success">৳{{ number_format($item->subtotal, 0) }}</div>
          </div>
          @endforeach

          {{-- Totals --}}
          <div class="px-4 py-3 bg-light" style="border-top:2px solid #e2e8f0;border-radius:0 0 14px 14px;">
            <div class="d-flex justify-content-between small text-muted mb-1">
              <span>সাবটোটাল</span>
              <span>৳{{ number_format($order->items->sum('subtotal'), 0) }}</span>
            </div>
            <div class="d-flex justify-content-between small text-muted mb-2">
              <span>শিপিং চার্জ</span>
              <span>{{ $order->shipping_charge > 0 ? '৳'.number_format($order->shipping_charge,0) : 'ফ্রি' }}</span>
            </div>
            <div class="d-flex justify-content-between fw-bold" style="font-size:16px;color:#1a9e4a;">
              <span>মোট</span>
              <span>৳{{ number_format($order->total_amount, 0) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Delivery & Payment Info --}}
    <div class="col-md-5 d-flex flex-column gap-3">
      {{-- Shipping --}}
      <div class="card border-0 shadow-sm" style="border-radius:14px;">
        <div class="card-header bg-white fw-semibold py-2 border-bottom" style="font-size:13px;">
          <i class="fas fa-map-marker-alt me-2 text-success"></i> ডেলিভারি ঠিকানা
        </div>
        <div class="card-body py-3" style="font-size:13px;color:#374151;">
          <div class="fw-semibold">{{ $order->name }}</div>
          <div class="text-muted">{{ $order->address }}</div>
          @if($order->city)
            <div class="text-muted">{{ $order->city }}</div>
          @endif
          @if($order->phone)
            <div class="mt-1">
              <i class="fas fa-phone me-1 text-muted"></i>{{ $order->phone }}
            </div>
          @endif
        </div>
      </div>

      {{-- Payment --}}
      <div class="card border-0 shadow-sm" style="border-radius:14px;">
        <div class="card-header bg-white fw-semibold py-2 border-bottom" style="font-size:13px;">
          <i class="fas fa-credit-card me-2 text-success"></i> পেমেন্ট তথ্য
        </div>
        <div class="card-body py-3" style="font-size:13px;">
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">পদ্ধতি</span>
            <span class="fw-semibold">{{ strtoupper($order->payment_method ?? 'COD') }}</span>
          </div>
          <div class="d-flex justify-content-between">
            <span class="text-muted">স্ট্যাটাস</span>
            @if($order->payment_status === 'paid')
              <span class="badge bg-success">পেমেন্ট হয়েছে ✓</span>
            @else
              <span class="badge bg-secondary">পেমেন্ট বাকি</span>
            @endif
          </div>
        </div>
      </div>

      {{-- Need Help? --}}
      <div class="card border-0" style="border-radius:14px;background:#f0fdf4;border:1px dashed #86efac;">
        <div class="card-body py-3 text-center" style="font-size:13px;">
          <i class="fas fa-headset fa-2x mb-2 d-block" style="color:#1a9e4a;"></i>
          <div class="fw-semibold text-success mb-1">সাহায্য দরকার?</div>
          <a href="mailto:{{ config('mail.from.address') }}" class="btn btn-sm btn-outline-success">
            <i class="fas fa-envelope me-1"></i>যোগাযোগ করুন
          </a>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection
