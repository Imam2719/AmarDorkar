@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'ফ্ল্যাশ সেল: '.$flashSale->title_bn : 'Flash Sale: '.$flashSale->title_en)
@section('page-title', app()->getLocale()==='bn' ? 'ফ্ল্যাশ সেল: '.$flashSale->title_bn : 'Flash Sale: '.$flashSale->title_en)
@php
  $bn    = app()->getLocale() === 'bn';
  $now   = now();
  $isLive    = $flashSale->is_active && $flashSale->start_time <= $now && $flashSale->end_time >= $now;
  $isExpired = $flashSale->end_time < $now;
  $isUpcoming= $flashSale->start_time > $now && $flashSale->is_active;
@endphp

@push('styles')
<style>
/* ── Show page ── */
.info-label {
  font-size: 10px; text-transform: uppercase; letter-spacing: .6px;
  color: var(--muted); font-weight: 600; margin-bottom: 3px;
}
.time-block {
  background: var(--bg); border: 1px solid var(--border);
  border-radius: 10px; padding: 12px 16px; text-align: center;
}
.product-row {
  display: grid;
  grid-template-columns: 52px 1fr auto auto auto auto;
  align-items: center; gap: 12px;
  padding: 12px 16px;
  border-bottom: 1px solid var(--border);
  transition: background .15s;
}
.product-row:last-child { border-bottom: none; }
.product-row:hover { background: var(--hover); }

.prog-sm { height: 5px; border-radius: 99px; background: var(--border-2); overflow: hidden; }
.prog-sm-bar { height: 100%; border-radius: 99px; background: linear-gradient(90deg, var(--primary), var(--success)); }

