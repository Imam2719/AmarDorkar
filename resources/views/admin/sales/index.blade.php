@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'বিক্রয় ইতিহাস' : 'Sales History')
@section('page-title', app()->getLocale()==='bn' ? 'বিক্রয় ইতিহাস' : 'Sales History')

@php $bn = app()->getLocale()==='bn'; @endphp

@push('styles')
<style>
/* ── Sales page styles ── */
.sales-section-title{
  font-size:13px;font-weight:700;color:var(--muted);
  text-transform:uppercase;letter-spacing:.6px;margin-bottom:12px;
}
.top-product-row{
  display:flex;align-items:center;gap:10px;
  padding:11px 16px;border-bottom:1px solid var(--border);
}
.top-product-row:last-child{border-bottom:none;}
.top-product-row:hover{background:var(--hover);}
.top-product-rank{
  width:26px;height:26px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:11px;font-weight:800;flex-shrink:0;
  background:var(--border);color:var(--muted);
}
.top-product-rank.gold{background:#fef3c7;color:#92400e;}
.top-product-rank.silver{background:#f1f5f9;color:#475569;}
.top-product-rank.bronze{background:#fde8d8;color:#9a3412;}
.adm-dark .top-product-rank.silver{background:#334155;color:#cbd5e1;}
.progress-thin{height:5px;border-radius:99px;background:var(--border-2);overflow:hidden;}
.progress-thin .bar{height:100%;border-radius:99px;background:var(--success);transition:width .6s;}
.cat-row{
  display:flex;align-items:center;justify-content:space-between;
  padding:10px 16px;border-bottom:1px solid var(--border);gap:8px;
}
.cat-row:last-child{border-bottom:none;}
.cat-row:hover{background:var(--hover);}
.cat-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;}
.day-bar-wrap{flex:1;min-width:0;}
.day-bar{height:8px;border-radius:99px;background:var(--primary);opacity:.85;}
.best-day-badge{
  display:inline-flex;align-items:center;gap:5px;
  background:var(--warning-s);color:var(--warning);
  border:1px solid rgba(217,119,6,.2);
  border-radius:20px;padding:3px 10px;font-size:11px;font-weight:700;
}
.row-cancelled td{background:rgba(220,38,38,.04) !important;}
.adm-dark .row-cancelled td{background:rgba(220,38,38,.08) !important;}
.tbl-address{max-width:155px;font-size:12px;}

/* ── Infinite Scroll ── */
#sales-tbody tr{
  animation: rowFadeIn .25s ease both;
}
@keyframes rowFadeIn{
  from{opacity:0;transform:translateY(6px);}
  to{opacity:1;transform:translateY(0);}
}
#scroll-sentinel{
  height:60px;
  display:flex;align-items:center;justify-content:center;
  gap:8px;color:var(--muted);font-size:13px;
}
#scroll-sentinel .spin{
  width:20px;height:20px;border:2px solid var(--border);
  border-top-color:var(--primary);border-radius:50%;
  animation:spin .7s linear infinite;
}
@keyframes spin{to{transform:rotate(360deg);}}
#scroll-sentinel.hidden{display:none;}
#no-more-banner{
  text-align:center;padding:16px;
  font-size:13px;color:var(--muted);
  border-top:1px solid var(--border);
}
#no-more-banner.hidden{display:none;}

/* scroll-to-top btn */
#scrollTopBtn{
  position:fixed;bottom:24px;right:24px;z-index:999;
  width:40px;height:40px;border-radius:50%;
  background:var(--primary);color:#fff;border:none;
  box-shadow:var(--sh);cursor:pointer;
  display:none;align-items:center;justify-content:center;
  transition:opacity .2s;
}
#scrollTopBtn:hover{background:var(--primary-h);}

/* progress bar at top */
#load-progress{
  position:fixed;top:0;left:0;height:2px;
  background:var(--primary);width:0%;
  transition:width .3s ease;z-index:9999;
}

/* record counter badge */
#record-counter{
  display:inline-flex;align-items:center;gap:6px;
  background:var(--primary-s);color:var(--primary);
  border:1px solid var(--primary-t);border-radius:20px;
  padding:3px 12px;font-size:12px;font-weight:700;
}
#record-counter .loaded{color:var(--muted);font-weight:400;}

