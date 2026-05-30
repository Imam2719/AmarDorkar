@extends('layouts.admin')
@section('title',      app()->getLocale()==='bn' ? 'ফ্ল্যাশ সেল ব্যবস্থাপনা' : 'Flash Sale Management')
@section('page-title', app()->getLocale()==='bn' ? 'ফ্ল্যাশ সেল ব্যবস্থাপনা' : 'Flash Sale Management')
@php $bn = app()->getLocale()==='bn'; @endphp

@push('styles')
<style>
/* ── Flash Sale Cards ── */
.status-badge {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 3px 10px; border-radius: 20px;
  font-size: 11px; font-weight: 700; letter-spacing: .3px;
}
.status-badge.flash-live {
  background: rgba(220,38,38,.12); color: #dc2626;
  border: 1px solid rgba(220,38,38,.3);
  animation: pulse-live 1.6s ease-in-out infinite;
}
.status-badge.flash-upcoming { background: rgba(217,119,6,.12); color: var(--warning); border: 1px solid rgba(217,119,6,.25); }
.status-badge.status-pending { background: rgba(22,163,74,.12); color: var(--success); border: 1px solid rgba(22,163,74,.25); }
.status-badge.status-cancelled { background: rgba(100,116,139,.12); color: var(--muted); border: 1px solid rgba(100,116,139,.25); }
.status-badge.status-inactive { background: rgba(100,116,139,.08); color: var(--muted-2); border: 1px solid var(--border); }

@keyframes pulse-live {
  0%,100% { box-shadow: 0 0 0 0 rgba(220,38,38,.35); }
  50%      { box-shadow: 0 0 0 5px rgba(220,38,38,0); }
}
.flash-card {
  border: 1px solid var(--border);
  border-radius: 12px;
  background: var(--white);
  overflow: hidden;
  transition: transform .18s ease, box-shadow .18s ease;
  animation: rowFadeIn .25s ease both;
}
.flash-card:hover { transform: translateY(-2px); box-shadow: var(--sh-lg); }
.flash-card.is-live { border-color: rgba(220,38,38,.35); }
.flash-card.is-expired { opacity: .68; }

.flash-card-header {
  padding: 14px 16px 12px;
  border-bottom: 1px solid var(--border);
  display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;
}
.flash-card-body { padding: 12px 16px; }
.flash-card-footer {
  padding: 10px 16px;
  background: var(--hover);
  border-top: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;
}

.prog-bar-wrap { height: 6px; border-radius: 99px; background: var(--border-2); overflow: hidden; }
.prog-bar      { height: 100%; border-radius: 99px; transition: width .6s ease; }
.prog-bar.time { background: linear-gradient(90deg, #ef4444, #f97316); }
.prog-bar.stock{ background: linear-gradient(90deg, var(--primary), var(--success)); }

.countdown-badge {
  display: inline-flex; align-items: center; gap: 5px;
  background: rgba(220,38,38,.1); color: #dc2626;
  border-radius: 20px; padding: 3px 10px;
  font-size: 11px; font-weight: 700; font-variant-numeric: tabular-nums;
}

@keyframes rowFadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to   { opacity: 1; transform: translateY(0); }
}
#scroll-sentinel {
  height: 64px; display: flex; align-items: center; justify-content: center;
  gap: 8px; color: var(--muted); font-size: 13px;
}
#scroll-sentinel .spin {
  width: 20px; height: 20px; border: 2px solid var(--border);
  border-top-color: var(--primary); border-radius: 50%;
  animation: spinIt .7s linear infinite;
}
@keyframes spinIt { to { transform: rotate(360deg); } }
#scroll-sentinel.hidden, #no-more-banner.hidden { display: none; }
#no-more-banner {
  text-align: center; padding: 16px;
  font-size: 13px; color: var(--muted);
  border-top: 1px solid var(--border);
}
#load-progress {
  position: fixed; top: 0; left: 0; height: 2px;
  background: #f97316; width: 0%; transition: width .3s ease; z-index: 9999; opacity: 0;
}
#scrollTopBtn {
  position: fixed; bottom: 24px; right: 24px; z-index: 999;
  width: 40px; height: 40px; border-radius: 50%;
  background: #f97316; color: #fff; border: none;
  box-shadow: var(--sh); cursor: pointer;
  display: none; align-items: center; justify-content: center;
}
#scrollTopBtn:hover { background: #ea580c; }
#record-counter {
  display: inline-flex; align-items: center; gap: 6px;
  background: rgba(249,115,22,.1); color: #ea580c;
  border: 1px solid rgba(249,115,22,.25);
  border-radius: 20px; padding: 3px 12px;
  font-size: 12px; font-weight: 700;
}
#record-counter .loaded { color: var(--muted); font-weight: 400; }
#flash-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 16px;
}
@media(max-width: 575px) {
  #flash-grid { grid-template-columns: 1fr; }
}

