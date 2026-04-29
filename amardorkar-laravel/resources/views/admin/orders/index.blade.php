@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'অর্ডার ব্যবস্থাপনা' : 'Order Management')
@section('page-title', app()->getLocale()==='bn' ? 'অর্ডার ব্যবস্থাপনা' : 'Order Management')

@section('content')

{{-- Stats --}}
<div class="stats-grid">
  @foreach([
    ['icon'=>'fa-shopping-bag','num'=>$stats['total_orders'],    'bn'=>'মোট অর্ডার',   'en'=>'Total Orders',   'g'=>'var(--primary)'],
    ['icon'=>'fa-chart-line',  'num'=>'৳'.number_format($stats['total_revenue']), 'bn'=>'মোট আয়', 'en'=>'Total Revenue', 'g'=>'var(--success)'],
    ['icon'=>'fa-clock',       'num'=>$stats['pending_orders'],  'bn'=>'মুলতবি',        'en'=>'Pending',        'g'=>'var(--warning)'],
    ['icon'=>'fa-check-circle','num'=>$stats['delivered_orders'],'bn'=>'ডেলিভার্ড',     'en'=>'Delivered',      'g'=>'var(--danger)'],
    ['icon'=>'fa-calendar-day','num'=>$stats['today_orders'],    'bn'=>'আজকের অর্ডার', 'en'=>"Today's",        'g'=>'var(--primary)'],
  ] as $c)
  <div class="stat-card animate-fadeInUp">
    <div class="stat-header d-flex justify-content-between align-items-start">
      <div>
        <div class="stat-number">{{ $c['num'] }}</div>
        <div class="stat-label bengali-text">{{ app()->getLocale()==='bn' ? $c['bn'] : $c['en'] }}</div>
      </div>
      <div class="stat-icon" style="background:{{ $c['g'] }}"><i class="fas {{ $c['icon'] }}"></i></div>
    </div>
  </div>
  @endforeach
</div>

