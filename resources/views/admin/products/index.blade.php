@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'পণ্য ব্যবস্থাপনা' : 'Product Management')
@section('page-title', app()->getLocale()==='bn' ? 'পণ্য ব্যবস্থাপনা' : 'Product Management')

@push('styles')
<style>
/* ── Product Index — Full Dark + Infinite Scroll ───────── */
.pi-filter-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  padding: 18px 20px;
  margin-bottom: 20px;
  box-shadow: var(--sh-xs);
}
.pi-filter-row {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  align-items: flex-end;
}
.pi-filter-row .fg { flex: 1 1 180px; min-width: 0; }
.pi-filter-row .fg-fixed { flex: 0 0 auto; }

.pi-main-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  box-shadow: var(--sh-xs);
  overflow: hidden;
}
.pi-card-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 10px;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
  background: var(--white);
}
.pi-card-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 15px;
  font-weight: 700;
  color: var(--text);
}
.pi-head-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

/* Table */
.pi-table-wrap { overflow-x: auto; }
.pi-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.pi-table thead tr {
  background: var(--bg);
  border-bottom: 1px solid var(--border);
}
.pi-table thead th {
  padding: 11px 14px;
  text-align: left;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .6px;
  color: var(--muted);
  white-space: nowrap;
}
.pi-table tbody tr {
  border-bottom: 1px solid var(--border-2);
  transition: background .12s;
}
.pi-table tbody tr:last-child { border-bottom: none; }
.pi-table tbody tr:hover { background: var(--hover); }
.pi-table td {
  padding: 12px 14px;
  vertical-align: middle;
  color: var(--text);
}

/* Product thumb */
.pi-thumb {
  width: 52px; height: 52px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid var(--border);
  display: block;
}
.pi-thumb-placeholder {
  width: 52px; height: 52px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background: var(--bg);
  display: flex; align-items: center; justify-content: center;
  color: var(--muted-2);
}

/* Badges */
.pi-badge {
  display: inline-block;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .3px;
}
.pi-badge-type   { background: rgba(100,116,139,.15); color: var(--muted); }
.pi-badge-size   { background: rgba(14,165,233,.15); color: #0ea5e9; }
.pi-badge-active { background: rgba(22,163,74,.15); color: #16a34a; }
.pi-badge-inactive { background: rgba(220,38,38,.12); color: #dc2626; }
.pi-badge-disc   { background: rgba(16,185,129,.15); color: #10b981; }

/* Action buttons */
.pi-action-btns { display: flex; gap: 5px; flex-wrap: wrap; }
.pi-btn-icon {
  display: inline-flex; align-items: center; justify-content: center;
  width: 30px; height: 30px; border-radius: 7px;
  border: 1px solid transparent; cursor: pointer;
  background: none; transition: all .15s; text-decoration: none;
  font-size: 12px;
}
.pi-btn-icon:hover { transform: translateY(-1px); }
.pi-btn-view  { color: var(--primary); border-color: var(--primary); }
.pi-btn-view:hover  { background: rgba(37,99,235,.1); }
.pi-btn-edit  { color: var(--warning); border-color: var(--warning); }
.pi-btn-edit:hover  { background: rgba(217,119,6,.1); }
.pi-btn-notify { color: #10b981; border-color: #10b981; }
.pi-btn-notify:hover { background: rgba(16,185,129,.1); }
.pi-btn-del   { color: var(--danger); border-color: var(--danger); }
.pi-btn-del:hover   { background: rgba(220,38,38,.1); }

/* Scroll loader */
#pi-scroll-loader {
  display: none;
  text-align: center;
  padding: 24px;
  color: var(--muted);
  font-size: 13px;
}
#pi-scroll-loader .pi-spinner {
  display: inline-block;
  width: 22px; height: 22px;
  border: 3px solid var(--border);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: pi-spin .7s linear infinite;
  margin-right: 8px;
  vertical-align: middle;
}
@keyframes pi-spin { to { transform: rotate(360deg); } }

#pi-end-marker {
  display: none;
  text-align: center;
  padding: 18px;
  color: var(--muted-2);
  font-size: 12px;
}

/* Empty state */
.pi-empty {
  text-align: center;
  padding: 48px 20px;
  color: var(--muted);
}
.pi-empty i { font-size: 2.2rem; opacity: .35; display: block; margin-bottom: 10px; }

/* Responsive tweaks */
@media (max-width: 768px) {
  .pi-table thead th:nth-child(4),
  .pi-table tbody td:nth-child(4) { display: none; } /* hide Size col on mobile */
  .pi-filter-row .fg { flex: 1 1 140px; }
}
@media (max-width: 520px) {
  .pi-card-head { padding: 12px 14px; }
  .pi-table td, .pi-table th { padding: 10px 10px; }
  .pi-thumb { width: 40px; height: 40px; }
  .pi-thumb-placeholder { width: 40px; height: 40px; }
}
</style>
@endpush

@section('content')

{{-- ── Alert ── --}}
@if(session('success'))
<div class="adm-alert adm-alert-success mb-3" style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:var(--r);background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.25);color:#16a34a;">
  <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="adm-alert adm-alert-danger mb-3" style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:var(--r);background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.25);color:#dc2626;">
  <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

