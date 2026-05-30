@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'রিভিউ ব্যবস্থাপনা' : 'Review Management')
@section('page-title', app()->getLocale()==='bn' ? 'রিভিউ ব্যবস্থাপনা' : 'Review Management')

@push('styles')
<style>
/* ── Reviews Index — Dark + Infinite Scroll ─── */
.rv-filter-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  padding: 18px 20px;
  margin-bottom: 20px;
  box-shadow: var(--sh-xs);
}
.rv-filter-row {
  display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;
}
.rv-filter-row .fg  { flex: 1 1 180px; min-width: 0; }
.rv-filter-row .fg-fixed { flex: 0 0 auto; }

.rv-main-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  box-shadow: var(--sh-xs);
  overflow: hidden;
}
.rv-card-head {
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 10px;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
  background: var(--white);
}
.rv-card-title {
  display: flex; align-items: center; gap: 8px;
  font-size: 15px; font-weight: 700; color: var(--text);
}

/* Table */
.rv-table-wrap { overflow-x: auto; }
.rv-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.rv-table thead tr {
  background: var(--bg);
  border-bottom: 1px solid var(--border);
}
.rv-table thead th {
  padding: 11px 14px;
  text-align: left;
  font-size: 11px; font-weight: 700;
  text-transform: uppercase; letter-spacing: .6px;
  color: var(--muted); white-space: nowrap;
}
.rv-table tbody tr {
  border-bottom: 1px solid var(--border-2);
  transition: background .12s;
}
.rv-table tbody tr:last-child { border-bottom: none; }
.rv-table tbody tr:hover { background: var(--hover); }
.rv-table td {
  padding: 12px 14px;
  vertical-align: middle;
  color: var(--text);
}

/* Stars */
.rv-stars { display: inline-flex; gap: 1px; font-size: 13px; }
.rv-star-on  { color: var(--warning); }
.rv-star-off { color: var(--muted-2); }

/* Review image */
.rv-img {
  width: 42px; height: 42px;
  object-fit: cover;
  border-radius: 7px;
  border: 1px solid var(--border);
  display: block;
}

/* Badges */
.rv-badge {
  display: inline-block; padding: 3px 9px;
  border-radius: 6px; font-size: 11px; font-weight: 700;
}
.rv-badge-approved { background: rgba(22,163,74,.13); color: #16a34a; }
.rv-badge-pending  { background: rgba(217,119,6,.13);  color: #d97706; }

/* Action buttons */
.rv-action-btns { display: flex; gap: 5px; }
.rv-btn {
  display: inline-flex; align-items: center; justify-content: center;
  width: 30px; height: 30px; border-radius: 7px;
  border: 1px solid transparent; cursor: pointer;
  background: none; transition: all .15s; font-size: 12px;
}
.rv-btn:hover { transform: translateY(-1px); }
.rv-btn-approve { color: #16a34a; border-color: #16a34a; }
.rv-btn-approve:hover { background: rgba(22,163,74,.1); }
.rv-btn-reject  { color: var(--warning); border-color: var(--warning); }
.rv-btn-reject:hover  { background: rgba(217,119,6,.1); }
.rv-btn-del     { color: var(--danger); border-color: var(--danger); }
.rv-btn-del:hover     { background: rgba(220,38,38,.1); }

/* Scroll loader */
#rv-scroll-loader {
  display: none; text-align: center; padding: 24px;
  color: var(--muted); font-size: 13px;
}
#rv-scroll-loader .rv-spinner {
  display: inline-block; width: 22px; height: 22px;
  border: 3px solid var(--border); border-top-color: var(--primary);
  border-radius: 50%; animation: rv-spin .7s linear infinite;
  margin-right: 8px; vertical-align: middle;
}
@keyframes rv-spin { to { transform: rotate(360deg); } }
#rv-end-marker {
  display: none; text-align: center; padding: 18px;
  color: var(--muted-2); font-size: 12px;
}

/* Empty */
.rv-empty { text-align: center; padding: 48px 20px; color: var(--muted); }
.rv-empty i { font-size: 2.2rem; opacity: .35; display: block; margin-bottom: 10px; }