/* Toast notification */
.fs-toast {
  position: fixed; top: 20px; right: 20px; z-index: 10000;
  padding: 12px 20px; border-radius: 10px; font-size: 13px; font-weight: 600;
  display: flex; align-items: center; gap: 8px;
  box-shadow: var(--sh-lg); max-width: 320px;
  animation: toastIn .3s ease both;
  color: #fff;
}
.fs-toast.success { background: var(--success); }
.fs-toast.error   { background: var(--danger); }
@keyframes toastIn { from { opacity:0; transform: translateX(40px); } to { opacity:1; transform: translateX(0); } }
@keyframes toastOut { to { opacity:0; transform: translateX(40px); } }

/* Modal dark mode fix */
.adm-dark .modal-content { background: var(--white); color: var(--text); border-color: var(--border); }
.adm-dark .modal-header { border-color: var(--border); }
.adm-dark .modal-footer { border-color: var(--border); }
.adm-dark .form-control, .adm-dark .form-select {
  background: var(--bg); color: var(--text);
  border-color: var(--border);
}
.adm-dark .form-control::placeholder { color: var(--muted-2); }
.adm-dark .form-control:focus, .adm-dark .form-select:focus {
  background: var(--bg); color: var(--text); border-color: var(--primary);
  box-shadow: 0 0 0 .2rem rgba(37,99,235,.25);
}
.adm-dark .btn-close { filter: invert(1); }
.adm-dark .alert-info { background: rgba(37,99,235,.12); border-color: rgba(37,99,235,.3); color: var(--text); }
</style>
@endpush

@section('content')

<div id="load-progress"></div>
<button id="scrollTopBtn" aria-label="{{ $bn ? 'উপরে যান' : 'Back to top' }}">
  <i class="fas fa-arrow-up" style="font-size:14px;"></i>
</button>

