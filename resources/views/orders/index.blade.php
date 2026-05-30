@extends('layouts.app')
@section('title', app()->getLocale()==='bn' ? 'আমার অর্ডার' : 'My Orders')

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

  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <a href="{{ route('dashboard') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);text-decoration:none;margin-bottom:6px;transition:color .2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--muted)'">
        <i class="fas fa-arrow-left" style="font-size:11px;"></i>
        <span class="bengali-text">{{ $isBn ? 'ড্যাশবোর্ডে ফিরুন' : 'Back to Dashboard' }}</span>
      </a>
      <h4 class="fw-bold mb-0" style="color:#1a9e4a;">
        <i class="fas fa-shopping-bag me-2"></i>
        <span class="bengali-text">{{ $isBn ? 'আমার অর্ডার' : 'My Orders' }}</span>
      </h4>
      <small class="text-muted bengali-text">{{ $isBn ? 'আপনার সব অর্ডারের তালিকা' : 'All your orders' }}</small>
    </div>
    <a href="{{ route('home') }}" class="btn btn-outline-success btn-sm">
      <i class="fas fa-store me-1"></i>
      <span class="bengali-text">{{ $isBn ? 'কেনাকাটা চালিয়ে যান' : 'Continue Shopping' }}</span>
    </a>
    <a href="{{ route('returns.index') }}" class="btn btn-outline-warning btn-sm">
      <i class="fas fa-undo-alt me-1"></i>
      <span class="bengali-text">{{ $isBn ? 'আমার রিটার্ন' : 'My Returns' }}</span>
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
      <h5 class="text-muted bengali-text">{{ $isBn ? 'এখনো কোনো অর্ডার নেই' : 'No orders yet' }}</h5>
      <p class="text-muted small bengali-text">{{ $isBn ? 'আপনার প্রথম অর্ডার দিন!' : 'Place your first order!' }}</p>
      <a href="{{ route('home') }}" class="btn btn-success mt-2">
        <i class="fas fa-shopping-cart me-2"></i>{{ $isBn ? 'এখনই কিনুন' : 'Shop Now' }}
      </a>
    </div>
  @else

  <div class="d-flex flex-column gap-3" id="is-orders-list">
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
      <div class="d-flex align-items-center justify-content-between px-4 py-2"
           style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
        <div class="d-flex align-items-center gap-3">
          <span class="fw-bold" style="color:#1a9e4a;font-size:14px;">#{{ $order->order_number }}</span>
          <span class="text-muted small"><i class="fas fa-calendar-alt me-1"></i>{{ $order->created_at->format('d M Y') }}</span>
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
                <span class="small text-muted">+{{ $order->items->count() - 2 }} {{ $isBn ? 'আরও পণ্য' : 'more items' }}</span>
              @endif
            </div>
          </div>
          <div class="col-md-3 text-md-center mt-2 mt-md-0">
            <div class="small text-muted">{{ $isBn ? 'মোট' : 'Total' }}</div>
            <div class="fw-bold" style="color:#1a9e4a;font-size:18px;">৳{{ number_format($order->total_amount, 0) }}</div>
            <div class="small" style="color:{{ $order->payment_status==='paid' ? '#16a34a' : '#94a3b8' }}">
              {{ $order->payment_status === 'paid' ? ($isBn ? '✓ পেমেন্ট হয়েছে' : '✓ Paid') : ($isBn ? 'পেমেন্ট বাকি' : 'Pending') }}
            </div>
          </div>
          <div class="col-md-2 text-md-end mt-2 mt-md-0">
            <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-success w-100 mb-1">
              <i class="fas fa-eye me-1"></i>{{ $isBn ? 'বিস্তারিত' : 'Details' }}
            </a>
            @if($order->status === 'pending')
            <form method="POST" action="{{ route('orders.cancel', $order) }}"
                  onsubmit="return confirm(this.dataset.msg)" data-msg="{{ $isBn ? 'অর্ডার #'.$order->order_number.' বাতিল করতে চান?' : 'Cancel Order #'.$order->order_number.'?' }}">
              @csrf
              <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                <i class="fas fa-times me-1"></i>{{ $isBn ? 'বাতিল করুন' : 'Cancel' }}
              </button>
            </form>
            @endif
            @if($order->status === 'delivered')
            @php
              $retDeadline  = $order->updated_at->copy()->addDays(7);
              $retCanReturn = \Carbon\Carbon::now()->lte($retDeadline);
              $retExisting  = \App\Models\ReturnRequest::where('order_id', $order->id)
                                ->where('user_id', auth()->id())
                                ->whereNotIn('status', ['rejected'])
                                ->first();
            @endphp
            @if($retExisting)
              <a href="{{ route('returns.show', $retExisting) }}"
                 class="btn btn-sm btn-outline-warning w-100 mt-1">
                <i class="fas fa-undo-alt me-1"></i>
                <span style="font-size:10px;">{{ $isBn ? 'রিটার্ন' : 'Return' }}</span>
                <span class="badge bg-{{ $retExisting->status_badge }} ms-1" style="font-size:9px;padding:2px 4px;">
                  {{ $isBn ? $retExisting->status_label_bn : $retExisting->status_label }}
                </span>
              </a>
            @elseif($retCanReturn)
              <a href="{{ route('returns.create', ['order_id' => $order->id]) }}"
                 class="btn btn-sm btn-warning w-100 mt-1 fw-semibold">
                <i class="fas fa-undo-alt me-1"></i>
                <span style="font-size:11px;">{{ $isBn ? 'রিটার্ন করুন' : 'Return' }}</span>
              </a>
              <div class="text-muted mt-1 text-center" style="font-size:9px;">
                <i class="fas fa-clock me-1"></i>
                {{ $isBn ? 'শেষ: ' : 'Till: ' }}{{ $retDeadline->format('d M') }}
              </div>
            @else
              <div class="text-muted mt-1 text-center" style="font-size:9px;">
                <i class="fas fa-lock me-1"></i>
                {{ $isBn ? 'রিটার্ন মেয়াদ শেষ' : 'Return expired' }}
              </div>
            @endif
            @endif
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  {{-- লোডিং স্পিনার --}}
  <div id="is-scroll-loader">
    <span class="is-spinner"></span>
    <span style="margin-left:10px;font-size:13px;color:var(--muted);" class="bengali-text">
      {{ $isBn ? 'আরও অর্ডার লোড হচ্ছে...' : 'Loading more orders...' }}
    </span>
  </div>
  {{-- শেষ চিহ্ন --}}
  <div id="is-end-marker">
    <i class="fas fa-check-circle" style="color:var(--primary);margin-right:6px;"></i>
    <span class="bengali-text">{{ $isBn ? 'সব অর্ডার দেখা হয়েছে' : 'All orders loaded' }}</span>
  </div>
  {{-- Sentinel --}}
  <div id="is-sentinel" style="height:1px;"></div>

  @endif