{{-- ── Filters ── --}}
<div class="pi-filter-card">
  <form method="GET" id="piFilterForm">
    <div class="pi-filter-row">
      <div class="fg">
        <label class="adm-label">{{ app()->getLocale()==='bn' ? 'পণ্য খুঁজুন' : 'Search' }}</label>
        <input type="text" class="adm-input" name="search" id="piSearchInput"
          placeholder="{{ app()->getLocale()==='bn' ? 'নাম, SKU...' : 'Name, SKU...' }}"
          value="{{ $search }}">
      </div>
      <div class="fg">
        <label class="adm-label">{{ app()->getLocale()==='bn' ? 'ধরন' : 'Type' }}</label>
        <select class="adm-select" name="type" id="piTypeInput">
          <option value="">{{ app()->getLocale()==='bn' ? 'সব ধরনের' : 'All Types' }}</option>
          @foreach($productTypes as $type)
          <option value="{{ $type }}" {{ $typeFilter==$type ? 'selected' : '' }}>{{ $type }}</option>
          @endforeach
        </select>
      </div>
      <div class="fg-fixed">
        <label class="adm-label">&nbsp;</label>
        <button type="submit" class="adm-btn adm-btn-primary">
          <i class="fas fa-search"></i>{{ app()->getLocale()==='bn' ? 'ফিল্টার' : 'Filter' }}
        </button>
      </div>
    </div>
  </form>
</div>

{{-- ── Main Card ── --}}
<div class="pi-main-card fade-up">
  <div class="pi-card-head">
    <div class="pi-card-title">
      <i class="fas fa-box" style="color:var(--primary)"></i>
      {{ app()->getLocale()==='bn' ? 'পণ্যের তালিকা' : 'Products List' }}
      <span class="pi-badge pi-badge-type ms-1" id="piTotalBadge">{{ $total }}</span>
    </div>
    <div class="pi-head-actions">
      <a href="{{ route('admin.products.create') }}" class="adm-btn adm-btn-primary adm-btn-sm">
        <i class="fas fa-plus"></i>{{ app()->getLocale()==='bn' ? 'নতুন পণ্য' : 'Add Product' }}
      </a>
    </div>
  </div>

  <div class="pi-table-wrap">
    <table class="pi-table">
      <thead>
        <tr>
          <th>{{ app()->getLocale()==='bn' ? 'ছবি' : 'Image' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'পণ্য' : 'Product' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'ধরন' : 'Type' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'সাইজ' : 'Size' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'দাম' : 'Price' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'ছাড়' : 'Discount' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'স্ট্যাটাস' : 'Status' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'কার্যক্রম' : 'Actions' }}</th>
        </tr>
      </thead>
      <tbody id="piTableBody">
        @forelse($products as $product)
        @include('admin.products._row', ['p' => $product])
        @empty
        <tr id="piEmptyRow">
          <td colspan="8">
            <div class="pi-empty">
              <i class="fas fa-box-open"></i>
              {{ app()->getLocale()==='bn' ? 'কোন পণ্য পাওয়া যায়নি' : 'No products found' }}
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Scroll loader --}}
  <div id="pi-scroll-loader">
    <span class="pi-spinner"></span>
    {{ app()->getLocale()==='bn' ? 'লোড হচ্ছে...' : 'Loading more...' }}
  </div>
  <div id="pi-end-marker">
    <i class="fas fa-check-circle me-1" style="color:var(--primary)"></i>
    {{ app()->getLocale()==='bn' ? 'সব পণ্য দেখানো হয়েছে' : 'All products loaded' }}
  </div>
</div>

