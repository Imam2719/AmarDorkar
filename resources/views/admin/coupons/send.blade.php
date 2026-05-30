{{-- PATH: resources/views/admin/coupons/send.blade.php --}}
@extends('layouts.admin')
@php $bn = app()->getLocale() === 'bn'; @endphp
@section('title', $bn ? 'কুপন পাঠান' : 'Send Coupon')
@section('page-title', $bn ? 'গ্রাহকদের কুপন পাঠান' : 'Send Coupon to Customers')

@push('styles')
<style>
.template-card {
    border: 2px solid var(--border); border-radius: 12px; padding: 14px 16px;
    cursor: pointer; transition: all .2s; background: var(--surface); color: var(--text);
}
.template-card:hover, .template-card.selected {
    border-color: var(--primary); background: rgba(37,99,235,.06);
}
.adm-dark .template-card.selected { background: rgba(37,99,235,.15); }

.segment-btn {
    border: 2px solid var(--border); border-radius: 10px;
    padding: 9px 16px; cursor: pointer; transition: all .2s;
    background: var(--surface); color: var(--text); font-size: 13px; font-weight: 600;
}
.segment-btn.active {
    border-color: var(--primary); background: rgba(37,99,235,.06); color: var(--primary);
}
.adm-dark .segment-btn.active { background: rgba(37,99,235,.15); }

.channel-check {
    display: flex; align-items: center; gap: 10px; padding: 12px 16px;
    border: 2px solid var(--border); border-radius: 10px; cursor: pointer;
    transition: all .2s; background: var(--surface); color: var(--text);
}
.channel-check:has(input:checked) { border-color: var(--primary); background: rgba(37,99,235,.06); }
.adm-dark .channel-check:has(input:checked) { background: rgba(37,99,235,.15); }

/* Coupon card — shows preset info inline */
.coupon-opt-card {
    border: 2px solid var(--border); border-radius: 10px; padding: 12px 14px;
    cursor: pointer; transition: all .2s; background: var(--surface);
}
.coupon-opt-card:hover, .coupon-opt-card.selected {
    border-color: var(--primary); background: rgba(37,99,235,.06);
}
.adm-dark .coupon-opt-card.selected { background: rgba(37,99,235,.15); }

/* Unique-code auto-notice */
.unique-notice {
    padding: 8px 12px; border-radius: 8px; font-size: 12px;
    background: rgba(37,99,235,.06); border: 1px solid rgba(37,99,235,.2);
    color: var(--primary); display:none;
}

.user-chip {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(37,99,235,.1); color: var(--primary);
    padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; margin: 2px;
}
.user-chip button { background:none; border:none; cursor:pointer; color:var(--primary); font-size:14px; line-height:1; }

.adm-dark .form-control, .adm-dark .form-select {
    background: var(--white) !important; border-color: var(--border) !important; color: var(--text) !important;
}
.adm-dark .form-control::placeholder { color: var(--muted) !important; }
.adm-dark .form-control:focus {
    background: var(--white) !important; border-color: var(--primary) !important;
    box-shadow: 0 0 0 3px rgba(37,99,235,.2) !important;
}
.adm-dark .table { color: var(--text); }
.adm-dark .table thead th { background: var(--white); color: var(--text-2); border-color: var(--border); }
.adm-dark .table tbody td { border-color: var(--border-2); }
.adm-dark .table-hover tbody tr:hover td { background: var(--hover); }

.step-badge {
    display: inline-flex; align-items: center; justify-content: center;
    width: 22px; height: 22px; border-radius: 50%;
    background: var(--primary); color: #fff;
    font-size: 11px; font-weight: 800; margin-right: 6px; flex-shrink: 0;
}
</style>
@endpush

@section('content')
<form action="{{ route('admin.coupons.send') }}" method="POST" id="sendForm">
@csrf