.status-chip {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 4px 12px; border-radius: 20px;
  font-size: 12px; font-weight: 700;
}
.chip-live     { background: rgba(220,38,38,.12); color: #dc2626; border: 1px solid rgba(220,38,38,.3); }
.chip-active   { background: rgba(217,119,6,.12);  color: var(--warning); border: 1px solid rgba(217,119,6,.25); }
.chip-inactive { background: rgba(100,116,139,.1); color: var(--muted); border: 1px solid var(--border); }
.chip-expired  { background: rgba(100,116,139,.1); color: var(--muted-2); border: 1px solid var(--border); }

.flash-price { color: #dc2626; font-weight: 800; font-size: 15px; }
.orig-price  { color: var(--muted-2); text-decoration: line-through; font-size: 12px; }

@media(max-width: 575px) {
  .product-row {
    grid-template-columns: 44px 1fr;
    grid-template-rows: auto auto;
  }
  .product-row .col-hide-sm { display: none; }
}

/* Dark mode form fixes */
.adm-dark .form-control, .adm-dark .form-select {
  background: var(--bg); color: var(--text);
  border-color: var(--border);
}
.adm-dark .form-control:focus, .adm-dark .form-select:focus {
  background: var(--bg); color: var(--text);
  border-color: var(--primary); box-shadow: 0 0 0 .2rem rgba(37,99,235,.25);
}
.adm-dark .form-control::placeholder { color: var(--muted-2); }
.adm-dark .alert-info { background: rgba(37,99,235,.12); border-color: rgba(37,99,235,.3); color: var(--text); }
.adm-dark .alert-warning { background: rgba(217,119,6,.12); border-color: rgba(217,119,6,.25); color: var(--text); }
.adm-dark .btn-close { filter: invert(1); }

/* Toast */
.fs-toast {
  position: fixed; top: 20px; right: 20px; z-index: 10000;
  padding: 12px 20px; border-radius: 10px; font-size: 13px; font-weight: 600;
  display: flex; align-items: center; gap: 8px;
  box-shadow: var(--sh-lg); max-width: 320px; color: #fff;
  animation: toastIn .3s ease both;
}
.fs-toast.success { background: var(--success); }
.fs-toast.error   { background: var(--danger); }
@keyframes toastIn  { from { opacity:0; transform: translateX(40px); } to { opacity:1; transform: translateX(0); } }
@keyframes toastOut { to   { opacity:0; transform: translateX(40px); } }
</style>
@endpush

@section('content')

{{-- ════ HEADER INFO CARD ════ --}}
<div class="row mb-4 g-3">
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header" style="background:linear-gradient(135deg,#dc2626,#f97316);color:#fff;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <h5 class="mb-0 bengali-text">
            <i class="fas fa-bolt me-2"></i>
            {{ $bn ? $flashSale->title_bn : $flashSale->title_en }}
          </h5>
          <div class="d-flex gap-2 align-items-center">
            {{-- Status chip --}}
            @if($isLive)
              <span class="status-chip chip-live" style="background:rgba(255,255,255,.2);color:#fff;border-color:rgba(255,255,255,.4);">
                <i class="fas fa-bolt"></i>{{ $bn ? 'লাইভ' : 'LIVE' }}
              </span>
            @elseif($isUpcoming)
              <span class="status-chip" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.4);">
                <i class="fas fa-clock"></i>{{ $bn ? 'আসছে' : 'Upcoming' }}
              </span>
            @elseif($flashSale->is_active)
              <span class="status-chip" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.4);">
                <i class="fas fa-check-circle"></i>{{ $bn ? 'সক্রিয়' : 'Active' }}
              </span>
            @elseif($isExpired)
              <span class="status-chip" style="background:rgba(255,255,255,.15);color:#ffffffcc;border:1px solid rgba(255,255,255,.3);">
                <i class="fas fa-history"></i>{{ $bn ? 'মেয়াদ শেষ' : 'Expired' }}
              </span>
            @else
              <span class="status-chip" style="background:rgba(255,255,255,.15);color:#ffffffcc;border:1px solid rgba(255,255,255,.3);">
                <i class="fas fa-pause-circle"></i>{{ $bn ? 'নিষ্ক্রিয়' : 'Inactive' }}
              </span>
            @endif

            {{-- Toggle --}}
            <button type="button"
              class="btn btn-sm {{ $flashSale->is_active ? 'btn-light' : 'btn-outline-light' }} toggle-main-btn"
              data-url="{{ route('admin.flash-sales.toggle', $flashSale) }}"
              id="toggleBtn">
              <i class="fas {{ $flashSale->is_active ? 'fa-pause' : 'fa-play' }} me-1"></i>
              {{ $flashSale->is_active ? ($bn ? 'বন্ধ করুন' : 'Deactivate') : ($bn ? 'চালু করুন' : 'Activate') }}
            </button>

            {{-- Delete Flash Sale --}}
            <button type="button"
              class="btn btn-sm btn-outline-light"
              id="deleteSaleBtn"
              data-url="{{ route('admin.flash-sales.destroy', $flashSale) }}"
              data-name="{{ e($flashSale->title_en) }}"
              title="{{ $bn ? 'ফ্ল্যাশ সেল মুছুন' : 'Delete Flash Sale' }}">
              <i class="fas fa-trash me-1"></i>{{ $bn ? 'মুছুন' : 'Delete' }}
            </button>

            <a href="{{ route('admin.flash-sales.index') }}" class="btn btn-sm btn-outline-light">
              <i class="fas fa-arrow-left me-1"></i>{{ $bn ? 'ফিরে যান' : 'Back' }}
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-4">
            <div class="time-block">
              <div class="info-label">{{ $bn ? 'শুরু' : 'Start' }}</div>
              <div class="fw-bold" style="font-size:13px;">{{ $flashSale->start_time->format('d M Y') }}</div>
              <div class="text-muted" style="font-size:12px;">{{ $flashSale->start_time->format('h:i A') }}</div>
            </div>
          </div>
          <div class="col-4 d-flex align-items-center justify-content-center">
            <i class="fas fa-arrow-right text-muted"></i>
          </div>
          <div class="col-4">
            <div class="time-block">
              <div class="info-label">{{ $bn ? 'শেষ' : 'End' }}</div>
              <div class="fw-bold {{ $isExpired ? 'text-danger' : '' }}" style="font-size:13px;">
                {{ $flashSale->end_time->format('d M Y') }}
              </div>
              <div class="text-muted" style="font-size:12px;">{{ $flashSale->end_time->format('h:i A') }}</div>
            </div>
          </div>
        </div>

        @if($isLive)
        <div class="mt-3 p-3 rounded-3" style="background:rgba(220,38,38,.07);border:1px solid rgba(220,38,38,.2);">
          <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:12px;">
            <span style="color:#dc2626;font-weight:700;">
              <i class="fas fa-bolt me-1"></i>{{ $bn ? 'লাইভ চলছে' : 'Currently LIVE' }}
            </span>
            <span id="live-countdown" class="text-muted fw-semibold" data-end="{{ $flashSale->end_time->timestamp }}">
              {{ $flashSale->end_time->diffForHumans(['parts' => 2]) }}
            </span>
          </div>
          @php
            $totalDur = max(1, $flashSale->end_time->diffInSeconds($flashSale->start_time));
            $elapsed  = $now->diffInSeconds($flashSale->start_time);
            $progPct  = min(100, round(($elapsed / $totalDur) * 100));
          @endphp
          <div style="height:6px;border-radius:99px;background:rgba(220,38,38,.15);overflow:hidden;">
            <div style="height:100%;width:{{ $progPct }}%;border-radius:99px;background:linear-gradient(90deg,#ef4444,#f97316);"></div>
          </div>
          <div class="text-muted mt-1" style="font-size:11px;">{{ $progPct }}% {{ $bn ? 'সময় শেষ' : 'elapsed' }}</div>
        </div>
        @endif
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-body d-flex flex-column justify-content-center align-items-center text-center gap-3">
        <div>
          <div style="font-size:42px;font-weight:800;color:#dc2626;line-height:1;">
            {{ $flashSale->products->count() }}
          </div>
          <div class="text-muted bengali-text" style="font-size:13px;">{{ $bn ? 'মোট পণ্য' : 'Total Products' }}</div>
        </div>
        <div class="w-100" style="border-top:1px solid var(--border);padding-top:12px;">
          <div class="fw-bold text-success" style="font-size:18px;">
            {{ number_format($flashSale->products->sum('sold_quantity')) }}
          </div>
          <div class="text-muted bengali-text" style="font-size:13px;">{{ $bn ? 'মোট বিক্রয়' : 'Total Sold' }}</div>
        </div>
        @php
          $tSold = $flashSale->products->sum('sold_quantity');
          $tMax  = $flashSale->products->sum('max_quantity');
          $sPct  = $tMax > 0 ? min(100, round(($tSold / $tMax) * 100)) : 0;
        @endphp
        @if($tMax > 0)
        <div class="w-100">
          <div class="d-flex justify-content-between mb-1" style="font-size:11px;color:var(--muted);">
            <span>{{ $bn ? 'স্টক অগ্রগতি' : 'Stock Progress' }}</span>
            <span>{{ $sPct }}%</span>
          </div>
          <div style="height:6px;border-radius:99px;background:var(--border-2);overflow:hidden;">
            <div style="height:100%;width:{{ $sPct }}%;border-radius:99px;background:linear-gradient(90deg,var(--primary),var(--success));"></div>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- ════ EDIT FORM ════ --}}