{{-- ════ STAT CARDS ════ --}}
<div class="stats-grid mb-4">
  @foreach([
    ['icon'=>'fa-bolt',         'num'=>$statTotal,   'bn'=>'মোট ফ্ল্যাশ সেল', 'en'=>'Total Flash Sales', 'g'=>'#f97316'],
    ['icon'=>'fa-fire',         'num'=>$statLive,    'bn'=>'লাইভ চলছে',        'en'=>'Currently Live',    'g'=>'#dc2626'],
    ['icon'=>'fa-check-circle', 'num'=>$statActive,  'bn'=>'সক্রিয়',           'en'=>'Active',            'g'=>'var(--success)'],
    ['icon'=>'fa-history',      'num'=>$statExpired, 'bn'=>'মেয়াদ শেষ',        'en'=>'Expired',           'g'=>'var(--muted)'],
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

{{-- ════ ACTION BAR ════ --}}
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
  <form method="GET" id="filterForm" class="d-flex flex-wrap gap-2 align-items-center">
    <input type="text" class="form-control form-control-sm" name="search"
      placeholder="{{ $bn ? 'শিরোনাম খুঁজুন...' : 'Search title…' }}"
      value="{{ $search }}" style="max-width:200px;">
    <select class="form-select form-select-sm" name="status" style="max-width:170px;">
      <option value=""         {{ $status===''         ? 'selected':'' }}>{{ $bn ? 'সব স্ট্যাটাস' : 'All Status' }}</option>
      <option value="live"     {{ $status==='live'     ? 'selected':'' }}>{{ $bn ? 'লাইভ চলছে'    : 'Live Now'   }}</option>
      <option value="upcoming" {{ $status==='upcoming' ? 'selected':'' }}>{{ $bn ? 'আসছে'          : 'Upcoming'   }}</option>
      <option value="active"   {{ $status==='active'   ? 'selected':'' }}>{{ $bn ? 'সক্রিয়'       : 'Active'     }}</option>
      <option value="inactive" {{ $status==='inactive' ? 'selected':'' }}>{{ $bn ? 'নিষ্ক্রিয়'   : 'Inactive'  }}</option>
      <option value="expired"  {{ $status==='expired'  ? 'selected':'' }}>{{ $bn ? 'মেয়াদ শেষ'   : 'Expired'    }}</option>
    </select>
    <button type="submit" class="btn btn-sm btn-primary">
      <i class="fas fa-search me-1"></i>{{ $bn ? 'ফিল্টার' : 'Filter' }}
    </button>
    @if($search || $status)
    <a href="{{ route('admin.flash-sales.index') }}" class="btn btn-sm btn-outline-secondary">
      <i class="fas fa-times"></i>
    </a>
    @endif
  </form>

  <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addFlashSaleModal">
    <i class="fas fa-bolt me-2"></i>{{ $bn ? 'নতুন ফ্ল্যাশ সেল' : 'New Flash Sale' }}
  </button>
</div>

{{-- ════ MAIN CARD ════ --}}
<div class="card animate-fadeInUp">
  <div class="card-header">
    <h5 class="card-title bengali-text">
      <i class="fas fa-bolt me-2 text-warning"></i>
      {{ $bn ? 'সকল ফ্ল্যাশ সেল' : 'All Flash Sales' }}
    </h5>
    <div id="record-counter">
      <i class="fas fa-bolt fa-xs"></i>
      <span id="loaded-count">{{ min($perPage, $total) }}</span>
      <span class="loaded">/ {{ number_format($total) }} {{ $bn ? 'টি' : 'total' }}</span>
    </div>
  </div>

  <div class="card-body">
    <div id="flash-grid">
      @forelse($initial as $fs)
      @php
        $now        = now();
        $isLive     = $fs->is_active && $fs->start_time <= $now && $fs->end_time >= $now;
        $isExpired  = $fs->end_time < $now;
        $isUpcoming = $fs->start_time > $now && $fs->is_active;
        $totalSold  = $fs->products->sum('sold_quantity');
        $totalMax   = $fs->products->sum('max_quantity');
        $stockPct   = $totalMax > 0 ? min(100, round(($totalSold / $totalMax) * 100)) : 0;
        $progPct    = 0;
        if ($isLive) {
            $totalDur = max(1, $fs->end_time->diffInSeconds($fs->start_time));
            $elapsed  = $now->diffInSeconds($fs->start_time);
            $progPct  = min(100, round(($elapsed / $totalDur) * 100));
        }
      @endphp
      <div class="flash-card {{ $isLive ? 'is-live' : '' }} {{ $isExpired ? 'is-expired' : '' }}"
           id="flash-card-{{ $fs->id }}">

        {{-- Header --}}
        <div class="flash-card-header">
          <div class="flex-grow-1 min-w-0">
            <div class="fw-bold" style="font-size:14px;line-height:1.3;">{{ $fs->title_en }}</div>
            <div class="bengali-text text-muted" style="font-size:12px;margin-top:2px;">{{ $fs->title_bn }}</div>
          </div>
          <div class="flex-shrink-0">
            @if($isLive)
            <span class="status-badge flash-live"><i class="fas fa-bolt"></i>{{ $bn ? 'লাইভ' : 'LIVE' }}</span>
            @elseif($isUpcoming)
            <span class="status-badge flash-upcoming"><i class="fas fa-clock"></i>{{ $bn ? 'আসছে' : 'Upcoming' }}</span>
            @elseif($fs->is_active)
            <span class="status-badge status-pending"><i class="fas fa-check-circle"></i>{{ $bn ? 'সক্রিয়' : 'Active' }}</span>
            @elseif($isExpired)
            <span class="status-badge status-cancelled"><i class="fas fa-history"></i>{{ $bn ? 'মেয়াদ শেষ' : 'Expired' }}</span>
            @else
            <span class="status-badge status-inactive"><i class="fas fa-pause-circle"></i>{{ $bn ? 'নিষ্ক্রিয়' : 'Inactive' }}</span>
            @endif
          </div>
        </div>

        {{-- Body --}}
        <div class="flash-card-body">
          <div class="d-flex gap-3 mb-3" style="font-size:12px;">
            <div>
              <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">
                {{ $bn ? 'শুরু' : 'Start' }}
              </div>
              <div class="fw-semibold">{{ $fs->start_time->format('d M Y') }}</div>
              <div class="text-muted">{{ $fs->start_time->format('h:i A') }}</div>
            </div>
            <div style="color:var(--muted);align-self:center;"><i class="fas fa-arrow-right"></i></div>
            <div>
              <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">
                {{ $bn ? 'শেষ' : 'End' }}
              </div>
              <div class="fw-semibold {{ $isExpired ? 'text-danger' : '' }}">{{ $fs->end_time->format('d M Y') }}</div>
              <div class="text-muted">{{ $fs->end_time->format('h:i A') }}</div>
            </div>
            <div class="ms-auto text-end" style="align-self:center;">
              <span class="badge bg-primary" style="font-size:12px;">
                {{ $fs->products->count() }} {{ $bn ? 'টি পণ্য' : 'products' }}
              </span>
            </div>
          </div>

          @if($isLive)
          <div class="mb-2">
            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:11px;">
              <span class="countdown-badge" id="countdown-{{ $fs->id }}" data-end="{{ $fs->end_time->timestamp }}">
                <i class="fas fa-hourglass-half"></i>
                {{ $fs->end_time->diffForHumans(['parts' => 2]) }}
              </span>
              <span class="text-muted">{{ $progPct }}% {{ $bn ? 'সময় শেষ' : 'elapsed' }}</span>
            </div>
            <div class="prog-bar-wrap">
              <div class="prog-bar time" style="width:{{ $progPct }}%"></div>
            </div>
          </div>
          @endif

          @if($totalMax > 0)
          <div>
            <div class="d-flex justify-content-between mb-1" style="font-size:11px;color:var(--muted);">
              <span>{{ $bn ? 'স্টক বিক্রয়:' : 'Stock sold:' }}
                <strong style="color:var(--text);">{{ number_format($totalSold) }} / {{ number_format($totalMax) }}</strong>
              </span>
              <span>{{ $stockPct }}%</span>
            </div>
            <div class="prog-bar-wrap">
              <div class="prog-bar stock" style="width:{{ $stockPct }}%"></div>
            </div>
          </div>
          @endif
        </div>

        {{-- Footer --}}
        <div class="flash-card-footer">
          <div>
            <button type="button"
              class="btn btn-sm {{ $fs->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }} toggle-btn"
              data-id="{{ $fs->id }}"
              data-url="{{ route('admin.flash-sales.toggle', $fs) }}"
              data-active="{{ $fs->is_active ? '1' : '0' }}"
              title="{{ $fs->is_active ? ($bn ? 'বন্ধ করুন' : 'Deactivate') : ($bn ? 'চালু করুন' : 'Activate') }}">
              <i class="fas {{ $fs->is_active ? 'fa-pause' : 'fa-play' }}"></i>
              <span class="d-none d-sm-inline ms-1">
                {{ $fs->is_active ? ($bn ? 'বন্ধ' : 'Deactivate') : ($bn ? 'চালু' : 'Activate') }}
              </span>
            </button>
          </div>

          <div class="d-flex gap-2">
            <a href="{{ route('admin.flash-sales.show', $fs) }}"
               class="btn btn-sm btn-primary"
               title="{{ $bn ? 'পণ্য ব্যবস্থাপনা' : 'Manage Products' }}">
              <i class="fas fa-cog me-1"></i>{{ $bn ? 'ব্যবস্থাপনা' : 'Manage' }}
            </a>
            <button type="button"
                    class="btn btn-sm btn-outline-danger delete-btn"
                    data-id="{{ $fs->id }}"
                    data-url="{{ route('admin.flash-sales.destroy', $fs) }}"
                    data-name="{{ e($fs->title_en) }}"
                    title="{{ $bn ? 'মুছে ফেলুন' : 'Delete' }}">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>
      </div>
      @empty
      <div id="empty-state" style="grid-column:1/-1;">
        <div class="text-center py-5 text-muted">
          <i class="fas fa-bolt fa-3x mb-3 d-block opacity-25"></i>
          <p class="bengali-text mb-2">{{ $bn ? 'কোনো ফ্ল্যাশ সেল নেই' : 'No Flash Sales found' }}</p>
          <button class="btn btn-danger mt-2" data-bs-toggle="modal" data-bs-target="#addFlashSaleModal">
            <i class="fas fa-plus me-1"></i>{{ $bn ? 'প্রথম ফ্ল্যাশ সেল তৈরি করুন' : 'Create First Flash Sale' }}
          </button>
        </div>
      </div>
      @endforelse
    </div>

    @if($total > $perPage)
    <div id="scroll-sentinel" class="mt-3">
      <div class="spin"></div>
      <span class="bengali-text">{{ $bn ? 'আরো লোড হচ্ছে...' : 'Loading more…' }}</span>
    </div>
    @endif

    <div id="no-more-banner" class="{{ $total <= $perPage ? '' : 'hidden' }}">
      @if($total > 0)
      <i class="fas fa-check-circle me-2 text-success"></i>
      <span class="bengali-text">
        {{ $bn ? 'সব ' . number_format($total) . 'টি ফ্ল্যাশ সেল লোড হয়েছে' : 'All ' . number_format($total) . ' flash sales loaded' }}
      </span>
      @endif
    </div>
  </div>
</div>

{{-- ════ DELETE CONFIRM MODAL ════ --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-modal="true" role="dialog">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <div class="d-flex align-items-center gap-2">
          <div style="width:36px;height:36px;border-radius:50%;background:rgba(220,38,38,.12);
               display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-trash" style="color:#dc2626;font-size:14px;"></i>
          </div>
          <h6 class="modal-title mb-0 bengali-text">{{ $bn ? 'নিশ্চিত করুন' : 'Confirm Delete' }}</h6>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        <p class="text-muted bengali-text mb-1" style="font-size:13px;">
          {{ $bn ? '"' : 'Delete "' }}<strong id="delete-name-display"></strong>{{ $bn ? '" মুছে ফেলবেন?' : '"?' }}
        </p>
        <small class="text-danger bengali-text">
          <i class="fas fa-exclamation-triangle me-1"></i>
          {{ $bn ? 'সব পণ্যও মুছে যাবে। এটি পূর্বাবস্থায় ফেরানো যাবে না।' : 'All products will be removed. This cannot be undone.' }}
        </small>
      </div>
      <div class="modal-footer border-0 pt-0 gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
          {{ $bn ? 'বাতিল' : 'Cancel' }}
        </button>
        <button type="button" id="confirm-delete-btn" class="btn btn-sm btn-danger">
          <i class="fas fa-trash me-1"></i>{{ $bn ? 'মুছুন' : 'Delete' }}
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ════ ADD FLASH SALE MODAL ════ --}}
<div class="modal fade" id="addFlashSaleModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('admin.flash-sales.store') }}">
        @csrf
        <div class="modal-header" style="background:linear-gradient(135deg,#dc2626,#f97316);color:#fff;">
          <h5 class="modal-title bengali-text">
            <i class="fas fa-bolt me-2"></i>
            {{ $bn ? 'নতুন ফ্ল্যাশ সেল তৈরি' : 'Create New Flash Sale' }}
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">
                {{ $bn ? 'শিরোনাম (ইংরেজি)' : 'Title (English)' }} <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control" name="title_en" required
                placeholder="{{ $bn ? 'যেমন: Eid Mega Flash Sale' : 'e.g. Eid Mega Flash Sale' }}"
                value="Flash Sale">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold bengali-text">
                শিরোনাম (বাংলা) <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control bengali-text" name="title_bn" required
                placeholder="যেমন: ঈদ মেগা ফ্ল্যাশ সেল" value="ফ্ল্যাশ সেল">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">
                <i class="fas fa-calendar-alt me-1 text-danger"></i>
                {{ $bn ? 'শুরুর সময়' : 'Start Time' }} <span class="text-danger">*</span>
              </label>
              <input type="datetime-local" class="form-control" name="start_time"
                value="{{ now()->format('Y-m-d\TH:i') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">
                <i class="fas fa-calendar-check me-1" style="color:#f97316;"></i>
                {{ $bn ? 'শেষের সময়' : 'End Time' }} <span class="text-danger">*</span>
              </label>
              <input type="datetime-local" class="form-control" name="end_time"
                value="{{ now()->addHours(24)->format('Y-m-d\TH:i') }}" required>
            </div>
          </div>
          <div class="alert alert-info mb-0 mt-3" style="font-size:13px;">
            <i class="fas fa-info-circle me-2"></i>
            <span class="bengali-text">
              {{ $bn ? 'তৈরির পর পণ্য যোগ করতে পারবেন এবং সেল চালু করতে পারবেন।' : 'After creating, you can add products and activate the sale.' }}
            </span>
          </div>
        </div>
        <div class="modal-footer gap-2">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            {{ $bn ? 'বাতিল' : 'Cancel' }}
          </button>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-bolt me-2"></i>
            {{ $bn ? 'ফ্ল্যাশ সেল তৈরি করুন' : 'Create Flash Sale' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
  var FS_AJAX_URL  = '{{ route("admin.flash-sales.index") }}';
  var FS_PER_PAGE  = {{ $perPage }};
  var FS_TOTAL_INT = {{ $total }};
  var FS_BN        = {{ $bn ? 'true' : 'false' }};
  var FS_FILTERS   = {
    search: {!! json_encode($search) !!},
    status: {!! json_encode($status) !!},
  };
</script>
@verbatim
<script>
(function () {
  'use strict';

  const AJAX_URL  = window.FS_AJAX_URL;
  const PER_PAGE  = window.FS_PER_PAGE;
  const TOTAL_INT = window.FS_TOTAL_INT;
  const BN        = window.FS_BN;
  const FILTERS   = window.FS_FILTERS;
  const CSRF      = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  let currentPage = 1;
  let loading     = false;
  let hasMore     = TOTAL_INT > PER_PAGE;

  const grid       = document.getElementById('flash-grid');
  const sentinel   = document.getElementById('scroll-sentinel');
  const noMore     = document.getElementById('no-more-banner');
  const loadedSpan = document.getElementById('loaded-count');
  const progress   = document.getElementById('load-progress');
  const scrollBtn  = document.getElementById('scrollTopBtn');

  // ── Toast ──────────────────────────────────────────────────────
  function showToast(msg, type) {
    var t = document.createElement('div');
    t.className = 'fs-toast ' + type;
    t.innerHTML = '<i class="fas ' + (type==='success'?'fa-check-circle':'fa-exclamation-circle') + '"></i> ' + msg;
    document.body.appendChild(t);
    setTimeout(function() {
      t.style.animation = 'toastOut .3s ease forwards';
      setTimeout(function() { t.remove(); }, 300);
    }, 3000);
  }

  // ── Progress bar ───────────────────────────────────────────────
  function showProgress(p) { progress.style.width = p + '%'; progress.style.opacity = '1'; }
  function hideProgress()  { progress.style.width = '100%'; setTimeout(function(){ progress.style.opacity='0'; progress.style.width='0%'; }, 300); }

  // ── Build card from AJAX JSON ──────────────────────────────────
  function buildCard(r) {
    var div = document.createElement('div');
    div.className = 'flash-card' + (r.is_live ? ' is-live' : '') + (r.is_expired ? ' is-expired' : '');
    div.id = 'flash-card-' + r.id;

    var stockBar = r.total_max > 0 ? (
      '<div>' +
        '<div class="d-flex justify-content-between mb-1" style="font-size:11px;color:var(--muted);">' +
          '<span>' + (BN ? 'স্টক বিক্রয়:' : 'Stock sold:') +
            ' <strong style="color:var(--text);">' + r.total_sold + ' / ' + r.total_max + '</strong></span>' +
          '<span>' + r.stock_pct + '%</span>' +
        '</div>' +
        '<div class="prog-bar-wrap"><div class="prog-bar stock" style="width:' + r.stock_pct + '%"></div></div>' +
      '</div>'
    ) : '';

    var liveBar = r.is_live ? (
      '<div class="mb-2">' +
        '<div class="d-flex justify-content-between align-items-center mb-1" style="font-size:11px;">' +
          '<span class="countdown-badge" id="countdown-' + r.id + '">' +
            '<i class="fas fa-hourglass-half"></i> ' + r.remaining +
          '</span>' +
          '<span class="text-muted">' + r.prog_pct + '% ' + (BN ? 'সময় শেষ' : 'elapsed') + '</span>' +
        '</div>' +
        '<div class="prog-bar-wrap"><div class="prog-bar time" style="width:' + r.prog_pct + '%"></div></div>' +
      '</div>'
    ) : '';

    div.innerHTML =
      '<div class="flash-card-header">' +
        '<div class="flex-grow-1 min-w-0">' +
          '<div class="fw-bold" style="font-size:14px;line-height:1.3;">' + escHtml(r.title_en) + '</div>' +
          '<div class="bengali-text text-muted" style="font-size:12px;margin-top:2px;">' + escHtml(r.title_bn) + '</div>' +
        '</div>' +
        '<div class="flex-shrink-0">' + r.status_badge + '</div>' +
      '</div>' +
      '<div class="flash-card-body">' +
        '<div class="d-flex gap-3 mb-3" style="font-size:12px;">' +
          '<div>' +
            '<div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">' + (BN?'শুরু':'Start') + '</div>' +
            '<div class="fw-semibold">' + r.start_fmt.split(',')[0] + '</div>' +
            '<div class="text-muted">' + (r.start_fmt.split(',')[1]||'').trim() + '</div>' +
          '</div>' +
          '<div style="color:var(--muted);align-self:center;"><i class="fas fa-arrow-right"></i></div>' +
          '<div>' +
            '<div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">' + (BN?'শেষ':'End') + '</div>' +
            '<div class="fw-semibold ' + (r.is_expired?'text-danger':'') + '">' + r.end_fmt.split(',')[0] + '</div>' +
            '<div class="text-muted">' + (r.end_fmt.split(',')[1]||'').trim() + '</div>' +
          '</div>' +
          '<div class="ms-auto text-end" style="align-self:center;">' +
            '<span class="badge bg-primary" style="font-size:12px;">' + r.product_count + ' ' + (BN?'টি পণ্য':'products') + '</span>' +
          '</div>' +
        '</div>' +
        liveBar + stockBar +
      '</div>' +
      '<div class="flash-card-footer">' +
        '<div>' +
          '<button type="button" class="btn btn-sm ' + (r.is_active ? 'btn-outline-secondary' : 'btn-outline-success') + ' toggle-btn"' +
            ' data-id="' + r.id + '"' +
            ' data-url="' + escAttr(r.toggle_url) + '"' +
            ' data-active="' + (r.is_active ? '1' : '0') + '">' +
            '<i class="fas ' + (r.is_active ? 'fa-pause' : 'fa-play') + '"></i>' +
            '<span class="d-none d-sm-inline ms-1">' + (r.is_active ? (BN?'বন্ধ':'Deactivate') : (BN?'চালু':'Activate')) + '</span>' +
          '</button>' +
        '</div>' +
        '<div class="d-flex gap-2">' +
          '<a href="' + escAttr(r.show_url) + '" class="btn btn-sm btn-primary">' +
            '<i class="fas fa-cog me-1"></i>' + (BN?'ব্যবস্থাপনা':'Manage') +
          '</a>' +
          '<button type="button" class="btn btn-sm btn-outline-danger delete-btn"' +
            ' data-id="' + r.id + '"' +
            ' data-url="' + escAttr(r.delete_url) + '"' +
            ' data-name="' + escAttr(r.title_en) + '">' +
            '<i class="fas fa-trash"></i>' +
          '</button>' +
        '</div>' +
      '</div>';

    return div;
  }

  function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }
  function escAttr(s) {
    return String(s).replace(/"/g,'&quot;').replace(/'/g,'&#39;');
  }

  // ── Infinite scroll ────────────────────────────────────────────
  async function loadMore() {
    if (loading || !hasMore) return;
    loading = true;
    showProgress(40);

    var nextPage = currentPage + 1;
    var params   = new URLSearchParams(Object.assign({}, FILTERS, { page: nextPage }));

    try {
      var res  = await fetch(AJAX_URL + '?' + params.toString(), {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept':           'application/json',
          'X-CSRF-TOKEN':     CSRF,
        }
      });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      showProgress(80);
      var data = await res.json();

      var emptyState = document.getElementById('empty-state');
      if (emptyState) emptyState.remove();

      data.rows.forEach(function(r, i) {
        var card = buildCard(r);
        card.style.animationDelay = (i * 40) + 'ms';
        grid.appendChild(card);
      });

      currentPage = data.page;
      hasMore     = data.hasMore;
      var loaded  = Math.min(currentPage * PER_PAGE, TOTAL_INT);
      if (loadedSpan) loadedSpan.textContent = loaded;

      if (!hasMore) {
        if (sentinel) sentinel.classList.add('hidden');
        if (noMore)   noMore.classList.remove('hidden');
      }
    } catch (e) {
      console.error('Infinite scroll error:', e);
    } finally {
      loading = false;
      hideProgress();
    }
  }

  if (sentinel && 'IntersectionObserver' in window) {
    new IntersectionObserver(function(entries) {
      if (entries[0].isIntersecting) loadMore();
    }, { rootMargin: '200px' }).observe(sentinel);
  }
  if (!hasMore && noMore) noMore.classList.remove('hidden');

  // ── Scroll top ────────────────────────────────────────────────
  window.addEventListener('scroll', function() {
    if (scrollBtn) scrollBtn.style.display = window.scrollY > 400 ? 'flex' : 'none';
  }, { passive: true });
  if (scrollBtn) scrollBtn.addEventListener('click', function() { window.scrollTo({ top: 0, behavior: 'smooth' }); });

  // ── DELETE — AJAX ─────────────────────────────────────────────
  var pendingDeleteId   = null;
  var pendingDeleteUrl  = null;
  var pendingDeleteName = null;
  var deleteModal       = null;
  var deleteNameDisplay = document.getElementById('delete-name-display');

  // Helper: always get fresh reference in case DOM wasn't ready at init
  function getConfirmBtn() {
    return document.getElementById('confirm-delete-btn');
  }

  function getDeleteModal() {
    if (!deleteModal) {
      deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    }
    return deleteModal;
  }

  // Open delete confirm modal
  document.addEventListener('click', function(e) {
    var btn = e.target.closest('.delete-btn');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();

    pendingDeleteId   = btn.getAttribute('data-id');
    pendingDeleteUrl  = btn.getAttribute('data-url');
    pendingDeleteName = btn.getAttribute('data-name') || '';

    if (deleteNameDisplay) deleteNameDisplay.textContent = pendingDeleteName;
    getDeleteModal().show();
  });

  // Confirm and fire DELETE request
  document.addEventListener('click', function(e) {
    if (!e.target.closest('#confirm-delete-btn')) return;

    var confirmBtn = getConfirmBtn();
    if (!confirmBtn) return;
    {
      if (!pendingDeleteUrl || !pendingDeleteId) return;

      var url    = pendingDeleteUrl;
      var cardId = pendingDeleteId;
      var isBn   = BN;

      confirmBtn.disabled = true;
      confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>' + (isBn ? 'মুছছে...' : 'Deleting…');

      var formData = new FormData();
      formData.append('_method', 'DELETE');
      formData.append('_token',  CSRF);

      fetch(url, {
        method:  'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept':           'application/json',
          'X-CSRF-TOKEN':     CSRF,
        },
        body: formData,
      })
      .then(function(res) {
        if (!res.ok && res.status !== 422) throw new Error('HTTP ' + res.status);
        return res.json();
      })
      .then(function(data) {
        resetConfirmBtn(isBn);
        getDeleteModal().hide();
        pendingDeleteId = pendingDeleteUrl = pendingDeleteName = null;

        if (data.success === false) {
          showToast(data.message || (isBn ? 'মুছতে ব্যর্থ হয়েছে।' : 'Delete failed.'), 'error');
          return;
        }

        // Remove card from DOM with animation
        var card = document.getElementById('flash-card-' + cardId);
        if (card) {
          card.style.transition = 'opacity .3s ease, transform .3s ease';
          card.style.opacity    = '0';
          card.style.transform  = 'scale(.92)';
          setTimeout(function() { card.remove(); }, 320);
        }

        // Update counter
        if (loadedSpan) {
          var n = Math.max(0, parseInt(loadedSpan.textContent, 10) - 1);
          loadedSpan.textContent = n;
        }

        showToast(data.message || (isBn ? 'ফ্ল্যাশ সেল মুছে ফেলা হয়েছে।' : 'Flash Sale deleted.'), 'success');
      })
      .catch(function(err) {
        resetConfirmBtn(isBn);
        getDeleteModal().hide();
        pendingDeleteId = pendingDeleteUrl = pendingDeleteName = null;
        console.error('Delete error:', err);
        showToast(isBn ? 'মুছতে ব্যর্থ হয়েছে। আবার চেষ্টা করুন।' : 'Delete failed. Please try again.', 'error');
      });
    }
  });

  function resetConfirmBtn(isBn) {
    var confirmBtn = getConfirmBtn();
    if (!confirmBtn) return;
    confirmBtn.disabled = false;
    confirmBtn.innerHTML = '<i class="fas fa-trash me-1"></i>' + (isBn ? 'মুছুন' : 'Delete');
  }

  // ── TOGGLE — AJAX ─────────────────────────────────────────────
  document.addEventListener('click', function(e) {
    var btn = e.target.closest('.toggle-btn');
    if (!btn) return;
    e.preventDefault();

    var id      = btn.getAttribute('data-id');
    var url     = btn.getAttribute('data-url');
    var isBn    = BN;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    var formData = new FormData();
    formData.append('_token', CSRF);

    fetch(url, {
      method:  'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept':           'application/json',
        'X-CSRF-TOKEN':     CSRF,
      },
      body: formData,
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
      showToast(data.message || (isBn ? 'আপডেট হয়েছে।' : 'Updated.'), 'success');
      // Reload to reflect accurate state
      setTimeout(function() { window.location.reload(); }, 800);
    })
    .catch(function() {
      btn.disabled = false;
      showToast(isBn ? 'আপডেট ব্যর্থ হয়েছে।' : 'Update failed.', 'error');
    });
  });

  // ── Live countdown ────────────────────────────────────────────
  function updateCountdowns() {
    document.querySelectorAll('.countdown-badge[data-end]').forEach(function(el) {
      var end  = parseInt(el.getAttribute('data-end'), 10) * 1000;
      var diff = end - Date.now();
      if (diff <= 0) {
        el.innerHTML = '<i class="fas fa-check"></i> ' + (BN ? 'শেষ' : 'Ended');
        return;
      }
      var h = Math.floor(diff / 3600000);
      var m = Math.floor((diff % 3600000) / 60000);
      var s = Math.floor((diff % 60000) / 1000);
      var pad = function(n) { return n < 10 ? '0' + n : n; };
      el.innerHTML = '<i class="fas fa-hourglass-half"></i> ' + pad(h) + 'h ' + pad(m) + 'm ' + pad(s) + 's';
    });
  }
  updateCountdowns();
  setInterval(updateCountdowns, 1000);

})();
</script>
@endverbatim
@endpush

@endsection