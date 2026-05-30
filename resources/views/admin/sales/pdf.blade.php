<!DOCTYPE html>
<html lang="{{ $bn ? 'bn' : 'en' }}">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>{{ $bn ? 'বিক্রয় ইতিহাস রিপোর্ট' : 'Sales History Report' }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
  font-family: 'DejaVu Sans', Arial, sans-serif;
  font-size: 10.5px;
  color: #111;
  background: #fff;
  padding: 16px;
}

/* ── Header ── */
.report-header {
  text-align: center;
  padding-bottom: 12px;
  border-bottom: 2px solid #2563eb;
  margin-bottom: 14px;
}
.report-header h1 {
  font-size: 17px;
  color: #2563eb;
  font-weight: 800;
}
.report-header p {
  color: #64748b;
  font-size: 10px;
  margin-top: 4px;
}

/* ── Summary (table layout — DomPDF safe) ── */
.summary-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 6px;
  margin-bottom: 14px;
}
.summary-cell {
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 10px 10px;
  text-align: center;
  vertical-align: middle;
  background: #fff;
  width: 20%;
}
.summary-num {
  font-size: 16px;
  font-weight: 800;
  color: #2563eb;
}
.summary-num.green { color: #16a34a; }
.summary-num.red   { color: #dc2626; }
.summary-num.orange{ color: #d97706; }
.summary-lbl {
  font-size: 9.5px;
  color: #64748b;
  margin-top: 3px;
}

/* ── Data Table ── */
table.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 10px;
}
table.data-table thead tr {
  background: #2563eb;
  color: #fff;
}
table.data-table thead th {
  padding: 7px 5px;
  text-align: left;
  white-space: nowrap;
  font-size: 9.5px;
}
table.data-table tbody tr:nth-child(even) { background: #f8fafc; }
table.data-table tbody tr.cancelled-row { background: #fff5f5; }
table.data-table tbody td {
  padding: 5px 5px;
  border-bottom: 1px solid #e2e8f0;
  vertical-align: top;
}
.badge {
  display: inline-block;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 9px;
  font-weight: 700;
}
.badge-sold      { background: #d1fae5; color: #065f46; }
.badge-cancelled { background: #fee2e2; color: #991b1b; }

/* ── Footer ── */
.report-footer {
  margin-top: 14px;
  text-align: center;
  color: #94a3b8;
  font-size: 9.5px;
  border-top: 1px solid #e2e8f0;
  padding-top: 10px;
}

@page { margin: 10mm; size: A4 landscape; }
@media print { body { padding: 0; } }
</style>
</head>
<body>

{{-- ── HEADER ── --}}
<div class="report-header">
  <h1>{{ config('app.name','AmardoKar') }} — {{ $bn ? 'বিক্রয় ইতিহাস রিপোর্ট' : 'Sales History Report' }}</h1>
  <p>
    {{ $bn ? 'তৈরি হয়েছে' : 'Generated' }}: {{ now()->format('d M Y, h:i A') }}
    @if($dateFrom || $dateTo)
      &nbsp;|&nbsp; {{ $bn ? 'সময়কাল' : 'Period' }}:
      {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d M Y') : ($bn ? 'শুরু' : 'Start') }}
      &rarr; {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d M Y') : ($bn ? 'এখন' : 'Now') }}
    @endif
  </p>
</div>

{{-- ── SUMMARY (table layout, DomPDF compatible) ── --}}
<table class="summary-table">
  <tr>
    <td class="summary-cell">
      <div class="summary-num green">{{ $totalSoldOrders }}</div>
      <div class="summary-lbl">{{ $bn ? 'বিক্রয় অর্ডার' : 'Sold Orders' }}</div>
    </td>
    <td class="summary-cell">
      <div class="summary-num green">Taka: {{ number_format($totalRevenue, 2) }}</div>
      <div class="summary-lbl">{{ $bn ? 'মোট আয়' : 'Total Revenue' }}</div>
    </td>
    <td class="summary-cell">
      <div class="summary-num orange">{{ number_format($totalQty) }}</div>
      <div class="summary-lbl">{{ $bn ? 'পণ্য বিক্রয়' : 'Items Sold' }}</div>
    </td>
    <td class="summary-cell">
      <div class="summary-num">Taka:{{ number_format($totalDiscount, 2) }}</div>
      <div class="summary-lbl">{{ $bn ? 'মোট ডিসকাউন্ট' : 'Total Discount' }}</div>
    </td>
    <td class="summary-cell">
      <div class="summary-num red">{{ $totalCancelled }}</div>
      <div class="summary-lbl">{{ $bn ? 'বাতিল অর্ডার' : 'Cancelled Orders' }}</div>
    </td>
  </tr>
</table>

{{-- ── DATA TABLE ── --}}
<table class="data-table">
  <thead>
    <tr>
      <th>#</th>
      <th>{{ $bn ? 'অর্ডার নং' : 'Order No.' }}</th>
      <th>{{ $bn ? 'পণ্য' : 'Product' }}</th>
      <th>{{ $bn ? 'ক্যাটাগরি' : 'Category' }}</th>
      <th>{{ $bn ? 'গ্রাহক' : 'Customer' }}</th>
      <th>{{ $bn ? 'মোবাইল' : 'Mobile' }}</th>
      <th>{{ $bn ? 'ঠিকানা' : 'Address' }}</th>
      <th>{{ $bn ? 'পরিমাণ' : 'Qty' }}</th>
      <th>{{ $bn ? 'একক মূল্য' : 'Unit Price' }}</th>
      <th>{{ $bn ? 'ডিসকাউন্ট' : 'Discount' }}</th>
      <th>{{ $bn ? 'সাবটোটাল' : 'Subtotal' }}</th>
      <th>{{ $bn ? 'পেমেন্ট' : 'Payment' }}</th>
      <th>{{ $bn ? 'স্ট্যাটাস' : 'Status' }}</th>
      <th>{{ $bn ? 'তারিখ ও সময়' : 'Date &amp; Time' }}</th>
    </tr>
  </thead>
  <tbody>
    @forelse($rows as $i => $r)
    @php $sold = $r->order_status === 'delivered'; @endphp
    <tr class="{{ !$sold ? 'cancelled-row' : '' }}">
      <td>{{ $i + 1 }}</td>
      <td><strong>{{ $r->order_number }}</strong></td>
      <td>
        <strong>{{ $bn ? $r->product_name_bn : $r->product_name_en }}</strong>
        @if($r->product_type || $r->product_size)
        <br><span style="color:#64748b;font-size:9px;">{{ $r->product_type }}{{ $r->product_size ? ' · '.$r->product_size : '' }}</span>
        @endif
      </td>
      <td style="color:#64748b;">{{ $bn ? $r->category_name_bn : $r->category_name_en }}</td>
      <td>
        {{ $r->customer_name }}<br>
        <span style="color:#64748b;font-size:9px;">{{ $r->customer_email }}</span>
      </td>
      <td>{{ $r->customer_mobile }}</td>
      <td style="max-width:110px;">
        <span style="font-size:9px;">{{ $r->customer_address }}</span>
        @if($r->shipping_location)
        <br><span style="color:#2563eb;font-size:8.5px;">{{ $r->shipping_location }}</span>
        @endif
      </td>
      <td style="text-align:center;font-weight:700;">{{ $r->quantity }}</td>
      <td>৳{{ number_format($r->unit_price, 2) }}</td>
      <td>
        @if($r->discount_amount > 0)
        <span style="color:#dc2626;">-৳{{ number_format($r->discount_amount, 2) }}</span>
        @else
        <span style="color:#94a3b8;">—</span>
        @endif
      </td>
      <td><strong style="color:{{ $sold ? '#16a34a' : '#dc2626' }};">৳{{ number_format($r->subtotal, 2) }}</strong></td>
      <td>
        {{ strtoupper($r->payment_method) }}<br>
        <span style="color:#64748b;font-size:9px;">{{ $r->payment_status }}</span>
      </td>
      <td>
        <span class="badge {{ $sold ? 'badge-sold' : 'badge-cancelled' }}">
          {{ $sold ? ($bn ? 'বিক্রয়' : 'Sold') : ($bn ? 'বাতিল' : 'Cancelled') }}
        </span>
      </td>
      <td style="white-space:nowrap;">
        {{ \Carbon\Carbon::parse($r->order_date)->format('d M Y') }}<br>
        <span style="color:#64748b;">{{ \Carbon\Carbon::parse($r->order_date)->format('h:i A') }}</span>
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="14" style="text-align:center;padding:20px;color:#94a3b8;">
        {{ $bn ? 'কোনো রেকর্ড নেই' : 'No records found' }}
      </td>
    </tr>
    @endforelse
  </tbody>
</table>

<div class="report-footer">
  {{ config('app.name','AmardoKar') }} Admin Panel &mdash; {{ $bn ? 'বিক্রয় রিপোর্ট' : 'Sales Report' }} &mdash; {{ now()->format('d M Y h:i A') }}
</div>

<script>
window.onload = function(){ setTimeout(function(){ window.print(); }, 400); };
</script>
</body>
</html>
