@extends('layouts.app')
@section('title', __('messages.my_returns'))

@push('styles')
<style>
#is-scroll-loader { display:none; text-align:center; padding:24px 0; }
#is-scroll-loader .is-spinner { display:inline-block; width:28px; height:28px; border:3px solid var(--border); border-top-color:var(--primary); border-radius:50%; animation:is-spin .7s linear infinite; }
@keyframes is-spin { to { transform:rotate(360deg) } }
#is-end-marker { display:none; text-align:center; padding:16px 0; font-size:13px; color:var(--muted); }
</style>
@endpush

@section('content')
@php $isBn = app()->getLocale()==='bn'; @endphp
<div class="container py-4" style="max-width:900px;">

  {{-- Flash messages --}}
  @foreach(['success','error','info'] as $type)
  @if(session($type))
  <div class="alert alert-{{ $type === 'info' ? 'info' : ($type === 'error' ? 'danger' : 'success') }} alert-dismissible fade show" role="alert" style="border-radius:10px;">
    {{ session($type) }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif
  @endforeach

  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <h5 class="fw-bold mb-0"><i class="fas fa-undo-alt me-2 text-warning"></i>{{ __('messages.my_returns') }}</h5>
      <small class="text-muted">{{ __('messages.returns_subtitle') }}</small>
    </div>
    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left me-1"></i>{{ __('messages.back_to_orders') }}
    </a>
  </div>

  @if($returns->isEmpty())
  <div class="text-center py-5">
    <i class="fas fa-box-open fa-3x text-muted mb-3 d-block"></i>
    <p class="text-muted">{{ __('messages.no_returns_yet') }}</p>
    <a href="{{ route('orders.index') }}" class="btn btn-success btn-sm mt-2">
      {{ __('messages.view_orders') }}
    </a>
  </div>
  @else

  <div class="d-flex flex-column gap-3" id="is-returns-list">
    @foreach($returns as $ret)
    <div class="card border-0 shadow-sm" style="border-radius:12px;">
      <div class="card-body p-3 p-md-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
          <div>
            <span class="fw-bold" style="font-size:.95rem;">{{ $ret->return_number }}</span>
            <span class="ms-2 badge bg-{{ $ret->status_badge }}">
              {{ $isBn ? $ret->status_label_bn : $ret->status_label }}
            </span>
          </div>
          <small class="text-muted">{{ $ret->created_at->format('d M Y') }}</small>
        </div>
        <div class="mt-2 text-muted" style="font-size:.88rem;">
          <i class="fas fa-receipt me-1"></i>
          {{ __('messages.order') }}: <strong>{{ $ret->order->order_number ?? '—' }}</strong>
          &nbsp;·&nbsp;
          {{ $isBn ? $ret->reason_label_bn : $ret->reason_label }}
        </div>
        @if($ret->refund_amount)
        <div class="mt-1" style="font-size:.88rem;">
          <i class="fas fa-money-bill-wave me-1 text-success"></i>
          {{ __('messages.refund_amount') }}: <strong class="text-success">৳{{ number_format($ret->refund_amount, 2) }}</strong>
        </div>
        @endif
        <div class="mt-3">
          <a href="{{ route('returns.show', $ret) }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-eye me-1"></i>{{ __('messages.view_details') }}
          </a>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  {{-- লোডিং স্পিনার --}}
  <div id="is-scroll-loader">
    <span class="is-spinner"></span>
    <span style="margin-left:10px;font-size:13px;color:var(--muted);" class="bengali-text">
      {{ $isBn ? 'আরও রিটার্ন লোড হচ্ছে...' : 'Loading more returns...' }}
    </span>
  </div>
  {{-- শেষ চিহ্ন --}}
  <div id="is-end-marker">
    <i class="fas fa-check-circle" style="color:var(--primary);margin-right:6px;"></i>
    <span class="bengali-text">{{ $isBn ? 'সব রিটার্ন দেখা হয়েছে' : 'All returns loaded' }}</span>
  </div>
  {{-- Sentinel --}}
  <div id="is-sentinel" style="height:1px;"></div>

  @endif
</div>
@endsection

@push('scripts')
<script>
(function() {
    const LOAD_URL       = "{{ route('returns.index') }}";
    const IS_BN          = {{ $isBn ? 'true' : 'false' }};
    const ORDER_LBL      = "{{ __('messages.order') }}";
    const REFUND_LBL     = "{{ __('messages.refund_amount') }}";
    const DETAILS_LBL    = "{{ __('messages.view_details') }}";

    let currentPage = 2;
    let isLoading   = false;
    let hasMore     = {{ $hasMore ? 'true' : 'false' }};

    const list      = document.getElementById('is-returns-list');
    const loader    = document.getElementById('is-scroll-loader');
    const endMarker = document.getElementById('is-end-marker');
    const sentinel  = document.getElementById('is-sentinel');

    if (!hasMore && endMarker) endMarker.style.display = 'block';

    async function loadMore() {
        if (isLoading || !hasMore || !list) return;
        isLoading = true;
        if (loader) loader.style.display = 'block';

        try {
            const res  = await fetch(`${LOAD_URL}?page=${currentPage}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const data = await res.json();

            if (data.returns && data.returns.length) {
                data.returns.forEach(r => list.insertAdjacentHTML('beforeend', buildReturnCard(r)));
            }

            hasMore     = data.has_more;
            currentPage = data.next_page;

            if (!hasMore) {
                if (endMarker) endMarker.style.display = 'block';
                observer.disconnect();
            }
        } catch(e) {
            console.error('রিটার্ন লোড ব্যর্থ:', e);
        } finally {
            isLoading = false;
            if (loader) loader.style.display = 'none';
        }
    }

    function buildReturnCard(r) {
        const refundHtml = r.refund_amount
            ? `<div class="mt-1" style="font-size:.88rem;">
                <i class="fas fa-money-bill-wave me-1 text-success"></i>
                ${REFUND_LBL}: <strong class="text-success">৳${r.refund_amount}</strong>
               </div>`
            : '';
        return `
        <div class="card border-0 shadow-sm" style="border-radius:12px;">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div>
                        <span class="fw-bold" style="font-size:.95rem;">${escHtml(r.return_number)}</span>
                        <span class="ms-2 badge bg-${r.status_badge}">${escHtml(r.status_label)}</span>
                    </div>
                    <small class="text-muted">${r.created_at}</small>
                </div>
                <div class="mt-2 text-muted" style="font-size:.88rem;">
                    <i class="fas fa-receipt me-1"></i>
                    ${ORDER_LBL}: <strong>${escHtml(r.order_number)}</strong>
                    &nbsp;·&nbsp; ${escHtml(r.reason_label)}
                </div>
                ${refundHtml}
                <div class="mt-3">
                    <a href="${r.detail_url}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye me-1"></i>${DETAILS_LBL}
                    </a>
                </div>
            </div>
        </div>`;
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    const observer = new IntersectionObserver(entries => {
        if (entries[0].isIntersecting) loadMore();
    }, { rootMargin: '200px' });

    if (sentinel && hasMore) observer.observe(sentinel);
})();
</script>
@endpush
