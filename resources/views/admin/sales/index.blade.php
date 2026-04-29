@extends('layouts.admin')
@section('title', app()->getLocale()==='bn' ? 'বিক্রয় ইতিহাস' : 'Sales History')
@section('page-title', app()->getLocale()==='bn' ? 'বিক্রয় ইতিহাস' : 'Sales History')

@section('content')

{{-- Stats --}}
<div class="stats-grid mb-4">
  @foreach([
    ['icon'=>'fa-cash-register','num'=>number_format($stats->total_sales),          'bn'=>'মোট বিক্রয়',     'en'=>'Total Sales',       'g'=>'var(--primary)'],
    ['icon'=>'fa-chart-line',   'num'=>'৳'.number_format($stats->total_revenue),    'bn'=>'মোট আয়',         'en'=>'Total Revenue',     'g'=>'var(--success)'],
    ['icon'=>'fa-box',          'num'=>number_format($stats->total_quantity),        'bn'=>'মোট পরিমাণ',     'en'=>'Total Qty',         'g'=>'var(--warning)'],
    ['icon'=>'fa-users',        'num'=>number_format($stats->unique_customers),      'bn'=>'অনন্য গ্রাহক',  'en'=>'Unique Customers',  'g'=>'var(--danger)'],
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
      <div class="col-md-4">
        <input type="text" class="form-control" name="search"
          placeholder="{{ app()->getLocale()==='bn' ? 'খুঁজুন...' : 'Search...' }}"
          value="{{ $search }}">
      </div>
      <div class="col-md-3">
        <input type="date" class="form-control" name="date_from" value="{{ $dateFrom }}"
          placeholder="{{ app()->getLocale()==='bn' ? 'শুরু তারিখ' : 'From Date' }}">
      </div>
      <div class="col-md-3">
        <input type="date" class="form-control" name="date_to" value="{{ $dateTo }}"
          placeholder="{{ app()->getLocale()==='bn' ? 'শেষ তারিখ' : 'To Date' }}">
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">
          <i class="fas fa-search me-1"></i>{{ app()->getLocale()==='bn' ? 'ফিল্টার' : 'Filter' }}
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Table --}}
<div class="card animate-fadeInUp">
  <div class="card-header">
    <h5 class="card-title bengali-text"><i class="fas fa-history me-2"></i>{{ app()->getLocale()==='bn' ? 'বিক্রয়ের তালিকা' : 'Sales Records' }}</h5>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ app()->getLocale()==='bn' ? 'পণ্য' : 'Product' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'গ্রাহক' : 'Customer' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'পরিমাণ' : 'Qty' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'চূড়ান্ত মূল্য' : 'Final Amount' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'বিক্রয়কারী' : 'Sold By' }}</th>
            <th>{{ app()->getLocale()==='bn' ? 'তারিখ' : 'Date' }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($sales as $sale)
          <tr>
            <td><small class="text-muted">#{{ $sale->order_id }}</small></td>
            <td>
              <strong class="bengali-text">{{ $sale->product_name }}</strong><br>
              <small class="text-muted">{{ $sale->product_type }} {{ $sale->product_size }}</small>
            </td>
            <td>
              <div class="bengali-text">{{ $sale->customer_name }}</div>
              <small class="text-muted">{{ $sale->customer_email }}</small>
            </td>
            <td>{{ $sale->quantity }}</td>
            <td>
              <strong class="text-success">৳{{ number_format($sale->final_amount) }}</strong>
              @if($sale->is_discounted)
              <br><small class="text-muted text-decoration-line-through">৳{{ number_format($sale->original_price * $sale->quantity) }}</small>
              @endif
            </td>
            <td><small>{{ $sale->sold_by }}</small></td>
            <td>
              {{ $sale->selling_time?->format('M d, Y') }}<br>
              <small class="text-muted">{{ $sale->selling_time?->format('H:i') }}</small>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="text-center py-4 text-muted bengali-text">
              {{ app()->getLocale()==='bn' ? 'কোন বিক্রয় পাওয়া যায়নি' : 'No sales found' }}
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($sales->hasPages())
    <div class="p-3 border-top">{{ $sales->withQueryString()->links() }}</div>
    @endif
  </div>
</div>
@endsection