@media(max-width:767px){
  .hide-mobile{display:none !important;}
  .tbl-address{max-width:110px;}
}
@media(max-width:575px){
  .hide-sm{display:none !important;}
}
</style>
@endpush

@section('content')

{{-- Top progress bar --}}
<div id="load-progress"></div>
<button id="scrollTopBtn" title="{{ $bn ? 'উপরে যান' : 'Back to top' }}">
  <i class="fas fa-arrow-up" style="font-size:14px;"></i>
</button>

{{-- ════ STAT CARDS ════ --}}
<div class="stats-grid">
  @foreach([
    ['icon'=>'fa-check-circle',  'num'=>number_format($soldStats->total_orders   ?? 0),       'bn'=>'মোট বিক্রয় অর্ডার', 'en'=>'Sold Orders',      'g'=>'var(--success)'],
    ['icon'=>'fa-chart-line',    'num'=>'৳'.number_format($soldStats->total_revenue   ?? 0),  'bn'=>'মোট আয়',             'en'=>'Total Revenue',    'g'=>'var(--primary)'],
    ['icon'=>'fa-boxes',         'num'=>number_format($soldStats->total_qty        ?? 0),      'bn'=>'মোট পণ্য বিক্রয়',   'en'=>'Items Sold',       'g'=>'var(--warning)'],
    ['icon'=>'fa-users',         'num'=>number_format($soldStats->unique_customers ?? 0),      'bn'=>'অনন্য গ্রাহক',       'en'=>'Unique Customers', 'g'=>'var(--primary)'],
    ['icon'=>'fa-times-circle',  'num'=>number_format($cancelledStats->total_orders ?? 0),     'bn'=>'বাতিল অর্ডার',       'en'=>'Cancelled Orders', 'g'=>'var(--danger)'],
  ] as $c)
  <div class="stat-card animate-fadeInUp">
    <div class="stat-header d-flex justify-content-between align-items-start">
      <div>
        <div class="stat-number">{{ $c['num'] }}</div>
        <div class="stat-label bengali-text">{{ $bn ? $c['bn'] : $c['en'] }}</div>
      </div>
      <div class="stat-icon" style="background:{{ $c['g'] }}">
        <i class="fas {{ $c['icon'] }}"></i>
      </div>
    </div>
  </div>
  @endforeach
</div>

