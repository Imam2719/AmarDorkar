@extends('layouts.app')
@section('title', app()->getLocale()==='bn' ? 'আমার ড্যাশবোর্ড' : 'My Dashboard')

@section('content')
@php
    $userId = auth()->id();
    $user   = auth()->user();

    $totalOrders     = \App\Models\Order::where('user_id', $userId)->count();
    $pendingOrders   = \App\Models\Order::where('user_id', $userId)->where('status','pending')->count();
    $deliveredOrders = \App\Models\Order::where('user_id', $userId)->where('status','delivered')->count();
    $totalSpent      = \App\Models\Order::where('user_id', $userId)->whereIn('status',['delivered','processing'])->sum('total_amount');

    $recentOrders = \App\Models\Order::where('user_id', $userId)->with('items')->latest()->take(20)->get();

    $myReviews          = \App\Models\ProductReview::where('user_id', $userId)->with('product.primaryImage')->latest()->get();
    $reviewedProductIds = $myReviews->pluck('product_id')->toArray();
    $purchasedProducts  = \App\Models\OrderItem::whereHas('order', fn($q) => $q->where('user_id', $userId)->where('status','delivered'))
        ->with('product.primaryImage')->get()->unique('product_id');
@endphp

<style>
/* ══ PREMIUM USER DASHBOARD ══ */
.db-wrap{display:flex;gap:0;min-height:calc(100vh - 135px);max-width:1280px;margin:0 auto;padding:28px clamp(12px,2vw,28px);}
.db-sidebar{width:260px;flex-shrink:0;margin-right:24px;}
.db-sidebar-card{background:var(--surface);border:1px solid var(--border);border-radius:20px;overflow:hidden;position:sticky;top:90px;box-shadow:0 4px 24px rgba(0,0,0,.07);}
.db-avatar{background:linear-gradient(145deg,#1a9e4a 0%,#0d7a38 50%,#0a5a2a 100%);padding:32px 20px 24px;text-align:center;position:relative;overflow:hidden;}
.db-avatar::before{content:'';position:absolute;top:-30px;right:-30px;width:120px;height:120px;background:rgba(255,255,255,.07);border-radius:50%;}
.db-avatar::after{content:'';position:absolute;bottom:-20px;left:-20px;width:80px;height:80px;background:rgba(255,255,255,.05);border-radius:50%;}
.db-avatar-circle{width:72px;height:72px;border-radius:50%;background:rgba(255,255,255,.22);border:3px solid rgba(255,255,255,.55);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:28px;font-weight:800;color:#fff;position:relative;z-index:1;box-shadow:0 4px 16px rgba(0,0,0,.2);}
.db-avatar-name{font-size:16px;font-weight:700;color:#fff;margin-bottom:3px;position:relative;z-index:1;}
.db-avatar-email{font-size:11px;color:rgba(255,255,255,.7);word-break:break-all;position:relative;z-index:1;}
.db-avatar-badge{display:inline-block;background:rgba(255,255,255,.18);color:#fff;font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;margin-top:8px;border:1px solid rgba(255,255,255,.25);position:relative;z-index:1;}
.db-nav{padding:8px 0;}
.db-nav-item{display:flex;align-items:center;gap:12px;padding:11px 20px;color:var(--text);font-size:13px;font-weight:500;cursor:pointer;border-left:3px solid transparent;transition:all .2s;text-decoration:none;}
.db-nav-item:hover{background:rgba(26,158,74,.06);color:var(--primary);border-left-color:var(--primary);}
.db-nav-item i{width:18px;text-align:center;font-size:14px;}
.db-nav-divider{height:1px;background:var(--border);margin:6px 16px;}
.db-nav-logout{display:flex;align-items:center;gap:12px;padding:11px 20px;color:#ef4444;font-size:13px;font-weight:500;cursor:pointer;width:100%;background:none;border:none;text-align:left;transition:all .2s;border-left:3px solid transparent;}
.db-nav-logout:hover{background:#fee2e2;border-left-color:#ef4444;}
.db-main{flex:1;min-width:0;}

/* Welcome Banner */
.db-welcome{background:linear-gradient(135deg,#1a9e4a 0%,#0d7a38 55%,#0a5a2a 100%);border-radius:20px;padding:28px 32px;margin-bottom:22px;color:#fff;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(26,158,74,.25);}
.db-welcome::before{content:'';position:absolute;top:-50px;right:-50px;width:200px;height:200px;background:rgba(255,255,255,.06);border-radius:50%;}
.db-welcome::after{content:'';position:absolute;bottom:-40px;left:30%;width:150px;height:150px;background:rgba(255,255,255,.04);border-radius:50%;}
.db-welcome-text{position:relative;z-index:1;}
.db-welcome-btn{position:relative;z-index:1;background:rgba(255,255,255,.18);color:#fff;border:1.5px solid rgba(255,255,255,.4);padding:10px 22px;border-radius:12px;text-decoration:none;font-size:13px;font-weight:700;display:inline-flex;align-items:center;gap:8px;transition:background .2s,transform .2s;backdrop-filter:blur(4px);}
.db-welcome-btn:hover{background:rgba(255,255,255,.28);transform:translateY(-2px);color:#fff;text-decoration:none;}

/* Stats */
.db-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px;}
.db-stat{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:18px 16px;position:relative;overflow:hidden;transition:transform .2s,box-shadow .2s;}
.db-stat:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.1);}
.db-stat::after{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:16px 16px 0 0;}
.db-stat:nth-child(1)::after{background:linear-gradient(90deg,#1a9e4a,#0d7a38);}
.db-stat:nth-child(2)::after{background:linear-gradient(90deg,#f59e0b,#d97706);}
.db-stat:nth-child(3)::after{background:linear-gradient(90deg,#3b82f6,#1d4ed8);}
.db-stat:nth-child(4)::after{background:linear-gradient(90deg,#8b5cf6,#6d28d9);}
.db-stat-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:10px;font-size:16px;}
.db-stat-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--muted);margin-bottom:4px;}
.db-stat-val{font-size:26px;font-weight:800;line-height:1;}

/* Quick Actions */
.db-actions{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;}
.db-action-card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:18px 14px;display:flex;flex-direction:column;align-items:center;gap:10px;cursor:pointer;transition:all .25s;text-align:center;position:relative;overflow:hidden;}
.db-action-card::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(26,158,74,.04),transparent);opacity:0;transition:opacity .25s;}
.db-action-card:hover{border-color:var(--primary);transform:translateY(-4px);box-shadow:0 10px 28px rgba(0,0,0,.1);}
.db-action-card:hover::before{opacity:1;}
.db-action-icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:20px;transition:transform .25s;}
.db-action-card:hover .db-action-icon{transform:scale(1.12);}

/* Popups */
.db-popup-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:3000;align-items:flex-end;justify-content:center;backdrop-filter:blur(2px);}
.db-popup-overlay.show{display:flex;}
.db-popup{background:var(--surface);border-radius:24px 24px 0 0;width:100%;max-width:660px;max-height:88vh;overflow-y:auto;animation:dbSlideUp .3s cubic-bezier(.4,0,.2,1);}
.db-popup-header{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--border);position:sticky;top:0;background:var(--surface);z-index:2;}
.db-popup-handle{width:42px;height:4px;background:var(--border);border-radius:2px;margin:12px auto 0;}
.db-popup-body{padding:20px 22px;}
.db-status{display:inline-flex;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;}
.star-filled{color:#f59e0b;}
.star-empty{color:#d1d5db;}
@keyframes dbSlideUp{from{transform:translateY(50px);opacity:0}to{transform:none;opacity:1}}
@media(max-width:768px){
    .db-wrap{flex-direction:column;padding:16px 12px;}
    .db-sidebar{width:100%;margin-right:0;margin-bottom:16px;}
    .db-sidebar-card{position:static;}
    .db-stats{grid-template-columns:repeat(2,1fr);}
    .db-actions{grid-template-columns:repeat(3,1fr);}
    .db-welcome{padding:20px 20px;}
}
@media(max-width:400px){
    .db-actions{grid-template-columns:repeat(2,1fr);}
    .db-stats{grid-template-columns:repeat(2,1fr);}
}
</style>

<div class="db-wrap">

  {{-- SIDEBAR --}}
  <aside class="db-sidebar">
    <div class="db-sidebar-card">
      <div class="db-avatar">
        <div class="db-avatar-circle">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
        <div class="db-avatar-name bengali-text">{{ $user->name }}</div>
        <div class="db-avatar-email">{{ $user->email }}</div>
        <div class="db-avatar-badge"><i class="fas fa-star me-1" style="color:#fbbf24;font-size:9px;"></i>{{ app()->getLocale()=='bn' ? 'ভেরিফাইড সদস্য' : 'Verified Member' }}</div>
      </div>
      <div class="db-nav">
        <div class="db-nav-item" onclick="openPopup('popupOrders')">
          <i class="fas fa-shopping-bag" style="color:var(--primary);"></i>
          <span class="bengali-text">{{ app()->getLocale()==='bn' ? 'আমার অর্ডার' : 'My Orders' }}</span>
          @if($totalOrders > 0)
          <span style="margin-left:auto;background:var(--primary);color:#fff;font-size:10px;padding:1px 7px;border-radius:20px;">{{ $totalOrders }}</span>
          @endif
        </div>
        <a href="{{ route('orders.index') }}" class="db-nav-item" style="text-decoration:none;">
          <i class="fas fa-list-alt" style="color:#0284c7;"></i>
          <span class="bengali-text">{{ app()->getLocale()==='bn' ? 'সব অর্ডার ও ট্র্যাকিং' : 'All Orders & Tracking' }}</span>
        </a>
        <div class="db-nav-item" onclick="openPopup('popupHistory')">
          <i class="fas fa-history" style="color:#f59e0b;"></i>
          <span class="bengali-text">{{ app()->getLocale()==='bn' ? 'কেনার ইতিহাস' : 'Purchase History' }}</span>
        </div>
        <div class="db-nav-item" onclick="openPopup('popupReviews')">
          <i class="fas fa-star" style="color:#7c3aed;"></i>
          <span class="bengali-text">{{ app()->getLocale()==='bn' ? 'আমার রিভিউ' : 'My Reviews' }}</span>
        </div>
        <div class="db-nav-divider"></div>

        {{-- পণ্য বিভাগ section --}}
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--muted);padding:10px 18px 4px;" class="bengali-text">
            {{ app()->getLocale()==='bn' ? 'পণ্য বিভাগ' : 'Shop' }}
        </div>
        @php $shopCategories = \App\Models\Category::where('status', true)->orderBy('sort_order')->take(8)->get(); @endphp
        @forelse($shopCategories as $cat)
        <a href="{{ route('search', ['category' => $cat->id]) }}" style="display:flex;align-items:center;gap:10px;padding:9px 18px;color:var(--text);text-decoration:none;font-size:12.5px;font-weight:500;border-left:3px solid transparent;transition:all .2s;" class="bengali-text db-nav-item" onmouseover="this.style.borderLeftColor='{{ $cat->color ?? 'var(--primary)' }}'" onmouseout="this.style.borderLeftColor='transparent'">
            <div style="width:22px;height:22px;border-radius:5px;background:{{ $cat->color ?? 'var(--primary)' }}20;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="{{ $cat->icon ?? 'fas fa-tag' }}" style="font-size:11px;color:{{ $cat->color ?? 'var(--primary)' }};"></i>
            </div>
            {{ app()->getLocale()==='bn' ? $cat->name_bn : $cat->name_en }}
        </a>
        @empty
        @endforelse
        <div class="db-nav-divider"></div>
        <div class="db-nav-item" onclick="openPopup('popupProfile')">
          <i class="fas fa-user-circle" style="color:#0284c7;"></i>
          <span class="bengali-text">{{ app()->getLocale()==='bn' ? 'প্রোফাইল' : 'Profile' }}</span>
        </div>
        <div class="db-nav-divider"></div>
        <button class="db-nav-logout" onclick="openPopup('popupLogout')">
          <i class="fas fa-sign-out-alt"></i>
          <span class="bengali-text">{{ app()->getLocale()==='bn' ? 'লগআউট' : 'Logout' }}</span>
        </button>
      </div>
    </div>
  </aside>

  {{-- MAIN --}}
  <main class="db-main">

    {{-- Welcome --}}
    <div class="db-welcome">
      <div class="db-welcome-text">
        <div style="display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.15);border-radius:20px;padding:4px 12px;font-size:11px;font-weight:700;letter-spacing:.3px;margin-bottom:10px;border:1px solid rgba(255,255,255,.2);">          <i class="fas fa-leaf"></i> {{ app()->getLocale()==='bn' ? 'সদস্য' : 'MEMBER' }}
        </div>
        <h1 style="font-size:22px;font-weight:800;margin-bottom:5px;text-shadow:0 2px 8px rgba(0,0,0,.15);" class="bengali-text">
          {{ app()->getLocale()==='bn' ? '👋 স্বাগতম,' : '👋 Welcome back,' }} {{ $user->name }}!
        </h1>
        <p style="font-size:13px;opacity:.82;margin:0;" class="bengali-text">
          {{ app()->getLocale()==='bn' ? 'আপনার ড্যাশবোর্ডে আপনাকে স্বাগত জানাই' : "Here's a summary of your activity" }}
        </p>
      </div>
      <a href="{{ route('home') }}" class="db-welcome-btn">
        <i class="fas fa-shopping-bag"></i>
        {{ app()->getLocale()==='bn' ? 'কেনাকাটা করুন' : 'Shop Now' }}
      </a>
    </div>

    {{-- Stats --}}
    <div class="db-stats">
      <div class="db-stat">
        <div class="db-stat-icon" style="background:#e8f5e9;"><i class="fas fa-shopping-bag" style="color:var(--primary);"></i></div>
        <div class="db-stat-label bengali-text">{{ app()->getLocale()==='bn' ? 'মোট অর্ডার' : 'Total Orders' }}</div>
        <div class="db-stat-val" style="color:var(--primary);">{{ $totalOrders }}</div>
      </div>
      <div class="db-stat">
        <div class="db-stat-icon" style="background:#fef3c7;"><i class="fas fa-clock" style="color:#f59e0b;"></i></div>
        <div class="db-stat-label bengali-text">{{ app()->getLocale()==='bn' ? 'পেন্ডিং' : 'Pending' }}</div>
        <div class="db-stat-val" style="color:#f59e0b;">{{ $pendingOrders }}</div>
      </div>
      <div class="db-stat">
        <div class="db-stat-icon" style="background:#dbeafe;"><i class="fas fa-check-circle" style="color:#3b82f6;"></i></div>
        <div class="db-stat-label bengali-text">{{ app()->getLocale()==='bn' ? 'ডেলিভারি' : 'Delivered' }}</div>
        <div class="db-stat-val" style="color:#3b82f6;">{{ $deliveredOrders }}</div>
      </div>
      <div class="db-stat">
        <div class="db-stat-icon" style="background:#ede9fe;"><i class="fas fa-taka-sign" style="color:#8b5cf6;"></i></div>
        <div class="db-stat-label bengali-text">{{ app()->getLocale()==='bn' ? 'মোট খরচ' : 'Total Spent' }}</div>
        <div class="db-stat-val" style="color:#8b5cf6;font-size:20px;">৳{{ number_format($totalSpent, 0) }}</div>
      </div>
    </div>

    {{-- Quick Actions --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:18px;margin-bottom:20px;">
      <h6 style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:14px;" class="bengali-text">
        {{ app()->getLocale()==='bn' ? 'দ্রুত অ্যাক্সেস' : 'Quick Access' }}
      </h6>
      <div class="db-actions">
        <div class="db-action-card" onclick="openPopup('popupOrders')">
          <div class="db-action-icon" style="background:#e8f5e9;"><i class="fas fa-shopping-bag" style="color:var(--primary);"></i></div>
          <span style="font-size:12px;font-weight:600;" class="bengali-text">{{ app()->getLocale()==='bn' ? 'আমার অর্ডার' : 'My Orders' }}</span>
        </div>
        <div class="db-action-card" onclick="openPopup('popupHistory')">
          <div class="db-action-icon" style="background:#fef3c7;"><i class="fas fa-history" style="color:#f59e0b;"></i></div>
          <span style="font-size:12px;font-weight:600;" class="bengali-text">{{ app()->getLocale()==='bn' ? 'কেনার ইতিহাস' : 'History' }}</span>
        </div>
        <div class="db-action-card" onclick="openPopup('popupReviews')">
          <div class="db-action-icon" style="background:#ede9fe;"><i class="fas fa-star" style="color:#7c3aed;"></i></div>
          <span style="font-size:12px;font-weight:600;" class="bengali-text">{{ app()->getLocale()==='bn' ? 'রিভিউ' : 'Reviews' }}</span>
        </div>
        <div class="db-action-card" onclick="openPopup('popupProfile')">
          <div class="db-action-icon" style="background:#e0f2fe;"><i class="fas fa-user-circle" style="color:#0284c7;"></i></div>
          <span style="font-size:12px;font-weight:600;" class="bengali-text">{{ app()->getLocale()==='bn' ? 'প্রোফাইল' : 'Profile' }}</span>
        </div>
        <div class="db-action-card" onclick="window.location='{{ route('checkout') }}'">
          <div class="db-action-icon" style="background:#fce7f3;"><i class="fas fa-cart-arrow-down" style="color:#db2777;"></i></div>
          <span style="font-size:12px;font-weight:600;" class="bengali-text">{{ app()->getLocale()==='bn' ? 'চেকআউট' : 'Checkout' }}</span>
        </div>
        <div class="db-action-card" onclick="openPopup('popupLogout')">
          <div class="db-action-icon" style="background:#fee2e2;"><i class="fas fa-sign-out-alt" style="color:#ef4444;"></i></div>
          <span style="font-size:12px;font-weight:600;color:#ef4444;" class="bengali-text">{{ app()->getLocale()==='bn' ? 'লগআউট' : 'Logout' }}</span>
        </div>
      </div>
    </div>

    {{-- Recent Orders Table --}}
    @if($recentOrders->count())
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden;">
      <div style="padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
        <h6 style="margin:0;font-size:14px;font-weight:600;" class="bengali-text">
          <i class="fas fa-clock me-2" style="color:var(--primary);"></i>
          {{ app()->getLocale()==='bn' ? 'সাম্প্রতিক অর্ডার' : 'Recent Orders' }}
        </h6>
        <button onclick="openPopup('popupOrders')" style="background:none;border:1px solid var(--border);border-radius:7px;padding:4px 12px;font-size:12px;cursor:pointer;color:var(--primary);">
          {{ app()->getLocale()==='bn' ? 'সব দেখুন' : 'View All' }}
        </button>
      </div>
      <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
          <thead>
            <tr style="background:var(--surface-2);">
              <th style="padding:10px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:var(--muted);" class="bengali-text">{{ app()->getLocale()==='bn' ? 'অর্ডার' : 'Order' }}</th>
              <th style="padding:10px 12px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:var(--muted);" class="bengali-text">{{ app()->getLocale()==='bn' ? 'তারিখ' : 'Date' }}</th>
              <th style="padding:10px 12px;text-align:right;font-size:11px;font-weight:600;text-transform:uppercase;color:var(--muted);" class="bengali-text">{{ app()->getLocale()==='bn' ? 'মোট' : 'Total' }}</th>
              <th style="padding:10px 18px;text-align:center;font-size:11px;font-weight:600;text-transform:uppercase;color:var(--muted);" class="bengali-text">{{ app()->getLocale()==='bn' ? 'স্ট্যাটাস' : 'Status' }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($recentOrders->take(5) as $order)
            @php
              $sc=['pending'=>['#fef3c7','#92400e'],'processing'=>['#dbeafe','#1e40af'],'shipped'=>['#e0f2fe','#0369a1'],'delivered'=>['#d1fae5','#065f46'],'cancelled'=>['#fee2e2','#991b1b']];
              [$bg,$tc]=$sc[$order->status]??['#f3f4f6','#374151'];
              $sl=['pending'=>['পেন্ডিং','Pending'],'processing'=>['প্রসেসিং','Processing'],'shipped'=>['শিপড','Shipped'],'delivered'=>['ডেলিভারি হয়েছে','Delivered'],'cancelled'=>['বাতিল','Cancelled']];
              $s=$sl[$order->status]??[$order->status,$order->status];
            @endphp
            <tr style="border-bottom:1px solid var(--border);">
              <td style="padding:12px 18px;font-weight:600;color:var(--primary);">{{ $order->order_number }}</td>
              <td style="padding:12px 12px;color:var(--muted);">{{ $order->created_at->format('d M Y') }}</td>
              <td style="padding:12px 12px;text-align:right;font-weight:600;">৳{{ number_format($order->total_amount,0) }}</td>
              <td style="padding:12px 18px;text-align:center;">
                <span class="db-status bengali-text" style="background:{{$bg}};color:{{$tc}};">{{ app()->getLocale()==='bn' ? $s[0] : $s[1] }}</span>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @else
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:44px;text-align:center;color:var(--muted);">
      <i class="fas fa-box-open" style="font-size:44px;margin-bottom:12px;display:block;opacity:.3;"></i>
      <p class="bengali-text mb-3">{{ app()->getLocale()==='bn' ? 'এখনো কোনো অর্ডার নেই' : 'No orders yet' }}</p>
      <a href="{{ route('home') }}" class="btn btn-success btn-sm">{{ app()->getLocale()==='bn' ? 'কেনাকাটা শুরু করুন' : 'Start Shopping' }}</a>
    </div>
    @endif

  </main>
</div>


{{-- ══ POPUP: MY ORDERS ══ --}}
<div class="db-popup-overlay" id="popupOrders" onclick="closePopupOutside(event,'popupOrders')">
  <div class="db-popup">
    <div class="db-popup-handle"></div>
    <div class="db-popup-header">
      <div style="display:flex;align-items:center;gap:10px;">
        <i class="fas fa-shopping-bag" style="color:var(--primary);"></i>
        <span style="font-size:15px;font-weight:700;" class="bengali-text">{{ app()->getLocale()==='bn' ? 'আমার অর্ডার' : 'My Orders' }}</span>
      </div>
      <button onclick="closePopup('popupOrders')" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);">×</button>
    </div>
    <div class="db-popup-body">
      @if($recentOrders->isEmpty())
      <div style="text-align:center;padding:40px;color:var(--muted);">
        <i class="fas fa-box-open fa-2x mb-3 d-block opacity-30"></i>
        <p class="bengali-text">{{ app()->getLocale()==='bn' ? 'এখনো কোনো অর্ডার নেই' : 'No orders yet' }}</p>
        <a href="{{ route('home') }}" class="btn btn-success btn-sm mt-2">{{ app()->getLocale()==='bn' ? 'কেনাকাটা করুন' : 'Shop Now' }}</a>
      </div>
      @else
      <div style="display:flex;flex-direction:column;gap:10px;">
        @foreach($recentOrders as $order)
        @php
          $sc2=['pending'=>['#fef3c7','#92400e'],'processing'=>['#dbeafe','#1e40af'],'shipped'=>['#e0f2fe','#0369a1'],'delivered'=>['#d1fae5','#065f46'],'cancelled'=>['#fee2e2','#991b1b']];
          [$bg2,$tc2]=$sc2[$order->status]??['#f3f4f6','#374151'];
          $sl2=['pending'=>['পেন্ডিং','Pending'],'processing'=>['প্রসেসিং','Processing'],'shipped'=>['শিপড','Shipped'],'delivered'=>['ডেলিভারি হয়েছে','Delivered'],'cancelled'=>['বাতিল','Cancelled']];
          $s2=$sl2[$order->status]??[$order->status,$order->status];
        @endphp
        <div style="background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:14px 16px;">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
            <span style="font-weight:700;color:var(--primary);font-size:14px;">{{ $order->order_number }}</span>
            <span class="db-status bengali-text" style="background:{{$bg2}};color:{{$tc2}};">{{ app()->getLocale()==='bn' ? $s2[0] : $s2[1] }}</span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--muted);">
            <span>{{ $order->created_at->format('d M Y, H:i') }}</span>
            <span style="font-weight:700;color:var(--text);">৳{{ number_format($order->total_amount,0) }}</span>
          </div>
          @if($order->items->count())
          <div style="font-size:11px;color:var(--muted);margin-top:5px;" class="bengali-text">
            {{ $order->items->count() }} {{ app()->getLocale()==='bn' ? 'টি পণ্য:' : 'item(s):' }}
            {{ $order->items->take(2)->map(fn($i) => $i->product_name ?? '—')->join(', ') }}
            @if($order->items->count() > 2) ... @endif
          </div>
          @endif
        </div>
        @endforeach
      </div>
      @endif
    </div>
  </div>
</div>


{{-- ══ POPUP: PURCHASE HISTORY ══ --}}
<div class="db-popup-overlay" id="popupHistory" onclick="closePopupOutside(event,'popupHistory')">
  <div class="db-popup">
    <div class="db-popup-handle"></div>
    <div class="db-popup-header">
      <div style="display:flex;align-items:center;gap:10px;">
        <i class="fas fa-history" style="color:#f59e0b;"></i>
        <span style="font-size:15px;font-weight:700;" class="bengali-text">{{ app()->getLocale()==='bn' ? 'কেনার ইতিহাস' : 'Purchase History' }}</span>
      </div>
      <button onclick="closePopup('popupHistory')" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);">×</button>
    </div>
    <div class="db-popup-body">
      @php $delivered = $recentOrders->where('status','delivered'); @endphp
      @if($delivered->isEmpty())
      <div style="text-align:center;padding:40px;color:var(--muted);">
        <i class="fas fa-box fa-2x mb-3 d-block opacity-30"></i>
        <p class="bengali-text">{{ app()->getLocale()==='bn' ? 'কোনো ডেলিভারি হওয়া অর্ডার নেই' : 'No delivered orders yet' }}</p>
      </div>
      @else
      <div style="margin-bottom:12px;padding:10px 14px;background:#e8f5e9;border-radius:10px;">
        <span style="font-size:13px;color:var(--primary);font-weight:600;" class="bengali-text">
          <i class="fas fa-check-circle me-2"></i>
          {{ app()->getLocale()==='bn' ? 'মোট ডেলিভারি: ' . $delivered->count() . ' টি' : 'Total delivered: ' . $delivered->count() }}
        </span>
      </div>
      <div style="display:flex;flex-direction:column;gap:10px;">
        @foreach($delivered as $order)
        <div style="background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:14px 16px;">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
            <span style="font-weight:700;color:var(--primary);font-size:14px;">{{ $order->order_number }}</span>
            <span style="font-size:11px;color:#065f46;background:#d1fae5;padding:2px 9px;border-radius:20px;font-weight:600;" class="bengali-text">
              <i class="fas fa-check-circle me-1"></i>{{ app()->getLocale()==='bn' ? 'ডেলিভারি হয়েছে' : 'Delivered' }}
            </span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--muted);">
            <span>{{ $order->created_at->format('d M Y') }}</span>
            <span style="font-weight:700;color:var(--text);">৳{{ number_format($order->total_amount,0) }}</span>
          </div>
          @if($order->items->count())
          <div style="font-size:11px;color:var(--muted);margin-top:5px;" class="bengali-text">
            {{ $order->items->map(fn($i) => $i->product_name ?? '—')->join(' • ') }}
          </div>
          @endif
        </div>
        @endforeach
      </div>
      @endif
    </div>
  </div>
</div>


{{-- ══ POPUP: REVIEWS ══ --}}
<div class="db-popup-overlay" id="popupReviews" onclick="closePopupOutside(event,'popupReviews')">
  <div class="db-popup">
    <div class="db-popup-handle"></div>
    <div class="db-popup-header">
      <div style="display:flex;align-items:center;gap:10px;">
        <i class="fas fa-star" style="color:#7c3aed;"></i>
        <span style="font-size:15px;font-weight:700;" class="bengali-text">{{ app()->getLocale()==='bn' ? 'আমার রিভিউ' : 'My Reviews' }}</span>
      </div>
      <button onclick="closePopup('popupReviews')" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);">×</button>
    </div>
    <div class="db-popup-body">

      @if(session('success'))
      <div style="background:#d1fae5;color:#065f46;padding:10px 14px;border-radius:9px;margin-bottom:14px;font-size:13px;">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
      </div>
      @endif
      @if(session('error'))
      <div style="background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:9px;margin-bottom:14px;font-size:13px;">
        <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
      </div>
      @endif
      @if($errors->any())
      <div style="background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:9px;margin-bottom:14px;font-size:13px;">
        <i class="fas fa-times-circle me-2"></i>
        @foreach($errors->all() as $err)
          <div>{{ $err }}</div>
        @endforeach
      </div>
      @endif

      @php $pendingReview = $purchasedProducts->filter(fn($i) => $i->product && !in_array($i->product_id, $reviewedProductIds)); @endphp

      @if($pendingReview->count())
      <h6 style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;" class="bengali-text">
        {{ app()->getLocale()==='bn' ? 'রিভিউ দেওয়া বাকি' : 'Pending Reviews' }} ({{ $pendingReview->count() }})
      </h6>
      <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:20px;">
        @foreach($pendingReview as $item)
        @if($item->product)
        <div style="background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:14px 16px;">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            @if($item->product->primaryImage)
              <img src="{{ $item->product->image_url }}" style="width:46px;height:46px;object-fit:cover;border-radius:9px;">
            @elseif($item->product->image_url)
              <img src="{{ $item->product->image_url }}" style="width:46px;height:46px;object-fit:cover;border-radius:9px;">
            @else
              <div style="width:46px;height:46px;background:var(--surface-2);border-radius:9px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-box text-muted"></i></div>
            @endif
            <div>
              <div style="font-size:13px;font-weight:600;" class="bengali-text">{{ $item->product->name }}</div>
              <div style="font-size:11px;color:#16a34a;"><i class="fas fa-check-circle me-1"></i>{{ app()->getLocale()==='bn' ? 'ডেলিভারি হয়েছে' : 'Delivered' }}</div>
            </div>
          </div>
          <form method="POST" action="{{ route('reviews.store') }}" enctype="multipart/form-data" id="reviewForm_{{ $item->product_id }}">
            @csrf
            <input type="hidden" name="product_id" value="{{ $item->product_id }}">
            {{-- Star Rating --}}
            <div style="margin-bottom:10px;">
              <div class="star-group" data-product="{{ $item->product_id }}" style="display:flex;gap:5px;">
                @for($i=1;$i<=5;$i++)
                <input type="radio" name="rating" id="sr{{ $item->product_id }}_{{ $i }}" value="{{ $i }}" style="display:none;" {{ $i==5?'checked':'' }}>
                <label for="sr{{ $item->product_id }}_{{ $i }}" data-val="{{ $i }}"
                  style="font-size:26px;cursor:pointer;color:#f59e0b;line-height:1;"
                  class="star-label"
                  onmouseover="hoverStars(this)"
                  onmouseout="resetStarsGroup(this.closest('.star-group'))"
                  onclick="selectStar(this)">★</label>
                @endfor
              </div>
            </div>
            {{-- Comment --}}
            <textarea name="comment" rows="2" required
              placeholder="{{ app()->getLocale()==='bn' ? 'আপনার মতামত লিখুন...' : 'Write your review...' }}"
              style="width:100%;border-radius:9px;border:1.5px solid var(--border);padding:9px 12px;font-size:13px;background:var(--surface);color:var(--text);resize:vertical;min-height:65px;margin-bottom:10px;"
              class="bengali-text"></textarea>
            {{-- Image Upload --}}
            <div style="margin-bottom:12px;">
              <label style="display:inline-flex;align-items:center;gap:7px;cursor:pointer;padding:7px 14px;border:1.5px dashed var(--border);border-radius:9px;font-size:12px;color:var(--muted);">
                <i class="fas fa-camera" style="color:var(--primary);"></i>
                <span class="bengali-text" id="imgLbl_{{ $item->product_id }}">{{ app()->getLocale()==='bn' ? 'ছবি যোগ করুন (ঐচ্ছিক)' : 'Add Photo (optional)' }}</span>
                <input type="file" name="review_image" accept="image/*" style="display:none;"
                       id="reviewImg_{{ $item->product_id }}"
                       onchange="previewReviewImg(this, '{{ $item->product_id }}')">
              </label>
              <div id="imgPrev_{{ $item->product_id }}" style="display:none;margin-top:8px;align-items:center;gap:8px;">
                <img id="imgPrevImg_{{ $item->product_id }}" style="max-width:80px;max-height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                <button type="button" onclick="clearReviewImg('{{ $item->product_id }}')"
                  style="background:#fee2e2;color:#dc3545;border:none;border-radius:7px;padding:3px 9px;font-size:12px;cursor:pointer;">✕</button>
              </div>
            </div>
            <button type="submit" style="background:var(--primary);color:#fff;border:none;padding:8px 18px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;">
              <i class="fas fa-paper-plane me-1"></i>{{ app()->getLocale()==='bn' ? 'জমা দিন' : 'Submit' }}
            </button>
          </form>
        </div>
        @endif
        @endforeach
      </div>
      @endif

      @if($myReviews->count())
      <h6 style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;" class="bengali-text">
        {{ app()->getLocale()==='bn' ? 'আমার জমা দেওয়া রিভিউ' : 'My Reviews' }} ({{ $myReviews->count() }})
      </h6>
      <div style="display:flex;flex-direction:column;gap:10px;">
        @foreach($myReviews as $rev)
        <div style="background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:12px 14px;">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
            <div style="display:flex;align-items:center;gap:9px;flex:1;">
              @if($rev->product?->primaryImage)
                <img src="{{ $rev->product->image_url }}" style="width:40px;height:40px;object-fit:cover;border-radius:8px;flex-shrink:0;">
              @elseif($rev->product?->image_url)
                <img src="{{ $rev->product->image_url }}" style="width:40px;height:40px;object-fit:cover;border-radius:8px;flex-shrink:0;">
              @endif
              <div style="flex:1;">
                <div style="font-size:13px;font-weight:600;" class="bengali-text">{{ $rev->product?->name }}</div>
                <div style="font-size:14px;">
                  <span class="star-filled">{{ str_repeat('★', $rev->rating) }}</span><span class="star-empty">{{ str_repeat('★', 5-$rev->rating) }}</span>
                </div>
                <p style="font-size:12px;color:var(--muted);margin:4px 0 0;line-height:1.5;" class="bengali-text">{{ $rev->comment }}</p>
                <div style="font-size:11px;color:var(--muted);margin-top:3px;">{{ $rev->created_at->diffForHumans() }}</div>
              </div>
            </div>
            <form method="POST" action="{{ route('reviews.destroy', $rev) }}" onsubmit="return confirm('{{ app()->getLocale()==='bn' ? 'মুছে ফেলবেন?' : 'Delete?' }}')">
              @csrf @method('DELETE')
              <button type="submit" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:13px;padding:4px;">
                <i class="fas fa-trash-alt"></i>
              </button>
            </form>
          </div>
        </div>
        @endforeach
      </div>
      @elseif(!$pendingReview->count())
      <div style="text-align:center;padding:40px;color:var(--muted);">
        <i class="fas fa-star fa-2x mb-3 d-block opacity-20"></i>
        <p class="bengali-text" style="font-size:13px;">{{ app()->getLocale()==='bn' ? 'পণ্য ডেলিভারি হলে রিভিউ দিতে পারবেন' : 'Reviews available after delivery' }}</p>
        <a href="{{ route('home') }}" class="btn btn-success btn-sm mt-2">{{ app()->getLocale()==='bn' ? 'কেনাকাটা করুন' : 'Shop Now' }}</a>
      </div>
      @endif
    </div>
  </div>
</div>


{{-- ══ POPUP: PROFILE ══ --}}
<div class="db-popup-overlay" id="popupProfile" onclick="closePopupOutside(event,'popupProfile')">
  <div class="db-popup">
    <div class="db-popup-handle"></div>
    <div class="db-popup-header">
      <div style="display:flex;align-items:center;gap:10px;">
        <i class="fas fa-user-circle" style="color:#0284c7;"></i>
        <span style="font-size:15px;font-weight:700;" class="bengali-text">{{ app()->getLocale()==='bn' ? 'আমার প্রোফাইল' : 'My Profile' }}</span>
      </div>
      <button onclick="closePopup('popupProfile')" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);">×</button>
    </div>
    <div class="db-popup-body">
      <div style="background:linear-gradient(135deg,#1a9e4a,#0d7a38);border-radius:14px;padding:22px;margin-bottom:18px;text-align:center;color:#fff;">
        <div style="width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,.25);border:3px solid rgba(255,255,255,.5);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:28px;font-weight:700;">
          {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <h5 style="font-size:17px;font-weight:700;margin-bottom:3px;" class="bengali-text">{{ $user->name }}</h5>
        <p style="font-size:13px;opacity:.85;margin-bottom:2px;">{{ $user->email }}</p>
        @if($user->mobile)
        <p style="font-size:13px;opacity:.75;margin:0;"><i class="fas fa-phone me-1"></i>{{ $user->mobile }}</p>
        @endif
      </div>
      <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:18px;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:var(--bg);border:1px solid var(--border);border-radius:10px;">
          <span style="font-size:13px;font-weight:600;color:var(--muted);display:flex;align-items:center;gap:8px;" class="bengali-text">
            <i class="fas fa-user" style="color:var(--primary);width:14px;"></i>
            {{ app()->getLocale()==='bn' ? 'নাম' : 'Name' }}
          </span>
          <span style="font-size:13px;font-weight:600;">{{ $user->name }}</span>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:var(--bg);border:1px solid var(--border);border-radius:10px;">
          <span style="font-size:13px;font-weight:600;color:var(--muted);display:flex;align-items:center;gap:8px;">
            <i class="fas fa-envelope" style="color:var(--primary);width:14px;"></i>Email
          </span>
          <span style="font-size:13px;">{{ $user->email }}</span>
        </div>
        @if($user->mobile)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:var(--bg);border:1px solid var(--border);border-radius:10px;">
          <span style="font-size:13px;font-weight:600;color:var(--muted);display:flex;align-items:center;gap:8px;" class="bengali-text">
            <i class="fas fa-phone" style="color:var(--primary);width:14px;"></i>
            {{ app()->getLocale()==='bn' ? 'মোবাইল' : 'Mobile' }}
          </span>
          <span style="font-size:13px;">{{ $user->mobile }}</span>
        </div>
        @endif
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:var(--bg);border:1px solid var(--border);border-radius:10px;">
          <span style="font-size:13px;font-weight:600;color:var(--muted);display:flex;align-items:center;gap:8px;" class="bengali-text">
            <i class="fas fa-shopping-bag" style="color:var(--primary);width:14px;"></i>
            {{ app()->getLocale()==='bn' ? 'মোট অর্ডার' : 'Total Orders' }}
          </span>
          <span style="font-size:14px;font-weight:700;color:var(--primary);">{{ $totalOrders }}</span>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:var(--bg);border:1px solid var(--border);border-radius:10px;">
          <span style="font-size:13px;font-weight:600;color:var(--muted);display:flex;align-items:center;gap:8px;" class="bengali-text">
            <i class="fas fa-calendar" style="color:var(--primary);width:14px;"></i>
            {{ app()->getLocale()==='bn' ? 'যোগদান' : 'Joined' }}
          </span>
          <span style="font-size:13px;">{{ $user->created_at->format('d M Y') }}</span>
        </div>
      </div>
      <a href="{{ route('profile.edit') }}" style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;background:var(--primary);color:#fff;text-decoration:none;padding:11px;border-radius:10px;font-size:14px;font-weight:600;">
        <i class="fas fa-edit"></i>
        {{ app()->getLocale()==='bn' ? 'প্রোফাইল সম্পাদনা করুন' : 'Edit Profile' }}
      </a>
    </div>
  </div>
</div>


{{-- ══ POPUP: LOGOUT CONFIRM ══ --}}
<div class="db-popup-overlay" id="popupLogout" onclick="closePopupOutside(event,'popupLogout')">
  <div class="db-popup" style="max-width:380px;">
    <div class="db-popup-handle"></div>
    <div class="db-popup-body" style="text-align:center;padding:30px 24px;">
      <div style="width:60px;height:60px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
        <i class="fas fa-sign-out-alt" style="font-size:22px;color:#ef4444;"></i>
      </div>
      <h5 style="font-size:16px;font-weight:700;margin-bottom:6px;" class="bengali-text">
        {{ app()->getLocale()==='bn' ? 'লগআউট করবেন?' : 'Log out?' }}
      </h5>
      <p style="font-size:13px;color:var(--muted);margin-bottom:20px;" class="bengali-text">
        {{ app()->getLocale()==='bn' ? 'আপনার সেশন শেষ হয়ে যাবে।' : 'Your session will end.' }}
      </p>
      <div style="display:flex;gap:10px;justify-content:center;">
        <button onclick="closePopup('popupLogout')" style="flex:1;max-width:130px;padding:10px;border:1px solid var(--border);border-radius:9px;background:var(--surface);color:var(--text);font-size:14px;font-weight:600;cursor:pointer;">
          {{ app()->getLocale()==='bn' ? 'না' : 'Cancel' }}
        </button>
        <form method="POST" action="{{ route('logout') }}" style="flex:1;max-width:130px;margin:0;">
          @csrf
          <button type="submit" style="width:100%;padding:10px;background:#ef4444;color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;">
            {{ app()->getLocale()==='bn' ? 'লগআউট' : 'Logout' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
function openPopup(id){
    document.getElementById(id).classList.add('show');
    document.body.style.overflow='hidden';
}
function closePopup(id){
    document.getElementById(id).classList.remove('show');
    document.body.style.overflow='';
}
function closePopupOutside(e,id){
    if(e.target.id===id) closePopup(id);
}
document.addEventListener('keydown',e=>{
    if(e.key==='Escape'){
        document.querySelectorAll('.db-popup-overlay.show').forEach(p=>p.classList.remove('show'));
        document.body.style.overflow='';
    }
});
function hoverStars(lbl){
    const val=parseInt(lbl.dataset.val);
    lbl.closest('.star-group').querySelectorAll('.star-label').forEach(l=>{
        l.style.color=parseInt(l.dataset.val)<=val?'#f59e0b':'#d1d5db';
    });
}
function resetStarsGroup(grp){
    const checked=grp.querySelector('input:checked');
    const val=checked?parseInt(checked.value):5;
    grp.querySelectorAll('.star-label').forEach(l=>{
        l.style.color=parseInt(l.dataset.val)<=val?'#f59e0b':'#d1d5db';
    });
}
function selectStar(lbl){
    const val=parseInt(lbl.dataset.val);
    const grp=lbl.closest('.star-group');
    grp.querySelector(`input[value="${val}"]`).checked=true;
    grp.querySelectorAll('.star-label').forEach(l=>{
        l.style.color=parseInt(l.dataset.val)<=val?'#f59e0b':'#d1d5db';
    });
}
document.querySelectorAll('.star-group').forEach(grp=>resetStarsGroup(grp));

// Review image preview helpers
function previewReviewImg(input, productId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imgPrevImg_' + productId).src = e.target.result;
            const wrap = document.getElementById('imgPrev_' + productId);
            wrap.style.display = 'flex';
            document.getElementById('imgLbl_' + productId).textContent = input.files[0].name;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function clearReviewImg(productId) {
    document.getElementById('reviewImg_' + productId).value = '';
    document.getElementById('imgPrev_' + productId).style.display = 'none';
    @if(app()->getLocale() === 'bn')
    document.getElementById('imgLbl_' + productId).textContent = 'ছবি যোগ করুন (ঐচ্ছিক)';
    @else
    document.getElementById('imgLbl_' + productId).textContent = 'Add Photo (optional)';
    @endif
}
@if(session('success') || session('error') || $errors->any())
window.addEventListener('load',()=>openPopup('popupReviews'));
@endif
</script>
@endpush