<div class="card mb-4">
  <div class="card-header">
    <h6 class="card-title bengali-text">
      <i class="fas fa-edit me-2 text-primary"></i>
      {{ $bn ? 'ফ্ল্যাশ সেল সম্পাদনা করুন' : 'Edit Flash Sale' }}
    </h6>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.flash-sales.update', $flashSale) }}">
      @csrf
      @method('PUT')
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label fw-semibold">
            {{ $bn ? 'শিরোনাম (ইংরেজি)' : 'Title (English)' }} <span class="text-danger">*</span>
          </label>
          <input type="text" class="form-control" name="title_en"
            value="{{ old('title_en', $flashSale->title_en) }}" required>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold bengali-text">
            শিরোনাম (বাংলা) <span class="text-danger">*</span>
          </label>
          <input type="text" class="form-control bengali-text" name="title_bn"
            value="{{ old('title_bn', $flashSale->title_bn) }}" required>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">
            {{ $bn ? 'শুরুর সময়' : 'Start Time' }} <span class="text-danger">*</span>
          </label>
          <input type="datetime-local" class="form-control" name="start_time"
            value="{{ old('start_time', $flashSale->start_time->format('Y-m-d\TH:i')) }}" required>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">
            {{ $bn ? 'শেষের সময়' : 'End Time' }} <span class="text-danger">*</span>
          </label>
          <input type="datetime-local" class="form-control" name="end_time"
            value="{{ old('end_time', $flashSale->end_time->format('Y-m-d\TH:i')) }}" required>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-save me-1"></i>{{ $bn ? 'আপডেট করুন' : 'Update' }}
          </button>
        </div>
      </div>

      @if($errors->any())
      <div class="alert alert-danger mt-3 mb-0 py-2" style="font-size:13px;">
        <ul class="mb-0 ps-3">
          @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
          @endforeach
        </ul>
      </div>
      @endif
    </form>
  </div>