{{-- Filters --}}
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" class="row g-3">
      <div class="col-md-5">
        <input type="text" class="form-control" name="search"
          placeholder="{{ app()->getLocale()==='bn' ? 'অর্ডার খুঁজুন...' : 'Search orders...' }}"
          value="{{ $search }}">
      </div>
      <div class="col-md-4">
        <select class="form-select" name="status">
          <option value="">{{ app()->getLocale()==='bn' ? 'সব অবস্থা' : 'All Status' }}</option>
          @foreach(['pending'=>'মুলতবি','confirmed'=>'নিশ্চিত','processing'=>'প্রক্রিয়াকরণ','shipped'=>'পাঠানো','delivered'=>'ডেলিভার্ড','cancelled'=>'বাতিল'] as $val=>$bn)
          <option value="{{ $val }}" {{ $statusFilter==$val ? 'selected' : '' }}>
            {{ app()->getLocale()==='bn' ? $bn : ucfirst($val) }}
          </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-primary w-100">
          <i class="fas fa-search me-2"></i>{{ app()->getLocale()==='bn' ? 'ফিল্টার' : 'Filter' }}
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Orders Table --}}
<div class="card animate-fadeInUp">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title bengali-text">
      <i class="fas fa-shopping-bag me-2"></i>{{ app()->getLocale()==='bn' ? 'অর্ডার তালিকা' : 'Orders List' }}
    </h5>
    <button class="btn btn-outline-primary btn-sm" onclick="location.reload()">
      <i class="fas fa-sync-alt me-1"></i>{{ app()->getLocale()==='bn' ? 'রিফ্রেশ' : 'Refresh' }}
    </button>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>{{ app()->getLocale()==='bn' ? 'অর্ডার #' : 'Order #' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'গ্রাহক' : 'Customer' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'পণ্য' : 'Products' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'পরিমাণ' : 'Amount' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'অবস্থা' : 'Status' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'পেমেন্ট' : 'Payment' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'তারিখ' : 'Date' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'কার্যক্রম' : 'Actions' }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($orders as $order)
          @php
            $statusBn = ['pending'=>'মুলতবি','confirmed'=>'নিশ্চিত','processing'=>'প্রক্রিয়াকরণ','shipped'=>'পাঠানো','delivered'=>'ডেলিভার্ড','cancelled'=>'বাতিল'];
            $paymentBn = ['pending'=>'মুলতবি','paid'=>'পরিশোধিত','failed'=>'ব্যর্থ','refunded'=>'ফেরত'];
            $payStatus = $order->payment_status ?? 'pending';
          @endphp
          <tr>
            <td><strong class="text-primary">#{{ $order->order_number }}</strong></td>
            <td>
              <strong class="bengali-text">{{ $order->customer_name }}</strong><br>
              <small class="text-muted">{{ $order->customer_mobile }}</small><br>
              <small class="text-muted">{{ $order->customer_email }}</small>
            </td>
            <td>
              @foreach($order->items->take(2) as $item)
              <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                @php $idxImg = $item->product ? ($item->product->image_url ?? null) : null; @endphp
                @if($idxImg && !str_ends_with($idxImg, 'no-image.png'))
                  <img src="{{ $idxImg }}" style="width:32px;height:32px;object-fit:cover;border-radius:5px;border:1px solid var(--border);flex-shrink:0;">
                @else
                  <div style="width:32px;height:32px;background:var(--bg);border:1px solid var(--border);border-radius:5px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-box" style="color:#94a3b8;font-size:11px;"></i>
                  </div>
                @endif
                <small class="bengali-text">{{ $item->product_name }} × {{ $item->quantity }}</small>
              </div>
              @endforeach
              @if($order->items->count() > 2)
              <small class="text-muted">+{{ $order->items->count()-2 }} {{ app()->getLocale()==='bn' ? 'আরও' : 'more' }}</small>
              @endif
            </td>
            <td><strong class="text-success">৳{{ number_format($order->total_amount) }}</strong></td>
            <td>
              <span class="status-badge status-{{ $order->status }}">
                {{ app()->getLocale()==='bn' ? ($statusBn[$order->status]??ucfirst($order->status)) : ucfirst($order->status) }}
              </span>
            </td>
            <td>
              <span class="status-badge payment-{{ $payStatus }}">
                {{ app()->getLocale()==='bn' ? ($paymentBn[$payStatus]??ucfirst($payStatus)) : ucfirst($payStatus) }}
              </span>
            </td>
            <td>
              {{ $order->created_at->format('M d, Y') }}<br>
              <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
            </td>
            <td>
              <div class="btn-group" role="group">
                <button class="btn btn-sm btn-outline-info" onclick="viewOrder({{ $order->id }})" title="{{ app()->getLocale()==='bn' ? 'দেখুন' : 'View' }}">
                  <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-outline-warning update-status-btn"
                  data-id="{{ $order->id }}"
                  data-status="{{ $order->status }}"
                  data-payment="{{ $payStatus }}"
                  title="{{ app()->getLocale()==='bn' ? 'স্ট্যাটাস আপডেট' : 'Update Status' }}">
                  <i class="fas fa-edit"></i>
                </button>
                @if(!in_array($order->status, ['delivered','cancelled']))
                <button class="btn btn-sm btn-outline-success sell-order-btn"
                  data-id="{{ $order->id }}"
                  title="{{ app()->getLocale()==='bn' ? 'বিক্রয় করুন' : 'Sell' }}">
                  <i class="fas fa-check-circle"></i>
                </button>
                @endif
                <button class="btn btn-sm btn-outline-danger remove-order-btn"
                  data-id="{{ $order->id }}"
                  title="{{ app()->getLocale()==='bn' ? 'মুছুন' : 'Remove' }}">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" class="text-center py-5 text-muted bengali-text">
              <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
              {{ app()->getLocale()==='bn' ? 'কোন অর্ডার পাওয়া যায়নি' : 'No orders found' }}
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    @if($orders->hasPages())
    <div class="p-3 border-top">
      {{ $orders->withQueryString()->links() }}
    </div>
    @endif
  </div>
</div>

{{-- ── Modals ───────────────────────────────────────── --}}

{{-- Status Update Modal --}}
<div class="modal fade" id="statusModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title bengali-text">{{ app()->getLocale()==='bn' ? 'স্ট্যাটাস আপডেট' : 'Update Status' }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="statusForm">
        @csrf @method('PUT')
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'অর্ডার স্ট্যাটাস' : 'Order Status' }}</label>
            <select class="form-select" name="status" id="modalStatus">
              @foreach(['pending'=>'মুলতবি','confirmed'=>'নিশ্চিত','processing'=>'প্রক্রিয়াকরণ','shipped'=>'পাঠানো','delivered'=>'ডেলিভার্ড','cancelled'=>'বাতিল'] as $val=>$bn)
              <option value="{{ $val }}">{{ app()->getLocale()==='bn' ? $bn : ucfirst($val) }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label bengali-text">{{ app()->getLocale()==='bn' ? 'পেমেন্ট স্ট্যাটাস' : 'Payment Status' }}</label>
            <select class="form-select" name="payment_status" id="modalPayment">
              @foreach(['pending'=>'মুলতবি','paid'=>'পরিশোধিত','failed'=>'ব্যর্থ','refunded'=>'ফেরত'] as $val=>$bn)
              <option value="{{ $val }}">{{ app()->getLocale()==='bn' ? $bn : ucfirst($val) }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel' }}</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>{{ app()->getLocale()==='bn' ? 'আপডেট' : 'Update' }}</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Sell Confirm Modal --}}
<div class="modal fade" id="sellModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title bengali-text">{{ app()->getLocale()==='bn' ? 'বিক্রয় নিশ্চিত করুন' : 'Confirm Sale' }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="sellForm">
        @csrf
        <div class="modal-body">
          <p class="bengali-text">{{ app()->getLocale()==='bn' ? 'এই অর্ডারটি বিক্রয় ইতিহাসে স্থানান্তর করবেন?' : 'Move this order to sales history?' }}</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel' }}</button>
          <button type="submit" class="btn btn-success"><i class="fas fa-check-circle me-2"></i>{{ app()->getLocale()==='bn' ? 'হ্যাঁ, বিক্রয় করুন' : 'Yes, Sell' }}</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Remove Confirm Modal --}}
<div class="modal fade" id="removeModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title bengali-text">{{ app()->getLocale()==='bn' ? 'মুছে ফেলা নিশ্চিত করুন' : 'Confirm Removal' }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="removeForm">
        @csrf @method('DELETE')
        <div class="modal-body">
          <p class="text-danger bengali-text"><i class="fas fa-exclamation-triangle me-2"></i>{{ app()->getLocale()==='bn' ? 'এই অর্ডারটি স্থায়ীভাবে মুছে ফেলা হবে!' : 'This order will be permanently deleted!' }}</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel' }}</button>
          <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-2"></i>{{ app()->getLocale()==='bn' ? 'হ্যাঁ, মুছুন' : 'Yes, Remove' }}</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- View Order Modal --}}
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title bengali-text">{{ app()->getLocale()==='bn' ? 'অর্ডার বিস্তারিত' : 'Order Details' }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="viewOrderContent">
        <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.update-status-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.id;
    document.getElementById('modalStatus').value   = btn.dataset.status;
    document.getElementById('modalPayment').value  = btn.dataset.payment;
    document.getElementById('statusForm').action   = `/admin/orders/${id}`;
    new bootstrap.Modal(document.getElementById('statusModal')).show();
  });
});

