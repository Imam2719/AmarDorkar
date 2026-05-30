@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'ছাড় ব্যবস্থাপনা' : 'Discount Management')
@section('page-title', app()->getLocale()==='bn' ? 'ছাড় ব্যবস্থাপনা' : 'Discount Management')

@push('styles')
<style>
/* ════════════════════════════════════════════
   DISCOUNT LIST — Infinite Scroll Styles
   Uses admin layout CSS variables correctly:
     --white   → card / surface bg  (light:#fff  dark:#1e293b)
     --bg      → page / input bg    (light:#f8fafc dark:#0f172a)
     --surface → same as --white
     --border  → border color
     --text    → body text
     --muted   → secondary text
     --hover   → row hover bg
     --primary → accent color
════════════════════════════════════════════ */

/* ── Top bar ── */
.dc-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 18px;
}

/* ── Search wrap ── */
.dc-search-wrap {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 14px 18px;
  margin-bottom: 18px;
  animation: dc-fadeInUp .35s ease both;
}
.dc-search-form {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  align-items: flex-end;
}
.dc-search-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex: 1;
  min-width: 200px;
}
.dc-label {
  font-size: 11px;
  font-weight: 600;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: .05em;
}

/* ── Custom input — respects both themes via CSS vars ── */
.dc-input {
  height: 38px;
  padding: 0 12px;
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 13px;
  background: var(--bg);
  color: var(--text);
  transition: border-color .2s, background .2s;
  width: 100%;
}
.dc-input:focus {
  outline: none;
  border-color: var(--primary);
  background: var(--white);
  box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.dc-input::placeholder { color: var(--muted-2); }

/* ── Buttons ── */
.dc-btn {
  height: 38px;
  padding: 0 16px;
  border-radius: 8px;
  border: none;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: opacity .2s, transform .15s;
  white-space: nowrap;
  text-decoration: none;
}
.dc-btn:hover { opacity: .88; transform: translateY(-1px); }
.dc-btn-primary   { background: var(--primary); color: #fff; }
.dc-btn-secondary { background: var(--border);  color: var(--text); }

/* ── Main card ── */
.dc-main-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 14px;
  overflow: hidden;
  animation: dc-fadeInUp .4s ease both;
}
.dc-card-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
  background: var(--white);
  flex-wrap: wrap;
  gap: 8px;
}
.dc-card-title {
  font-size: 15px;
  font-weight: 700;
  color: var(--text);
  display: flex;
  align-items: center;
  gap: 8px;
}
.dc-total-chip {
  background: rgba(100,116,139,.15);
  color: var(--muted);
  padding: 2px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
}

/* ── Table ── */
.dc-table-wrap { overflow-x: auto; }
.dc-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.dc-table thead tr {
  background: var(--bg);
}
.dc-table th {
  padding: 11px 16px;
  text-align: left;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .05em;
  color: var(--muted);
  border-bottom: 1px solid var(--border);
  white-space: nowrap;
}
.dc-table td {
  padding: 12px 16px;
  border-bottom: 1px solid var(--border);
  color: var(--text);
  vertical-align: middle;
}
.dc-table tbody tr { background: var(--white); }
.dc-table tbody tr:hover { background: var(--hover); }
.dc-table tbody tr:last-child td { border-bottom: none; }

/* ── Product cell ── */
.dc-product-cell { display: flex; align-items: center; gap: 10px; }
.dc-thumb {
  width: 42px; height: 42px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid var(--border);
  flex-shrink: 0;
}
.dc-thumb-placeholder {
  width: 42px; height: 42px;
  border-radius: 8px;
  background: var(--border);
  display: flex; align-items: center; justify-content: center;
  color: var(--muted);
  font-size: 16px;
  flex-shrink: 0;
}
.dc-product-name  { font-weight: 600; color: var(--text);  font-size: 13px; }
.dc-product-type  { font-size: 11px;  color: var(--muted); margin-top: 2px; }

