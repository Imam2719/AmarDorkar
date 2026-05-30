@extends('layouts.admin')
@section('title', app()->getLocale() === 'bn' ? 'রিটার্ন রিকোয়েস্ট' : 'Return Requests')
@section('page-title', app()->getLocale() === 'bn' ? 'রিটার্ন রিকোয়েস্ট' : 'Return Requests')

@push('styles')
<style>
/* ── Return Requests — Dark + Infinite Scroll ─── */
.rr-filter-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  padding: 18px 20px;
  margin-bottom: 20px;
  box-shadow: var(--sh-xs);
}
.rr-filter-row {
  display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;
}
.rr-filter-row .fg      { flex: 1 1 180px; min-width: 0; }
.rr-filter-row .fg-sm   { flex: 1 1 130px; min-width: 0; }
.rr-filter-row .fg-fixed{ flex: 0 0 auto; }

/* Stats grid */
.rr-stats {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 12px;
  margin-bottom: 20px;
}
.rr-stat-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--r);
  padding: 14px 16px;
  display: flex; align-items: center; gap: 12px;
  box-shadow: var(--sh-xs);
  border-left-width: 4px;
}
.rr-stat-icon {
  width: 38px; height: 38px; border-radius: 9px;
  display: flex; align-items: center; justify-content: center;
  font-size: 14px; flex-shrink: 0;
}
.rr-stat-val  { font-size: 20px; font-weight: 800; color: var(--text); line-height: 1; }
.rr-stat-lbl  { font-size: 11px; color: var(--muted); margin-top: 2px; }

/* Main card */
.rr-main-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  box-shadow: var(--sh-xs);
  overflow: hidden;
}
.rr-card-head {
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 10px;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
}
.rr-card-title {
  display: flex; align-items: center; gap: 8px;
  font-size: 15px; font-weight: 700; color: var(--text);
}

/* Table */
.rr-table-wrap { overflow-x: auto; }
.rr-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.rr-table thead tr {
  background: var(--bg);
  border-bottom: 1px solid var(--border);
}
.rr-table thead th {
  padding: 11px 14px;
  text-align: left;
  font-size: 11px; font-weight: 700;
  text-transform: uppercase; letter-spacing: .6px;
  color: var(--muted); white-space: nowrap;
}
.rr-table tbody tr {
  border-bottom: 1px solid var(--border-2);
  transition: background .12s;
}
.rr-table tbody tr:last-child { border-bottom: none; }
.rr-table tbody tr:hover { background: var(--hover); }
.rr-table td {
  padding: 12px 14px;
  vertical-align: middle;
  color: var(--text);
}

