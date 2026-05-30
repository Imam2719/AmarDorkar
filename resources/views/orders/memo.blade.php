<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Memo - {{ $order->order_number }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 12px;
    color: #1e293b;
    background: #fff;
  }

  /* ── Header ── */
  .memo-header {
    background: #1a9e4a;
    padding: 22px 30px 18px;
    color: #fff;
  }
  .header-table { width: 100%; border-collapse: collapse; }
  .header-brand-cell { vertical-align: middle; }
  .header-doc-cell { vertical-align: middle; text-align: right; }
  .memo-brand-name {
    font-size: 24px;
    font-weight: 700;
    color: #fff;
  }
  .memo-brand-tagline {
    font-size: 10px;
    color: rgba(255,255,255,0.80);
    margin-top: 2px;
  }
  .memo-doc-title {
    font-size: 18px;
    font-weight: 700;
    color: #fff;
    letter-spacing: 2px;
    text-transform: uppercase;
  }
  .memo-doc-number {
    font-size: 12px;
    color: rgba(255,255,255,0.85);
    margin-top: 3px;
  }
  .header-divider {
    border: 0;
    border-top: 1px solid rgba(255,255,255,0.30);
    margin: 12px 0;
  }
  .meta-table { width: 100%; border-collapse: collapse; }
  .meta-cell { vertical-align: top; width: 33.33%; }
  .meta-label {
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: rgba(255,255,255,0.65);
    margin-bottom: 3px;
  }
  .meta-value {
    font-size: 11px;
    color: #fff;
    font-weight: 700;
  }

  /* ── Body ── */
  .memo-body { padding: 22px 30px; }

  /* ── Address Row (table layout — DomPDF safe) ── */
  .addr-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
  .addr-cell-left  { width: 48%; vertical-align: top; padding-right: 8px; }
  .addr-cell-right { width: 48%; vertical-align: top; padding-left: 8px; }
  .addr-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 12px 14px;
  }
  .addr-box-title {
    font-size: 8px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #64748b;
    margin-bottom: 7px;
    padding-bottom: 5px;
    border-bottom: 1px solid #e2e8f0;
  }
  .addr-name { font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
  .addr-line { font-size: 11px; color: #475569; line-height: 1.7; }

  /* ── Items Table ── */
  .items-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
  .items-table thead tr { background: #1a9e4a; }
  .items-table thead th {
    padding: 9px 10px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #fff;
    text-align: left;
  }
  .items-table thead th.right { text-align: right; }
  .items-table tbody tr { border-bottom: 1px solid #f1f5f9; }
  .items-table tbody tr.even { background: #f8fafc; }
  .items-table tbody td {
    padding: 10px 10px;
    font-size: 11px;
    color: #334155;
    vertical-align: middle;
  }
  .items-table tbody td.right { text-align: right; font-weight: 600; }
  .item-name { font-weight: 600; color: #0f172a; font-size: 11.5px; }
  .item-meta { font-size: 10px; color: #94a3b8; margin-top: 2px; }

  /* ── Bottom section: payment left + totals right (table) ── */
  .bottom-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
  .bottom-left  { width: 52%; vertical-align: top; padding-right: 12px; }
  .bottom-right { width: 48%; vertical-align: top; }

  /* ── Payment boxes (table) ── */
  .pay-table { width: 100%; border-collapse: collapse; }
  .pay-cell { width: 50%; vertical-align: top; padding-right: 6px; }
  .pay-cell-last { padding-right: 0; }
  .pay-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 10px 12px;
  }
  .pay-box-label {
    font-size: 8px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #94a3b8;
    margin-bottom: 3px;
  }
  .pay-box-value { font-size: 12px; font-weight: 700; color: #0f172a; }

  /* ── Totals ── */
  .totals-table { width: 100%; border-collapse: collapse; }
  .totals-table td { padding: 6px 10px; font-size: 12px; }
  .totals-label { color: #64748b; text-align: left; }
  .totals-value { text-align: right; font-weight: 600; color: #0f172a; }
  .totals-divider { border: 0; border-top: 1px solid #e2e8f0; margin: 3px 0; }
  .grand-row { background: #1a9e4a; }
  .grand-label { color: #fff; font-weight: 700; font-size: 13px; padding: 9px 10px; }
  .grand-value { color: #fff; font-weight: 700; font-size: 15px; text-align: right; padding: 9px 10px; }

  /* ── Thank You ── */
  .thankyou-box {
    text-align: center;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 16px;
  }
  .thankyou-box.cancelled-style {
    background: #fef2f2;
    border-color: #fecaca;
  }
  .thankyou-title { font-size: 13px; font-weight: 700; color: #15803d; }
  .thankyou-title.cancelled-style { color: #dc2626; }
  .thankyou-sub { font-size: 11px; color: #16a34a; margin-top: 3px; }

  /* ── Footer ── */
  .memo-footer {
    padding-top: 14px;
    border-top: 2px solid #e2e8f0;
    text-align: center;
  }
  .footer-brand { font-size: 12px; font-weight: 700; color: #1a9e4a; }
  .footer-text { font-size: 10px; color: #94a3b8; line-height: 1.8; margin-top: 3px; }

  /* ── Cancelled watermark ── */
  .cancelled-watermark {
    position: fixed;
    top: 45%;
    left: 10%;
    font-size: 80px;
    font-weight: 900;
    color: rgba(239, 68, 68, 0.07);
    text-transform: uppercase;
    letter-spacing: 8px;
    white-space: nowrap;
    transform: rotate(-35deg);
  }
</style>
</head>
<body>

@if($order->status === 'cancelled')
<div class="cancelled-watermark">CANCELLED</div>
@endif

{{-- ── HEADER ── --}}
<div class="memo-header">
  <table class="header-table">
    <tr>
      <td class="header-brand-cell">
        <div class="memo-brand-name">AmardoKar</div>
        <div class="memo-brand-tagline">আমার দোকার — Your Trusted Online Shop</div>
      </td>
      <td class="header-doc-cell">
        <div class="memo-doc-title">Purchase Memo</div>
        <div class="memo-doc-number">#{{ $order->order_number }}</div>
      </td>
    </tr>
  </table>
  <hr class="header-divider">
  <table class="meta-table">
    <tr>
      <td class="meta-cell">
        <div class="meta-label">{{ app()->getLocale()==='bn' ? 'অর্ডার তারিখ' : 'Order Date' }}</div>
        <div class="meta-value">{{ $order->created_at->format('d M Y') }}</div>
      </td>
      <td class="meta-cell">
        <div class="meta-label">{{ app()->getLocale()==='bn' ? 'অর্ডার সময়' : 'Order Time' }}</div>
        <div class="meta-value">{{ $order->created_at->format('h:i A') }}</div>
      </td>
      <td class="meta-cell" style="text-align:right;">
        <div class="meta-label">{{ app()->getLocale()==='bn' ? 'স্ট্যাটাস' : 'Order Status' }}</div>
        <div class="meta-value">
          @php
            $statusLabels = [
              'pending'    => ['Pending',    'মুলতবি'],
              'confirmed'  => ['Confirmed',  'নিশ্চিত'],
              'processing' => ['Processing', 'প্রক্রিয়াধীন'],
              'shipped'    => ['Shipped',    'পাঠানো হয়েছে'],
              'delivered'  => ['Delivered',  'ডেলিভারি হয়েছে'],
              'cancelled'  => ['Cancelled',  'বাতিল'],
            ];
            $sl = $statusLabels[$order->status] ?? [ucfirst($order->status), $order->status];
          @endphp
          {{ app()->getLocale()==='bn' ? $sl[1] : $sl[0] }}
        </div>
      </td>
    </tr>
  </table>
</div>

{{-- ── BODY ── --}}
<div class="memo-body">

  {{-- Address Row --}}
  <table class="addr-table">
    <tr>
      <td class="addr-cell-left">
        <div class="addr-box">
          <div class="addr-box-title">{{ app()->getLocale()==='bn' ? 'বিক্রেতার তথ্য' : 'Sold By' }}</div>
          <div class="addr-name">AmardoKar</div>
          <div class="addr-line">
            Online Marketplace<br>
            Bangladesh<br>
            support@amardokar.com
          </div>
        </div>
      </td>
      <td class="addr-cell-right">
        <div class="addr-box">
          <div class="addr-box-title">{{ app()->getLocale()==='bn' ? 'ডেলিভারি ঠিকানা' : 'Ship To' }}</div>
          <div class="addr-name">{{ $order->customer_name }}</div>
          <div class="addr-line">
            {{ $order->customer_address }}<br>
            @if($order->shipping_location){{ $order->shipping_location }}<br>@endif
            {{ app()->getLocale()==='bn' ? 'মোবাইল:' : 'Mobile:' }} {{ $order->customer_mobile }}
            @if($order->customer_email)<br>{{ $order->customer_email }}@endif
          </div>
        </div>
      </td>
    </tr>
  </table>

  {{-- Items Table --}}
  <table class="items-table">
    <thead>
      <tr>
        <th style="width:36px;">#</th>
        <th>{{ app()->getLocale()==='bn' ? 'পণ্যের নাম' : 'Product' }}</th>
        <th class="right" style="width:80px;">{{ app()->getLocale()==='bn' ? 'একক মূল্য' : 'Unit Price' }}</th>
        <th class="right" style="width:55px;">{{ app()->getLocale()==='bn' ? 'পরিমাণ' : 'Qty' }}</th>
        @if($order->items->where('is_discounted', true)->count())
        <th class="right" style="width:80px;">{{ app()->getLocale()==='bn' ? 'ছাড়' : 'Discount' }}</th>
        @endif
        <th class="right" style="width:90px;">{{ app()->getLocale()==='bn' ? 'মোট' : 'Subtotal' }}</th>
      </tr>
    </thead>
    <tbody>
      @foreach($order->items as $index => $item)
      <tr class="{{ $index % 2 !== 0 ? 'even' : '' }}">
        <td style="color:#94a3b8;">{{ $index + 1 }}</td>
        <td>
          <div class="item-name">{{ $item->product_name ?? '—' }}</div>
          @if($item->selected_size)
          <div class="item-meta">{{ app()->getLocale()==='bn' ? 'সাইজ:' : 'Size:' }} {{ $item->selected_size }}</div>
          @endif
          @if($item->product_type && $item->product_type !== 'simple')
          <div class="item-meta">{{ app()->getLocale()==='bn' ? 'ধরন:' : 'Type:' }} {{ ucfirst($item->product_type) }}</div>
          @endif
        </td>
        <td class="right">Taka:{{ number_format($item->price, 0) }}</td>
        <td class="right">{{ $item->quantity }}</td>
        @if($order->items->where('is_discounted', true)->count())
        <td class="right" style="color:#16a34a;">
          @if($item->is_discounted && $item->discount_amount > 0)
            -Taka:{{ number_format($item->discount_amount * $item->quantity, 0) }}
          @else
            —
          @endif
        </td>
        @endif
        <td class="right">Taka: {{ number_format($item->subtotal, 0) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{-- Bottom: Payment Left + Totals Right --}}
  <table class="bottom-table">
    <tr>
      <td class="bottom-left">
        <table class="pay-table">
          <tr>
            <td class="pay-cell">
              <div class="pay-box">
                <div class="pay-box-label">{{ app()->getLocale()==='bn' ? 'পেমেন্ট পদ্ধতি' : 'Payment Method' }}</div>
                <div class="pay-box-value">
                  @php
                    $pmLabels = [
                      'cod'    => ['Cash on Delivery','ক্যাশ অন ডেলিভারি'],
                      'bkash'  => ['bKash','বিকাশ'],
                      'nagad'  => ['Nagad','নগদ'],
                      'rocket' => ['Rocket','রকেট'],
                      'bank'   => ['Bank Transfer','ব্যাংক ট্রান্সফার'],
                      'online' => ['Online Payment','অনলাইন পেমেন্ট'],
                    ];
                    $pm = $pmLabels[$order->payment_method] ?? [ucfirst($order->payment_method ?? 'N/A'), $order->payment_method ?? 'N/A'];
                  @endphp
                  {{ app()->getLocale()==='bn' ? $pm[1] : $pm[0] }}
                </div>
              </div>
            </td>
            <td class="pay-cell pay-cell-last">
              <div class="pay-box">
                <div class="pay-box-label">{{ app()->getLocale()==='bn' ? 'পেমেন্ট স্ট্যাটাস' : 'Payment Status' }}</div>
                <div class="pay-box-value" style="color:{{ $order->payment_status === 'paid' ? '#16a34a' : ($order->payment_status === 'failed' ? '#dc2626' : '#d97706') }};">
                  @php
                    $psLabels = ['paid'=>['Paid','পরিশোধিত'],'pending'=>['Pending','বাকি'],'failed'=>['Failed','ব্যর্থ'],'refunded'=>['Refunded','ফেরত দেওয়া']];
                    $ps = $psLabels[$order->payment_status ?? 'pending'] ?? ['Pending','বাকি'];
                  @endphp
                  {{ app()->getLocale()==='bn' ? $ps[1] : $ps[0] }}
                </div>
              </div>
            </td>
          </tr>
        </table>
      </td>
      <td class="bottom-right">
        <table class="totals-table">
          <tr>
            <td class="totals-label">{{ app()->getLocale()==='bn' ? 'পণ্যের মূল্য' : 'Subtotal' }}</td>
            <td class="totals-value">Taka:{{ number_format($order->subtotal ?? ($order->total_amount - $order->shipping_charge), 0) }}</td>
          </tr>
          @if($order->shipping_charge > 0)
          <tr>
            <td class="totals-label">{{ app()->getLocale()==='bn' ? 'ডেলিভারি চার্জ' : 'Shipping' }}</td>
            <td class="totals-value">Taka: {{ number_format($order->shipping_charge, 0) }}</td>
          </tr>
          @endif
          @if($order->coupon_discount > 0)
          <tr>
            <td class="totals-label" style="color:#16a34a;">
              {{ app()->getLocale()==='bn' ? 'কুপন ছাড়' : 'Coupon Discount' }}
              @if($order->coupon_code)
                <br><span style="font-size:9px;color:#94a3b8;">{{ $order->coupon_code }}</span>
              @endif
            </td>
            <td class="totals-value" style="color:#16a34a;">-Taka: {{ number_format($order->coupon_discount, 0) }}</td>
          </tr>
          @endif
          <tr><td colspan="2"><hr class="totals-divider"></td></tr>
          <tr class="grand-row">
            <td class="grand-label">{{ app()->getLocale()==='bn' ? 'সর্বমোট' : 'Grand Total' }}</td>
            <td class="grand-value">Taka: {{ number_format($order->total_amount, 0) }}</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  {{-- Thank You --}}
  @if($order->status !== 'cancelled')
  <div class="thankyou-box">
    <div class="thankyou-title">
      {{ app()->getLocale()==='bn' ? 'আপনার কেনাকাটার জন্য ধন্যবাদ!' : 'Thank you for shopping with us!' }}
    </div>
    <div class="thankyou-sub">
      {{ app()->getLocale()==='bn' ? 'আপনার অর্ডারটি যত্ন সহকারে প্রক্রিয়া করা হচ্ছে।' : 'Your order is being carefully processed. We appreciate your trust.' }}
    </div>
  </div>
  @else
  <div class="thankyou-box cancelled-style">
    <div class="thankyou-title cancelled-style">
      {{ app()->getLocale()==='bn' ? 'এই অর্ডারটি বাতিল করা হয়েছে।' : 'This order has been cancelled.' }}
    </div>
  </div>
  @endif

  {{-- Footer --}}
  <div class="memo-footer">
    <div class="footer-brand">AmardoKar</div>
    <div class="footer-text">
      {{ app()->getLocale()==='bn'
        ? 'এই মেমো একটি কম্পিউটার-জেনারেটেড ডকুমেন্ট। কোনো প্রশ্নের জন্য আমাদের সাথে যোগাযোগ করুন।'
        : 'This is a computer-generated document. For queries, please contact our support team.' }}<br>
      support@amardokar.com &nbsp;|&nbsp; amardokar.com &nbsp;|&nbsp; {{ now()->format('d M Y') }}
    </div>
  </div>

</div>
</body>
</html>