</div>

{{-- ════ ADD PRODUCT FORM ════ --}}
@if($products->count())
<div class="card mb-4">
  <div class="card-header">
    <h6 class="card-title bengali-text">
      <i class="fas fa-plus-circle me-2 text-success"></i>
      {{ $bn ? 'পণ্য যোগ করুন' : 'Add Product to Flash Sale' }}
    </h6>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.flash-sales.add-product', $flashSale) }}">
      @csrf
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label fw-semibold">
            {{ $bn ? 'পণ্য নির্বাচন করুন' : 'Select Product' }} <span class="text-danger">*</span>
          </label>
          <select class="form-select" name="product_id" id="productSelect" required>
            <option value="">-- {{ $bn ? 'পণ্য বেছে নিন' : 'Choose a product' }} --</option>
            @foreach($products as $p)
            <option value="{{ $p->id }}" data-price="{{ $p->price }}">
              {{ $p->name_en }} — ৳{{ number_format($p->price) }}
            </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">
            {{ $bn ? 'ফ্ল্যাশ মূল্য (৳)' : 'Flash Price (৳)' }} <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" class="form-control" name="flash_price"
              id="flashPriceInput" step="0.01" min="1" required
              placeholder="{{ $bn ? '০.০০' : '0.00' }}">
          </div>
          <small class="text-muted" id="priceSuggestion" style="font-size:11px;display:none;"></small>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">
            {{ $bn ? 'সর্বোচ্চ পরিমাণ' : 'Max Quantity' }} <span class="text-danger">*</span>
          </label>
          <input type="number" class="form-control" name="max_quantity"
            min="1" value="50" required>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-danger w-100">
            <i class="fas fa-bolt me-1"></i>{{ $bn ? 'যোগ করুন' : 'Add' }}
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@else
<div class="alert alert-info mb-4" style="font-size:13px;">
  <i class="fas fa-info-circle me-2"></i>
  <span class="bengali-text">
    {{ $bn ? 'সব সক্রিয় পণ্য ইতিমধ্যে এই ফ্ল্যাশ সেলে যোগ করা হয়েছে।' : 'All active products have already been added to this flash sale.' }}
  </span>
</div>
@endif