{{-- ════ FILTER BAR ════ --}}
<div class="card mb-4">
  <div class="card-body py-3">
    <form method="GET" id="filterForm">
      <div class="row g-2 align-items-end">
        <div class="col-12 col-sm-6 col-md-3">
          <input type="text" class="form-control" name="search"
            placeholder="{{ $bn ? 'নাম, ইমেইল, পণ্য, অর্ডার নং...' : 'Name, email, product, order no…' }}"
            value="{{ $search }}">
        </div>
        <div class="col-6 col-sm-3 col-md-2">
          <input type="date" class="form-control" name="date_from"
            title="{{ $bn ? 'শুরু তারিখ' : 'From date' }}" value="{{ $dateFrom }}">
        </div>
        <div class="col-6 col-sm-3 col-md-2">
          <input type="date" class="form-control" name="date_to"
            title="{{ $bn ? 'শেষ তারিখ' : 'To date' }}" value="{{ $dateTo }}">
        </div>
        <div class="col-6 col-sm-4 col-md-2">
          <select class="form-select" name="status">
            <option value=""          {{ $statusFilter===''          ? 'selected':'' }}>{{ $bn ? 'সব (বিক্রয় + বাতিল)' : 'All (Sold + Cancelled)' }}</option>
            <option value="sold"      {{ $statusFilter==='sold'      ? 'selected':'' }}>{{ $bn ? 'শুধু বিক্রয়'           : 'Sold Only'             }}</option>
            <option value="cancelled" {{ $statusFilter==='cancelled' ? 'selected':'' }}>{{ $bn ? 'শুধু বাতিল'            : 'Cancelled Only'        }}</option>
          </select>
        </div>
        <div class="col-6 col-sm-4 col-md-2">
          <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-search me-1"></i>{{ $bn ? 'ফিল্টার' : 'Filter' }}
          </button>
        </div>
        <div class="col-12 col-sm-4 col-md-1">
          <a href="{{ route('admin.sales.index') }}" class="btn btn-outline-secondary w-100">
            <i class="fas fa-times"></i>
          </a>
        </div>
      </div>
    </form>
  </div>

  {{-- Export bar --}}
  <div class="card-footer border-top" style="background:var(--bg);padding:12px 18px;">
    @if(session('export_success'))
    <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
      <i class="fas fa-check-circle me-2"></i>{{ session('export_success') }}
    </div>
    @endif
    @if(session('export_error'))
    <div class="alert alert-danger py-2 mb-3" style="font-size:13px;">
      <i class="fas fa-exclamation-circle me-2"></i>{{ session('export_error') }}
    </div>
    @endif

    <div class="d-flex flex-wrap align-items-center gap-2">
      <span class="text-muted" style="font-size:12px;font-weight:600;">
        <i class="fas fa-download me-1"></i>{{ $bn ? 'ডাউনলোড:' : 'Download:' }}
      </span>
      <a href="{{ route('admin.sales.export.csv') }}?{{ http_build_query(['search'=>$search,'date_from'=>$dateFrom,'date_to'=>$dateTo,'status'=>$statusFilter]) }}"
         class="btn btn-sm btn-outline-success">
        <i class="fas fa-file-csv me-1"></i>CSV
      </a>
      <a href="{{ route('admin.sales.export.pdf') }}?{{ http_build_query(['search'=>$search,'date_from'=>$dateFrom,'date_to'=>$dateTo,'status'=>$statusFilter]) }}"
         target="_blank" class="btn btn-sm btn-outline-danger">
        <i class="fas fa-file-pdf me-1"></i>{{ $bn ? 'PDF / প্রিন্ট' : 'PDF / Print' }}
      </a>
      <div class="vr mx-1 d-none d-sm-block"></div>
      <span class="text-muted" style="font-size:12px;font-weight:600;">
        <i class="fas fa-share-alt me-1"></i>{{ $bn ? 'শেয়ার:' : 'Share:' }}
      </span>
      <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#emailModal">
        <i class="fas fa-envelope me-1"></i>{{ $bn ? 'ইমেইল' : 'Email' }}
      </button>
      <button class="btn btn-sm" id="waBtn" style="background:#25D366;color:#fff;border:none;" onclick="openWhatsApp()">
        <i class="fab fa-whatsapp me-1"></i>WhatsApp
      </button>
    </div>
  </div>
</div>

