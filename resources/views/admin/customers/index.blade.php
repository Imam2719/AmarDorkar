@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'ব্যবহারকারী' : 'Users')
@section('page-title', app()->getLocale()==='bn' ? 'ব্যবহারকারী ও বার্তা' : 'Users & Messaging')

@push('styles')
<style>
/* ── Avatar ── */
.user-avatar{width:38px;height:38px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.85rem;flex-shrink:0;}
/* ── Message Modal ── */
.msg-overlay{display:none;position:fixed;inset:0;z-index:3000;background:rgba(0,0,0,.55);align-items:center;justify-content:center;}
.msg-overlay.show{display:flex;}
.msg-modal{background:var(--surface);border-radius:18px;width:100%;max-width:680px;margin:16px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.25);animation:slideUp .3s cubic-bezier(.4,0,.2,1);}
@keyframes slideUp{from{transform:translateY(30px);opacity:0}to{transform:none;opacity:1}}
.recipient-chip{display:inline-flex;align-items:center;gap:5px;background:var(--primary-light,#e8f5e9);color:var(--primary);padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;margin:3px;}
.recipient-chip button{background:none;border:none;cursor:pointer;color:var(--primary);font-size:13px;line-height:1;padding:0 0 0 2px;}
.channel-btn{flex:1;padding:9px;border:1.5px solid var(--border-color,#e2e8f0);border-radius:9px;background:var(--surface-2,#f8fafc);cursor:pointer;font-size:13px;font-weight:600;color:var(--muted,#64748b);text-align:center;transition:all .2s;}
.channel-btn.active{border-color:var(--primary);background:var(--primary-light,#e8f5e9);color:var(--primary);}
.cat-bar{height:10px;border-radius:5px;background:linear-gradient(90deg,var(--primary),#0d7a38);transition:width .6s ease;}
/* ── Section toggle ── */
.section-toggle-bar{display:flex;align-items:center;gap:8px;flex-wrap:wrap;padding:10px 0 14px;}
.section-toggle-btn{display:inline-flex;align-items:center;gap:6px;padding:5px 13px;border-radius:20px;border:1.5px solid var(--border-color,#e2e8f0);background:var(--surface,#fff);color:var(--muted,#64748b);font-size:12px;font-weight:600;cursor:pointer;transition:all .2s;user-select:none;}
.section-toggle-btn.active{border-color:var(--primary);background:var(--primary-light,#e8f5e9);color:var(--primary);}
.section-toggle-btn i{font-size:11px;}
.section-panel{transition:opacity .2s;}
.section-panel.hidden{display:none;}
/* ── Infinite Scroll ── */
#ul-scroll-loader{display:none;text-align:center;padding:18px 0;}
#ul-scroll-loader .ul-spinner{display:inline-block;width:26px;height:26px;border:3px solid var(--border-color,#e2e8f0);border-top-color:var(--primary);border-radius:50%;animation:ul-spin .7s linear infinite;}
@keyframes ul-spin{to{transform:rotate(360deg)}}
#ul-end-marker{display:none;text-align:center;padding:12px 0;font-size:12px;color:var(--muted,#64748b);}
</style>
@endpush

@section('content')

{{-- ── Section Toggle Bar ── --}}
<div class="section-toggle-bar">
  <span style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-right:4px;">
    {{ app()->getLocale()==='bn' ? 'সেকশন:' : 'Sections:' }}
  </span>
  <button class="section-toggle-btn active" onclick="toggleSection('stats',this)" data-section="stats">
    <i class="fas fa-chart-bar"></i> {{ app()->getLocale()==='bn' ? 'পরিসংখ্যান' : 'Stats' }}
  </button>
  <button class="section-toggle-btn active" onclick="toggleSection('category',this)" data-section="category">
    <i class="fas fa-tags"></i> {{ app()->getLocale()==='bn' ? 'ক্যাটাগরি' : 'Category Overview' }}
  </button>
  <button class="section-toggle-btn active" onclick="toggleSection('topbuyers',this)" data-section="topbuyers">
    <i class="fas fa-trophy"></i> {{ app()->getLocale()==='bn' ? 'শীর্ষ ক্রেতা' : 'Top Buyers' }}
  </button>
  <button class="section-toggle-btn active" onclick="toggleSection('activity',this)" data-section="activity">
    <i class="fas fa-clock"></i> {{ app()->getLocale()==='bn' ? 'সাম্প্রতিক' : 'Recent Activity' }}
  </button>
  <button class="section-toggle-btn active" onclick="toggleSection('users',this)" data-section="users">
    <i class="fas fa-users"></i> {{ app()->getLocale()==='bn' ? 'সকল গ্রাহক' : 'All Customers' }}
  </button>
</div>

{{-- ── Stats ── --}}
<div class="section-panel" id="section-stats">
<div class="stats-grid mb-4">
  @foreach([
    ['icon'=>'fa-users',      'num'=>number_format($allStats->total_customers),          'bn'=>'মোট গ্রাহক',   'en'=>'Total Customers',  'g'=>'var(--primary)'],
    ['icon'=>'fa-chart-line', 'num'=>'৳'.number_format($allStats->total_revenue),        'bn'=>'মোট আয়',      'en'=>'Total Revenue',    'g'=>'var(--success)'],
    ['icon'=>'fa-shopping-bag','num'=>number_format($allStats->total_sales),             'bn'=>'মোট বিক্রয়',  'en'=>'Total Sales',      'g'=>'var(--warning)'],
    ['icon'=>'fa-tag',        'num'=>'৳'.number_format($allStats->total_discounts_given),'bn'=>'মোট ছাড়',    'en'=>'Discounts Given',  'g'=>'var(--danger)'],
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
</div>{{-- /section-stats --}}

{{-- ── Category Overview ── --}}
<div class="section-panel" id="section-category">
@if($categoryStats->count())
<div class="card animate-fadeInUp mb-4">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h6 class="card-title bengali-text mb-0">
      <i class="fas fa-chart-bar me-2" style="color:var(--primary);"></i>
      {{ app()->getLocale()==='bn' ? 'ক্যাটাগরি অনুযায়ী কেনাকাটার চিত্র' : 'Category-wise Buying Overview' }}
    </h6>
    <span class="badge" style="background:var(--primary);color:#fff;font-size:11px;">{{ $categoryStats->count() }} {{ app()->getLocale()==='bn' ? 'টি ক্যাটাগরি' : 'categories' }}</span>
  </div>
  <div class="card-body">
    @php $maxQty = $categoryStats->max('total_qty') ?: 1; @endphp
    <div class="row g-3">
      @foreach($categoryStats as $cat)
      @php
        $pct = round(($cat->total_qty / $maxQty) * 100);
        $topProduct = $topProductsByCategory[$cat->category_id] ?? null;
        $color = $cat->color ?? '#1a9e4a';
      @endphp
      <div class="col-md-6 col-lg-4">
        <div style="background:var(--bg,#f0f4f8);border:1px solid var(--border-color,#e2e8f0);border-radius:12px;padding:14px 16px;height:100%;">
          <div class="d-flex align-items-center gap-2 mb-2">
            <div style="width:32px;height:32px;border-radius:8px;background:{{ $color }}20;border:1px solid {{ $color }}40;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="{{ $cat->icon ?? 'fas fa-tag' }}" style="color:{{ $color }};font-size:13px;"></i>
            </div>
            <div class="flex-grow-1 overflow-hidden">
              <div class="fw-bold bengali-text text-truncate" style="font-size:13px;color:var(--text);">{{ $cat->category_name }}</div>
              <div style="font-size:11px;color:var(--muted,#64748b);">{{ $cat->unique_buyers }} {{ app()->getLocale()==='bn' ? 'জন ক্রেতা' : 'buyers' }}</div>
            </div>
            <div class="text-end">
              <div class="fw-bold" style="font-size:15px;color:{{ $color }};">{{ number_format($cat->total_qty) }}</div>
              <div style="font-size:10px;color:var(--muted);">{{ app()->getLocale()==='bn' ? 'পিস' : 'units' }}</div>
            </div>
          </div>
          <div style="background:var(--border-color,#e2e8f0);height:8px;border-radius:5px;overflow:hidden;margin-bottom:8px;">
            <div class="cat-bar" style="width:{{ $pct }}%;background:linear-gradient(90deg,{{ $color }},{{ $color }}cc);"></div>
          </div>
          <div class="d-flex justify-content-between" style="font-size:11px;color:var(--muted);">
            <span>৳{{ number_format($cat->total_revenue) }} {{ app()->getLocale()==='bn' ? 'আয়' : 'revenue' }}</span>
            <span>{{ $cat->total_transactions }} {{ app()->getLocale()==='bn' ? 'লেনদেন' : 'txns' }}</span>
          </div>
          @if($topProduct)
          <div style="margin-top:8px;padding:7px 10px;background:{{ $color }}10;border-radius:8px;border-left:3px solid {{ $color }};">
            <div style="font-size:10px;color:{{ $color }};font-weight:700;margin-bottom:2px;">🏆 {{ app()->getLocale()==='bn' ? 'সেরা পণ্য' : 'Top Product' }}</div>
            <div class="bengali-text text-truncate" style="font-size:12px;color:var(--text);font-weight:600;">{{ Str::limit($topProduct->product_name, 30) }}</div>
            <div style="font-size:11px;color:var(--muted);">{{ number_format($topProduct->total_sold) }} {{ app()->getLocale()==='bn' ? 'বিক্রি' : 'sold' }} · ৳{{ number_format($topProduct->revenue) }}</div>
          </div>
          @endif
        </div>
      </div>
      @endforeach
    </div>
  </div>
</div>
@endif
</div>{{-- /section-category --}}

<div class="row">
  {{-- Top Customers --}}
  <div class="section-panel col-md-4 mb-4" id="section-topbuyers">
    <div class="card animate-fadeInUp h-100">
      <div class="card-header">
        <h6 class="card-title bengali-text mb-0"><i class="fas fa-trophy me-2 text-warning"></i>{{ app()->getLocale()==='bn' ? 'শীর্ষ ক্রেতা' : 'Top Buyers' }}</h6>
      </div>
      <div class="card-body p-0">
        @forelse($topCustomers as $i=>$c)
        <div class="d-flex align-items-center p-3 {{ $i < $topCustomers->count()-1 ? 'border-bottom' : '' }}" style="border-color:var(--border-color)!important">
          <div class="me-3">
            <div class="user-avatar" style="background:{{ ['#1a9e4a','#3b82f6','#f59e0b','#ef4444','#8b5cf6'][$i % 5] }}">{{ $i+1 }}</div>
          </div>
          <div class="flex-grow-1 overflow-hidden">
            <div class="bengali-text fw-semibold text-truncate">{{ $c->customer_name }}</div>
            <small class="text-muted">{{ $c->purchase_count }} {{ app()->getLocale()==='bn' ? 'বার' : 'purchases' }}</small>
          </div>
          <div class="text-end ms-2 flex-shrink-0">
            <div class="text-success fw-bold" style="font-size:13px;">৳{{ number_format($c->total_spent) }}</div>
          </div>
        </div>
        @empty
        <div class="p-3 text-center text-muted bengali-text small">{{ app()->getLocale()==='bn' ? 'কোন তথ্য নেই' : 'No data' }}</div>
        @endforelse
      </div>
    </div>
  </div>

  {{-- Recent Activity --}}
  <div class="section-panel col-md-8 mb-4" id="section-activity">
    <div class="card animate-fadeInUp h-100">
      <div class="card-header">
        <h6 class="card-title bengali-text mb-0"><i class="fas fa-clock me-2"></i>{{ app()->getLocale()==='bn' ? 'সাম্প্রতিক কার্যক্রম' : 'Recent Activity' }}</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0" style="font-size:13px;">
            <thead>
              <tr>
                <th>{{ app()->getLocale()==='bn' ? 'গ্রাহক' : 'Customer' }}</th>
                <th>{{ app()->getLocale()==='bn' ? 'পণ্য' : 'Product' }}</th>
                <th>{{ app()->getLocale()==='bn' ? 'মূল্য' : 'Amount' }}</th>
                <th>{{ app()->getLocale()==='bn' ? 'তারিখ' : 'Date' }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentActivity as $a)
              <tr>
                <td>
                  <div class="bengali-text fw-semibold" style="font-size:13px;">{{ $a->customer_name }}</div>
                  <small class="text-muted">{{ $a->customer_email }}</small>
                </td>
                <td class="bengali-text" style="font-size:12px;">{{ Str::limit($a->product_name, 28) }}</td>
                <td><strong class="text-success">৳{{ number_format($a->final_amount) }}</strong></td>
                <td style="font-size:12px;">{{ $a->selling_time?->format('M d, H:i') }}</td>
              </tr>
              @empty
              <tr><td colspan="4" class="text-center py-3 text-muted bengali-text small">{{ app()->getLocale()==='bn' ? 'কোন কার্যক্রম নেই' : 'No activity' }}</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ── User List + Messaging ── --}}
<div class="section-panel" id="section-users">
<div class="card animate-fadeInUp">
  <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <h6 class="card-title mb-0 bengali-text">
      <i class="fas fa-users me-2" style="color:var(--primary);"></i>
      {{ app()->getLocale()==='bn' ? 'নিবন্ধিত ব্যবহারকারী' : 'Registered Users' }}
      <span class="badge ms-2" style="background:var(--primary);color:#fff;font-size:11px;" id="ul-total-badge">{{ number_format($total) }}</span>
    </h6>
    <div class="d-flex gap-2 align-items-center flex-wrap">
      <form method="GET" class="d-flex gap-2">
        <input type="text" class="form-control form-control-sm" name="search" style="min-width:200px;"
          placeholder="{{ app()->getLocale()==='bn' ? 'নাম/ইমেইল/মোবাইল...' : 'Name / email / mobile...' }}"
          value="{{ $search }}">
        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
      </form>
      {{-- Send to ALL --}}
      <button onclick="openMsgModal('all')" class="btn btn-sm" style="background:var(--primary);color:#fff;">
        <i class="fas fa-paper-plane me-1"></i>{{ app()->getLocale()==='bn' ? 'সবাইকে বার্তা' : 'Message All' }}
      </button>
      {{-- Send to selected --}}
      <button onclick="openMsgModal('selected')" class="btn btn-sm btn-outline-primary" id="btnMsgSelected" disabled>
        <i class="fas fa-check-square me-1"></i>{{ app()->getLocale()==='bn' ? 'নির্বাচিতদের বার্তা' : 'Message Selected' }}
        <span id="selectedCount" class="badge ms-1" style="background:var(--primary);"></span>
      </button>
    </div>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0" style="font-size:13px;">
        <thead style="position:sticky;top:0;z-index:2;background:var(--surface,#fff);">
          <tr>
            <th style="width:40px;">
              <input type="checkbox" id="checkAll" onchange="toggleAllChecks(this)" style="accent-color:var(--primary);">
            </th>
            <th>{{ app()->getLocale()==='bn' ? 'ব্যবহারকারী' : 'User' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'মোবাইল' : 'Mobile' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'মোট অর্ডার' : 'Orders' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'মোট খরচ' : 'Total Spent' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'যোগ দিয়েছেন' : 'Joined' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'শেষ অর্ডার' : 'Last Order' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'বার্তা' : 'Message' }}</th>
          </tr>
        </thead>
        <tbody id="ulTableBody">
          @forelse($users as $u)
          <tr>
            <td>
              <input type="checkbox" class="user-check" value="{{ $u->id }}"
                data-name="{{ $u->name }}" data-email="{{ $u->email }}"
                onchange="updateSelectedCount()"
                style="accent-color:var(--primary);">
            </td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="user-avatar" style="font-size:.75rem;">{{ strtoupper(substr($u->name,0,1)) }}</div>
                <div>
                  <div class="bengali-text fw-semibold">{{ $u->name }}</div>
                  <small class="text-muted">{{ $u->email }}</small>
                </div>
              </div>
            </td>
            <td>{{ $u->mobile ?? '<span class="text-muted">—</span>' }}</td>
            <td><strong>{{ $u->total_orders }}</strong></td>
            <td><strong class="text-success">৳{{ number_format($u->total_spent) }}</strong></td>
            <td style="font-size:12px;color:var(--muted);">{{ $u->created_at->format('d M Y') }}</td>
            <td style="font-size:12px;color:var(--muted);">
              {{ $u->last_order_date ? \Carbon\Carbon::parse($u->last_order_date)->format('d M Y') : '—' }}
            </td>
            <td>
              <button onclick="openMsgModal('single',{{ $u->id }},'{{ addslashes($u->name) }}','{{ $u->email }}')"
                class="btn btn-sm" style="background:var(--primary-light,#e8f5e9);color:var(--primary);border:1px solid var(--primary);padding:4px 10px;font-size:11px;">
                <i class="fas fa-envelope"></i>
              </button>
              <a href="{{ route('admin.customers.orders', $u->id) }}" class="btn btn-sm ms-1" style="background:rgba(2,132,199,.12);color:#38bdf8;border:1px solid rgba(2,132,199,.4);padding:4px 10px;font-size:11px;" title="{{ app()->getLocale()==='bn' ? 'অর্ডার দেখুন' : 'View Orders' }}">
                <i class="fas fa-shopping-bag"></i>
              </a>
            </td>
          </tr>
          @empty
          <tr id="ulEmptyRow"><td colspan="8" class="text-center py-5 text-muted bengali-text">{{ app()->getLocale()==='bn' ? 'কোন ব্যবহারকারী পাওয়া যায়নি' : 'No users found' }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Infinite scroll loader --}}
    <div id="ul-scroll-loader">
      <div class="ul-spinner"></div>
    </div>
    <div id="ul-end-marker">
      <i class="fas fa-check-circle me-1" style="color:var(--primary);"></i>
      {{ app()->getLocale()==='bn' ? 'সকল ব্যবহারকারী লোড হয়েছে' : 'All users loaded' }}
      &nbsp;·&nbsp; {{ app()->getLocale()==='bn' ? 'মোট' : 'Total' }} <strong>{{ number_format($total) }}</strong>
    </div>

  </div>
</div>
</div>{{-- /section-users --}}
@endsection

{{-- ═══ MESSAGE MODAL ═══ --}}
@push('modals')
<div class="msg-overlay" id="msgOverlay">
  <div class="msg-modal">

    {{-- Header --}}
    <div style="padding:20px 24px 14px;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;">
      <div>
        <h5 class="mb-0 bengali-text fw-bold" style="font-size:16px;">
          <i class="fas fa-paper-plane me-2" style="color:var(--primary);"></i>
          {{ app()->getLocale()==='bn' ? 'বার্তা পাঠান' : 'Send Message' }}
        </h5>
        <small class="text-muted" id="msgModalSubtitle"></small>
      </div>
      <button onclick="closeMsgModal()" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);width:32px;height:32px;border-radius:7px;">×</button>
    </div>

    <div style="padding:20px 24px;">

      {{-- Recipients display --}}
      <div id="recipientDisplay" style="background:var(--surface-2,#f8fafc);border:1px solid var(--border-color);border-radius:10px;padding:10px 14px;margin-bottom:16px;min-height:44px;">
        <div id="recipientChips" style="display:flex;flex-wrap:wrap;gap:4px;align-items:center;">
          <span id="recipientLabel" style="font-size:13px;color:var(--muted);"></span>
        </div>
      </div>

      {{-- Channel --}}
      <div style="margin-bottom:16px;">
        <label style="display:block;font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;" class="bengali-text">{{ app()->getLocale()==='bn' ? 'মাধ্যম' : 'Channel' }}</label>
        <div style="display:flex;gap:8px;">
          <button type="button" onclick="setChannel('email')" id="ch_email" class="channel-btn active">
            <i class="fas fa-envelope me-1"></i>Email
          </button>
          <button type="button" onclick="setChannel('sms')" id="ch_sms" class="channel-btn">
            <i class="fas fa-sms me-1"></i>SMS
          </button>
          <button type="button" onclick="setChannel('both')" id="ch_both" class="channel-btn">
            <i class="fas fa-layer-group me-1"></i>{{ app()->getLocale()==='bn' ? 'দুটোই' : 'Both' }}
          </button>
        </div>
      </div>

      {{-- Subject --}}
      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;" class="bengali-text">{{ app()->getLocale()==='bn' ? 'বিষয়' : 'Subject' }}</label>
        <input type="text" id="msgSubject" maxlength="255"
          style="width:100%;padding:10px 14px;border:1.5px solid var(--border-color);border-radius:9px;font-size:14px;background:var(--bg,#f0f4f8);color:var(--text);"
          placeholder="{{ app()->getLocale()==='bn' ? 'বার্তার বিষয়...' : 'Message subject...' }}"
          onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-color)'">
      </div>

      {{-- Body --}}
      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;" class="bengali-text">{{ app()->getLocale()==='bn' ? 'বার্তা' : 'Message Body' }}</label>
        <textarea id="msgBody" rows="5" maxlength="2000"
          style="width:100%;padding:10px 14px;border:1.5px solid var(--border-color);border-radius:9px;font-size:14px;background:var(--bg,#f0f4f8);color:var(--text);resize:vertical;font-family:inherit;"
          placeholder="{{ app()->getLocale()==='bn' ? 'এখানে বার্তা লিখুন...' : 'Write your message here...' }}"
          onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-color)'"></textarea>
        <div style="text-align:right;font-size:11px;color:var(--muted);margin-top:3px;"><span id="bodyCharCount">0</span>/2000</div>
      </div>

      {{-- Notes (internal) --}}
      <div style="margin-bottom:20px;">
        <label style="display:block;font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;" class="bengali-text">{{ app()->getLocale()==='bn' ? 'অভ্যন্তরীণ নোট (ঐচ্ছিক)' : 'Internal Note (optional)' }}</label>
        <input type="text" id="msgNotes" maxlength="255"
          style="width:100%;padding:9px 14px;border:1.5px solid var(--border-color);border-radius:9px;font-size:13px;background:var(--bg,#f0f4f8);color:var(--text);"
          placeholder="{{ app()->getLocale()==='bn' ? 'কারণ বা নোট...' : 'Reason or note...' }}"
          onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-color)'">
      </div>

      {{-- Hidden inputs --}}
      <input type="hidden" id="msgRecipients" value="all">
      <input type="hidden" id="msgUserIds" value="">
      <input type="hidden" id="msgChannel" value="email">

      {{-- Result --}}
      <div id="msgResult" style="display:none;padding:12px 16px;border-radius:9px;font-size:13px;margin-bottom:14px;"></div>

      {{-- Buttons --}}
      <div style="display:flex;gap:10px;">
        <button onclick="closeMsgModal()" style="flex:1;padding:11px;border:1.5px solid var(--border-color);background:transparent;border-radius:10px;font-size:14px;cursor:pointer;color:var(--text);">
          {{ app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel' }}
        </button>
        <button onclick="sendMessage()" id="sendBtn" style="flex:2;padding:11px;background:var(--primary);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .2s;">
          <i class="fas fa-paper-plane"></i>
          <span id="sendBtnText">{{ app()->getLocale()==='bn' ? 'পাঠান' : 'Send Message' }}</span>
        </button>
      </div>

    </div>
  </div>
</div>
@endpush

@push('scripts')
<script>
(function(){
  const IS_BN    = {{ app()->getLocale() === 'bn' ? 'true' : 'false' }};
  const BASE_URL = '{{ route("admin.customers.index") }}';
  const ORDERS_BASE = '{{ url("/admin/customers") }}';

  // ── Section Toggle ────────────────────────────────────────
  const SECTION_KEY = 'amardorkar_admin_sections';

  window.toggleSection = function(id, btn) {
    const panel = document.getElementById('section-' + id);
    if (!panel) return;
    const isHidden = panel.classList.toggle('hidden');
    btn.classList.toggle('active', !isHidden);
    saveSectionState();
  };

  function saveSectionState() {
    const state = {};
    document.querySelectorAll('.section-toggle-btn').forEach(btn => {
      state[btn.dataset.section] = btn.classList.contains('active');
    });
    try { localStorage.setItem(SECTION_KEY, JSON.stringify(state)); } catch(e) {}
  }

  function loadSectionState() {
    let state;
    try { state = JSON.parse(localStorage.getItem(SECTION_KEY)); } catch(e) {}
    if (!state) return;
    document.querySelectorAll('.section-toggle-btn').forEach(btn => {
      const sid = btn.dataset.section;
      if (state[sid] === false) {
        const panel = document.getElementById('section-' + sid);
        if (panel) panel.classList.add('hidden');
        btn.classList.remove('active');
      }
    });
  }
  loadSectionState();

  // ── Infinite Scroll — User List ──────────────────────────
  let currentPage = 2;   // প্রথম 20টা server-side এসেছে
  let isLoading   = false;
  let hasMore     = {{ $hasMore ? 'true' : 'false' }};

  const tbody     = document.getElementById('ulTableBody');
  const loader    = document.getElementById('ul-scroll-loader');
  const endMarker = document.getElementById('ul-end-marker');

  if (!hasMore) endMarker.style.display = 'block';

  function buildUserRows(users) {
    return users.map(u => {
      const initial   = u.initial || '?';
      const mobile    = u.mobile || '<span style="color:var(--muted)">—</span>';
      const lastOrder = u.last_order_date || '—';
      const ordersUrl = `${ORDERS_BASE}/${u.id}/orders`;

      return `<tr>
        <td>
          <input type="checkbox" class="user-check" value="${u.id}"
            data-name="${u.name.replace(/"/g,'&quot;')}" data-email="${u.email}"
            onchange="updateSelectedCount()"
            style="accent-color:var(--primary);">
        </td>
        <td>
          <div class="d-flex align-items-center gap-2">
            <div class="user-avatar" style="font-size:.75rem;">${initial}</div>
            <div>
              <div class="bengali-text fw-semibold">${u.name}</div>
              <small class="text-muted">${u.email}</small>
            </div>
          </div>
        </td>
        <td>${mobile}</td>
        <td><strong>${u.total_orders}</strong></td>
        <td><strong class="text-success">৳${u.total_spent}</strong></td>
        <td style="font-size:12px;color:var(--muted);">${u.joined}</td>
        <td style="font-size:12px;color:var(--muted);">${lastOrder}</td>
        <td>
          <button onclick="openMsgModal('single',${u.id},'${u.name.replace(/'/g,"\\'")}','${u.email}')"
            class="btn btn-sm" style="background:var(--primary-light,#e8f5e9);color:var(--primary);border:1px solid var(--primary);padding:4px 10px;font-size:11px;">
            <i class="fas fa-envelope"></i>
          </button>
          <a href="${ordersUrl}" class="btn btn-sm ms-1" style="background:rgba(2,132,199,.12);color:#38bdf8;border:1px solid rgba(2,132,199,.4);padding:4px 10px;font-size:11px;">
            <i class="fas fa-shopping-bag"></i>
          </a>
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

      // সার্ভার-সাইড empty row সরাও
      const emptyRow = document.getElementById('ulEmptyRow');
      if (emptyRow) emptyRow.remove();

      if (data.users && data.users.length) {
        tbody.insertAdjacentHTML('beforeend', buildUserRows(data.users));
      }

      hasMore     = data.has_more;
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
  sentinel.id    = 'ul-sentinel';
  sentinel.style.height = '1px';
  endMarker.parentNode.insertBefore(sentinel, endMarker);

  const observer = new IntersectionObserver(entries => {
    if (entries[0].isIntersecting) loadMore();
  }, { rootMargin: '200px' });
  observer.observe(sentinel);

  // ── CSRF ─────────────────────────────────────────────────
  const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
  let selectedChannel = 'email';
  let selectedUserIds  = [];

  // ── Open modal ───────────────────────────────────────────
  window.openMsgModal = function(mode, uid, uname, uemail) {
    selectedUserIds = [];

    if (mode === 'all') {
      document.getElementById('msgRecipients').value = 'all';
      document.getElementById('recipientChips').innerHTML =
        `<span id="recipientLabel" style="font-size:13px;font-weight:600;color:var(--primary);"><i class="fas fa-users me-1"></i>${IS_BN ? 'সকল ব্যবহারকারী' : 'All Users'}</span>`;
      document.getElementById('msgModalSubtitle').textContent =
        IS_BN ? 'সকলের কাছে একসাথে বার্তা' : 'Broadcasting to all users';

    } else if (mode === 'single') {
      selectedUserIds = [uid];
      document.getElementById('msgRecipients').value = 'custom';
      document.getElementById('msgUserIds').value    = uid;
      document.getElementById('recipientChips').innerHTML =
        `<span class="recipient-chip"><i class="fas fa-user me-1"></i>${uname} &lt;${uemail}&gt;</span>`;
      document.getElementById('msgModalSubtitle').textContent = uemail;

    } else if (mode === 'selected') {
      const checks = document.querySelectorAll('.user-check:checked');
      if (!checks.length) { alert(IS_BN ? 'কাউকে নির্বাচন করুন' : 'Select at least one user'); return; }
      selectedUserIds = [];
      let chips = '';
      checks.forEach(c => {
        selectedUserIds.push(c.value);
        chips += `<span class="recipient-chip"><i class="fas fa-user me-1"></i>${c.dataset.name}<button onclick="removeChip(this,'${c.value}')">×</button></span>`;
      });
      document.getElementById('msgRecipients').value = 'custom';
      document.getElementById('msgUserIds').value    = selectedUserIds.join(',');
      document.getElementById('recipientChips').innerHTML = chips;
      document.getElementById('msgModalSubtitle').textContent = `${selectedUserIds.length} ${IS_BN ? 'জন নির্বাচিত' : 'selected'}`;
    }

    document.getElementById('msgSubject').value  = '';
    document.getElementById('msgBody').value     = '';
    document.getElementById('msgNotes').value    = '';
    document.getElementById('bodyCharCount').textContent = '0';
    document.getElementById('msgResult').style.display  = 'none';
    setChannel('email');
    document.getElementById('msgOverlay').classList.add('show');
    document.body.style.overflow = 'hidden';
  };

  window.closeMsgModal = function() {
    document.getElementById('msgOverlay').classList.remove('show');
    document.body.style.overflow = '';
  };

  window.setChannel = function(ch) {
    selectedChannel = ch;
    document.getElementById('msgChannel').value = ch;
    ['email','sms','both'].forEach(c => {
      document.getElementById('ch_'+c).classList.toggle('active', c === ch);
    });
  };

  window.removeChip = function(btn, uid) {
    btn.closest('.recipient-chip').remove();
    selectedUserIds = selectedUserIds.filter(id => id != uid);
    document.getElementById('msgUserIds').value = selectedUserIds.join(',');
    if (!selectedUserIds.length) closeMsgModal();
  };

  document.getElementById('msgBody').addEventListener('input', function() {
    document.getElementById('bodyCharCount').textContent = this.value.length;
  });

  window.sendMessage = function() {
    const subject    = document.getElementById('msgSubject').value.trim();
    const body       = document.getElementById('msgBody').value.trim();
    const recipients = document.getElementById('msgRecipients').value;
    const userIds    = document.getElementById('msgUserIds').value;
    const notes      = document.getElementById('msgNotes').value.trim();

    if (!subject) { showMsgResult(IS_BN ? 'বিষয় লিখুন' : 'Please enter a subject', 'error'); return; }
    if (!body)    { showMsgResult(IS_BN ? 'বার্তা লিখুন' : 'Please enter the message body', 'error'); return; }

    const payload = new FormData();
    payload.append('_token',     CSRF);
    payload.append('subject',    subject);
    payload.append('body',       body);
    payload.append('channel',    selectedChannel);
    payload.append('recipients', recipients);
    payload.append('notes',      notes);

    if (recipients === 'custom') {
      userIds.split(',').forEach(id => payload.append('user_ids[]', id));
    }

    const btn     = document.getElementById('sendBtn');
    const btnText = document.getElementById('sendBtnText');
    btn.disabled  = true;
    btnText.textContent = IS_BN ? 'পাঠানো হচ্ছে...' : 'Sending...';

    fetch('{{ route("admin.users.message") }}', { method: 'POST', body: payload })
      .then(r => r.json())
      .then(d => {
        if (d.success) {
          showMsgResult(
            (d.sent > 0 ? `✅ ${d.sent} ${IS_BN ? 'জনের কাছে পাঠানো হয়েছে' : 'message(s) sent'}` : '') +
            (d.failed > 0 ? ` ⚠️ ${d.failed} ${IS_BN ? 'টি ব্যর্থ' : 'failed'}` : ''),
            'success'
          );
          setTimeout(closeMsgModal, 1800);
        } else {
          showMsgResult(d.message || 'Error', 'error');
        }
      })
      .catch(() => showMsgResult('Network error', 'error'))
      .finally(() => {
        btn.disabled = false;
        btnText.textContent = IS_BN ? 'পাঠান' : 'Send Message';
      });
  };

  function showMsgResult(msg, type) {
    const el = document.getElementById('msgResult');
    el.style.display    = 'block';
    el.style.background = type === 'success' ? '#d1fae5' : '#fee2e2';
    el.style.color      = type === 'success' ? '#065f46' : '#991b1b';
    el.innerHTML        = msg;
  }

  // ── Checkbox helpers ────────────────────────────────────
  window.toggleAllChecks = function(masterCb) {
    document.querySelectorAll('.user-check').forEach(c => c.checked = masterCb.checked);
    updateSelectedCount();
  };

  window.updateSelectedCount = function() {
    const n = document.querySelectorAll('.user-check:checked').length;
    document.getElementById('selectedCount').textContent = n || '';
    document.getElementById('btnMsgSelected').disabled   = !n;
    document.getElementById('checkAll').checked =
      n > 0 && n === document.querySelectorAll('.user-check').length;
  };

  // Close on backdrop
  document.getElementById('msgOverlay').addEventListener('click', function(e){
    if (e.target === this) closeMsgModal();
  });

})();
</script>
@endpush