{{-- ════ PRODUCTS TABLE ════ --}}
<div class="card">
  <div class="card-header">
    <h6 class="card-title bengali-text">
      <i class="fas fa-list me-2"></i>
      {{ $bn ? 'ফ্ল্যাশ সেলের পণ্যসমূহ' : 'Flash Sale Products' }}
      <span class="badge bg-primary ms-2" style="font-size:11px;">{{ $flashSale->products->count() }}</span>
    </h6>
  </div>

  @forelse($flashSale->products as $fsp)
  @php
    $origPrice   = $fsp->product?->price ?? 0;
    $flashPrice  = $fsp->flash_price;
    $discountPct = $origPrice > 0 ? max(0, round((($origPrice - $flashPrice) / $origPrice) * 100)) : 0;
    $soldPct     = $fsp->max_quantity > 0 ? min(100, round(($fsp->sold_quantity / $fsp->max_quantity) * 100)) : 0;
    $imgUrl      = $fsp->product?->image_url ?? null;
  @endphp
  <div class="product-row" id="fsp-row-{{ $fsp->id }}">
    {{-- Image --}}
    <div>
      @if($imgUrl)
        <img src="{{ $imgUrl }}" alt="{{ $fsp->product?->name_en }}"
          style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
      @else
        <div style="width:48px;height:48px;border-radius:8px;background:var(--border-2);
             display:flex;align-items:center;justify-content:center;border:1px solid var(--border);">
          <i class="fas fa-image" style="color:var(--muted-2);font-size:18px;"></i>
        </div>
      @endif
    </div>

    {{-- Name & type --}}
    <div class="min-w-0">
      <div class="fw-semibold" style="font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        {{ $fsp->product?->name_en ?? '—' }}
      </div>
      @if($bn && $fsp->product?->name_bn)
      <div class="bengali-text text-muted" style="font-size:11px;">{{ $fsp->product->name_bn }}</div>
      @endif
      <div class="text-muted" style="font-size:11px;">{{ $fsp->product?->product_type ?? '' }}</div>
    </div>

    {{-- Prices --}}
    <div class="text-end col-hide-sm">
      <div class="orig-price">৳{{ number_format($origPrice) }}</div>
      <div class="flash-price">৳{{ number_format($flashPrice) }}</div>
    </div>

    {{-- Discount badge --}}
    <div class="col-hide-sm">
      @if($discountPct > 0)
        <span class="badge bg-warning text-dark">{{ $discountPct }}% OFF</span>
      @elseif($discountPct < 0)
        <span class="badge bg-danger" title="{{ $bn ? 'ফ্ল্যাশ মূল্য মূল মূল্যের চেয়ে বেশি' : 'Flash price exceeds original' }}">
          <i class="fas fa-exclamation-triangle"></i>
        </span>
      @else
        <span class="badge bg-secondary">—</span>
      @endif
    </div>

    {{-- Sold / Max + progress --}}
    <div style="min-width:110px;" class="col-hide-sm">
      <div class="d-flex justify-content-between mb-1" style="font-size:11px;color:var(--muted);">
        <span>
          <strong style="color:{{ $soldPct >= 100 ? '#dc2626' : 'var(--success)' }};">{{ number_format($fsp->sold_quantity) }}</strong>
          / {{ number_format($fsp->max_quantity) }}
        </span>
        <span>{{ $soldPct }}%</span>
      </div>
      <div class="prog-sm">
        <div class="prog-sm-bar" style="width:{{ $soldPct }}%;
          background: {{ $soldPct >= 90 ? 'linear-gradient(90deg,#dc2626,#f97316)' : 'linear-gradient(90deg,var(--primary),var(--success))' }};"></div>
      </div>
    </div>

    {{-- Remove --}}
    <div>
      <form method="POST"
            action="{{ route('admin.flash-sales.remove-product', [$flashSale, $fsp]) }}"
            class="remove-product-form"
            data-name="{{ addslashes($fsp->product?->name_en ?? '') }}"
            data-row="fsp-row-{{ $fsp->id }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-outline-danger"
          title="{{ $bn ? 'এই পণ্য সরিয়ে ফেলুন' : 'Remove this product' }}">
          <i class="fas fa-times"></i>
        </button>
      </form>
    </div>
  </div>
  @empty
  <div class="text-center py-5 text-muted">
    <i class="fas fa-bolt fa-2x mb-3 d-block opacity-25"></i>
    <p class="bengali-text mb-0">{{ $bn ? 'এখনো কোনো পণ্য যোগ করা হয়নি' : 'No products added yet' }}</p>
    @if($products->count())
    <p class="text-muted mt-1" style="font-size:12px;">
      {{ $bn ? 'উপরের ফর্ম থেকে পণ্য যোগ করুন।' : 'Use the form above to add products.' }}
    </p>
    @endif
  </div>
  @endforelse