/* ── Badges ── */
.dc-badge {
  display: inline-flex; align-items: center;
  padding: 3px 9px;
  border-radius: 20px;
  font-size: 11px; font-weight: 700;
  letter-spacing: .03em;
}
.dc-badge-active   { background: rgba(34,197,94,.15);  color: #16a34a; }
.dc-badge-inactive { background: rgba(239,68,68,.12);  color: #ef4444; }
.dc-badge-pct      { background: rgba(245,158,11,.15); color: #d97706; }
.dc-price-original { font-weight: 700; color: var(--text); }
.dc-price-discount { font-weight: 700; color: #16a34a; }

/* ── Action buttons ── */
.dc-action-btns { display: flex; gap: 5px; align-items: center; }
.dc-act-btn {
  width: 30px; height: 30px;
  border-radius: 7px;
  border: 1px solid var(--border);
  background: transparent;
  cursor: pointer;
  display: inline-flex; align-items: center; justify-content: center;
  font-size: 12px;
  transition: background .15s, color .15s, border-color .15s;
}
.dc-act-edit        { color: #d97706; border-color: rgba(217,119,6,.35); }
.dc-act-edit:hover  { background: rgba(245,158,11,.12); }
.dc-act-toggle-on        { color: var(--muted); border-color: var(--border); }
.dc-act-toggle-on:hover  { background: var(--hover); }
.dc-act-toggle-off       { color: #16a34a; border-color: rgba(34,197,94,.35); }
.dc-act-toggle-off:hover { background: rgba(34,197,94,.12); }
.dc-act-del        { color: #ef4444; border-color: rgba(239,68,68,.35); }
.dc-act-del:hover  { background: rgba(239,68,68,.12); }

/* ── Scroll loader ── */
#dc-scroll-loader {
  display: none;
  align-items: center; justify-content: center;
  gap: 10px;
  padding: 20px;
  color: var(--muted);
  font-size: 13px;
  background: var(--white);
}
.dc-spinner {
  width: 18px; height: 18px;
  border: 2px solid var(--border);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: dc-spin .7s linear infinite;
}
@keyframes dc-spin { to { transform: rotate(360deg); } }

/* ── End marker ── */
#dc-end-marker {
  display: none;
  text-align: center;
  padding: 16px;
  font-size: 12px;
  color: var(--muted);
  border-top: 1px solid var(--border);
  background: var(--white);
}

/* ── Empty state ── */
.dc-empty {
  text-align: center; padding: 48px 20px;
  color: var(--muted); font-size: 14px;
  display: flex; flex-direction: column; align-items: center; gap: 10px;
}
.dc-empty i { font-size: 36px; opacity: .4; }

/* ════════════════════════════════════════════
   DARK THEME OVERRIDES (.adm-dark)
   Forces every custom element to use dark
   surface colours — mirrors admin layout
════════════════════════════════════════════ */
.adm-dark .dc-search-wrap,
.adm-dark .dc-main-card,
.adm-dark .dc-card-head,
.adm-dark #dc-scroll-loader,
.adm-dark #dc-end-marker {
  background: var(--white) !important;   /* dark: #1e293b */
  border-color: var(--border) !important;
  color: var(--text) !important;
}

.adm-dark .dc-table thead tr { background: var(--bg) !important; }   /* dark: #0f172a */
.adm-dark .dc-table th        { color: var(--muted) !important; background: var(--bg) !important; }
.adm-dark .dc-table tbody tr  { background: var(--white) !important; }
.adm-dark .dc-table tbody tr:hover { background: var(--hover) !important; }
.adm-dark .dc-table td        { color: var(--text) !important; border-color: var(--border) !important; }

.adm-dark .dc-input {
  background: var(--bg) !important;      /* dark: #0f172a */
  border-color: var(--border) !important;
  color: var(--text) !important;
}
.adm-dark .dc-input:focus {
  background: var(--surface) !important; /* dark: #1e293b */
  border-color: var(--primary) !important;
}
.adm-dark .dc-input::placeholder { color: var(--muted-2) !important; }

.adm-dark .dc-btn-secondary {
  background: var(--border) !important;
  color: var(--text) !important;
}
.adm-dark .dc-product-name { color: var(--text) !important; }
.adm-dark .dc-product-type { color: var(--muted) !important; }
.adm-dark .dc-price-original { color: var(--text) !important; }

.adm-dark .dc-act-btn {
  background: transparent !important;
  border-color: var(--border) !important;
}
.adm-dark .dc-act-edit  { color: #f59e0b !important; border-color: rgba(245,158,11,.4) !important; }
.adm-dark .dc-act-edit:hover  { background: rgba(245,158,11,.15) !important; }
.adm-dark .dc-act-toggle-on   { color: var(--muted) !important; }
.adm-dark .dc-act-toggle-on:hover  { background: var(--hover) !important; }
.adm-dark .dc-act-toggle-off  { color: #4ade80 !important; border-color: rgba(74,222,128,.4) !important; }
.adm-dark .dc-act-toggle-off:hover { background: rgba(74,222,128,.12) !important; }
.adm-dark .dc-act-del   { color: #f87171 !important; border-color: rgba(248,113,113,.4) !important; }
.adm-dark .dc-act-del:hover   { background: rgba(248,113,113,.12) !important; }

.adm-dark .dc-thumb-placeholder { background: var(--border) !important; }
.adm-dark .dc-total-chip { background: rgba(100,116,139,.2) !important; color: var(--muted) !important; }
.adm-dark .dc-card-title  { color: var(--text) !important; }
.adm-dark .dc-label       { color: var(--muted) !important; }
.adm-dark .dc-spinner     { border-color: var(--border) !important; border-top-color: var(--primary) !important; }

/* ── Responsive ── */
@media (max-width: 768px) {
  .dc-table th:nth-child(6),
  .dc-table td:nth-child(6) { display: none; }
  .dc-topbar { flex-direction: column; align-items: stretch; }
}

@keyframes dc-fadeInUp {
  from { opacity: 0; transform: translateY(14px); }
  to   { opacity: 1; transform: translateY(0); }
}
</style>
@endpush

@section('content')

{{-- ── Alerts ── --}}
@if($success)
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
  <i class="fas fa-check-circle me-2"></i>{{ $success }}
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($error)
<div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
  <i class="fas fa-exclamation-circle me-2"></i>{{ $error }}
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ── Top bar ── --}}
<div class="dc-topbar">
  <div></div>
  <button class="dc-btn dc-btn-primary" data-bs-toggle="modal" data-bs-target="#addDiscountModal">
    <i class="fas fa-plus"></i>
    {{ app()->getLocale()==='bn' ? 'নতুন ছাড় যোগ করুন' : 'Add New Discount' }}
  </button>
</div>

{{-- ── Search bar ── --}}
<div class="dc-search-wrap">
  <form method="GET" action="{{ route('admin.discounts.index') }}" class="dc-search-form">
    <div class="dc-search-group">
      <label class="dc-label">{{ app()->getLocale()==='bn' ? 'পণ্য খুঁজুন' : 'Search Product' }}</label>
      <input
        type="text"
        name="search"
        value="{{ $search }}"
        class="dc-input"
        placeholder="{{ app()->getLocale()==='bn' ? 'পণ্যের নাম লিখুন...' : 'Type product name...' }}"
        autocomplete="off"
      >
    </div>
    <div style="display:flex;gap:8px;align-items:flex-end;">
      <button type="submit" class="dc-btn dc-btn-primary">
        <i class="fas fa-search"></i>
        {{ app()->getLocale()==='bn' ? 'ফিল্টার' : 'Filter' }}
      </button>
      @if($search)
      <a href="{{ route('admin.discounts.index') }}" class="dc-btn dc-btn-secondary">
        <i class="fas fa-times"></i>
        {{ app()->getLocale()==='bn' ? 'রিসেট' : 'Reset' }}
      </a>
      @endif
    </div>
  </form>
</div>

{{-- ── Main card ── --}}
<div class="dc-main-card">
  <div class="dc-card-head">
    <div class="dc-card-title">
      <i class="fas fa-percent" style="color:var(--primary)"></i>
      {{ app()->getLocale()==='bn' ? 'সকল ছাড়' : 'All Discounts' }}
      <span class="dc-total-chip" id="dcTotalBadge">{{ $total }}</span>
    </div>
  </div>

  <div class="dc-table-wrap">
    <table class="dc-table">
      <thead>
        <tr>
          <th>{{ app()->getLocale()==='bn' ? 'পণ্য' : 'Product' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'মূল মূল্য' : 'Original' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'ছাড়ের মূল্য' : 'Discount' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'ছাড়%' : 'Disc. %' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'স্ট্যাটাস' : 'Status' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'শেষ তারিখ' : 'End Date' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'কার্যক্রম' : 'Actions' }}</th>
        </tr>
      </thead>
      <tbody id="dcTableBody">
        @forelse($discounts as $discount)
        <tr id="dc-row-{{ $discount->id }}">
          <td>
            <div class="dc-product-cell">
              @if($discount->product?->image_url)
                <img src="{{ $discount->product->image_url }}" class="dc-thumb" alt="">
              @else
                <div class="dc-thumb-placeholder"><i class="fas fa-box"></i></div>
              @endif
              <div>
                <div class="dc-product-name bengali-text">
                  {{ app()->getLocale()==='bn'
                      ? ($discount->product?->name_bn ?? $discount->product?->name_en ?? '—')
                      : ($discount->product?->name_en ?? '—') }}
                </div>
                <div class="dc-product-type">{{ $discount->product?->product_type }}</div>
              </div>
            </div>
          </td>
          <td><span class="dc-price-original">৳{{ number_format($discount->original_price) }}</span></td>
          <td><span class="dc-price-discount">৳{{ number_format($discount->discount_price) }}</span></td>
          <td><span class="dc-badge dc-badge-pct">{{ number_format($discount->discount_percentage, 1) }}%</span></td>
          <td>
            <span class="dc-badge {{ $discount->is_active ? 'dc-badge-active' : 'dc-badge-inactive' }}">
              {{ $discount->is_active
                  ? (app()->getLocale()==='bn' ? 'সক্রিয়' : 'Active')
                  : (app()->getLocale()==='bn' ? 'নিষ্ক্রিয়' : 'Inactive') }}
            </span>
          </td>
          <td style="font-size:12px;color:var(--muted);">{{ $discount->end_date->format('M d, Y') }}</td>
          <td>
            <div class="dc-action-btns">
              {{-- Edit --}}
              <button
                class="dc-act-btn dc-act-edit edit-discount-btn"
                title="{{ app()->getLocale()==='bn' ? 'সম্পাদনা' : 'Edit' }}"
                data-id="{{ $discount->id }}"
                data-original="{{ $discount->original_price }}"
                data-discount="{{ $discount->discount_price }}"
                data-start="{{ $discount->start_date->format('Y-m-d\TH:i') }}"
                data-end="{{ $discount->end_date->format('Y-m-d\TH:i') }}"
                data-active="{{ $discount->is_active ? 1 : 0 }}">
                <i class="fas fa-edit"></i>
              </button>
              {{-- Toggle --}}
              <form method="POST" action="{{ route('admin.discounts.toggle', $discount) }}" class="d-inline">
                @csrf
                <button type="submit"
                  class="dc-act-btn {{ $discount->is_active ? 'dc-act-toggle-on' : 'dc-act-toggle-off' }}"
                  title="{{ $discount->is_active
                    ? (app()->getLocale()==='bn' ? 'নিষ্ক্রিয় করুন' : 'Deactivate')
                    : (app()->getLocale()==='bn' ? 'সক্রিয় করুন'   : 'Activate') }}">
                  <i class="fas {{ $discount->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                </button>
              </form>
              {{-- Delete --}}
              <button
                class="dc-act-btn dc-act-del delete-discount-btn"
                title="{{ app()->getLocale()==='bn' ? 'মুছুন' : 'Delete' }}"
                data-id="{{ $discount->id }}">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </td>
        </tr>
        @empty
        <tr id="dcEmptyRow">
          <td colspan="7">
            <div class="dc-empty">
              <i class="fas fa-percent"></i>
              {{ app()->getLocale()==='bn' ? 'কোনো ছাড় পাওয়া যায়নি।' : 'No discounts found.' }}
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Scroll loader --}}
  <div id="dc-scroll-loader">
    <span class="dc-spinner"></span>
    {{ app()->getLocale()==='bn' ? 'লোড হচ্ছে...' : 'Loading more...' }}
  </div>

  {{-- End marker --}}
  <div id="dc-end-marker">
    <i class="fas fa-check-circle me-1" style="color:var(--primary)"></i>
    {{ app()->getLocale()==='bn' ? 'সব ছাড় দেখানো হয়েছে' : 'All discounts loaded' }}
  </div>
</div>

{{-- ════════ Add Modal ════════ --}}
<div class="modal fade" id="addDiscountModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="{{ route('admin.discounts.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title bengali-text">
            <i class="fas fa-plus-circle me-2" style="color:var(--primary)"></i>
            {{ app()->getLocale()==='bn' ? 'নতুন ছাড় যোগ করুন' : 'Add Discount' }}
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'পণ্য নির্বাচন *' : 'Select Product *' }}</label>
                <select class="form-select" name="product_id" required>
                  <option value="">{{ app()->getLocale()==='bn' ? 'পণ্য নির্বাচন করুন' : 'Select Product' }}</option>
                  @foreach($products as $p)
                  <option value="{{ $p->id }}">{{ $p->name_en }} ({{ $p->product_type }})</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="mb-3">
                <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'মূল মূল্য *' : 'Original Price *' }}</label>
                <input type="number" class="form-control" name="original_price" step="0.01" required placeholder="0.00">
              </div>
            </div>
            <div class="col-md-3">
              <div class="mb-3">
                <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'ছাড়ের মূল্য *' : 'Discount Price *' }}</label>
                <input type="number" class="form-control" name="discount_price" step="0.01" required placeholder="0.00">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'শুরু তারিখ *' : 'Start Date *' }}</label>
                <input type="datetime-local" class="form-control" name="start_date" value="{{ now()->format('Y-m-d\TH:i') }}" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'শেষ তারিখ *' : 'End Date *' }}</label>
                <input type="datetime-local" class="form-control" name="end_date" value="{{ now()->addDays(30)->format('Y-m-d\TH:i') }}" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel' }}</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>{{ app()->getLocale()==='bn' ? 'যোগ করুন' : 'Add' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ════════ Edit Modal ════════ --}}