{{-- ════ EMAIL MODAL ════ --}}
<div class="modal fade" id="emailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title bengali-text">
          <i class="fas fa-envelope me-2 text-primary"></i>
          {{ $bn ? 'রিপোর্ট ইমেইল করুন' : 'Email Sales Report' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('admin.sales.send.email') }}">
        @csrf
        <input type="hidden" name="search"    value="{{ $search }}">
        <input type="hidden" name="date_from" value="{{ $dateFrom }}">
        <input type="hidden" name="date_to"   value="{{ $dateTo }}">
        <input type="hidden" name="status"    value="{{ $statusFilter }}">
        <input type="hidden" name="locale"    value="{{ app()->getLocale() }}">
        <div class="modal-body">
          @if($dateFrom || $dateTo || $search || $statusFilter)
          <div class="alert alert-info py-2 mb-3" style="font-size:12px;">
            <i class="fas fa-filter me-1"></i>
            <strong>{{ $bn ? 'সক্রিয় ফিল্টার:' : 'Active filters:' }}</strong>
            @if($search) &quot;{{ $search }}&quot; @endif
            @if($dateFrom) {{ $bn ? 'শুরু:' : 'From:' }} {{ $dateFrom }} @endif
            @if($dateTo) {{ $bn ? 'শেষ:' : 'To:' }} {{ $dateTo }} @endif
            @if($statusFilter) | {{ ucfirst($statusFilter) }} @endif
          </div>
          @else
          <div class="alert alert-warning py-2 mb-3" style="font-size:12px;">
            <i class="fas fa-info-circle me-1"></i>
            {{ $bn ? 'কোনো ফিল্টার নেই — সব রেকর্ড পাঠানো হবে।' : 'No filter active — all records will be sent.' }}
          </div>
          @endif
          <div>
            <label class="form-label fw-semibold bengali-text" style="font-size:13px;">
              <i class="fas fa-at me-1 text-primary"></i>
              {{ $bn ? 'প্রাপকের ইমেইল ঠিকানা' : 'Recipient Email Address' }}
            </label>
            <input type="email" name="recipient_email" class="form-control"
              placeholder="example@email.com" required autofocus>
            <div class="form-text" style="font-size:11.5px;">
              {{ $bn ? 'CSV ফাইল attachment হিসেবে পাঠানো হবে।' : 'Report will be sent with CSV file attached.' }}
            </div>
          </div>
        </div>
        <div class="modal-footer gap-2">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
            {{ $bn ? 'বাতিল' : 'Cancel' }}
          </button>
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-paper-plane me-1"></i>{{ $bn ? 'পাঠান' : 'Send Report' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ════ TOP PRODUCTS + CATEGORY ════ --}}
<div class="row g-3 mb-4">
  <div class="col-12 col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title bengali-text">
          <i class="fas fa-trophy text-warning me-2"></i>
          {{ $bn ? 'সর্বোচ্চ বিক্রিত পণ্য' : 'Top Selling Products' }}
        </h5>
        <small class="text-muted">{{ $bn ? 'পরিমাণ অনুযায়ী' : 'by qty' }}</small>
      </div>
      @php $maxQty = $topProducts->max('total_qty') ?: 1; @endphp
      @forelse($topProducts as $i => $prod)
      @php
        $rankClass = $i===0 ? 'gold' : ($i===1 ? 'silver' : ($i===2 ? 'bronze' : ''));
        $pct       = round(($prod->total_qty / $maxQty) * 100);
      @endphp
      <div class="top-product-row">
        <div class="top-product-rank {{ $rankClass }}">{{ $i+1 }}</div>
        <div class="flex-grow-1 min-w-0">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="bengali-text fw-semibold text-truncate" style="font-size:13px;max-width:65%;">
              {{ $bn ? $prod->name_bn : $prod->name_en }}
            </span>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
              <span class="badge bg-success" style="font-size:11px;">
                {{ number_format($prod->total_qty) }} {{ $bn ? 'পিস' : 'pcs' }}
              </span>
              <span class="text-muted" style="font-size:11px;">৳{{ number_format($prod->total_revenue) }}</span>
            </div>
          </div>
          <div class="progress-thin"><div class="bar" style="width:{{ $pct }}%"></div></div>
        </div>
      </div>
      @empty
      <div class="text-center py-4 text-muted bengali-text" style="font-size:13px;">
        <i class="fas fa-box-open fa-2x mb-2 d-block opacity-25"></i>
        {{ $bn ? 'কোনো তথ্য নেই' : 'No data available' }}
      </div>
      @endforelse
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title bengali-text">
          <i class="fas fa-tags me-2" style="color:var(--primary)"></i>
          {{ $bn ? 'ক্যাটাগরি অনুযায়ী বিক্রয় ও বাতিল' : 'Category-wise Sold & Cancelled' }}
        </h5>
      </div>
      @forelse($categoryStats as $cat)
      <div class="cat-row">
        <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
          <div class="cat-dot" style="background:{{ $cat->color ?? 'var(--primary)' }}"></div>
          <i class="{{ $cat->icon ?? 'fas fa-tag' }}" style="color:{{ $cat->color ?? 'var(--primary)' }};font-size:12px;width:14px;"></i>
          <span class="bengali-text text-truncate" style="font-size:13px;">
            {{ $bn ? $cat->name_bn : $cat->name_en }}
          </span>
        </div>
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
          <span class="badge bg-success" style="font-size:11px;" title="{{ $bn ? 'বিক্রয়' : 'Sold' }}">
            <i class="fas fa-check fa-xs me-1"></i>{{ number_format($cat->sold_qty) }}
          </span>
          @if($cat->cancelled_qty > 0)
          <span class="badge bg-danger" style="font-size:11px;" title="{{ $bn ? 'বাতিল' : 'Cancelled' }}">
            <i class="fas fa-times fa-xs me-1"></i>{{ number_format($cat->cancelled_qty) }}
          </span>
          @endif
          <span class="text-muted" style="font-size:11px;min-width:60px;text-align:right;">
            ৳{{ number_format($cat->revenue) }}
          </span>
        </div>
      </div>
      @empty
      <div class="text-center py-4 text-muted bengali-text" style="font-size:13px;">
        <i class="fas fa-tags fa-2x mb-2 d-block opacity-25"></i>
        {{ $bn ? 'কোনো তথ্য নেই' : 'No data available' }}
      </div>
      @endforelse
    </div>
  </div>