/* Responsive */
@media (max-width: 768px) {
  .rv-table thead th:nth-child(6),
  .rv-table tbody td:nth-child(6) { display: none; }
}
@media (max-width: 540px) {
  .rv-table thead th:nth-child(8),
  .rv-table tbody td:nth-child(8) { display: none; }
  .rv-table td, .rv-table th { padding: 10px 10px; }
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

{{-- ── Stats ── --}}
<div class="adm-stats fade-up" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px;">
  <div class="adm-stat blue">
    <div class="adm-stat-icon blue"><i class="fas fa-star"></i></div>
    <div class="adm-stat-val">{{ $stats['total'] }}</div>
    <div class="adm-stat-lbl">{{ app()->getLocale()==='bn' ? 'মোট রিভিউ' : 'Total Reviews' }}</div>
  </div>
  <div class="adm-stat green">
    <div class="adm-stat-icon green"><i class="fas fa-check-circle"></i></div>
    <div class="adm-stat-val">{{ $stats['approved'] }}</div>
    <div class="adm-stat-lbl">{{ app()->getLocale()==='bn' ? 'অনুমোদিত' : 'Approved' }}</div>
  </div>
  <div class="adm-stat amber">
    <div class="adm-stat-icon amber"><i class="fas fa-clock"></i></div>
    <div class="adm-stat-val">{{ $stats['pending'] }}</div>
    <div class="adm-stat-lbl">{{ app()->getLocale()==='bn' ? 'অপেক্ষমাণ' : 'Pending' }}</div>
  </div>
</div>

{{-- ── Filters ── --}}
<div class="rv-filter-card fade-up-1">
  <form method="GET" id="rvFilterForm">
    <div class="rv-filter-row">
      <div class="fg">
        <label class="adm-label">{{ app()->getLocale()==='bn' ? 'খুঁজুন' : 'Search' }}</label>
        <input type="text" class="adm-input" name="search"
          placeholder="{{ app()->getLocale()==='bn' ? 'রিভিউ, পণ্য বা ব্যবহারকারী...' : 'Review, product or user...' }}"
          value="{{ $search }}">
      </div>
      <div class="fg">
        <label class="adm-label">{{ app()->getLocale()==='bn' ? 'স্ট্যাটাস' : 'Status' }}</label>
        <select class="adm-select" name="filter">
          <option value="">{{ app()->getLocale()==='bn' ? 'সব রিভিউ' : 'All Reviews' }}</option>
          <option value="approved" {{ $filter==='approved' ? 'selected' : '' }}>{{ app()->getLocale()==='bn' ? 'অনুমোদিত' : 'Approved' }}</option>
          <option value="pending"  {{ $filter==='pending'  ? 'selected' : '' }}>{{ app()->getLocale()==='bn' ? 'অপেক্ষমাণ' : 'Pending' }}</option>
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
<div class="rv-main-card fade-up-2">
  <div class="rv-card-head">
    <div class="rv-card-title">
      <i class="fas fa-comment-alt" style="color:var(--primary)"></i>
      {{ app()->getLocale()==='bn' ? 'রিভিউ তালিকা' : 'Reviews List' }}
      <span style="background:rgba(100,116,139,.15);color:var(--muted);padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700;" id="rvTotalBadge">{{ $total }}</span>
    </div>
  </div>

  <div class="rv-table-wrap">
    <table class="rv-table">
      <thead>
        <tr>
          <th>#</th>
          <th>{{ app()->getLocale()==='bn' ? 'পণ্য' : 'Product' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'ব্যবহারকারী' : 'User' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'রেটিং' : 'Rating' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'মন্তব্য' : 'Comment' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'ছবি' : 'Image' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'স্ট্যাটাস' : 'Status' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'তারিখ' : 'Date' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'কার্যক্রম' : 'Actions' }}</th>
        </tr>
      </thead>
      <tbody id="rvTableBody">
        @forelse($reviews as $review)
          @include('admin.reviews._row', ['r' => $review])
        @empty
        <tr id="rvEmptyRow">
          <td colspan="9">
            <div class="rv-empty">
              <i class="fas fa-star"></i>
              {{ app()->getLocale()==='bn' ? 'কোনো রিভিউ পাওয়া যায়নি।' : 'No reviews found.' }}
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div id="rv-scroll-loader">
    <span class="rv-spinner"></span>
    {{ app()->getLocale()==='bn' ? 'লোড হচ্ছে...' : 'Loading more...' }}
  </div>
  <div id="rv-end-marker">
    <i class="fas fa-check-circle me-1" style="color:var(--primary)"></i>
    {{ app()->getLocale()==='bn' ? 'সব রিভিউ দেখানো হয়েছে' : 'All reviews loaded' }}
  </div>
</div>

@endsection

@push('scripts')
<script>
(function(){
  const IS_BN    = {{ app()->getLocale() === 'bn' ? 'true' : 'false' }};
  const BASE_URL = '{{ route("admin.reviews.index") }}';
  const CSRF     = '{{ csrf_token() }}';

  let currentPage = 2;
  let isLoading   = false;
  let hasMore     = {{ $hasMore ? 'true' : 'false' }};

  const tbody     = document.getElementById('rvTableBody');
  const loader    = document.getElementById('rv-scroll-loader');
  const endMarker = document.getElementById('rv-end-marker');

  if (!hasMore) endMarker.style.display = 'block';

  // ── Build stars HTML ────────────────────────────────────
  function stars(rating) {
    let s = '';
    for (let i = 1; i <= 5; i++) {
      s += `<span class="${i <= rating ? 'rv-star-on' : 'rv-star-off'}">★</span>`;
    }
    return `<span class="rv-stars">${s}</span>`;
  }

  // ── Build row HTML from JSON ─────────────────────────────
  function buildRows(reviews) {
    return reviews.map(r => {
      const imgHtml = r.image_url
        ? `<a href="${r.image_url}" target="_blank"><img src="${r.image_url}" class="rv-img" alt="review"></a>`
        : `<span style="color:var(--muted-2)">—</span>`;

      const statusHtml = r.is_approved
        ? `<span class="rv-badge rv-badge-approved">${IS_BN ? 'অনুমোদিত' : 'Approved'}</span>`
        : `<span class="rv-badge rv-badge-pending">${IS_BN ? 'অপেক্ষমাণ' : 'Pending'}</span>`;

      const approveBtn = !r.is_approved
        ? `<button class="rv-btn rv-btn-approve" onclick="doAction('${r.approve_url}','PATCH')" title="${IS_BN ? 'অনুমোদন করুন' : 'Approve'}"><i class="fas fa-check"></i></button>`
        : `<button class="rv-btn rv-btn-reject"  onclick="doAction('${r.reject_url}','PATCH')"  title="${IS_BN ? 'প্রত্যাখ্যান করুন' : 'Reject'}"><i class="fas fa-ban"></i></button>`;

      return `<tr id="rv-row-${r.id}">
        <td style="color:var(--muted)">${r.id}</td>
        <td><strong style="color:var(--text);font-size:13px">${r.product}</strong></td>
        <td style="color:var(--text)">${r.user}</td>
        <td>${stars(r.rating)}</td>
        <td style="max-width:200px;color:var(--text);font-size:13px">${r.comment}</td>
        <td>${imgHtml}</td>
        <td>${statusHtml}</td>
        <td style="color:var(--muted);font-size:12px">${r.date}</td>
        <td>
          <div class="rv-action-btns">
            ${approveBtn}
            <button class="rv-btn rv-btn-del" onclick="doDelete('${r.delete_url}', ${r.id})" title="${IS_BN ? 'মুছুন' : 'Delete'}">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>`;
    }).join('');
  }

  // ── Inline AJAX approve / reject / delete ───────────────
  window.doAction = async function(url, method) {
    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': CSRF,
          'X-HTTP-Method-Override': method,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        }
      });
      // Just reload page to reflect new status (simple & reliable)
      window.location.reload();
    } catch(e) {
      console.error(e);
    }
  };

  window.doDelete = function(url, id) {
    const msg = IS_BN ? 'এই রিভিউটি মুছে ফেলবেন?' : 'Delete this review?';
    if (!confirm(msg)) return;
    doAction(url, 'DELETE');
  };

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

      const emptyRow = document.getElementById('rvEmptyRow');
      if (emptyRow) emptyRow.remove();

      if (data.reviews.length) {
        tbody.insertAdjacentHTML('beforeend', buildRows(data.reviews));
      }

      hasMore     = data.has_more;
      currentPage = data.next_page;

      if (!hasMore) endMarker.style.display = 'block';
    } catch(err) {
      console.error('Review scroll error:', err);
    } finally {
      isLoading = false;
      loader.style.display = 'none';
    }
  }

  // Sentinel for IntersectionObserver
  const sentinel = document.createElement('div');
  sentinel.style.height = '1px';
  endMarker.parentNode.insertBefore(sentinel, endMarker);

  const observer = new IntersectionObserver(entries => {
    if (entries[0].isIntersecting) loadMore();
  }, { rootMargin: '200px' });
  observer.observe(sentinel);
})();
</script>
@endpush