<div class="row g-4">

  {{-- ── STEP 1: Select Coupon ──────────────────────────────────── --}}
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h6 class="card-title mb-0">
          <span class="step-badge">1</span>
          {{ $bn ? 'কুপন নির্বাচন করুন' : 'Select a Coupon' }}
        </h6>
      </div>
      <div class="card-body">
        @if($coupons->isEmpty())
          <p class="text-center py-3" style="color:var(--muted);">
            {{ $bn ? 'কোনো সক্রিয় কুপন নেই। আগে কুপন তৈরি করুন।' : 'No active coupons. Create one first.' }}
          </p>
        @else
        <div class="row g-2">
          @foreach($coupons as $c)
          @php
            $pu = $c->per_user_limit;
            $gl = $c->global_limit;
            if ($pu === 1 && $gl === null)      { $plabel = $bn ? '🎯 একজন একবার'           : '🎯 Single Use';          $autoUnique = 'true'; }
            elseif ($pu === null && $gl === null){ $plabel = $bn ? '📢 সবাই যতবার'           : '📢 Unlimited';           $autoUnique = 'false'; }
            elseif ($pu === null && $gl !== null){ $plabel = $bn ? "🏆 প্রথম {$gl} জন"       : "🏆 First {$gl} uses";   $autoUnique = 'false'; }
            else {
              $parts2 = [];
              if ($pu) $parts2[] = $bn ? "{$pu}/জন" : "{$pu}/user";
              if ($gl) $parts2[] = $bn ? "মোট {$gl}" : "{$gl} total";
              $plabel = '⚙️ ' . implode(', ', $parts2);
              $autoUnique = 'false';
            }
          @endphp
          <div class="col-md-3 col-6">
            <label style="cursor:pointer;display:block;">
              <input type="radio" name="coupon_id" value="{{ $c->id }}" required
                style="display:none;" class="coupon-radio"
                data-auto-unique="{{ $autoUnique }}">
              <div class="coupon-opt-card text-center coupon-opt" data-id="{{ $c->id }}">
                <div style="font-family:monospace;font-size:1.1rem;font-weight:800;letter-spacing:4px;color:var(--primary);">
                  {{ $c->code }}
                </div>
                <div style="font-size:12px;margin-top:4px;">
                  @if($c->type === 'percentage')
                    <span style="background:rgba(230,81,0,.1);color:#e65100;padding:2px 8px;border-radius:20px;">
                      {{ $c->discount_value }}% {{ $bn ? 'ছাড়' : 'off' }}
                    </span>
                  @else
                    <span style="background:rgba(13,71,161,.1);color:#0d47a1;padding:2px 8px;border-radius:20px;">
                      ৳{{ number_format($c->discount_value, 0) }} {{ $bn ? 'ছাড়' : 'off' }}
                    </span>
                  @endif
                </div>
                <div style="font-size:11px;color:var(--muted);margin-top:4px;">
                  {{ $plabel }}
                  @if($c->expires_at)
                    · {{ $bn ? 'মেয়াদ:' : 'Exp:' }} {{ $c->expires_at->format('d M') }}
                  @endif
                </div>
              </div>
            </label>
          </div>
          @endforeach
        </div>

        {{-- Auto unique-code notice --}}
        <div id="uniqueNotice" class="unique-notice mt-3">
          <i class="fas fa-info-circle me-1"></i>
          <strong>{{ $bn ? 'স্বয়ংক্রিয়:' : 'Auto:' }}</strong>
          {{ $bn
            ? 'এই কুপনটি "একজন একবার" ধরনের, তাই প্রতিটি গ্রাহকের জন্য আলাদা কোড তৈরি হবে।'
            : 'This coupon is "single use per customer" — a unique code will be auto-generated for each recipient.' }}
        </div>

        {{-- Hidden send_unique — auto-set via JS --}}
        <input type="hidden" name="send_unique" id="sendUniqueInput" value="0">

        @endif
      </div>
    </div>
  </div>

  {{-- ── STEP 2 + 3 row ─────────────────────────────────────────── --}}
  <div class="col-md-6">
    {{-- STEP 2: Channel --}}
    <div class="card h-100">
      <div class="card-header">
        <h6 class="card-title mb-0">
          <span class="step-badge">2</span>
          {{ $bn ? 'পাঠানোর মাধ্যম' : 'Send Via' }}
        </h6>
      </div>
      <div class="card-body d-flex flex-column gap-3">
        <label class="channel-check">
          <input type="checkbox" name="channels[]" value="email" checked>
          <i class="fas fa-envelope fa-lg" style="color:var(--success,#16a34a);"></i>
          <div>
            <div style="font-weight:700;">{{ $bn ? 'ইমেইল' : 'Email' }}</div>
            <div style="font-size:12px;color:var(--muted);">
              {{ $bn ? 'রেজিস্টার্ড ইমেইলে পাঠানো হবে' : 'Sent to registered email' }}
            </div>
          </div>
        </label>
        <label class="channel-check">
          <input type="checkbox" name="channels[]" value="whatsapp">
          <i class="fab fa-whatsapp fa-lg" style="color:#25D366;"></i>
          <div>
            <div style="font-weight:700;">WhatsApp</div>
            <div style="font-size:12px;color:var(--muted);">
              {{ $bn ? 'মোবাইল নম্বরে WhatsApp বার্তা' : 'WhatsApp to mobile number' }}
            </div>
          </div>
        </label>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    {{-- STEP 3: Template --}}
    <div class="card h-100">
      <div class="card-header">
        <h6 class="card-title mb-0">
          <span class="step-badge">3</span>
          {{ $bn ? 'মেসেজ টেমপ্লেট' : 'Message Template' }}
        </h6>
      </div>
      <div class="card-body">
        @php
        $templates = [
          1 => ['icon' => '🎁', 'name_bn' => 'বিশেষ উপহার',    'name_en' => 'Special Gift',  'desc_bn' => 'সবুজ থিম',   'desc_en' => 'Green theme'],
          2 => ['icon' => '⚡', 'name_bn' => 'এক্সক্লুসিভ',    'name_en' => 'Exclusive',     'desc_bn' => 'কমলা থিম',   'desc_en' => 'Orange theme'],
          3 => ['icon' => '🌟', 'name_bn' => 'ধন্যবাদ বার্তা', 'name_en' => 'Thank You',     'desc_bn' => 'বেগুনি থিম', 'desc_en' => 'Purple theme'],
          4 => ['icon' => '🛒', 'name_bn' => 'কেনাকাটা করুন', 'name_en' => 'Shop & Save',   'desc_bn' => 'নীল থিম',    'desc_en' => 'Blue theme'],
          5 => ['icon' => '🔥', 'name_bn' => 'সীমিত সময়',     'name_en' => 'Limited Time',  'desc_bn' => 'লাল থিম',    'desc_en' => 'Red theme'],
        ];
        @endphp
        <div class="row g-2">
          @foreach($templates as $id => $t)
          <div class="col-6">
            <label style="cursor:pointer;display:block;">
              <input type="radio" name="template" value="{{ $id }}"
                {{ $id === 1 ? 'checked' : '' }} style="display:none;" class="template-radio">
              <div class="template-card template-opt text-center" data-id="{{ $id }}">
                <span style="font-size:22px;">{{ $t['icon'] }}</span>
                <div style="font-weight:700;font-size:13px;margin-top:4px;">
                  {{ $bn ? $t['name_bn'] : $t['name_en'] }}
                </div>
                <div style="font-size:11px;color:var(--muted);">
                  {{ $bn ? $t['desc_bn'] : $t['desc_en'] }}
                </div>
              </div>
            </label>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- ── STEP 4: Recipients ──────────────────────────────────────── --}}
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h6 class="card-title mb-0">
          <span class="step-badge">4</span>
          {{ $bn ? 'প্রাপক নির্বাচন' : 'Select Recipients' }}
        </h6>
      </div>
      <div class="card-body">
        <div class="d-flex flex-wrap gap-2 mb-4">
          @php
          $segments = [
            ['value' => 'all',              'label_bn' => '👥 সকল গ্রাহক',       'label_en' => '👥 All Customers',     'count' => $allCustomers->count()],
            ['value' => 'monthly_top',      'label_bn' => '📅 মাসের সেরা',       'label_en' => '📅 Monthly Top',       'count' => $monthlyTop->count()],
            ['value' => 'weekly_top',       'label_bn' => '📆 সপ্তাহের সেরা',   'label_en' => '📆 Weekly Top',        'count' => $weeklyTop->count()],
            ['value' => 'yearly_top',       'label_bn' => '🏆 বছরের সেরা',      'label_en' => '🏆 Yearly Top',        'count' => $yearlyTop->count()],
            ['value' => 'highest_purchase', 'label_bn' => '💎 সর্বোচ্চ ক্রেতা', 'label_en' => '💎 Highest Spenders', 'count' => $highestPurchase->count()],
            ['value' => 'selected',         'label_bn' => '✔ নির্বাচিত',         'label_en' => '✔ Pick Manually',      'count' => null],
          ];
          @endphp
          @foreach($segments as $seg)
          <button type="button"
            class="segment-btn {{ $seg['value'] === 'all' ? 'active' : '' }}"
            onclick="selectSegment('{{ $seg['value'] }}', this)">
            {{ $bn ? $seg['label_bn'] : $seg['label_en'] }}
            @if($seg['count'] !== null)
              <span style="font-size:11px;opacity:.7;">({{ $seg['count'] }})</span>
            @endif
          </button>
          @endforeach
        </div>
        <input type="hidden" name="recipient_type" id="recipientType" value="all">

        <div id="previewAll" class="segment-preview">
          <p style="font-size:13px;color:var(--muted);">
            {{ $bn
              ? 'সকল ' . $allCustomers->count() . ' জন রেজিস্টার্ড গ্রাহককে কুপন পাঠানো হবে।'
              : 'Coupon will be sent to all ' . $allCustomers->count() . ' registered customers.' }}
          </p>
        </div>

        @foreach(['monthly_top' => $monthlyTop, 'weekly_top' => $weeklyTop, 'yearly_top' => $yearlyTop, 'highest_purchase' => $highestPurchase] as $key => $list)
        <div id="preview{{ ucfirst(str_replace('_', '', $key)) }}" class="segment-preview" style="display:none;">
          <div class="table-responsive" style="max-height:200px;overflow-y:auto;">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>{{ $bn ? 'নাম' : 'Name' }}</th>
                  <th>{{ $bn ? 'ইমেইল' : 'Email' }}</th>
                  <th>{{ $bn ? 'মোবাইল' : 'Mobile' }}</th>
                  <th>{{ $bn ? 'মোট ব্যয়' : 'Total Spent' }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($list as $u)
                <tr>
                  <td>{{ $u->name }}</td>
                  <td style="font-size:12px;">{{ $u->email ?? '-' }}</td>
                  <td style="font-size:12px;">{{ $u->mobile ?? '-' }}</td>
                  <td style="font-size:12px;">৳{{ number_format($u->total_spent ?? 0, 0) }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
        @endforeach

        <div id="previewSelected" class="segment-preview" style="display:none;">
          <div class="mb-2">
            <input type="text" id="customerSearch" class="form-control form-control-sm"
              placeholder="{{ $bn ? 'নাম বা ইমেইল দিয়ে খুঁজুন...' : 'Search by name or email...' }}">
          </div>
          <div id="selectedChips" class="mb-2"></div>
          <div id="customerList" class="table-responsive" style="max-height:240px;overflow-y:auto;">
            <table class="table table-sm table-hover">
              <thead>
                <tr>
                  <th style="width:36px;"></th>
                  <th>{{ $bn ? 'নাম' : 'Name' }}</th>
                  <th>{{ $bn ? 'ইমেইল' : 'Email' }}</th>
                  <th>{{ $bn ? 'মোবাইল' : 'Mobile' }}</th>
                </tr>
              </thead>
              <tbody id="customerRows">
                @foreach($allCustomers as $u)
                <tr class="customer-row"
                  data-id="{{ $u->id }}" data-name="{{ $u->name }}"
                  data-email="{{ $u->email }}" data-mobile="{{ $u->mobile }}">
                  <td>
                    <input type="checkbox" class="cust-check"
                      data-id="{{ $u->id }}" data-name="{{ $u->name }}">
                  </td>
                  <td>{{ $u->name }}</td>
                  <td style="font-size:12px;">{{ $u->email ?? '-' }}</td>
                  <td style="font-size:12px;">{{ $u->mobile ?? '-' }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div id="selectedInputs"></div>
        </div>

      </div>
    </div>
  </div>

  {{-- ── Submit ───────────────────────────────────────────────────── --}}
  <div class="col-12">
    <div class="d-flex align-items-center gap-3 flex-wrap">
      <button type="submit" class="btn btn-success btn-lg px-5" id="sendBtn">
        <i class="fas fa-paper-plane me-2"></i>
        {{ $bn ? 'কুপন পাঠান' : 'Send Coupon' }}
      </button>
      <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>
        {{ $bn ? 'বাতিল করুন' : 'Cancel' }}
      </a>
      <span id="sendingMsg" style="display:none;color:var(--muted);font-size:13px;">
        <i class="fas fa-spinner fa-spin me-1"></i>
        {{ $bn ? 'পাঠানো হচ্ছে, অনুগ্রহ করে অপেক্ষা করুন...' : 'Sending, please wait...' }}
      </span>
    </div>
  </div>

</div>
</form>
@endsection

@push('scripts')
<script>
// ── Coupon select — auto-set send_unique from preset ──────────────
document.querySelectorAll('.coupon-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.coupon-opt').forEach(c => c.classList.remove('selected'));
        this.closest('label').querySelector('.coupon-opt').classList.add('selected');

        // Auto-decide unique code based on coupon preset
        const isUnique = this.dataset.autoUnique === 'true';
        document.getElementById('sendUniqueInput').value = isUnique ? '1' : '0';
        document.getElementById('uniqueNotice').style.display = isUnique ? '' : 'none';
    });
});