</div>

{{-- ════ DATE-WISE DAILY SALES ════ --}}
<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title bengali-text">
      <i class="fas fa-calendar-alt me-2" style="color:var(--primary)"></i>
      {{ $bn ? 'তারিখ অনুযায়ী মোট বিক্রয়' : 'Date-wise Total Sales' }}
    </h5>
    @if($bestDay)
    <div class="best-day-badge">
      <i class="fas fa-star"></i>
      {{ $bn ? 'সেরা দিন:' : 'Best:' }}
      {{ \Carbon\Carbon::parse($bestDay->sale_date)->format('d M') }}
      — ৳{{ number_format($bestDay->total_amount) }}
    </div>
    @endif
  </div>
  @if($dailySales->count())
  @php $maxAmt = $dailySales->max('total_amount') ?: 1; @endphp
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0" style="font-size:13px;">
        <thead>
          <tr>
            <th class="bengali-text">{{ $bn ? 'তারিখ' : 'Date' }}</th>
            <th class="text-center bengali-text hide-sm">{{ $bn ? 'অর্ডার' : 'Orders' }}</th>
            <th class="text-center bengali-text hide-sm">{{ $bn ? 'পণ্য' : 'Items' }}</th>
            <th class="text-center bengali-text hide-mobile">{{ $bn ? 'গড় অর্ডার মূল্য' : 'Avg. Order Value' }}</th>
            <th class="bengali-text" style="min-width:200px;">{{ $bn ? 'মোট বিক্রয়' : 'Total Amount' }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach($dailySales as $day)
          @php
            $isToday = \Carbon\Carbon::parse($day->sale_date)->isToday();
            $pct     = round(($day->total_amount / $maxAmt) * 100);
          @endphp
          <tr>
            <td>
              <strong class="{{ $isToday ? 'text-success' : '' }}">
                {{ \Carbon\Carbon::parse($day->sale_date)->format('d M Y') }}
              </strong>
              @if($isToday)
              <span class="badge bg-success ms-1" style="font-size:10px;">{{ $bn ? 'আজ' : 'Today' }}</span>
              @endif
              <br>
              <small class="text-muted" style="font-size:11px;">
                {{ \Carbon\Carbon::parse($day->sale_date)->format('l') }}
              </small>
            </td>
            <td class="text-center fw-bold hide-sm">{{ $day->order_count }}</td>
            <td class="text-center hide-sm">{{ number_format($day->qty_sold) }}</td>
            <td class="text-center hide-mobile">৳{{ number_format($day->avg_order_value) }}</td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="day-bar-wrap">
                  <div class="day-bar" style="width:{{ $pct }}%"></div>
                </div>
                <strong class="text-nowrap" style="min-width:85px;font-size:13px;color:var(--primary);">
                  ৳{{ number_format($day->total_amount) }}
                </strong>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @else
  <div class="card-body text-center py-4 text-muted bengali-text">
    <i class="fas fa-calendar-times fa-2x mb-2 d-block opacity-25"></i>
    {{ $bn ? 'নির্বাচিত সময়ে কোনো বিক্রয় নেই' : 'No sales in selected period' }}
  </div>
  @endif
</div>

{{-- ════ INFINITE SCROLL — FULL DETAIL TABLE ════ --}}
<div class="card animate-fadeInUp">
  <div class="card-header">
    <h5 class="card-title bengali-text">
      <i class="fas fa-history me-2"></i>
      {{ $bn ? 'সকল বিক্রয় ও বাতিল রেকর্ড' : 'All Sold & Cancelled Records' }}
    </h5>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <div id="record-counter">
        <i class="fas fa-list fa-xs"></i>
        <span id="loaded-count">{{ min($perPage, $totalRecords) }}</span>
        <span class="loaded">/ {{ number_format($totalRecords) }} {{ $bn ? 'রেকর্ড' : 'records' }}</span>
      </div>
      @if(($soldStats->total_orders ?? 0) > 0 || ($cancelledStats->total_orders ?? 0) > 0)
      <span style="font-size:11px;color:var(--muted);">
        (
        <span style="color:var(--success);font-weight:600;">
          {{ number_format($soldStats->total_qty ?? 0) }} {{ $bn ? 'পণ্য' : 'items' }}
          {{ $bn ? 'মধ্যে' : 'in' }}
          {{ number_format($soldStats->total_orders ?? 0) }} {{ $bn ? 'অর্ডার' : 'orders' }}
        </span>
        @if(($cancelledStats->total_orders ?? 0) > 0)
        &nbsp;•&nbsp;
        <span style="color:var(--danger);font-weight:600;">
          {{ number_format($cancelledStats->total_orders) }} {{ $bn ? 'বাতিল' : 'cancelled' }}
        </span>
        @endif
        )
      </span>
      @endif
    </div>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0" style="font-size:13px;">
        <thead>
          <tr>
            <th>{{ $bn ? 'অর্ডার' : 'Order' }}</th>
            <th>{{ $bn ? 'পণ্য' : 'Product' }}</th>
            <th class="hide-mobile">{{ $bn ? 'ক্যাটাগরি' : 'Category' }}</th>
            <th>{{ $bn ? 'গ্রাহক' : 'Customer' }}</th>
            <th class="hide-mobile">{{ $bn ? 'ডেলিভারি ঠিকানা' : 'Delivery Address' }}</th>
            <th class="text-center">{{ $bn ? 'পরিমাণ' : 'Qty' }}</th>
            <th class="hide-sm">{{ $bn ? 'মূল্য' : 'Price' }}</th>
            <th>{{ $bn ? 'স্ট্যাটাস' : 'Status' }}</th>
            <th>{{ $bn ? 'তারিখ ও সময়' : 'Date & Time' }}</th>
          </tr>
        </thead>
        <tbody id="sales-tbody">
          {{-- Initial 20 rows rendered server-side --}}
          @forelse($initialItems as $item)
          @php
            $isSold      = $item->order_status === 'delivered';
            $isCancelled = $item->order_status === 'cancelled';
          @endphp
          <tr class="{{ $isCancelled ? 'row-cancelled' : '' }}">
            <td>
              <a href="{{ route('admin.orders.show', $item->order_id) }}"
                 class="fw-semibold text-decoration-none" style="color:var(--primary);">
                {{ $item->order_number }}
              </a><br>
              <small class="text-muted" style="font-size:11px;">{{ strtoupper($item->payment_method) }}</small>
            </td>
            <td>
              <span class="bengali-text fw-semibold" style="font-size:12.5px;">
                {{ $bn ? $item->product_name_bn : $item->product_name_en }}
              </span>
              @if($item->product_type || $item->product_size)
              <br><small class="text-muted" style="font-size:11px;">
                {{ $item->product_type }}{{ $item->product_size ? ' · '.$item->product_size : '' }}
              </small>
              @endif
            </td>
            <td class="hide-mobile">
              <small class="bengali-text text-muted">
                {{ $bn ? $item->category_name_bn : $item->category_name_en }}
              </small>
            </td>
            <td>
              <div class="bengali-text fw-semibold" style="font-size:12.5px;">{{ $item->customer_name }}</div>
              <small class="text-muted" style="font-size:11px;">{{ $item->customer_email }}</small><br>
              <small class="text-muted" style="font-size:11px;">
                <i class="fas fa-phone fa-xs me-1"></i>{{ $item->customer_mobile }}
              </small>
            </td>
            <td class="hide-mobile tbl-address">
              <small class="bengali-text" style="font-size:11px;line-height:1.4;">{{ $item->customer_address }}</small>
              @if($item->shipping_location)
              <br><span class="badge bg-light text-dark mt-1" style="font-size:10px;">
                <i class="fas fa-map-marker-alt me-1" style="color:var(--danger)"></i>{{ $item->shipping_location }}
              </span>
              @endif
            </td>
            <td class="text-center fw-bold">{{ $item->quantity }}</td>
            <td class="hide-sm">
              @if($item->is_discounted && $item->discount_amount > 0)
              <small class="text-muted text-decoration-line-through d-block" style="font-size:11px;">
                ৳{{ number_format($item->original_price * $item->quantity) }}
              </small>
              <span class="badge bg-warning text-dark" style="font-size:10px;">-৳{{ number_format($item->discount_amount) }}</span>
              <br>
              @endif
              <strong class="{{ $isSold ? 'text-success' : 'text-muted' }}" style="font-size:13px;">
                ৳{{ number_format($item->subtotal) }}
              </strong>
              @if($item->shipping_charge > 0)
              <br><small class="text-muted" style="font-size:11px;">
                +৳{{ number_format($item->shipping_charge) }} {{ $bn ? 'শিপিং' : 'ship.' }}
              </small>
              @endif
            </td>
            <td>
              @if($isSold)
              <span class="status-badge status-delivered">
                <i class="fas fa-check-circle"></i>{{ $bn ? 'বিক্রয়' : 'Sold' }}
              </span>
              @else
              <span class="status-badge status-cancelled">
                <i class="fas fa-times-circle"></i>{{ $bn ? 'বাতিল' : 'Cancelled' }}
              </span>
              @endif
              <br>
              <span class="payment-{{ $item->payment_status }} status-badge mt-1" style="font-size:10px;">
                {{ $item->payment_status }}
              </span>
            </td>
            <td style="white-space:nowrap;">
              <strong style="font-size:12.5px;">
                {{ \Carbon\Carbon::parse($item->order_date)->format('d M Y') }}
              </strong><br>
              <small class="text-muted" style="font-size:11px;">
                <i class="fas fa-clock fa-xs me-1"></i>
                {{ \Carbon\Carbon::parse($item->order_date)->format('h:i A') }}
              </small>
            </td>
          </tr>
          @empty
          <tr id="empty-row">
            <td colspan="9" class="text-center py-5 text-muted">
              <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
              <span class="bengali-text">{{ $bn ? 'কোনো রেকর্ড পাওয়া যায়নি' : 'No records found' }}</span>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Infinite scroll sentinel --}}
    @if($totalRecords > $perPage)
    <div id="scroll-sentinel">
      <div class="spin"></div>
      <span class="bengali-text">{{ $bn ? 'আরো লোড হচ্ছে...' : 'Loading more…' }}</span>
    </div>
    @endif

    {{-- No more records banner --}}
    <div id="no-more-banner" class="{{ $totalRecords <= $perPage ? '' : 'hidden' }}">
      @if($totalRecords === 0)
      {{-- empty state handled in table --}}
      @else
      <i class="fas fa-check-circle me-2" style="color:var(--success)"></i>
      <span class="bengali-text">
        {{ $bn ? 'সব ' . number_format($totalRecords) . 'টি রেকর্ড লোড হয়েছে' : 'All ' . number_format($totalRecords) . ' records loaded' }}
      </span>
      @endif
    </div>

  </div>