</div>

@push('scripts')
<script>
(function() {
  'use strict';
  var BN   = {{ $bn ? 'true' : 'false' }};
  var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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

  // ── Product select → suggest flash price ──────────────────────
  var productSelect  = document.getElementById('productSelect');
  var flashPriceInput= document.getElementById('flashPriceInput');
  var priceSuggestion= document.getElementById('priceSuggestion');

  if (productSelect) {
    productSelect.addEventListener('change', function() {
      var opt   = this.options[this.selectedIndex];
      var price = parseFloat(opt.getAttribute('data-price') || 0);
      if (price > 0) {
        var suggested = Math.round(price * 0.7);
        flashPriceInput.value       = suggested;
        flashPriceInput.max         = price - 1;
        if (priceSuggestion) {
          priceSuggestion.textContent = (BN ? 'সাজেশন: ৳' : 'Suggested: ৳') + suggested + ' (30% off)';
          priceSuggestion.style.display = 'block';
        }
      } else {
        flashPriceInput.value = '';
        if (priceSuggestion) priceSuggestion.style.display = 'none';
      }
    });
  }

  // ── Toggle button (AJAX) ──────────────────────────────────────
  var toggleBtn = document.getElementById('toggleBtn');
  if (toggleBtn) {
    toggleBtn.addEventListener('click', function() {
      var url  = this.getAttribute('data-url');
      var btn  = this;
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>' + (BN ? 'আপডেট হচ্ছে...' : 'Updating…');

      var fd = new FormData();
      fd.append('_token', CSRF);

      fetch(url, {
        method:  'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept':           'application/json',
          'X-CSRF-TOKEN':     CSRF,
        },
        body: fd,
      })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        showToast(data.message || (BN ? 'আপডেট হয়েছে।' : 'Updated.'), 'success');
        setTimeout(function() { window.location.reload(); }, 900);
      })
      .catch(function() {
        btn.disabled = false;
        showToast(BN ? 'আপডেট ব্যর্থ হয়েছে।' : 'Update failed.', 'error');
      });
    });
  }

  // ── Remove product (intercept form submit → AJAX) ─────────────
  document.querySelectorAll('.remove-product-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      var name = form.getAttribute('data-name') || '';
      var rowId= form.getAttribute('data-row');
      var msg  = BN
        ? ('\"' + name + '\" সরিয়ে ফেলবেন?')
        : ('Remove \"' + name + '\" from this flash sale?');

      if (!confirm(msg)) return;

      var btn = form.querySelector('button[type="submit"]');
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

      var fd = new FormData(form);

      fetch(form.action, {
        method:  'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept':           'application/json',
          'X-CSRF-TOKEN':     CSRF,
        },
        body: fd,
      })
      .then(function(r) {
        if (r.ok || r.status === 302) {
          var row = document.getElementById(rowId);
          if (row) {
            row.style.transition = 'opacity .3s ease, transform .3s ease';
            row.style.opacity    = '0';
            row.style.transform  = 'translateX(20px)';
            setTimeout(function() { row.remove(); }, 320);
          }
          showToast(BN ? 'পণ্য সরানো হয়েছে।' : 'Product removed.', 'success');
          return;
        }
        throw new Error('HTTP ' + r.status);
      })
      .catch(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-times"></i>';
        showToast(BN ? 'সরাতে ব্যর্থ হয়েছে।' : 'Remove failed.', 'error');
      });
    });
  });

  // ── Live countdown ────────────────────────────────────────────
  var liveCountdown = document.getElementById('live-countdown');
  if (liveCountdown) {
    function tick() {
      var end  = parseInt(liveCountdown.getAttribute('data-end'), 10) * 1000;
      var diff = end - Date.now();
      if (diff <= 0) { liveCountdown.textContent = BN ? 'শেষ' : 'Ended'; return; }
      var h   = Math.floor(diff / 3600000);
      var m   = Math.floor((diff % 3600000) / 60000);
      var s   = Math.floor((diff % 60000) / 1000);
      var pad = function(n) { return n < 10 ? '0'+n : n; };
      liveCountdown.textContent = pad(h) + 'h ' + pad(m) + 'm ' + pad(s) + 's';
    }
    tick();
    setInterval(tick, 1000);
  }

  // ── DELETE FLASH SALE (from show page) ───────────────────────
  var deleteSaleBtn = document.getElementById('deleteSaleBtn');
  if (deleteSaleBtn) {
    var deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteSaleModal'));

    deleteSaleBtn.addEventListener('click', function() {
      deleteConfirmModal.show();
    });

    var confirmSaleDeleteBtn = document.getElementById('confirmSaleDeleteBtn');
    if (confirmSaleDeleteBtn) {
      confirmSaleDeleteBtn.addEventListener('click', function() {
        var url  = deleteSaleBtn.getAttribute('data-url');
        confirmSaleDeleteBtn.disabled = true;
        confirmSaleDeleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>' + (BN ? 'মুছছে...' : 'Deleting…');

        var formData = new FormData();
        formData.append('_method', 'DELETE');
        formData.append('_token',  CSRF);

        fetch(url, {
          method: 'POST',
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
          deleteConfirmModal.hide();
          if (data.success === false) {
            showToast(data.message || (BN ? 'মুছতে ব্যর্থ হয়েছে।' : 'Delete failed.'), 'error');
            confirmSaleDeleteBtn.disabled = false;
            confirmSaleDeleteBtn.innerHTML = '<i class="fas fa-trash me-1"></i>' + (BN ? 'মুছুন' : 'Delete');
            return;
          }
          showToast(data.message || (BN ? 'ফ্ল্যাশ সেল মুছে ফেলা হয়েছে।' : 'Flash Sale deleted.'), 'success');
          setTimeout(function() {
            window.location.href = '{{ route("admin.flash-sales.index") }}';
          }, 800);
        })
        .catch(function(err) {
          console.error('Delete error:', err);
          deleteConfirmModal.hide();
          confirmSaleDeleteBtn.disabled = false;
          confirmSaleDeleteBtn.innerHTML = '<i class="fas fa-trash me-1"></i>' + (BN ? 'মুছুন' : 'Delete');
          showToast(BN ? 'মুছতে ব্যর্থ হয়েছে। আবার চেষ্টা করুন।' : 'Delete failed. Please try again.', 'error');
        });
      });
    }
  }

})();
</script>

{{-- ════ DELETE FLASH SALE CONFIRM MODAL ════ --}}
<div class="modal fade" id="deleteSaleModal" tabindex="-1" role="dialog">
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
          {{ $bn ? '"' : 'Delete "' }}<strong>{{ $bn ? $flashSale->title_bn : $flashSale->title_en }}</strong>{{ $bn ? '" মুছে ফেলবেন?' : '"?' }}
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
        <button type="button" id="confirmSaleDeleteBtn" class="btn btn-sm btn-danger">
          <i class="fas fa-trash me-1"></i>{{ $bn ? 'মুছুন' : 'Delete' }}
        </button>
      </div>
    </div>
  </div>
</div>

@endpush

@endsection