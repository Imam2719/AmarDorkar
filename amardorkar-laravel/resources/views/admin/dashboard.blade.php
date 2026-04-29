@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'ড্যাশবোর্ড' : 'Dashboard')
@section('page-title', app()->getLocale()==='bn' ? 'ড্যাশবোর্ড' : 'Dashboard')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')

{{-- Stats Cards --}}
<div class="adm-stats fade-up">
  @php
    $cards = [
      ['icon'=>'fa-box',         'color'=>'blue',  'num'=>$stats['total_products'],  'bn'=>'মোট পণ্য',    'en'=>'Total Products'],
      ['icon'=>'fa-shopping-bag','color'=>'green', 'num'=>$stats['total_orders'],    'bn'=>'মোট অর্ডার',  'en'=>'Total Orders'],
      ['icon'=>'fa-users',       'color'=>'amber', 'num'=>$stats['total_customers'], 'bn'=>'মোট গ্রাহক',  'en'=>'Total Customers'],
      ['icon'=>'fa-chart-line',  'color'=>'red',   'num'=>'৳'.number_format($stats['total_revenue']), 'bn'=>'মোট আয়', 'en'=>'Total Revenue'],
    ];
  @endphp
  @foreach($cards as $c)
  <div class="adm-stat {{ $c['color'] }}">
    <div class="adm-stat-icon {{ $c['color'] }}">
      <i class="fas {{ $c['icon'] }}"></i>
    </div>
    <div class="adm-stat-val">{{ $c['num'] }}</div>
    <div class="adm-stat-lbl">{{ app()->getLocale()==='bn' ? $c['bn'] : $c['en'] }}</div>
  </div>
  @endforeach
</div>

{{-- Quick Actions --}}
<div class="row g-3 mb-4 fade-up-1">
  @php
  $actions = [
    ['href'=>route('admin.products.create'), 'icon'=>'fa-plus',     'color'=>'var(--primary)',  'bn'=>'নতুন পণ্য যোগ করুন', 'en'=>'Add New Product'],
    ['href'=>route('admin.orders.index'),    'icon'=>'fa-eye',      'color'=>'var(--success)',  'bn'=>'সব অর্ডার দেখুন',   'en'=>'View All Orders'],
    ['href'=>route('admin.discounts.index'), 'icon'=>'fa-percent',  'color'=>'var(--warning)',  'bn'=>'ছাড় যোগ করুন',      'en'=>'Manage Discounts'],
    ['href'=>route('admin.sales.index'),     'icon'=>'fa-chart-bar','color'=>'var(--purple)',   'bn'=>'বিক্রয় রিপোর্ট',   'en'=>'Sales Report'],
  ];
  @endphp
  @foreach($actions as $a)
  <div class="col-md-3 col-6">
    <a href="{{ $a['href'] }}" class="adm-btn w-100 justify-content-center"
       style="background:{{ $a['color'] }};color:#fff;padding:12px 16px;border-radius:var(--r);"
       onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
      <i class="fas {{ $a['icon'] }}"></i>
      <span>{{ app()->getLocale()==='bn' ? $a['bn'] : $a['en'] }}</span>
    </a>
  </div>
  @endforeach
</div>

{{-- Chart + Extra Stats --}}
<div class="row mb-4">
  <div class="col-lg-8 mb-4">
    <div class="adm-card fade-up-2">
      <div class="adm-card-head">
        <div class="adm-card-title">
          <i class="fas fa-chart-line text-primary"></i>
          {{ app()->getLocale()==='bn' ? 'বিক্রয়ের চার্ট (গত ৬ মাস)' : 'Sales Overview (Last 6 Months)' }}
        </div>
      </div>
      <div class="adm-card-body">
        <div style="position:relative;height:300px">
          <canvas id="salesChart"></canvas>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4 mb-4">
    <div class="adm-card fade-up-2">
      <div class="adm-card-head">
        <div class="adm-card-title">
          <i class="fas fa-chart-pie text-primary"></i>
          {{ app()->getLocale()==='bn' ? 'অতিরিক্ত তথ্য' : 'Additional Stats' }}
        </div>
      </div>
      <div class="adm-card-body">
        @foreach([
          ['icon'=>'fa-tag',       'val'=>$stats['active_discounts'], 'bn'=>'সক্রিয় ছাড়',    'en'=>'Active Discounts',  'color'=>'var(--success)'],
          ['icon'=>'fa-calendar',  'val'=>$stats['today_orders'],     'bn'=>'আজকের অর্ডার',  'en'=>"Today's Orders",    'color'=>'var(--primary)'],
          ['icon'=>'fa-clock',     'val'=>$stats['pending_orders'],   'bn'=>'মুলতবি অর্ডার', 'en'=>'Pending Orders',    'color'=>'var(--warning)'],
          ['icon'=>'fa-check',     'val'=>$stats['delivered_orders'], 'bn'=>'ডেলিভার্ড',      'en'=>'Delivered Orders',  'color'=>'var(--success)'],
        ] as $row)
        <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded" style="background:var(--bg)">
          <span style="font-size:13px;color:var(--text);">
            <i class="fas {{ $row['icon'] }} me-2" style="color:{{ $row['color'] }}"></i>
            {{ app()->getLocale()==='bn' ? $row['bn'] : $row['en'] }}
          </span>
          <strong style="color:{{ $row['color'] }};font-size:17px;">{{ $row['val'] }}</strong>
        </div>
        @endforeach
        <hr style="border-color:var(--border)">
        <div class="text-center">
          <small style="color:var(--muted);font-size:11px;">
            {{ app()->getLocale()==='bn' ? 'শেষ আপডেট: ' : 'Last updated: ' }}{{ now()->format('H:i') }}
          </small>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Recent Orders --}}