{{-- ── Delete Modal ── --}}
@push('modals')
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background:var(--white);border:1px solid var(--border);border-radius:var(--r-lg);">
      <div class="modal-header" style="border-color:var(--border);background:var(--bg);">
        <h5 class="modal-title" style="color:var(--text);">
          <i class="fas fa-trash me-2" style="color:var(--danger)"></i>
          {{ app()->getLocale()==='bn' ? 'মুছে ফেলা নিশ্চিত করুন' : 'Confirm Delete' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"
          style="{{ session('admin_theme')!=='dark' ?: 'filter:invert(1)' }}"></button>
      </div>
      <form method="POST" id="deleteForm">
        @csrf @method('DELETE')
        <div class="modal-body" style="background:var(--white);color:var(--text);">
          <p style="color:var(--danger);">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong id="deleteProductName"></strong>
            {{ app()->getLocale()==='bn' ? ' — মুছে ফেলবেন?' : ' will be permanently deleted!' }}
          </p>
        </div>
        <div class="modal-footer" style="border-color:var(--border);background:var(--bg);">
          <button type="button" class="adm-btn adm-btn-ghost" data-bs-dismiss="modal">
            {{ app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel' }}
          </button>
          <button type="submit" class="adm-btn adm-btn-danger">
            <i class="fas fa-trash"></i>{{ app()->getLocale()==='bn' ? 'মুছুন' : 'Delete' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endpush

@endsection

@push('scripts')
<script>
(function(){
  const IS_BN     = {{ app()->getLocale() === 'bn' ? 'true' : 'false' }};
  const BASE_URL  = '{{ route("admin.products.index") }}';
  const searchParams = new URLSearchParams(window.location.search);

  let currentPage = 2;   // প্রথম 20টা server-side এসেছে
  let isLoading   = false;
  let hasMore     = {{ $hasMore ? 'true' : 'false' }};

  const tbody     = document.getElementById('piTableBody');
  const loader    = document.getElementById('pi-scroll-loader');
  const endMarker = document.getElementById('pi-end-marker');

  if (!hasMore) endMarker.style.display = 'block';

  // ── Delete Modal ─────────────────────────────────────────
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('.pi-del-btn');
    if (!btn) return;
    document.getElementById('deleteProductName').textContent = btn.dataset.name;
    document.getElementById('deleteForm').action = btn.dataset.url;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
  });

  // ── Infinite Scroll ──────────────────────────────────────
  function buildRows(products) {
    return products.map(p => {
      const imgHtml = p.image_url && !p.image_url.includes('no-image')
        ? `<img src="${p.image_url}" class="pi-thumb" alt="${p.name}">`
        : `<div class="pi-thumb-placeholder"><i class="fas fa-image"></i></div>`;

      const discHtml = p.discount
        ? `<span class="pi-badge pi-badge-disc">${p.discount.pct}% OFF</span><br>
           <small style="color:var(--muted)">৳${p.discount.price}</small>`
        : `<span style="color:var(--muted-2)">—</span>`;

      const statusHtml = p.status
        ? `<span class="pi-badge pi-badge-active">${IS_BN ? 'সক্রিয়' : 'Active'}</span>`
        : `<span class="pi-badge pi-badge-inactive">${IS_BN ? 'নিষ্ক্রিয়' : 'Inactive'}</span>`;

      const notifyUrl = `{{ url('/admin/products') }}/${p.id}/notify`;

      return `<tr>
        <td>${imgHtml}</td>
        <td>
          <strong style="color:var(--text)">${p.name}</strong>
          <br><small style="color:var(--muted)">${p.description}</small>
        </td>
        <td><span class="pi-badge pi-badge-type">${p.product_type || '—'}</span></td>
        <td><span class="pi-badge pi-badge-size">${p.product_size || '—'}</span></td>
        <td><strong style="color:var(--text)">৳${p.price}</strong></td>
        <td>${discHtml}</td>
        <td>${statusHtml}</td>
        <td>
          <div class="pi-action-btns">
            <a href="${p.view_url}" class="pi-btn-icon pi-btn-view" title="${IS_BN ? 'দেখুন' : 'View'}"><i class="fas fa-eye"></i></a>
            <a href="${p.edit_url}" class="pi-btn-icon pi-btn-edit" title="${IS_BN ? 'সম্পাদনা' : 'Edit'}"><i class="fas fa-edit"></i></a>
            <a href="${notifyUrl}" class="pi-btn-icon pi-btn-notify" title="${IS_BN ? 'নোটিফিকেশন' : 'Notify'}"><i class="fas fa-bell"></i></a>
            <button class="pi-btn-icon pi-btn-del pi-del-btn"
              data-id="${p.id}" data-name="${p.name_en}"
              data-url="${p.delete_url}"
              title="${IS_BN ? 'মুছুন' : 'Delete'}"><i class="fas fa-trash"></i></button>
          </div>
        </td>
      </tr>`;
    }).join('');
  }

  async function loadMore() {
    if (isLoading || !hasMore) return;
    isLoading = true;
    loader.style.display = 'block';

    try {
      const params = new URLSearchParams(window.location.search);
      params.set('page', currentPage);
      const res  = await fetch(`${BASE_URL}?${params}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      });
      const data = await res.json();

      // Remove empty row if present
      const emptyRow = document.getElementById('piEmptyRow');
      if (emptyRow) emptyRow.remove();

      if (data.products.length) {
        tbody.insertAdjacentHTML('beforeend', buildRows(data.products));
      }

      hasMore = data.has_more;
      currentPage = data.next_page;

      if (!hasMore) {
        endMarker.style.display = 'block';
      }
    } catch(err) {
      console.error('Infinite scroll error:', err);
    } finally {
      isLoading = false;
      loader.style.display = 'none';
    }
  }

  // IntersectionObserver sentinel
  const sentinel = document.createElement('div');
  sentinel.id = 'pi-sentinel';
  sentinel.style.height = '1px';
  document.getElementById('pi-end-marker').parentNode.insertBefore(sentinel, document.getElementById('pi-end-marker'));

  const observer = new IntersectionObserver(entries => {
    if (entries[0].isIntersecting) loadMore();
  }, { rootMargin: '200px' });
  observer.observe(sentinel);

})();
</script>
@endpush