/* Status badges */
.rr-badge {
  display: inline-block; padding: 3px 9px;
  border-radius: 6px; font-size: 11px; font-weight: 700;
}
.rr-badge-pending          { background:rgba(245,158,11,.15);  color:#d97706; }
.rr-badge-under_review     { background:rgba(59,130,246,.15);  color:#3b82f6; }
.rr-badge-approved         { background:rgba(16,185,129,.15);  color:#10b981; }
.rr-badge-rejected         { background:rgba(239,68,68,.15);   color:#ef4444; }
.rr-badge-refund_initiated { background:rgba(139,92,246,.15);  color:#8b5cf6; }
.rr-badge-refunded         { background:rgba(99,102,241,.15);  color:#6366f1; }

/* Action btns */
.rr-action-btns { display: flex; gap: 5px; }
.rr-btn {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 5px 10px; border-radius: 7px;
  border: 1px solid transparent; cursor: pointer;
  background: none; transition: all .15s;
  font-size: 12px; font-weight: 600;
  text-decoration: none;
}
.rr-btn:hover { transform: translateY(-1px); }
.rr-btn-view { color: var(--primary); border-color: var(--primary); }
.rr-btn-view:hover { background: rgba(37,99,235,.1); color: var(--primary); }
.rr-btn-del  { color: var(--danger);  border-color: var(--danger); }
.rr-btn-del:hover  { background: rgba(220,38,38,.1); }

/* Scroll loader */
#rr-scroll-loader {
  display: none; text-align: center; padding: 24px;
  color: var(--muted); font-size: 13px;
}
#rr-scroll-loader .rr-spinner {
  display: inline-block; width: 22px; height: 22px;
  border: 3px solid var(--border); border-top-color: var(--primary);
  border-radius: 50%; animation: rr-spin .7s linear infinite;
  margin-right: 8px; vertical-align: middle;
}
@keyframes rr-spin { to { transform: rotate(360deg); } }
#rr-end-marker {
  display: none; text-align: center; padding: 18px;
  color: var(--muted-2); font-size: 12px;
}

/* Empty */
.rr-empty { text-align: center; padding: 48px 20px; color: var(--muted); }
.rr-empty i { font-size: 2.2rem; opacity: .35; display: block; margin-bottom: 10px; }

/* Responsive */
@media (max-width: 1100px) {
  .rr-stats { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 700px) {
  .rr-stats { grid-template-columns: repeat(2, 1fr); }
  .rr-table thead th:nth-child(4),
  .rr-table tbody td:nth-child(4) { display: none; }
}
@media (max-width: 480px) {
  .rr-stats { grid-template-columns: repeat(2, 1fr); }
  .rr-table thead th:nth-child(6),
  .rr-table tbody td:nth-child(6) { display: none; }
  .rr-table td, .rr-table th { padding: 10px 10px; }
}
</style>
@endpush

@section('content')

{{-- ── Alerts ── --}}
@if(session('success'))
<div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:var(--r);background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.25);color:#16a34a;margin-bottom:16px;">
  <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:var(--r);background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.25);color:#dc2626;margin-bottom:16px;">
  <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

{{-- ── Stats ── --}}
@php
$statCards = [
  ['label_en'=>'Total',           'label_bn'=>'মোট',               'key'=>'total',            'color'=>'#6366f1', 'icon'=>'fas fa-list'],
  ['label_en'=>'Pending',         'label_bn'=>'মুলতবি',             'key'=>'pending',           'color'=>'#f59e0b', 'icon'=>'fas fa-clock'],
  ['label_en'=>'Under Review',    'label_bn'=>'পর্যালোচনাধীন',     'key'=>'under_review',      'color'=>'#3b82f6', 'icon'=>'fas fa-search'],
  ['label_en'=>'Approved',        'label_bn'=>'অনুমোদিত',           'key'=>'approved',          'color'=>'#10b981', 'icon'=>'fas fa-check'],
  ['label_en'=>'Rejected',        'label_bn'=>'প্রত্যাখ্যাত',      'key'=>'rejected',          'color'=>'#ef4444', 'icon'=>'fas fa-times'],
  ['label_en'=>'Refunded',        'label_bn'=>'রিফান্ড সম্পন্ন',   'key'=>'refunded',          'color'=>'#8b5cf6', 'icon'=>'fas fa-money-bill-wave'],
];
@endphp
<div class="rr-stats fade-up">
  @foreach($statCards as $sc)
  <div class="rr-stat-card" style="border-left-color:{{ $sc['color'] }}">
    <div class="rr-stat-icon" style="background:{{ $sc['color'] }}22;color:{{ $sc['color'] }}">
      <i class="{{ $sc['icon'] }}"></i>
    </div>
    <div>
      <div class="rr-stat-val">{{ $stats[$sc['key']] }}</div>
      <div class="rr-stat-lbl">{{ app()->getLocale()==='bn' ? $sc['label_bn'] : $sc['label_en'] }}</div>
    </div>
  </div>
  @endforeach
</div>

{{-- ── Filters ── --}}
<div class="rr-filter-card fade-up-1">
  <form method="GET" action="{{ route('admin.returns.index') }}">
    <div class="rr-filter-row">
      <div class="fg">
        <label class="adm-label">{{ app()->getLocale()==='bn' ? 'খুঁজুন' : 'Search' }}</label>
        <input type="text" name="search" class="adm-input"
          value="{{ $search }}"
          placeholder="{{ app()->getLocale()==='bn' ? 'নাম, ইমেইল, রিটার্ন নম্বর...' : 'Name, email, return#...' }}">
      </div>
      <div class="fg-sm">
        <label class="adm-label">{{ app()->getLocale()==='bn' ? 'স্ট্যাটাস' : 'Status' }}</label>
        <select name="status" class="adm-select">
          <option value="">{{ app()->getLocale()==='bn' ? 'সব স্ট্যাটাস' : 'All Status' }}</option>
          @foreach([
            'pending'          => ['Pending',          'মুলতবি'],
            'under_review'     => ['Under Review',     'পর্যালোচনাধীন'],
            'approved'         => ['Approved',         'অনুমোদিত'],
            'rejected'         => ['Rejected',         'প্রত্যাখ্যাত'],
            'refund_initiated' => ['Refund Initiated', 'রিফান্ড শুরু'],
            'refunded'         => ['Refunded',         'রিফান্ড সম্পন্ন'],
          ] as $val => $labels)
          <option value="{{ $val }}" {{ $statusFilter == $val ? 'selected' : '' }}>
            {{ app()->getLocale()==='bn' ? $labels[1] : $labels[0] }}
          </option>
          @endforeach
        </select>
      </div>
      <div class="fg-fixed" style="display:flex;gap:8px;align-items:flex-end">
        <div>
          <label class="adm-label">&nbsp;</label>
          <button type="submit" class="adm-btn adm-btn-primary">
            <i class="fas fa-search"></i>{{ app()->getLocale()==='bn' ? 'ফিল্টার' : 'Filter' }}
          </button>
        </div>
        <div>
          <label class="adm-label">&nbsp;</label>
          <a href="{{ route('admin.returns.index') }}" class="adm-btn adm-btn-ghost" title="Clear">
            <i class="fas fa-times"></i>
          </a>
        </div>
      </div>
    </div>
  </form>
</div>

{{-- ── Main Table Card ── --}}
<div class="rr-main-card fade-up-2">
  <div class="rr-card-head">
    <div class="rr-card-title">
      <i class="fas fa-undo-alt" style="color:var(--warning)"></i>
      {{ app()->getLocale()==='bn' ? 'রিটার্ন রিকোয়েস্ট তালিকা' : 'Return Requests List' }}
      <span style="background:rgba(100,116,139,.15);color:var(--muted);padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700;" id="rrTotalBadge">{{ $total }}</span>
    </div>
  </div>

  <div class="rr-table-wrap">
    <table class="rr-table">
      <thead>
        <tr>
          <th>{{ app()->getLocale()==='bn' ? 'রিটার্ন নম্বর' : 'Return #' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'গ্রাহক' : 'Customer' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'অর্ডার' : 'Order' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'কারণ' : 'Reason' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'স্ট্যাটাস' : 'Status' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'তারিখ' : 'Date' }}</th>
          <th style="text-align:right">{{ app()->getLocale()==='bn' ? 'কার্যক্রম' : 'Actions' }}</th>
        </tr>
      </thead>
      <tbody id="rrTableBody">
        @forelse($returns as $ret)
          @include('admin.returns._row', ['ret' => $ret])
        @empty
        <tr id="rrEmptyRow">
          <td colspan="7">
            <div class="rr-empty">
              <i class="fas fa-inbox"></i>
              {{ app()->getLocale()==='bn' ? 'কোনো রিটার্ন রিকোয়েস্ট পাওয়া যায়নি' : 'No return requests found' }}
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div id="rr-scroll-loader">
    <span class="rr-spinner"></span>
    {{ app()->getLocale()==='bn' ? 'লোড হচ্ছে...' : 'Loading more...' }}
  </div>
  <div id="rr-end-marker">
    <i class="fas fa-check-circle me-1" style="color:var(--primary)"></i>
    {{ app()->getLocale()==='bn' ? 'সব রিটার্ন রিকোয়েস্ট দেখানো হয়েছে' : 'All return requests loaded' }}
  </div>
</div>

@endsection

@push('scripts')
<script>
(function(){
  const IS_BN    = {{ app()->getLocale() === 'bn' ? 'true' : 'false' }};
  const BASE_URL = '{{ route("admin.returns.index") }}';
  const CSRF     = '{{ csrf_token() }}';

  let currentPage = 2;
  let isLoading   = false;
  let hasMore     = {{ $hasMore ? 'true' : 'false' }};

  const tbody     = document.getElementById('rrTableBody');
  const loader    = document.getElementById('rr-scroll-loader');
  const endMarker = document.getElementById('rr-end-marker');

  if (!hasMore) endMarker.style.display = 'block';

  // Status badge class map
  const badgeMap = {
    pending:          'rr-badge-pending',
    under_review:     'rr-badge-under_review',
    approved:         'rr-badge-approved',
    rejected:         'rr-badge-rejected',
    refund_initiated: 'rr-badge-refund_initiated',
    refunded:         'rr-badge-refunded',
  };

  function buildRows(returns) {
    return returns.map(r => {
      const badgeCls  = badgeMap[r.status] || 'rr-badge-pending';
      const statusLbl = IS_BN ? r.status_bn : r.status_en;
      const reasonLbl = IS_BN ? r.reason_bn : r.reason_en;

      return `<tr>
        <td><strong style="color:var(--text)">${r.return_number}</strong></td>
        <td>
          <div style="color:var(--text)">${r.customer_name}</div>
          <small style="color:var(--muted)">${r.customer_email}</small>
        </td>
        <td>
          <a href="${r.order_url}" style="color:var(--primary);font-weight:600;font-size:13px;text-decoration:none">
            ${r.order_number}
          </a>
        </td>
        <td style="font-size:12px;color:var(--muted)">${reasonLbl}</td>
        <td><span class="rr-badge ${badgeCls}">${statusLbl}</span></td>
        <td style="color:var(--muted);font-size:12px">${r.date}</td>
        <td style="text-align:right">
          <div class="rr-action-btns" style="justify-content:flex-end">
            <a href="${r.view_url}" class="rr-btn rr-btn-view">
              <i class="fas fa-eye"></i>${IS_BN ? 'দেখুন' : 'View'}
            </a>
          </div>
        </td>
      </tr>`;
    }).join('');
  }

  // ── Infinite Scroll ──────────────────────────────────────
  async function loadMore() {
    if (isLoading || !hasMore) return;
    isLoading = true;
    loader.style.display = 'block';

    try {
      const params = new URLSearchParams(window.location.search);
      params.set('page', currentPage);

      const res  = await fetch(`${BASE_URL}?${params}`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        }
      });
      const data = await res.json();

      const emptyRow = document.getElementById('rrEmptyRow');
      if (emptyRow) emptyRow.remove();

      if (data.returns.length) {
        tbody.insertAdjacentHTML('beforeend', buildRows(data.returns));
      }

      hasMore     = data.has_more;
      currentPage = data.next_page;

      if (!hasMore) endMarker.style.display = 'block';
    } catch(err) {
      console.error('Return scroll error:', err);
    } finally {
      isLoading = false;
      loader.style.display = 'none';
    }
  }

  // IntersectionObserver sentinel
  const sentinel = document.createElement('div');
  sentinel.style.height = '1px';
  endMarker.parentNode.insertBefore(sentinel, endMarker);

  new IntersectionObserver(entries => {
    if (entries[0].isIntersecting) loadMore();
  }, { rootMargin: '200px' }).observe(sentinel);

})();
</script>
@endpush