// ── Template select ────────────────────────────────────────────────
document.querySelectorAll('.template-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.template-opt').forEach(c => c.classList.remove('selected'));
        this.closest('label').querySelector('.template-opt').classList.add('selected');
    });
});
const firstTpl = document.querySelector('.template-opt');
if (firstTpl) firstTpl.classList.add('selected');

// ── Segment selection ──────────────────────────────────────────────
function selectSegment(val, btn) {
    document.getElementById('recipientType').value = val;
    document.querySelectorAll('.segment-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.segment-preview').forEach(p => p.style.display = 'none');

    const map = {
        'all':              'previewAll',
        'monthly_top':      'previewMonthlytop',
        'weekly_top':       'previewWeeklytop',
        'yearly_top':       'previewYearlytop',
        'highest_purchase': 'previewHighestpurchase',
        'selected':         'previewSelected',
    };
    const el = document.getElementById(map[val]);
    if (el) el.style.display = '';
}

// ── Customer search ────────────────────────────────────────────────
document.getElementById('customerSearch')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.customer-row').forEach(row => {
        const hit = row.dataset.name.toLowerCase().includes(q)
                 || (row.dataset.email || '').toLowerCase().includes(q);
        row.style.display = hit ? '' : 'none';
    });
});

// ── Customer multi-select ──────────────────────────────────────────
const selectedUsers = new Map();
document.querySelectorAll('.cust-check').forEach(cb => {
    cb.addEventListener('change', function() {
        this.checked
            ? selectedUsers.set(this.dataset.id, this.dataset.name)
            : selectedUsers.delete(this.dataset.id);
        renderChips();
    });
});
function renderChips() {
    const chips  = document.getElementById('selectedChips');
    const inputs = document.getElementById('selectedInputs');
    chips.innerHTML = inputs.innerHTML = '';
    selectedUsers.forEach((name, id) => {
        chips.innerHTML  += `<span class="user-chip">${name} <button type="button" onclick="removeUser('${id}')">×</button></span>`;
        inputs.innerHTML += `<input type="hidden" name="user_ids[]" value="${id}">`;
    });
}
function removeUser(id) {
    selectedUsers.delete(id);
    const cb = document.querySelector(`.cust-check[data-id="${id}"]`);
    if (cb) cb.checked = false;
    renderChips();
}

// ── Form submit ────────────────────────────────────────────────────
const sendForm = document.getElementById('sendForm');
const sendBtn  = document.getElementById('sendBtn');
const sendMsg  = document.getElementById('sendingMsg');

sendForm.addEventListener('submit', function(e) {
    if (!document.querySelector('input[name="coupon_id"]:checked')) {
        e.preventDefault();
        alert('{{ $bn ? 'অনুগ্রহ করে একটি কুপন নির্বাচন করুন।' : 'Please select a coupon first.' }}');
        return;
    }
    if (!document.querySelector('input[name="channels[]"]:checked')) {
        e.preventDefault();
        alert('{{ $bn ? 'অনুগ্রহ করে অন্তত একটি পাঠানোর মাধ্যম নির্বাচন করুন।' : 'Please select at least one channel.' }}');
        return;
    }
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>{{ $bn ? 'পাঠানো হচ্ছে...' : 'Sending...' }}';
    if (sendMsg) sendMsg.style.display = 'inline';
    setTimeout(() => {
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>{{ $bn ? 'কুপন পাঠান' : 'Send Coupon' }}';
        if (sendMsg) sendMsg.style.display = 'none';
    }, 15000);
});
</script>
@endpush
