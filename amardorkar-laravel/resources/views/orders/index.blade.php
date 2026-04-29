@extends('layouts.app')
@section('title', 'আমার অর্ডার')

@section('content')
<div class="container py-4" style="max-width:900px;">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="fw-bold mb-0" style="color:#1a9e4a;"><i class="fas fa-shopping-bag me-2"></i>আমার অর্ডার</h4>
      <small class="text-muted">আপনার সব অর্ডারের তালিকা</small>
    </div>
    <a href="{{ route('home') }}" class="btn btn-outline-success btn-sm">
      <i class="fas fa-store me-1"></i> কেনাকাটা চালিয়ে যান
    </a>
  </div>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show mb-3" role="alert" style="border-radius:10px;">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif
  @if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert" style="border-radius:10px;">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  @if($orders->isEmpty())
    <div class="text-center py-5">
      <i class="fas fa-box-open fa-4x mb-3 d-block" style="color:#d1fae5;"></i>
      <h5 class="text-muted">এখনো কোনো অর্ডার নেই</h5>
      <p class="text-muted small">আপনার প্রথম অর্ডার দিন!</p>
      <a href="{{ route('home') }}" class="btn btn-success mt-2">
        <i class="fas fa-shopping-cart me-2"></i>এখনই কিনুন
      </a>
    </div>
  @else

  <div class="d-flex flex-column gap-3">
    @foreach($orders as $order)
    @php
      $statusColors = [
        'pending'=>['bg'=>'#fef3c7','text'=>'#92400e','border'=>'#fde68a'],
        'confirmed'=>['bg'=>'#dbeafe','text'=>'#1e40af','border'=>'#bfdbfe'],
        'processing'=>['bg'=>'#ede9fe','text'=>'#5b21b6','border'=>'#ddd6fe'],
        'shipped'=>['bg'=>'#e0f2fe','text'=>'#0369a1','border'=>'#bae6fd'],
        'delivered'=>['bg'=>'#dcfce7','text'=>'#166534','border'=>'#bbf7d0'],
        'cancelled'=>['bg'=>'#fee2e2','text'=>'#991b1b','border'=>'#fecaca'],
      ];
      $sc = $statusColors[$order->status] ?? ['bg'=>'#f3f4f6','text'=>'#374151','border'=>'#e5e7eb'];
    @endphp

    <div class="card border-0 shadow-sm" style="border-radius:14px;overflow:hidden;">
      {{-- Header bar --}}
      <div class="d-flex align-items-center justify-content-between px-4 py-2"
           style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
        <div class="d-flex align-items-center gap-3">
          <span class="fw-bold" style="color:#1a9e4a;font-size:14px;">
            #{{ $order->order_number }}
          </span>
          <span class="text-muted small">
            <i class="fas fa-calendar-alt me-1"></i>{{ $order->created_at->format('d M Y') }}
          </span>
        </div>
        <span class="badge rounded-pill px-3 py-2"
              style="background:{{ $sc['bg'] }};color:{{ $sc['text'] }};border:1px solid {{ $sc['border'] }};font-size:11px;font-weight:600;">
          {{ ucfirst($order->status) }}
        </span>
      </div>

      <div class="card-body px-4 py-3">
        <div class="row align-items-center">
          <div class="col-md-7">
            <div class="d-flex flex-column gap-1">
              @foreach($order->items->take(2) as $item)
                <div class="d-flex align-items-center gap-2">
                  <i class="fas fa-cube text-muted" style="font-size:11px;"></i>
                  <span class="small text-dark">{{ $item->product_name }}</span>
                  <span class="text-muted small">× {{ $item->quantity }}</span>
                </div>
              @endforeach
              @if($order->items->count() > 2)
                <span class="small text-muted">+{{ $order->items->count() - 2 }} আরও পণ্য</span>
              @endif
            </div>
          </div>
          <div class="col-md-3 text-md-center mt-2 mt-md-0">
            <div class="small text-muted">মোট</div>
            <div class="fw-bold" style="color:#1a9e4a;font-size:18px;">৳{{ number_format($order->total_amount, 0) }}</div>
            <div class="small" style="color:{{ $order->payment_status==='paid' ? '#16a34a' : '#94a3b8' }}">
              {{ $order->payment_status === 'paid' ? '✓ পেমেন্ট হয়েছে' : 'পেমেন্ট বাকি' }}
            </div>
          </div>
          <div class="col-md-2 text-md-end mt-2 mt-md-0">
            <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-success w-100 mb-1">
              <i class="fas fa-eye me-1"></i>বিস্তারিত
            </a>
            @if($order->status === 'pending')
            <form method="POST" action="{{ route('orders.cancel', $order) }}"
                  onsubmit="return confirm('অর্ডার #{{ $order->order_number }} বাতিল করতে চান?')">
              @csrf
              <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                <i class="fas fa-times me-1"></i>বাতিল করুন
              </button>
            </form>
            @endif
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  <div class="mt-4">
    {{ $orders->links() }}
  </div>

  @endif
</div>
@endsection