<div class="modal fade" id="editDiscountModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" id="editDiscountForm">
        @csrf @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title bengali-text">
            <i class="fas fa-edit me-2" style="color:#d97706"></i>
            {{ app()->getLocale()==='bn' ? 'ছাড় সম্পাদনা' : 'Edit Discount' }}
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'মূল মূল্য *' : 'Original Price *' }}</label>
                <input type="number" class="form-control" name="original_price" id="editOriginalPrice" step="0.01" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'ছাড়ের মূল্য *' : 'Discount Price *' }}</label>
                <input type="number" class="form-control" name="discount_price" id="editDiscountPrice" step="0.01" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">{{ app()->getLocale()==='bn' ? 'শুরু তারিখ' : 'Start Date' }}</label>
                <input type="datetime-local" class="form-control" name="start_date" id="editStartDate" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">{{ app()->getLocale()==='bn' ? 'শেষ তারিখ' : 'End Date' }}</label>
                <input type="datetime-local" class="form-control" name="end_date" id="editEndDate" required>
              </div>
            </div>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editIsActive">
            <label class="form-check-label bengali-text" for="editIsActive">
              {{ app()->getLocale()==='bn' ? 'সক্রিয়' : 'Active' }}
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel' }}</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>{{ app()->getLocale()==='bn' ? 'আপডেট' : 'Update' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ════════ Delete Modal ════════ --}}
<div class="modal fade" id="deleteDiscountModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title bengali-text">
          <i class="fas fa-trash me-2" style="color:#ef4444"></i>
          {{ app()->getLocale()==='bn' ? 'ছাড় মুছে ফেলুন' : 'Delete Discount' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="deleteDiscountForm">
        @csrf @method('DELETE')
        <div class="modal-body">
          <p class="text-danger bengali-text mb-0">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ app()->getLocale()==='bn'
                ? 'এই ছাড়টি মুছে ফেলবেন? এটি পূর্বাবস্থায় ফেরানো যাবে না।'
                : 'Delete this discount? This action cannot be undone.' }}
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel' }}</button>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-trash me-2"></i>{{ app()->getLocale()==='bn' ? 'মুছুন' : 'Delete' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
  'use strict';

  const IS_BN    = {{ app()->getLocale() === 'bn' ? 'true' : 'false' }};
  const BASE_URL = '{{ route("admin.discounts.index") }}';
  const CSRF     = '{{ csrf_token() }}';

  let currentPage = 2;
  let isLoading   = false;
  let hasMore     = {{ $hasMore ? 'true' : 'false' }};

  const tbody     = document.getElementById('dcTableBody');
  const loader    = document.getElementById('dc-scroll-loader');
  const endMarker = document.getElementById('dc-end-marker');

  if (!hasMore) endMarker.style.display = 'block';

  /* ── Build row HTML from AJAX JSON ── */
  function buildRows(discounts) {
    return discounts.map(d => {
      const imgHtml = d.image_url
        ? `<img src="${d.image_url}" class="dc-thumb" alt="">`
        : `<div class="dc-thumb-placeholder"><i class="fas fa-box"></i></div>`;

      const productName = IS_BN ? d.product_name_bn : d.product_name;

      const statusBadge = d.is_active
        ? `<span class="dc-badge dc-badge-active">${IS_BN ? 'সক্রিয়' : 'Active'}</span>`
        : `<span class="dc-badge dc-badge-inactive">${IS_BN ? 'নিষ্ক্রিয়' : 'Inactive'}</span>`;

      const tClass = d.is_active ? 'dc-act-toggle-on'  : 'dc-act-toggle-off';
      const tIcon  = d.is_active ? 'fa-pause'           : 'fa-play';
      const tTitle = d.is_active
        ? (IS_BN ? 'নিষ্ক্রিয় করুন' : 'Deactivate')
        : (IS_BN ? 'সক্রিয় করুন'   : 'Activate');

      return `<tr id="dc-row-${d.id}">
        <td>
          <div class="dc-product-cell">
            ${imgHtml}
            <div>
              <div class="dc-product-name bengali-text">${productName}</div>
              <div class="dc-product-type">${d.product_type}</div>
            </div>
          </div>
        </td>
        <td><span class="dc-price-original">৳${Number(d.original_price).toLocaleString()}</span></td>
        <td><span class="dc-price-discount">৳${Number(d.discount_price).toLocaleString()}</span></td>
        <td><span class="dc-badge dc-badge-pct">${d.discount_percentage}%</span></td>
        <td>${statusBadge}</td>
        <td style="font-size:12px;color:var(--muted);">${d.end_date_formatted}</td>
        <td>
          <div class="dc-action-btns">
            <button class="dc-act-btn dc-act-edit edit-discount-btn"
              title="${IS_BN ? 'সম্পাদনা' : 'Edit'}"
              data-id="${d.id}"
              data-original="${d.original_price}"
              data-discount="${d.discount_price}"
              data-start="${d.start_date}"
              data-end="${d.end_date_input}"
              data-active="${d.is_active ? 1 : 0}">
              <i class="fas fa-edit"></i>
            </button>
            <button class="dc-act-btn ${tClass} ajax-toggle-btn"
              title="${tTitle}" data-url="${d.toggle_url}">
              <i class="fas ${tIcon}"></i>
            </button>
            <button class="dc-act-btn dc-act-del delete-discount-btn"
              title="${IS_BN ? 'মুছুন' : 'Delete'}" data-id="${d.id}">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>`;
    }).join('');
  }

  /* ── Load next page ── */
  async function loadMore() {
    if (isLoading || !hasMore) return;
    isLoading = true;
    loader.style.display = 'flex';
    try {
      const params = new URLSearchParams(window.location.search);
      params.set('page', currentPage);
      const res  = await fetch(`${BASE_URL}?${params}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      });
      const data = await res.json();
      const emptyRow = document.getElementById('dcEmptyRow');
      if (emptyRow) emptyRow.remove();
      if (data.discounts.length) {
        tbody.insertAdjacentHTML('beforeend', buildRows(data.discounts));
        bindDynamicRows();
      }
      hasMore     = data.has_more;
      currentPage = data.next_page;
      if (!hasMore) endMarker.style.display = 'block';
    } catch (err) {
      console.error('Discount scroll error:', err);
    } finally {
      isLoading = false;
      loader.style.display = 'none';
    }
  }

  /* ── IntersectionObserver ── */
  const sentinel = document.createElement('div');
  sentinel.style.height = '1px';
  endMarker.parentNode.insertBefore(sentinel, endMarker);
  new IntersectionObserver(entries => {
    if (entries[0].isIntersecting) loadMore();
  }, { rootMargin: '200px' }).observe(sentinel);

  /* ── Modals ── */
  function openEditModal(btn) {
    document.getElementById('editOriginalPrice').value = btn.dataset.original;
    document.getElementById('editDiscountPrice').value = btn.dataset.discount;
    document.getElementById('editStartDate').value     = btn.dataset.start;
    document.getElementById('editEndDate').value       = btn.dataset.end;
    document.getElementById('editIsActive').checked   = btn.dataset.active == '1';
    document.getElementById('editDiscountForm').action = `/admin/discounts/${btn.dataset.id}`;
    new bootstrap.Modal(document.getElementById('editDiscountModal')).show();
  }
  function openDeleteModal(btn) {
    document.getElementById('deleteDiscountForm').action = `/admin/discounts/${btn.dataset.id}`;
    new bootstrap.Modal(document.getElementById('deleteDiscountModal')).show();
  }
  async function doToggle(url) {
    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (res.ok) window.location.reload();
    } catch (e) { console.error(e); }
  }

  /* ── Bind events (static + dynamic rows) ── */
  function bindDynamicRows() {
    document.querySelectorAll('.edit-discount-btn:not([data-bound])').forEach(btn => {
      btn.dataset.bound = '1';
      btn.addEventListener('click', () => openEditModal(btn));
    });
    document.querySelectorAll('.delete-discount-btn:not([data-bound])').forEach(btn => {
      btn.dataset.bound = '1';
      btn.addEventListener('click', () => openDeleteModal(btn));
    });
    document.querySelectorAll('.ajax-toggle-btn:not([data-bound])').forEach(btn => {
      btn.dataset.bound = '1';
      btn.addEventListener('click', () => doToggle(btn.dataset.url));
    });
  }

  bindDynamicRows();
})();
</script>
@endpush