<div class="adm-card fade-up-3">
  <div class="adm-card-head">
    <div class="adm-card-title">
      <i class="fas fa-shopping-bag text-primary"></i>
      {{ app()->getLocale()==='bn' ? 'সাম্প্রতিক অর্ডার' : 'Recent Orders' }}
    </div>
    <a href="{{ route('admin.orders.index') }}" class="adm-btn adm-btn-ghost adm-btn-sm">
      {{ app()->getLocale()==='bn' ? 'সব দেখুন' : 'View All' }}
    </a>
  </div>
  <div class="table-responsive">
    <table class="adm-table">
      <thead>
        <tr>
          <th>{{ app()->getLocale()==='bn' ? 'অর্ডার #' : 'Order #' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'গ্রাহক' : 'Customer' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'পরিমাণ' : 'Amount' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'অবস্থা' : 'Status' }}</th>
          <th>{{ app()->getLocale()==='bn' ? 'তারিখ' : 'Date' }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse($recentOrders as $order)
        <tr>
          <td><strong style="color:var(--primary)">#{{ $order->order_number }}</strong></td>
          <td>
            <div>{{ $order->customer_name }}</div>
            <small style="color:var(--muted)">{{ $order->customer_email }}</small>
          </td>
          <td><strong>৳{{ number_format($order->total_amount) }}</strong></td>
          <td>
            @php
              $statusBn = ['pending'=>'মুলতবি','confirmed'=>'নিশ্চিত','processing'=>'প্রক্রিয়াকরণ','shipped'=>'পাঠানো','delivered'=>'ডেলিভারি','cancelled'=>'বাতিল'];
            @endphp
            <span class="adm-badge s-{{ $order->status }}">
              {{ app()->getLocale()==='bn' ? ($statusBn[$order->status] ?? ucfirst($order->status)) : ucfirst($order->status) }}
            </span>
          </td>
          <td>
            {{ $order->created_at->format('M d, Y') }}<br>
            <small style="color:var(--muted)">{{ $order->created_at->format('H:i') }}</small>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" style="text-align:center;padding:32px;color:var(--muted)">
            {{ app()->getLocale()==='bn' ? 'কোন অর্ডার পাওয়া যায়নি' : 'No orders found' }}
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection

@push('scripts')
<script>
const salesData = @json($monthlySales);
const ctx = document.getElementById('salesChart').getContext('2d');
const isDark = document.documentElement.classList.contains('theme-dark');
const gridColor = isDark ? 'rgba(255,255,255,.07)' : '#f1f5f9';
const tickColor = isDark ? '#94a3b8' : '#64748b';
const g1 = ctx.createLinearGradient(0,0,0,300);
g1.addColorStop(0,'rgba(102,126,234,.35)'); g1.addColorStop(1,'rgba(102,126,234,.02)');
const g2 = ctx.createLinearGradient(0,0,0,300);
g2.addColorStop(0,'rgba(79,172,254,.35)');  g2.addColorStop(1,'rgba(79,172,254,.02)');

new Chart(ctx, {
  type:'line',
  data:{
    labels: salesData.map(d=>d.month),
    datasets:[
      {label:'{{ app()->getLocale()==="bn" ? "আয় (৳)" : "Revenue (৳)" }}',data:salesData.map(d=>d.revenue),borderColor:'#667eea',backgroundColor:g1,borderWidth:3,fill:true,tension:.4,pointBackgroundColor:'#667eea',pointBorderColor:'#fff',pointBorderWidth:2,pointRadius:6},
      {label:'{{ app()->getLocale()==="bn" ? "অর্ডার সংখ্যা" : "Orders Count" }}',data:salesData.map(d=>d.orders),borderColor:'#4facfe',backgroundColor:g2,borderWidth:3,fill:true,tension:.4,yAxisID:'y1',pointBackgroundColor:'#4facfe',pointBorderColor:'#fff',pointBorderWidth:2,pointRadius:6},
    ]
  },
  options:{
    responsive:true,maintainAspectRatio:false,
    plugins:{
      legend:{display:true,position:'top',labels:{color:tickColor,font:{size:12}}},
      tooltip:{cornerRadius:8,callbacks:{label:ctx=>ctx.datasetIndex===0?'৳'+ctx.parsed.y.toLocaleString():ctx.parsed.y.toLocaleString()}}
    },
    interaction:{mode:'index',intersect:false},
    scales:{
      x:{grid:{color:gridColor},ticks:{font:{size:11},color:tickColor}},
      y:{position:'left',grid:{color:gridColor},ticks:{callback:v=>'৳'+Number(v).toLocaleString(),font:{size:11},color:tickColor}},
      y1:{type:'linear',position:'right',grid:{drawOnChartArea:false},ticks:{font:{size:11},color:tickColor}},
    }
  }
});
</script>
@endpush
