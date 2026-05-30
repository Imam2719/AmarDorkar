<!DOCTYPE html>
<html lang="{{ $bn ? 'bn' : 'en' }}">
<head>
<meta charset="UTF-8">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:Arial,sans-serif;background:#f1f5f9;padding:20px;}
.wrap{max-width:680px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08);}
.hd{background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;padding:24px 28px;text-align:center;}
.hd h1{font-size:20px;font-weight:800;margin-bottom:4px;}
.hd p{font-size:12px;opacity:.85;}
.summary{display:flex;padding:16px 24px;gap:10px;background:#eff6ff;border-bottom:1px solid #dbeafe;flex-wrap:wrap;}
.sbox{flex:1;min-width:100px;text-align:center;}
.sbox .num{font-size:18px;font-weight:800;color:#2563eb;}
.sbox .lbl{font-size:11px;color:#64748b;margin-top:2px;}
.sbox.green .num{color:#16a34a;}
.sbox.red .num{color:#dc2626;}
.body{padding:20px 24px;}
.note{font-size:12px;color:#64748b;margin-bottom:14px;padding:10px 14px;background:#f8fafc;border-left:3px solid #2563eb;border-radius:4px;}
table{width:100%;border-collapse:collapse;font-size:11.5px;}
thead th{background:#2563eb;color:#fff;padding:8px 8px;text-align:left;white-space:nowrap;}
tbody tr:nth-child(even){background:#f8fafc;}
tbody td{padding:7px 8px;border-bottom:1px solid #e2e8f0;color:#334155;}
.badge{display:inline-block;padding:2px 6px;border-radius:4px;font-size:10px;font-weight:700;}
.sold{background:#d1fae5;color:#065f46;}
.cancelled{background:#fee2e2;color:#991b1b;}
.footer{background:#f8fafc;padding:14px 24px;text-align:center;font-size:11px;color:#94a3b8;border-top:1px solid #e2e8f0;}
</style>
</head>
<body>
<div class="wrap">
  <div class="hd">
    <h1>🛒 {{ $bn ? 'বিক্রয় ইতিহাস রিপোর্ট' : 'Sales History Report' }}</h1>
    <p>
      {{ $bn ? 'তৈরি হয়েছে' : 'Generated' }}: {{ now()->format('d M Y, h:i A') }}
      @if($dateFrom || $dateTo)
      &nbsp;|&nbsp; {{ $dateFrom ?: ($bn ? 'শুরু' : 'Start') }} → {{ $dateTo ?: ($bn ? 'এখন' : 'Now') }}
      @else
      &nbsp;|&nbsp; {{ $bn ? 'সব সময়ের রিপোর্ট' : 'All-time Report' }}
      @endif
    </p>
  </div>

  <div class="summary">
    <div class="sbox green">
      <div class="num">{{ $totalOrders }}</div>
      <div class="lbl">{{ $bn ? 'বিক্রয় অর্ডার' : 'Sold Orders' }}</div>
    </div>
    <div class="sbox green">
      <div class="num">৳{{ number_format($totalRevenue, 2) }}</div>
      <div class="lbl">{{ $bn ? 'মোট আয়' : 'Total Revenue' }}</div>
    </div>
    <div class="sbox">
      <div class="num">{{ $totalQty }}</div>
      <div class="lbl">{{ $bn ? 'পণ্য বিক্রয়' : 'Items Sold' }}</div>
    </div>
    <div class="sbox red">
      <div class="num">{{ $totalCancelled }}</div>
      <div class="lbl">{{ $bn ? 'বাতিল' : 'Cancelled' }}</div>
    </div>
  </div>

  <div class="body">
    <p class="note">
      📎 {{ $bn
        ? 'সম্পূর্ণ বিক্রয় তথ্য CSV ফাইল হিসেবে সংযুক্ত করা হয়েছে।'
        : 'Full sales data is attached as a CSV file. Open with Excel or Google Sheets.' }}
    </p>

    <table>
      <thead>
        <tr>
          <th>{{ $bn ? 'অর্ডার নং' : 'Order' }}</th>
          <th>{{ $bn ? 'পণ্য' : 'Product' }}</th>
          <th>{{ $bn ? 'গ্রাহক' : 'Customer' }}</th>
          <th>{{ $bn ? 'পরিমাণ' : 'Qty' }}</th>
          <th>{{ $bn ? 'সাবটোটাল' : 'Subtotal' }}</th>
          <th>{{ $bn ? 'স্ট্যাটাস' : 'Status' }}</th>
          <th>{{ $bn ? 'তারিখ' : 'Date' }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach($rows->take(50) as $r)
        @php $sold = $r->order_status === 'delivered'; @endphp
        <tr>
          <td><strong>{{ $r->order_number }}</strong></td>
          <td>
            {{ $bn ? $r->product_name_bn : $r->product_name_en }}<br>
            <small style="color:#94a3b8;">{{ $bn ? $r->category_name_bn : $r->category_name_en }}</small>
          </td>
          <td>
            {{ $r->customer_name }}<br>
            <small style="color:#94a3b8;">{{ $r->customer_mobile }}</small>
          </td>
          <td style="text-align:center;">{{ $r->quantity }}</td>
          <td><strong style="color:{{ $sold ? '#16a34a':'#dc2626' }}">৳{{ number_format($r->subtotal,2) }}</strong></td>
          <td><span class="badge {{ $sold ? 'sold':'cancelled' }}">{{ $sold ? ($bn?'বিক্রয়':'Sold') : ($bn?'বাতিল':'Cancelled') }}</span></td>
          <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($r->order_date)->format('d M Y') }}<br><small style="color:#94a3b8;">{{ \Carbon\Carbon::parse($r->order_date)->format('h:i A') }}</small></td>
        </tr>
        @endforeach
        @if($rows->count() > 50)
        <tr>
          <td colspan="7" style="text-align:center;padding:10px;color:#64748b;font-style:italic;font-size:11px;">
            {{ $bn ? '... আরও '.($rows->count()-50).' টি রেকর্ড সংযুক্ত CSV ফাইলে পাওয়া যাবে' : '... and '.($rows->count()-50).' more records in attached CSV file' }}
          </td>
        </tr>
        @endif
      </tbody>
    </table>
  </div>

  <div class="footer">
    {{ config('app.name','Amardorkar') }} Admin Panel &mdash; {{ now()->format('d M Y') }}
  </div>
</div>
</body>
</html>
