{{-- PATH: resources/views/admin/coupons/index.blade.php --}}
@extends('layouts.admin')
@php $bn = app()->getLocale() === 'bn'; @endphp
@section('title', $bn ? 'কুপন ম্যানেজমেন্ট' : 'Coupon Management')
@section('page-title', $bn ? 'কুপন ম্যানেজমেন্ট' : 'Coupon Management')

@push('styles')
<style>
.coupon-code {
    font-family: monospace; font-size: 1.05rem; letter-spacing: 3px;
    font-weight: 800; color: var(--primary);
    background: rgba(37,99,235,.1);
    padding: 3px 12px; border-radius: 6px; display: inline-block;
}
.adm-dark .coupon-code { background: rgba(37,99,235,.2); }

.badge-type { padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.badge-pct  { background: #fff3e0; color: #e65100; }
.badge-fix  { background: #e3f2fd; color: #0d47a1; }
.adm-dark .badge-pct { background: rgba(230,81,0,.2);   color: #fb923c; }
.adm-dark .badge-fix { background: rgba(13,71,161,.25); color: #60a5fa; }

.badge-preset {
    padding: 2px 9px; border-radius: 20px; font-size: 10px; font-weight: 700;
}
.badge-preset-single   { background:#fce4ec; color:#c62828; }
.badge-preset-multi    { background:#e8f5e9; color:#2e7d32; }
.badge-preset-firstn   { background:#e3f2fd; color:#1565c0; }
.badge-preset-advanced { background:#f3e5f5; color:#6a1b9a; }
.adm-dark .badge-preset-single   { background:rgba(198,40,40,.25);  color:#fca5a5; }
.adm-dark .badge-preset-multi    { background:rgba(46,125,50,.25);  color:#86efac; }
.adm-dark .badge-preset-firstn   { background:rgba(21,101,192,.25); color:#93c5fd; }
.adm-dark .badge-preset-advanced { background:rgba(106,27,154,.25); color:#d8b4fe; }

/* ── Preset selector cards ─────────────────────────────────────── */
.preset-card {
    border: 2px solid var(--border); border-radius: 10px;
    padding: 10px 12px; cursor: pointer; transition: all .2s;
    background: var(--surface); color: var(--text);
    display: flex; align-items: flex-start; gap: 10px;
}
.preset-card:hover { border-color: var(--primary); }
.preset-card.active {
    border-color: var(--primary) !important;
    background: rgba(37,99,235,.06) !important;
}
.adm-dark .preset-card.active { background: rgba(37,99,235,.15) !important; }
.preset-card input[type=radio] { margin-top: 3px; flex-shrink: 0; }

/* ── Tabs ──────────────────────────────────────────────────────── */
.coupon-scroll-wrap { max-height: 480px; overflow-y: auto; }
.coupon-scroll-wrap::-webkit-scrollbar { width: 5px; }
.coupon-scroll-wrap::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
.coupon-tabs { display: flex; gap: 8px; margin-bottom: 0; border-bottom: none; }
.coupon-tab-btn {
    padding: 8px 20px; border-radius: 8px 8px 0 0;
    border: 2px solid var(--border); border-bottom: none;
    background: var(--hover); cursor: pointer;
    font-weight: 700; font-size: 13px; color: var(--muted); transition: all .2s;
}
.coupon-tab-btn.active {
    background: var(--surface); color: var(--primary);
    border-color: var(--primary); border-bottom: 2px solid var(--surface);
    margin-bottom: -2px;
}
.coupon-tab-pane { display: none; }
.coupon-tab-pane.active { display: block; }

.adm-dark .form-control, .adm-dark .form-select {
    background: var(--white) !important; border-color: var(--border) !important; color: var(--text) !important;
}
.adm-dark .form-control::placeholder { color: var(--muted) !important; }
.adm-dark .form-control:focus, .adm-dark .form-select:focus {
    background: var(--white) !important; border-color: var(--primary) !important;
    box-shadow: 0 0 0 3px rgba(37,99,235,.2) !important;
}

.history-row { opacity: .85; }
.adm-dark .table { color: var(--text); }
.adm-dark .table thead th { background: var(--white); color: var(--text-2); border-color: var(--border); }
.adm-dark .table tbody td { border-color: var(--border-2); }
.adm-dark .table-hover tbody tr:hover td { background: var(--hover); }
</style>
@endpush

@section('content')
<div class="row g-3 mb-4">

  {{-- ── LEFT: Create Coupon Form ────────────────────────────────── --}}
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header">
        <h6 class="card-title mb-0">
          <i class="fas fa-plus-circle me-2" style="color:var(--primary);"></i>
          {{ $bn ? 'নতুন কুপন তৈরি করুন' : 'Create New Coupon' }}
        </h6>
      </div>
      <div class="card-body">
        <form action="{{ route('admin.coupons.store') }}" method="POST">
          @csrf

          {{-- Code --}}
          <div class="mb-3">
            <label class="form-label fw-600 small">
              {{ $bn ? 'কুপন কোড' : 'Coupon Code' }}
              <span class="text-muted">({{ $bn ? 'ঐচ্ছিক — ফাঁকা রাখলে স্বয়ংক্রিয় তৈরি' : 'optional — auto-generated if blank' }})</span>
            </label>
            <input type="text" name="code"
              class="form-control @error('code') is-invalid @enderror"
              placeholder="{{ $bn ? 'যেমন: SAVE10' : 'e.g. SAVE10' }}"
              maxlength="10" style="font-family:monospace;letter-spacing:3px;text-transform:uppercase;font-weight:700;"
              value="{{ old('code') }}">
            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          {{-- Discount type --}}
          <div class="mb-3">
            <label class="form-label fw-600 small">{{ $bn ? 'ছাড়ের ধরন' : 'Discount Type' }}</label>
            <div class="d-flex gap-3">
              <label class="d-flex align-items-center gap-2 cursor-pointer">
                <input type="radio" name="type" value="percentage" id="typePct" checked onchange="toggleMaxDiscount()">
                <span>{{ $bn ? 'শতাংশ (%)' : 'Percentage (%)' }}</span>
              </label>
              <label class="d-flex align-items-center gap-2 cursor-pointer">
                <input type="radio" name="type" value="fixed" id="typeFix" onchange="toggleMaxDiscount()">
                <span>{{ $bn ? 'নির্দিষ্ট পরিমাণ (৳)' : 'Fixed Amount (৳)' }}</span>
              </label>
            </div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label fw-600 small">{{ $bn ? 'ছাড়ের মান' : 'Discount Value' }}</label>
              <input type="number" name="discount_value"
                class="form-control @error('discount_value') is-invalid @enderror"
                placeholder="{{ $bn ? 'যেমন: ১০' : 'e.g. 10' }}"
                min="0.01" step="0.01" required value="{{ old('discount_value') }}">
              @error('discount_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-6" id="maxDiscountWrap">
              <label class="form-label fw-600 small">
                {{ $bn ? 'সর্বোচ্চ ছাড় সীমা (৳)' : 'Max Discount Cap (৳)' }}
                <span class="text-muted">({{ $bn ? 'ঐচ্ছিক' : 'optional' }})</span>
              </label>
              <input type="number" name="max_discount_amount" class="form-control"
                placeholder="{{ $bn ? 'যেমন: ১০০' : 'e.g. 100' }}"
                value="{{ old('max_discount_amount') }}">
            </div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label fw-600 small">{{ $bn ? 'সর্বনিম্ন অর্ডার (৳)' : 'Min Order (৳)' }}</label>
              <input type="number" name="min_order_amount" class="form-control"
                placeholder="0" min="0" value="{{ old('min_order_amount', 0) }}">
            </div>
            <div class="col-6">
              <label class="form-label fw-600 small">{{ $bn ? 'মেয়াদ শেষের তারিখ' : 'Expiry Date' }}</label>
              <input type="datetime-local" name="expires_at" class="form-control"
                value="{{ old('expires_at') }}">
            </div>
          </div>

          {{-- ── PRESET SELECTOR ─────────────────────────────────── --}}
          <div class="mb-3">
            <label class="form-label fw-600 small d-flex align-items-center gap-1">
              {{ $bn ? 'কুপনটি কীভাবে ব্যবহার করা যাবে?' : 'How can this coupon be used?' }}
              <span class="text-muted" style="font-weight:400;">({{ $bn ? 'বেছে নিন' : 'pick one' }})</span>
            </label>
            @error('preset') <div class="text-danger small mb-1">{{ $message }}</div> @enderror

            <div class="d-flex flex-column gap-2">

              {{-- Preset 1: একজন একবার --}}
              <label class="preset-card {{ old('preset','unlimited') === 'one_per_user' ? 'active' : '' }}">
                <input type="radio" name="preset" value="one_per_user" id="pOnePerUser"
                  {{ old('preset','unlimited') === 'one_per_user' ? 'checked' : '' }}
                  onchange="onPresetChange('one_per_user')">
                <div>
                  <div style="font-weight:700;font-size:13px;">🎯 {{ $bn ? 'একজন একবার' : 'One per customer' }}</div>
                  <div style="font-size:11px;color:var(--muted);">
                    {{ $bn ? 'প্রতিটি customer মাত্র একবার use করতে পারবে।' : 'Each customer can use this coupon only once.' }}
                  </div>
                </div>
              </label>

              {{-- Preset 2: সবাই যতবার খুশি --}}
              <label class="preset-card {{ old('preset','unlimited') === 'unlimited' ? 'active' : '' }}">
                <input type="radio" name="preset" value="unlimited" id="pUnlimited"
                  {{ old('preset','unlimited') === 'unlimited' ? 'checked' : '' }}
                  onchange="onPresetChange('unlimited')">
                <div>
                  <div style="font-weight:700;font-size:13px;">📢 {{ $bn ? 'সবাই যতবার খুশি' : 'Unlimited use' }}</div>
                  <div style="font-size:11px;color:var(--muted);">
                    {{ $bn ? 'যেকোনো customer, যেকোনো সময়, যতবার খুশি।' : 'Anyone can use this coupon as many times as they want.' }}
                  </div>
                </div>
              </label>

              {{-- Preset 3: প্রথম N জন --}}
              <label class="preset-card {{ old('preset') === 'first_n' ? 'active' : '' }}">
                <input type="radio" name="preset" value="first_n" id="pFirstN"
                  {{ old('preset') === 'first_n' ? 'checked' : '' }}
                  onchange="onPresetChange('first_n')">
                <div class="w-100">
                  <div style="font-weight:700;font-size:13px;">🏆 {{ $bn ? 'প্রথম N জন পাবে' : 'First N uses only' }}</div>
                  <div style="font-size:11px;color:var(--muted);">
                    {{ $bn ? 'নির্দিষ্ট সংখ্যকবার use হলে কুপন বন্ধ হয়ে যাবে।' : 'Coupon expires after N total uses.' }}
                  </div>
                  <div id="firstNInput" style="display:{{ old('preset') === 'first_n' ? '' : 'none' }};margin-top:8px;">
                    <input type="number" name="global_limit" class="form-control form-control-sm"
                      placeholder="{{ $bn ? 'কতজন? যেমন: ১০০' : 'How many? e.g. 100' }}"
                      min="1" value="{{ old('global_limit') }}"
                      style="max-width:180px;">
                    @error('global_limit') <div class="text-danger" style="font-size:11px;">{{ $message }}</div> @enderror
                  </div>
                </div>
              </label>

              {{-- Preset 4: Advanced --}}
              <label class="preset-card {{ old('preset') === 'advanced' ? 'active' : '' }}">
                <input type="radio" name="preset" value="advanced" id="pAdvanced"
                  {{ old('preset') === 'advanced' ? 'checked' : '' }}
                  onchange="onPresetChange('advanced')">
                <div class="w-100">
                  <div style="font-weight:700;font-size:13px;">⚙️ {{ $bn ? 'কাস্টম সেটিং' : 'Advanced / Custom' }}</div>
                  <div style="font-size:11px;color:var(--muted);">
                    {{ $bn ? 'জনপ্রতি সীমা এবং মোট সীমা আলাদা আলাদা ঠিক করুন।' : 'Set per-user and global limits independently.' }}
                  </div>
                  <div id="advancedInput" style="display:{{ old('preset') === 'advanced' ? '' : 'none' }};margin-top:8px;">
                    <div class="row g-2">
                      <div class="col-6">
                        <label style="font-size:11px;font-weight:600;">{{ $bn ? 'জনপ্রতি সীমা' : 'Per-user limit' }}</label>
                        <input type="number" name="per_user_limit" class="form-control form-control-sm"
                          placeholder="{{ $bn ? 'যেমন: ৩' : 'e.g. 3' }}"
                          min="1" value="{{ old('per_user_limit') }}">
                        <div style="font-size:10px;color:var(--muted);">{{ $bn ? 'ফাঁকা = সীমাহীন' : 'blank = unlimited' }}</div>
                      </div>
                      <div class="col-6">
                        <label style="font-size:11px;font-weight:600;">{{ $bn ? 'মোট সীমা' : 'Global limit' }}</label>
                        <input type="number" name="adv_global_limit" class="form-control form-control-sm"
                          placeholder="{{ $bn ? 'যেমন: ৫০' : 'e.g. 50' }}"
                          min="1" value="{{ old('adv_global_limit') }}">
                        <div style="font-size:10px;color:var(--muted);">{{ $bn ? 'ফাঁকা = সীমাহীন' : 'blank = unlimited' }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </label>

            </div>
          </div>

          {{-- Description --}}
          <div class="mb-3">
            <label class="form-label fw-600 small">
              {{ $bn ? 'বিবরণ / নোট' : 'Description / Note' }}
              <span class="text-muted">({{ $bn ? 'ঐচ্ছিক' : 'optional' }})</span>
            </label>
            <input type="text" name="description" class="form-control"
              placeholder="{{ $bn ? 'যেমন: ঈদ স্পেশাল অফার' : 'e.g. Eid special offer' }}"
              maxlength="255" value="{{ old('description') }}">
          </div>

          <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-magic me-2"></i>
            {{ $bn ? 'কুপন তৈরি করুন' : 'Create Coupon' }}
          </button>
        </form>
      </div>
    </div>
  </div>

  {{-- ── RIGHT: Active + History Tabs ────────────────────────────── --}}
  <div class="col-lg-7">

    <div class="card mb-3">
      <div class="card-body d-flex align-items-center gap-3 flex-wrap">
        <a href="{{ route('admin.coupons.send-form') }}" class="btn btn-success">
          <i class="fas fa-paper-plane me-2"></i>
          {{ $bn ? 'গ্রাহকদের কুপন পাঠান' : 'Send Coupons to Customers' }}
        </a>
        <span style="color:var(--muted);font-size:13px;">
          {{ $bn
            ? 'সক্রিয়: ' . $activeCoupons->count() . ' | ইতিহাস: ' . $historyCoupons->count()
            : 'Active: ' . $activeCoupons->count() . ' | History: ' . $historyCoupons->count() }}
        </span>
      </div>
    </div>

    <div class="card" style="border-radius:0 8px 8px 8px;">
      <div class="card-header" style="padding-bottom:0;border-bottom:none;background:transparent;">
        <div class="coupon-tabs">
          <button type="button" class="coupon-tab-btn active" onclick="switchTab('active', this)">
            <i class="fas fa-check-circle me-1" style="color:var(--success,#16a34a);"></i>
            {{ $bn ? 'সক্রিয় কুপন' : 'Active Coupons' }}
            <span class="badge bg-success ms-1" style="font-size:10px;">{{ $activeCoupons->count() }}</span>
          </button>
          <button type="button" class="coupon-tab-btn" onclick="switchTab('history', this)">
            <i class="fas fa-history me-1" style="color:var(--muted);"></i>
            {{ $bn ? 'ইতিহাস' : 'History' }}
            <span class="badge bg-secondary ms-1" style="font-size:10px;">{{ $historyCoupons->count() }}</span>
          </button>
        </div>
      </div>

      <div style="border-top:2px solid var(--primary);padding:0;">

        {{-- Active Tab --}}
        <div id="tab-active" class="coupon-tab-pane active">
          <div class="coupon-scroll-wrap">
            <table class="table table-hover mb-0">
              <thead style="position:sticky;top:0;z-index:1;background:var(--surface);">
                <tr>
                  <th>{{ $bn ? 'কোড' : 'Code' }}</th>
                  <th>{{ $bn ? 'ছাড়' : 'Discount' }}</th>
                  <th>{{ $bn ? 'ধরন' : 'Limits' }}</th>
                  <th>{{ $bn ? 'ব্যবহার' : 'Used' }}</th>
                  <th>{{ $bn ? 'মেয়াদ' : 'Expiry' }}</th>
                  <th>{{ $bn ? 'অ্যাকশন' : 'Action' }}</th>
                </tr>
              </thead>
              <tbody>
                @forelse($activeCoupons as $coupon)
                <tr>
                  <td>
                    <div class="coupon-code">{{ $coupon->code }}</div>
                    @if($coupon->description)
                      <div style="font-size:11px;color:var(--muted);margin-top:3px;">{{ $coupon->description }}</div>
                    @endif
                    @if($coupon->allowed_users_count > 0)
                      <div style="font-size:10px;color:#6366f1;margin-top:2px;">
                        <i class="fas fa-lock" style="font-size:9px;"></i>
                        {{ $bn ? $coupon->allowed_users_count . ' জন' : $coupon->allowed_users_count . ' user(s)' }}
                      </div>
                    @endif
                  </td>
                  <td>
                    <span class="badge-type {{ $coupon->type === 'percentage' ? 'badge-pct' : 'badge-fix' }}">
                      {{ $coupon->type === 'percentage'
                        ? $coupon->discount_value . '%'
                        : '৳' . number_format($coupon->discount_value, 0) }}
                    </span>
                    @if($coupon->min_order_amount > 0)
                      <div style="font-size:11px;color:var(--muted);">{{ $bn ? 'মিন:' : 'Min:' }} ৳{{ number_format($coupon->min_order_amount, 0) }}</div>
                    @endif
                  </td>
                  <td>
                    {{-- Preset badge --}}
                    @php
                      $pu = $coupon->per_user_limit;
                      $gl = $coupon->global_limit;
                    @endphp
                    @if($pu === 1 && $gl === null)
                      <span class="badge-preset badge-preset-single">🎯 {{ $bn ? 'একজন একবার' : '1/user' }}</span>
                    @elseif($pu === null && $gl === null)
                      <span class="badge-preset badge-preset-multi">📢 {{ $bn ? 'সীমাহীন' : 'Unlimited' }}</span>
                    @elseif($pu === null && $gl !== null)
                      <span class="badge-preset badge-preset-firstn">🏆 {{ $bn ? 'প্রথম ' . $gl : 'First ' . $gl }}</span>
                    @else
                      <span class="badge-preset badge-preset-advanced">⚙️
                        @if($pu) {{ $pu }}/{{ $bn ? 'জন' : 'user' }} @endif
                        @if($gl) · {{ $gl }} {{ $bn ? 'মোট' : 'total' }} @endif
                      </span>
                    @endif
                  </td>
                  <td>
                    <span style="font-weight:600;">{{ $coupon->used_count }}</span>
                    @if($coupon->global_limit)
                      <span style="color:var(--muted);font-size:12px;"> / {{ $coupon->global_limit }}</span>
                    @else
                      <span style="color:var(--muted);font-size:12px;"> / ∞</span>
                    @endif
                  </td>
                  <td style="font-size:12px;">
                    @if($coupon->expires_at)
                      <span>{{ $coupon->expires_at->format('d M Y') }}</span>
                    @else
                      <span style="color:var(--muted);">{{ $bn ? 'সীমাহীন' : 'No expiry' }}</span>
                    @endif
                  </td>
                  <td>
                    <div class="d-flex gap-1">
                      <form action="{{ route('admin.coupons.toggle', $coupon) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-warning" title="{{ $bn ? 'নিষ্ক্রিয় করুন' : 'Deactivate' }}">
                          <i class="fas fa-pause"></i>
                        </button>
                      </form>
                      <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST"
                        onsubmit="return confirm('{{ $bn ? 'এই কুপনটি মুছবেন?' : 'Delete this coupon?' }}')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" title="{{ $bn ? 'মুছুন' : 'Delete' }}">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="6" class="text-center py-5" style="color:var(--muted);">
                    <i class="fas fa-ticket-alt fa-2x mb-2 d-block"></i>
                    {{ $bn ? 'কোনো সক্রিয় কুপন নেই।' : 'No active coupons.' }}
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        {{-- History Tab --}}
        <div id="tab-history" class="coupon-tab-pane">

          @if($historyCoupons->count() > 0)
          <div style="padding:10px 16px;background:rgba(239,68,68,.05);border-bottom:1px solid rgba(239,68,68,.15);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <span style="font-size:13px;color:#991b1b;">
              <i class="fas fa-history me-1"></i>
              {{ $bn ? 'মোট ' . $historyCoupons->count() . 'টি পুরনো কুপন' : $historyCoupons->count() . ' expired coupon(s)' }}
            </span>
            <form action="{{ route('admin.coupons.bulk-delete-expired') }}" method="POST"
              onsubmit="return confirm('{{ $bn ? 'সমস্ত পুরনো কুপন মুছে ফেলবেন?' : 'Delete all expired coupons?' }}')">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn-sm btn-danger">
                <i class="fas fa-trash me-1"></i>
                {{ $bn ? 'সব মুছুন' : 'Delete All' }}
              </button>
            </form>
          </div>
          @endif

          <div class="coupon-scroll-wrap">
            <table class="table table-hover mb-0">
              <thead style="position:sticky;top:0;z-index:1;background:var(--surface);">
                <tr>
                  <th>{{ $bn ? 'কোড' : 'Code' }}</th>
                  <th>{{ $bn ? 'ছাড়' : 'Discount' }}</th>
                  <th>{{ $bn ? 'ধরন' : 'Limits' }}</th>
                  <th>{{ $bn ? 'মোট ব্যবহার' : 'Total Used' }}</th>
                  <th>{{ $bn ? 'মেয়াদ শেষ' : 'Expired On' }}</th>
                  <th>{{ $bn ? 'অ্যাকশন' : 'Action' }}</th>
                </tr>
              </thead>
              <tbody>
                @forelse($historyCoupons as $coupon)
                <tr class="history-row">
                  <td>
                    <div class="coupon-code" style="opacity:.65;">{{ $coupon->code }}</div>
                    @if($coupon->description)
                      <div style="font-size:11px;color:var(--muted);margin-top:3px;">{{ $coupon->description }}</div>
                    @endif
                  </td>
                  <td>
                    <span class="badge-type {{ $coupon->type === 'percentage' ? 'badge-pct' : 'badge-fix' }}">
                      {{ $coupon->type === 'percentage'
                        ? $coupon->discount_value . '%'
                        : '৳' . number_format($coupon->discount_value, 0) }}
                    </span>
                  </td>
                  <td style="font-size:12px;color:var(--muted);">
                    {{ $coupon->getPresetLabel($bn) }}
                  </td>
                  <td>
                    <span style="font-weight:600;color:var(--muted-2);">{{ $coupon->usages_count }}</span>
                    <span style="font-size:11px;color:var(--muted);"> {{ $bn ? 'বার' : 'time(s)' }}</span>
                  </td>
                  <td style="font-size:12px;color:#dc2626;">
                    @php $expiredOn = $coupon->expired_at ?? $coupon->expires_at; @endphp
                    @if($expiredOn)
                      <i class="fas fa-clock me-1"></i>{{ $expiredOn->format('d M Y, H:i') }}
                    @else
                      <span style="color:var(--muted);">—</span>
                    @endif
                  </td>
                  <td>
                    <div class="d-flex gap-1">
                      <form action="{{ route('admin.coupons.toggle', $coupon) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-success" title="{{ $bn ? 'পুনরায় সক্রিয় করুন' : 'Reactivate' }}">
                          <i class="fas fa-redo"></i>
                        </button>
                      </form>
                      <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST"
                        onsubmit="return confirm('{{ $bn ? 'এই কুপনটি মুছবেন?' : 'Delete this coupon?' }}')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" title="{{ $bn ? 'মুছুন' : 'Delete' }}">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="6" class="text-center py-5" style="color:var(--muted);">
                    <i class="fas fa-check-circle fa-2x mb-2 d-block" style="color:var(--success,#16a34a);"></i>
                    {{ $bn ? 'কোনো পুরনো কুপন নেই।' : 'No expired coupons in history.' }}
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
// ── Tab switching ──────────────────────────────────────────────────
function switchTab(name, btn) {
    document.querySelectorAll('.coupon-tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.coupon-tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
}

// ── Discount type toggle ───────────────────────────────────────────
function toggleMaxDiscount() {
    const isPct = document.getElementById('typePct').checked;
    document.getElementById('maxDiscountWrap').style.display = isPct ? '' : 'none';
}
toggleMaxDiscount();

// ── Preset change ──────────────────────────────────────────────────
function onPresetChange(preset) {
    // Sub-input visibility
    document.getElementById('firstNInput').style.display    = preset === 'first_n'  ? '' : 'none';
    document.getElementById('advancedInput').style.display  = preset === 'advanced' ? '' : 'none';

    // Active card highlight
    document.querySelectorAll('.preset-card').forEach(card => {
        const radio = card.querySelector('input[type=radio]');
        card.classList.toggle('active', radio.checked);
    });
}
// Init on page load (restore old() state)
const checkedPreset = document.querySelector('input[name="preset"]:checked');
if (checkedPreset) onPresetChange(checkedPreset.value);

// ── Auto-uppercase coupon code ─────────────────────────────────────
const codeInput = document.querySelector('input[name="code"]');
if (codeInput) {
    codeInput.addEventListener('input', function() {
        const pos = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);
    });
}

// ── Open history tab if URL has #history ──────────────────────────
if (window.location.hash === '#history') {
    document.querySelector('[onclick*="history"]')?.click();
}
</script>
@endpush