</div>
@endsection

@push('scripts')
<script>
(function() {
    const LOAD_URL       = "{{ route('orders.index') }}";
    const CSRF_TOKEN     = "{{ csrf_token() }}";
    const IS_BN          = {{ $isBn ? 'true' : 'false' }};
    const MORE_ITEMS_LBL = IS_BN ? 'আরও পণ্য' : 'more items';
    const TOTAL_LBL      = IS_BN ? 'মোট' : 'Total';
    const DETAILS_LBL    = IS_BN ? 'বিস্তারিত' : 'Details';
    const CANCEL_LBL     = IS_BN ? 'বাতিল করুন' : 'Cancel';
    const RETURN_LBL     = IS_BN ? 'রিটার্ন করুন' : 'Return';
    const RETURN_EXP_LBL = IS_BN ? 'রিটার্ন মেয়াদ শেষ' : 'Return expired';
    const PAID_LBL       = IS_BN ? '✓ পেমেন্ট হয়েছে' : '✓ Paid';
    const PENDING_LBL    = IS_BN ? 'পেমেন্ট বাকি' : 'Pending';
    const TILL_LBL       = IS_BN ? 'শেষ: ' : 'Till: ';
    const CANCEL_CONFIRM = IS_BN ? 'অর্ডার বাতিল করতে চান?' : 'Cancel this order?';

    let currentPage = 2;
    let isLoading   = false;
    let hasMore     = {{ $hasMore ? 'true' : 'false' }};

    const list      = document.getElementById('is-orders-list');
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

            if (data.orders && data.orders.length) {
                data.orders.forEach(o => list.insertAdjacentHTML('beforeend', buildOrderCard(o)));
            }

            hasMore     = data.has_more;
            currentPage = data.next_page;

            if (!hasMore) {
                if (endMarker) endMarker.style.display = 'block';
                observer.disconnect();
            }
        } catch(e) {
            console.error('অর্ডার লোড ব্যর্থ:', e);
        } finally {
            isLoading = false;
            if (loader) loader.style.display = 'none';
        }
    }

    function buildOrderCard(o) {
        // Item rows
        let itemsHtml = o.items.map(i =>
            `<div class="d-flex align-items-center gap-2">
                <i class="fas fa-cube text-muted" style="font-size:11px;"></i>
                <span class="small text-dark">${escHtml(i.name)}</span>
                <span class="text-muted small">× ${i.qty}</span>
            </div>`
        ).join('');
        if (o.extra_items > 0) {
            itemsHtml += `<span class="small text-muted">+${o.extra_items} ${MORE_ITEMS_LBL}</span>`;
        }

        // Action buttons
        let actionHtml = `<a href="${o.detail_url}" class="btn btn-sm btn-outline-success w-100 mb-1">
            <i class="fas fa-eye me-1"></i>${DETAILS_LBL}
        </a>`;
        if (o.is_pending) {
            actionHtml += `<button class="btn btn-sm btn-outline-danger w-100" onclick="cancelOrder('${o.cancel_url}','${escHtml(o.order_number)}')">
                <i class="fas fa-times me-1"></i>${CANCEL_LBL}
            </button>`;
        }
        if (o.is_delivered) {
            if (o.ret_existing) {
                actionHtml += `<a href="${o.ret_existing.url}" class="btn btn-sm btn-outline-warning w-100 mt-1">
                    <i class="fas fa-undo-alt me-1"></i><span style="font-size:10px;">${IS_BN ? 'রিটার্ন' : 'Return'}</span>
                    <span class="badge bg-${o.ret_existing.badge} ms-1" style="font-size:9px;padding:2px 4px;">${o.ret_existing.label}</span>
                </a>`;
            } else if (o.ret_can_return) {
                actionHtml += `<a href="${o.ret_create_url}" class="btn btn-sm btn-warning w-100 mt-1 fw-semibold">
                    <i class="fas fa-undo-alt me-1"></i><span style="font-size:11px;">${RETURN_LBL}</span>
                </a>
                <div class="text-muted mt-1 text-center" style="font-size:9px;">
                    <i class="fas fa-clock me-1"></i>${TILL_LBL}${o.ret_deadline}
                </div>`;
            } else {
                actionHtml += `<div class="text-muted mt-1 text-center" style="font-size:9px;">
                    <i class="fas fa-lock me-1"></i>${RETURN_EXP_LBL}
                </div>`;
            }
        }

        const payColor = o.payment_status === 'paid' ? '#16a34a' : '#94a3b8';

        return `
        <div class="card border-0 shadow-sm" style="border-radius:14px;overflow:hidden;">
            <div class="d-flex align-items-center justify-content-between px-4 py-2"
                 style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                <div class="d-flex align-items-center gap-3">
                    <span class="fw-bold" style="color:#1a9e4a;font-size:14px;">#${escHtml(o.order_number)}</span>
                    <span class="text-muted small"><i class="fas fa-calendar-alt me-1"></i>${o.created_at}</span>
                </div>
                <span class="badge rounded-pill px-3 py-2"
                      style="background:${o.status_bg};color:${o.status_text};border:1px solid ${o.status_border};font-size:11px;font-weight:600;">
                    ${o.status_label}
                </span>
            </div>
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-md-7"><div class="d-flex flex-column gap-1">${itemsHtml}</div></div>
                    <div class="col-md-3 text-md-center mt-2 mt-md-0">
                        <div class="small text-muted">${TOTAL_LBL}</div>
                        <div class="fw-bold" style="color:#1a9e4a;font-size:18px;">৳${o.total_amount}</div>
                        <div class="small" style="color:${payColor};">${o.payment_label}</div>
                    </div>
                    <div class="col-md-2 text-md-end mt-2 mt-md-0">${actionHtml}</div>
                </div>
            </div>
        </div>`;
    }

    function cancelOrder(url, orderNum) {
        if (!confirm(`${IS_BN ? 'অর্ডার #' : 'Cancel Order #'}${orderNum}${IS_BN ? ' বাতিল করতে চান?' : '?'}`)) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.innerHTML = `<input type="hidden" name="_token" value="${CSRF_TOKEN}">`;
        document.body.appendChild(form);
        form.submit();
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