</div>

@push('scripts')
<script>
(function () {
  // ── Config ───────────────────────────────────────────────────
  const AJAX_URL   = '{{ route("admin.sales.index") }}';
  const PER_PAGE   = {{ $perPage }};
  const TOTAL      = {{ $totalRecords }};
  const BN         = {{ $bn ? 'true' : 'false' }};
  const FILTERS    = {
    search:    {{ json_encode($search) }},
    date_from: {{ json_encode($dateFrom) }},
    date_to:   {{ json_encode($dateTo) }},
    status:    {{ json_encode($statusFilter) }},
  };

  // ── State ────────────────────────────────────────────────────
  let currentPage = 1;         // page 1 already rendered server-side
  let loading     = false;
  let hasMore     = TOTAL > PER_PAGE;

  // ── Elements ─────────────────────────────────────────────────
  const tbody      = document.getElementById('sales-tbody');
  const sentinel   = document.getElementById('scroll-sentinel');
  const noMore     = document.getElementById('no-more-banner');
  const loadedSpan = document.getElementById('loaded-count');
  const progress   = document.getElementById('load-progress');
  const scrollBtn  = document.getElementById('scrollTopBtn');

  // ── Progress bar helpers ──────────────────────────────────────
  function showProgress(pct) {
    progress.style.width = pct + '%';
    progress.style.opacity = '1';
  }
  function hideProgress() {
    progress.style.width = '100%';
    setTimeout(() => { progress.style.opacity = '0'; progress.style.width = '0%'; }, 300);
  }

  // ── Build a table row from JSON ───────────────────────────────
  function buildRow(r) {
    const tr = document.createElement('tr');
    if (r.is_cancelled) tr.classList.add('row-cancelled');

    tr.innerHTML = `
      <td>
        <a href="${r.order_route}" class="fw-semibold text-decoration-none" style="color:var(--primary);">
          ${r.order_number}
        </a><br>
        <small class="text-muted" style="font-size:11px;">${r.payment_method}</small>
      </td>
      <td>${r.product_html}</td>
      <td class="hide-mobile"><small class="bengali-text text-muted">${r.category}</small></td>
      <td>
        <div class="bengali-text fw-semibold" style="font-size:12.5px;">${r.customer_name}</div>
        <small class="text-muted" style="font-size:11px;">${r.customer_email}</small><br>
        <small class="text-muted" style="font-size:11px;"><i class="fas fa-phone fa-xs me-1"></i>${r.customer_mobile}</small>
      </td>
      <td class="hide-mobile tbl-address">${r.address_html}</td>
      <td class="text-center fw-bold">${r.quantity}</td>
      <td class="hide-sm">${r.price_html}</td>
      <td>${r.status_html}</td>
      <td style="white-space:nowrap;">
        <strong style="font-size:12.5px;">${r.date_fmt}</strong><br>
        <small class="text-muted" style="font-size:11px;">
          <i class="fas fa-clock fa-xs me-1"></i>${r.time_fmt}
        </small>
      </td>`;
    return tr;
  }

  // ── Fetch next page ───────────────────────────────────────────
  async function loadMore() {
    if (loading || !hasMore) return;
    loading = true;
    showProgress(40);

    const nextPage = currentPage + 1;
    const params   = new URLSearchParams({ ...FILTERS, page: nextPage });

    try {
      const res  = await fetch(`${AJAX_URL}?${params}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      });
      showProgress(80);
      const data = await res.json();

      // Remove initial empty-state row if present
      const emptyRow = document.getElementById('empty-row');
      if (emptyRow) emptyRow.remove();

      // Append rows with staggered animation delay
      data.rows.forEach((r, i) => {
        const tr = buildRow(r);
        tr.style.animationDelay = (i * 18) + 'ms';
        tbody.appendChild(tr);
      });

      currentPage = data.page;
      hasMore     = data.hasMore;

      // Update counter
      const loaded = Math.min(currentPage * PER_PAGE, TOTAL);
      if (loadedSpan) loadedSpan.textContent = loaded;

      if (!hasMore) {
        if (sentinel) sentinel.classList.add('hidden');
        if (noMore)   noMore.classList.remove('hidden');
      }

    } catch (err) {
      console.error('Infinite scroll fetch error:', err);
    } finally {
      loading = false;
      hideProgress();
    }
  }

  // ── IntersectionObserver ──────────────────────────────────────
  if (sentinel && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      if (entries[0].isIntersecting) loadMore();
    }, { rootMargin: '200px' });
    observer.observe(sentinel);
  }

  // ── Scroll-to-top button ──────────────────────────────────────
  window.addEventListener('scroll', () => {
    if (scrollBtn) {
      scrollBtn.style.display = window.scrollY > 400 ? 'flex' : 'none';
    }
  }, { passive: true });

  if (scrollBtn) {
    scrollBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // ── WhatsApp ──────────────────────────────────────────────────
  window.openWhatsApp = function () {
    const btn = document.getElementById('waBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>...';

    const params = new URLSearchParams(FILTERS);
    fetch('{{ route("admin.sales.whatsapp") }}?' + params.toString())
      .then(r => r.json())
      .then(data => { if (data.url) window.open(data.url, '_blank'); })
      .catch(() => alert('WhatsApp link generation failed.'))
      .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fab fa-whatsapp me-1"></i>WhatsApp';
      });
  };

  // ── If total ≤ perPage, show "all loaded" immediately ─────────
  if (!hasMore && noMore) {
    noMore.classList.remove('hidden');
  }

})();
</script>
@endpush

@endsection