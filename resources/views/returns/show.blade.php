@extends('layouts.app')
@section('title', __('messages.return_request') . ' #' . $returnRequest->return_number)

@section('content')
<div class="container py-4" style="max-width:860px;">

  {{-- Flash messages --}}
  @foreach(['success','error','info'] as $type)
  @if(session($type))
  <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} alert-dismissible fade show" style="border-radius:10px;">
    {{ session($type) }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif
  @endforeach

  {{-- Back + header --}}
  <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
    <a href="{{ route('returns.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left me-1"></i>{{ __('messages.back') }}
    </a>
    <div>
      <h5 class="fw-bold mb-0">
        <i class="fas fa-undo-alt me-2 text-warning"></i>{{ __('messages.return_request') }} #{{ $returnRequest->return_number }}
      </h5>
      <small class="text-muted">{{ $returnRequest->created_at->format('d M Y, h:i A') }}</small>
    </div>
    <span class="ms-auto badge bg-{{ $returnRequest->status_badge }} fs-6 px-3 py-2">
      {{ app()->getLocale() === 'bn' ? $returnRequest->status_label_bn : $returnRequest->status_label }}
    </span>
  </div>

  {{-- Status timeline --}}
  @php
    $allStatuses  = ['pending','under_review','approved','refund_initiated','refunded'];
    $currentIndex = array_search($returnRequest->status, $allStatuses);
    $rejected     = $returnRequest->status === 'rejected';
    $statusLabels = app()->getLocale() === 'bn'
      ? ['মুলতবি','পর্যালোচনাধীন','অনুমোদিত','রিফান্ড শুরু','রিফান্ড সম্পন্ন']
      : ['Pending','Under Review','Approved','Refund Initiated','Refunded'];
    $statusIcons  = ['fas fa-clock','fas fa-search','fas fa-check','fas fa-paper-plane','fas fa-check-double'];
  @endphp

  @if(!$rejected)
  <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
    <div class="card-header bg-white fw-semibold py-3 border-bottom">
      <i class="fas fa-map-marker-alt me-2 text-warning"></i>{{ __('messages.return_status') }}
    </div>
    <div class="card-body py-4 px-3 px-md-4">
      <div class="d-flex align-items-start justify-content-between position-relative overflow-auto">
        <div style="position:absolute;top:20px;left:20px;right:20px;height:3px;background:#e2e8f0;z-index:0;"></div>
        <div style="position:absolute;top:20px;left:20px;height:3px;
          width:{{ $currentIndex === false ? '0' : round(($currentIndex / (count($allStatuses)-1)) * 100) }}%;
          background:#f59e0b;z-index:1;transition:width .5s;"></div>
        @foreach($allStatuses as $i => $st)
        @php $done = $currentIndex !== false && $i <= $currentIndex; @endphp
        <div class="d-flex flex-column align-items-center flex-shrink-0" style="flex:1;z-index:2;min-width:60px;">
          <div style="width:40px;height:40px;border-radius:50%;
            background:{{ $done ? '#f59e0b' : '#f1f5f9' }};
            border:3px solid {{ $done ? '#f59e0b' : '#e2e8f0' }};
            display:flex;align-items:center;justify-content:center;">
            <i class="{{ $statusIcons[$i] }}" style="color:{{ $done ? '#fff' : '#94a3b8' }};font-size:13px;"></i>
          </div>
          <div class="mt-2 text-center" style="max-width:70px;">
            <div style="font-size:10px;font-weight:{{ $done ? '700' : '500' }};color:{{ $done ? '#f59e0b' : '#94a3b8' }};">
              {{ $statusLabels[$i] }}
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
  @else
  <div class="alert alert-danger d-flex gap-2 align-items-center" style="border-radius:12px;">
    <i class="fas fa-times-circle fa-lg flex-shrink-0"></i>
    <div>
      <strong>{{ __('messages.return_rejected_title') }}</strong><br>
      <small>{{ __('messages.return_rejected_desc') }}</small>
    </div>
  </div>
  @endif

  <div class="row g-4">
    {{-- Left: Request details --}}
    <div class="col-12 col-md-7">
      <div class="card border-0 shadow-sm h-100" style="border-radius:12px;">
        <div class="card-header bg-white fw-semibold py-3 border-bottom" style="border-radius:12px 12px 0 0;">
          <i class="fas fa-clipboard-list me-2 text-warning"></i>{{ __('messages.request_details') }}
        </div>
        <div class="card-body p-3 p-md-4">
          <table class="table table-borderless table-sm" style="font-size:.9rem;">
            <tr>
              <td class="text-muted" style="width:40%;">{{ __('messages.return_reason') }}</td>
              <td class="fw-semibold">{{ app()->getLocale() === 'bn' ? $returnRequest->reason_label_bn : $returnRequest->reason_label }}</td>
            </tr>
            <tr>
              <td class="text-muted">{{ __('messages.submitted_on') }}</td>
              <td>{{ $returnRequest->created_at->format('d M Y, h:i A') }}</td>
            </tr>
            @if($returnRequest->resolved_at)
            <tr>
              <td class="text-muted">{{ __('messages.resolved_on') }}</td>
              <td>{{ $returnRequest->resolved_at->format('d M Y, h:i A') }}</td>
            </tr>
            @endif
            @if($returnRequest->refund_amount)
            <tr>
              <td class="text-muted">{{ __('messages.refund_amount') }}</td>
              <td class="fw-bold text-success">৳{{ number_format($returnRequest->refund_amount, 2) }}</td>
            </tr>
            @endif
            @if($returnRequest->refund_method)
            <tr>
              <td class="text-muted">{{ __('messages.refund_method') }}</td>
              <td>{{ $returnRequest->refund_method }}</td>
            </tr>
            @endif
          </table>

          <div class="mt-2">
            <div class="text-muted fw-semibold mb-1" style="font-size:.87rem;">{{ __('messages.your_description') }}</div>
            <div class="p-3" style="background:#f8fafc;border-radius:8px;font-size:.9rem;line-height:1.7;">{{ $returnRequest->description }}</div>
          </div>

          @if($returnRequest->admin_notes)
          <div class="mt-3">
            <div class="text-muted fw-semibold mb-1" style="font-size:.87rem;">
              <i class="fas fa-comment-dots me-1 text-info"></i>{{ __('messages.admin_response') }}
            </div>
            <div class="p-3" style="background:#eff6ff;border-radius:8px;font-size:.9rem;line-height:1.7;border-left:4px solid #3b82f6;">
              {{ $returnRequest->admin_notes }}
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>

    {{-- Right: Order + Images --}}
    <div class="col-12 col-md-5">
      {{-- Order Info --}}
      <div class="card border-0 shadow-sm mb-3" style="border-radius:12px;">
        <div class="card-header bg-white fw-semibold py-3 border-bottom" style="border-radius:12px 12px 0 0;">
          <i class="fas fa-receipt me-2 text-success"></i>{{ __('messages.order_info') }}
        </div>
        <div class="card-body p-3" style="font-size:.88rem;">
          <div class="text-muted">{{ __('messages.order_number') }}</div>
          <div class="fw-bold mb-2">
            <a href="{{ route('orders.show', $returnRequest->order) }}" class="text-decoration-none">
              {{ $returnRequest->order->order_number }}
            </a>
          </div>
          <div class="text-muted">{{ __('messages.total_amount') }}</div>
          <div class="text-success fw-bold">৳{{ number_format($returnRequest->order->total_amount, 2) }}</div>
          <div class="mt-2">
            @foreach($returnRequest->order->items as $item)
            <div class="d-flex gap-2 align-items-center mb-1">
              @php $img = $item->product?->images->first(); @endphp
              @if($img)
              <img src="{{ route('image.product', $img->id) }}" style="width:32px;height:32px;object-fit:cover;border-radius:6px;">
              @endif
              <span style="font-size:.82rem;">{{ $item->product_name }} × {{ $item->quantity }}</span>
            </div>
            @endforeach
          </div>
        </div>
      </div>

      {{-- Uploaded images --}}
      @if($returnRequest->images->isNotEmpty())
      <div class="card border-0 shadow-sm" style="border-radius:12px;">
        <div class="card-header bg-white fw-semibold py-3 border-bottom" style="border-radius:12px 12px 0 0;">
          <i class="fas fa-images me-2 text-warning"></i>{{ __('messages.submitted_images') }}
        </div>
        <div class="card-body p-3">
          <div class="d-flex flex-wrap gap-2">
            @foreach($returnRequest->images as $img)
            <a href="{{ route('image.return', $img->id) }}" target="_blank">
              <img src="{{ route('image.return', $img->id) }}"
                   style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:2px solid #e5e7eb;transition:transform .2s;"
                   onmouseover="this.style.transform='scale(1.05)'"
                   onmouseout="this.style.transform='scale(1)'">
            </a>
            @endforeach
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>

</div>
@endsection