document.querySelectorAll('.sell-order-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('sellForm').action = `/admin/orders/${btn.dataset.id}/sell`;
    new bootstrap.Modal(document.getElementById('sellModal')).show();
  });
});

document.querySelectorAll('.remove-order-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('removeForm').action = `/admin/orders/${btn.dataset.id}`;
    new bootstrap.Modal(document.getElementById('removeModal')).show();
  });
});

function viewOrder(id) {
  const content = document.getElementById('viewOrderContent');
  content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
  new bootstrap.Modal(document.getElementById('viewModal')).show();

  fetch(`/admin/orders/${id}`, { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
      if (!data.success) { content.innerHTML = '<div class="alert alert-danger">Error loading order</div>'; return; }
      const o = data.order;
      const lang = '{{ app()->getLocale() }}';
      const isBn = lang === 'bn';
      const statusBn = {pending:'মুলতবি',confirmed:'নিশ্চিত',processing:'প্রক্রিয়াকরণ',shipped:'পাঠানো',delivered:'ডেলিভার্ড',cancelled:'বাতিল'};
      // Build items rows
      const itemsHtml = (o.items||[]).map(item => {
        const img = item.product_image
          ? `<img src="${item.product_image}" style="width:120px;height:120px;object-fit:cover;border-radius:8px;border:1px solid #475569;">`
          : `<div style="width:80px;height:80px;background:var(--bg);border:1px solid var(--border);border-radius:8px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-box" style="color:#94a3b8;font-size:13px;"></i></div>`;
        const sizeTag = item.selected_size ? `<span style="font-size:11px;background:rgba(3,105,161,.15);color:#60a5fa;padding:1px 6px;border-radius:10px;margin-left:4px;">${item.selected_size}</span>` : '';
        return `<tr>
          <td style="padding:8px;">${img}</td>
          <td style="padding:8px;"><strong>${item.product_name}</strong>${sizeTag}</td>
          <td style="padding:8px;text-align:center;">${item.quantity}</td>
          <td style="padding:8px;text-align:right;">৳${Number(item.price).toLocaleString()}</td>
          <td style="padding:8px;text-align:right;color:#16a34a;font-weight:600;">৳${Number(item.subtotal).toLocaleString()}</td>
        </tr>`;
      }).join('');

      content.innerHTML = `
        <div class="row mb-3">
          <div class="col-md-6">
            <h6 class="bengali-text fw-bold mb-2">${isBn?'অর্ডার তথ্য':'Order Info'}</h6>
            <table class="table table-sm">
              <tr><td>${isBn?'অর্ডার নং':'Order #'}</td><td><strong>#${o.order_number}</strong></td></tr>
              <tr><td>${isBn?'তারিখ':'Date'}</td><td>${new Date(o.created_at).toLocaleDateString()}</td></tr>
              <tr><td>${isBn?'অবস্থা':'Status'}</td><td><span class="status-badge status-${o.status}">${isBn?(statusBn[o.status]||o.status):o.status}</span></td></tr>
            </table>
          </div>
          <div class="col-md-6">
            <h6 class="bengali-text fw-bold mb-2">${isBn?'গ্রাহক তথ্য':'Customer Info'}</h6>
            <table class="table table-sm">
              <tr><td>${isBn?'নাম':'Name'}</td><td><strong>${o.customer_name}</strong></td></tr>
              <tr><td>${isBn?'মোবাইল':'Mobile'}</td><td>${o.customer_mobile}</td></tr>
              <tr><td>${isBn?'ঠিকানা':'Address'}</td><td>${o.customer_address||'-'}</td></tr>
            </table>
          </div>
        </div>
        <hr class="my-2">
        <h6 class="bengali-text fw-bold mb-2">${isBn?'অর্ডারের পণ্য':'Ordered Items'}</h6>
        <div style="overflow-x:auto;">
          <table class="table table-sm" style="min-width:400px;">
            <thead style="background:var(--bg);">
              <tr>
                <th style="width:52px;"></th>
                <th>${isBn?'পণ্য':'Product'}</th>
                <th style="text-align:center;">${isBn?'পরিমাণ':'Qty'}</th>
                <th style="text-align:right;">${isBn?'দাম':'Price'}</th>
                <th style="text-align:right;">${isBn?'সাবটোটাল':'Subtotal'}</th>
              </tr>
            </thead>
            <tbody>${itemsHtml}</tbody>
          </table>
        </div>
        <hr class="my-2">
        <div class="d-flex justify-content-between p-3 rounded" style="background:rgba(21,128,61,.12);">
          <span class="bengali-text text-muted">${isBn?'ডেলিভারি চার্জ':'Shipping'}: <strong>৳${Number(o.shipping_charge).toLocaleString()}</strong></span>
          <span class="text-success fw-bold fs-6">${isBn?'মোট':'Total'}: ৳${Number(o.total_amount).toLocaleString()}</span>
        </div>
      `;
    })
    .catch(() => { content.innerHTML = '<div class="alert alert-danger">Failed to load order details</div>'; });
}
</script>
@endpush