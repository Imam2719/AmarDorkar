<?php
// PATH: app/Jobs/SendOrderStatusUpdateEmail.php

namespace App\Jobs;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusUpdateEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries  = 3;
    public int $backoff = 60;

    private static array $statusLabels = [
        'pending'    => ['bn' => '⏳ অপেক্ষমান',        'en' => '⏳ Pending'],
        'confirmed'  => ['bn' => '✅ নিশ্চিত করা হয়েছে', 'en' => '✅ Confirmed'],
        'processing' => ['bn' => '🔄 প্রক্রিয়াধীন',       'en' => '🔄 Processing'],
        'shipped'    => ['bn' => '🚚 পাঠানো হয়েছে',       'en' => '🚚 Shipped'],
        'delivered'  => ['bn' => '🎉 ডেলিভারি সম্পন্ন',   'en' => '🎉 Delivered'],
        'cancelled'  => ['bn' => '❌ বাতিল করা হয়েছে',    'en' => '❌ Cancelled'],
    ];

    private static array $statusColors = [
        'pending'    => '#f59e0b',
        'confirmed'  => '#3b82f6',
        'processing' => '#8b5cf6',
        'shipped'    => '#06b6d4',
        'delivered'  => '#22c55e',
        'cancelled'  => '#ef4444',
    ];

    public function __construct(
        public Order  $order,
        public string $oldStatus,
        public string $newStatus,
        public string $locale = 'bn'            // ← dispatch করার সময় locale পাঠাও
    ) {}

    public function handle(): void
    {
        $order  = $this->order;
        $email  = $order->customer_email;
        $isBn   = $this->locale !== 'en';

        if (!$email) return;

        $order->loadMissing('items');

        $lang       = $isBn ? 'bn' : 'en';
        $labelStr   = self::$statusLabels[$this->newStatus][$lang]  ?? $this->newStatus;
        $labelEn    = self::$statusLabels[$this->newStatus]['en']    ?? $this->newStatus;
        $color      = self::$statusColors[$this->newStatus]          ?? '#1a9e4a';

        // ── Dhaka timezone, 12-hour ──────────────────────────────────
        $orderDate = Carbon::parse($order->created_at)
            ->timezone('Asia/Dhaka')
            ->format('d M Y');
        $orderTime = Carbon::parse($order->created_at)
            ->timezone('Asia/Dhaka')
            ->format('g:i A');

        // ── Timeline ─────────────────────────────────────────────────
        $steps       = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
        $isCancelled = $this->newStatus === 'cancelled';
        $currentIdx  = array_search($this->newStatus, $steps);

        $timelineHtml = '';
        if (!$isCancelled) {
            foreach ($steps as $i => $step) {
                $done   = ($currentIdx !== false && $i <= $currentIdx);
                $active = ($step === $this->newStatus);
                $dot    = $done ? '#22c55e' : '#e2e8f0';
                $txtCol = $done ? '#166534' : '#94a3b8';
                $lbl    = self::$statusLabels[$step][$lang] ?? $step;
                $timelineHtml .= "
                <td style=\"text-align:center;padding:0 4px;\">
                  <div style=\"width:28px;height:28px;border-radius:50%;background:{$dot};margin:0 auto 4px;display:flex;align-items:center;justify-content:center;\">
                    " . ($done ? '<span style="color:#fff;font-size:13px;">✓</span>' : '') . "
                  </div>
                  <div style=\"font-size:10px;color:{$txtCol};font-weight:" . ($active ? '700' : '400') . ";\">{$lbl}</div>
                </td>
                " . ($i < count($steps) - 1 ? "<td style=\"padding:0;width:20px;\"><div style=\"height:2px;background:" . ($done && $i < $currentIdx ? '#22c55e' : '#e2e8f0') . ";margin-bottom:18px;\"></div></td>" : '');
            }
        }

        // ── Items rows ────────────────────────────────────────────────
        $itemsHtml = '';
        foreach ($order->items as $item) {
            $size = $item->selected_size
                ? '<span style="font-size:11px;background:#e0f2fe;color:#0369a1;padding:1px 7px;border-radius:12px;margin-left:6px;">' . e($item->selected_size) . '</span>'
                : '';
            $itemsHtml .= "
            <tr>
              <td style=\"padding:10px 8px;border-bottom:1px solid #f1f5f9;font-size:13px;color:#1e293b;\">" . e($item->product_name) . $size . "</td>
              <td style=\"padding:10px 8px;border-bottom:1px solid #f1f5f9;font-size:13px;color:#475569;text-align:center;\">{$item->quantity}</td>
              <td style=\"padding:10px 8px;border-bottom:1px solid #f1f5f9;font-size:13px;color:#1a9e4a;text-align:right;font-weight:600;\">৳" . number_format($item->subtotal, 0) . "</td>
            </tr>";
        }

        // ── Per-language strings ──────────────────────────────────────
        if ($isBn) {
            $subject = $this->newStatus === 'delivered'
                ? "🎉 ডেলিভারি সম্পন্ন – #{$order->order_number} | AmardoKar"
                : "📦 অর্ডার আপডেট – #{$order->order_number} | {$labelEn} | AmardoKar";

            $deliveryNote = match ($this->newStatus) {
                'confirmed'  => 'আমরা আপনার অর্ডারটি নিশ্চিত করেছি এবং প্রস্তুতি শুরু হয়েছে।',
                'processing' => 'আপনার অর্ডারটি প্রক্রিয়া করা হচ্ছে এবং শীঘ্রই পাঠানো হবে।',
                'shipped'    => '🚚 আপনার পার্সেল রওনা হয়েছে! ১–২ কার্যদিবসের মধ্যে পৌঁছাবে।',
                'delivered'  => '🎉 আপনার অর্ডার ডেলিভারি সম্পন্ন হয়েছে। ধন্যবাদ AmardoKar থেকে কেনাকাটার জন্য!',
                'cancelled'  => '❌ আপনার অর্ডারটি বাতিল করা হয়েছে। কোনো প্রশ্ন থাকলে আমাদের সাথে যোগাযোগ করুন।',
                default      => 'আপনার অর্ডারের স্ট্যাটাস আপডেট হয়েছে।',
            };

            $t = [
                'brand_sub'     => 'অর্ডার স্ট্যাটাস আপডেট',
                'greeting'      => 'হ্যালো',
                'progress_lbl'  => '📍 অর্ডার অগ্রগতি',
                'order_lbl'     => '📋 অর্ডার তথ্য',
                'order_num'     => 'অর্ডার নং',
                'date_lbl'      => 'তারিখ',
                'items_lbl'     => '📦 অর্ডারের পণ্য',
                'col_product'   => 'পণ্য',
                'col_qty'       => 'পরিমাণ',
                'col_price'     => 'মূল্য',
                'free_ship'     => '🎁 বিনামূল্যে শিপিং',
                'ship_charge'   => 'শিপিং চার্জ',
                'total'         => 'সর্বমোট',
                'delivery_lbl'  => '📍 ডেলিভারি তথ্য',
                'name'          => 'নাম',
                'mobile'        => 'মোবাইল',
                'address'       => 'ঠিকানা',
                'area'          => 'এলাকা',
                'pay_online'    => 'Online Payment',
                'pay_cod'       => 'Cash on Delivery',
                'payment'       => 'পেমেন্ট',
                'footer'        => 'AmardoKar-এ কেনাকাটা করার জন্য ধন্যবাদ! 💚',
            ];
        } else {
            $subject = $this->newStatus === 'delivered'
                ? "🎉 Order Delivered – #{$order->order_number} | AmardoKar"
                : "📦 Order Update – #{$order->order_number} | {$labelEn} | AmardoKar";

            $deliveryNote = match ($this->newStatus) {
                'confirmed'  => 'We have confirmed your order and preparations have begun.',
                'processing' => 'Your order is being processed and will be shipped shortly.',
                'shipped'    => '🚚 Your parcel is on its way! Expected delivery in 1–2 business days.',
                'delivered'  => '🎉 Your order has been delivered. Thank you for shopping at AmardoKar!',
                'cancelled'  => '❌ Your order has been cancelled. Please contact us if you have any questions.',
                default      => 'Your order status has been updated.',
            };

            $t = [
                'brand_sub'     => 'Order Status Update',
                'greeting'      => 'Hello',
                'progress_lbl'  => '📍 Order Progress',
                'order_lbl'     => '📋 Order Details',
                'order_num'     => 'Order No',
                'date_lbl'      => 'Date',
                'items_lbl'     => '📦 Order Items',
                'col_product'   => 'Product',
                'col_qty'       => 'Qty',
                'col_price'     => 'Price',
                'free_ship'     => '🎁 Free Shipping',
                'ship_charge'   => 'Shipping Charge',
                'total'         => 'Total',
                'delivery_lbl'  => '📍 Delivery Information',
                'name'          => 'Name',
                'mobile'        => 'Mobile',
                'address'       => 'Address',
                'area'          => 'Area',
                'pay_online'    => 'Online Payment',
                'pay_cod'       => 'Cash on Delivery',
                'payment'       => 'Payment',
                'footer'        => 'Thank you for shopping at AmardoKar! 💚',
            ];
        }

        $shippingRow = $order->shipping_charge > 0
            ? '<tr><td colspan="2" style="padding:8px;font-size:13px;color:#64748b;">' . $t['ship_charge'] . '</td><td style="padding:8px;font-size:13px;color:#64748b;text-align:right;">৳' . number_format($order->shipping_charge, 0) . '</td></tr>'
            : '<tr><td colspan="2" style="padding:8px;font-size:13px;color:#22c55e;">' . $t['free_ship'] . '</td><td style="padding:8px;font-size:13px;color:#22c55e;text-align:right;">৳০</td></tr>';

        $paymentBadgeColor = $order->payment_method === 'online' ? '#dbeafe' : '#dcfce7';
        $paymentTextColor  = $order->payment_method === 'online' ? '#1d4ed8' : '#166534';
        $paymentLabel      = $order->payment_method === 'online' ? $t['pay_online'] : $t['pay_cod'];

        $timelineSection = !$isCancelled ? "
  <!-- Timeline -->
  <tr>
    <td style=\"padding:20px 32px 0;\">
      <div style=\"font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;\">{$t['progress_lbl']}</div>
      <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">
        <tr>{$timelineHtml}</tr>
      </table>
    </td>
  </tr>" : '';

        $langAttr = $isBn ? 'bn' : 'en';

        $html = '<!DOCTYPE html>
<html lang="' . $langAttr . '">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:\'Segoe UI\',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:32px 16px;">
<tr><td align="center">
<table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.10);max-width:560px;width:100%;">

  <!-- Header -->
  <tr>
    <td style="background:linear-gradient(135deg,#1a9e4a 0%,#15803d 100%);padding:28px 32px;text-align:center;">
      <div style="font-size:26px;font-weight:900;color:#fff;letter-spacing:-1px;">🛒 AmardoKar</div>
      <div style="color:rgba(255,255,255,0.9);font-size:13px;margin-top:4px;">' . $t['brand_sub'] . '</div>
    </td>
  </tr>

  <!-- Status badge -->
  <tr>
    <td style="padding:24px 32px 0;text-align:center;">
      <div style="display:inline-block;background:' . $color . '1a;border:2px solid ' . $color . ';border-radius:50px;padding:10px 28px;">
        <span style="color:' . $color . ';font-size:16px;font-weight:800;">' . $labelStr . '</span>
      </div>
    </td>
  </tr>

  <!-- Greeting -->
  <tr>
    <td style="padding:18px 32px 0;">
      <p style="font-size:15px;color:#1e293b;margin:0 0 6px;">' . $t['greeting'] . ' <strong style="color:#1a9e4a;">' . e($order->customer_name) . '</strong>,</p>
      <p style="font-size:13px;color:#64748b;margin:0;">' . $deliveryNote . '</p>
    </td>
  </tr>

  ' . $timelineSection . '

  <!-- Order info card -->
  <tr>
    <td style="padding:20px 32px 0;">
      <div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1.5px solid #86efac;border-radius:14px;padding:16px 20px;">
        <div style="font-size:11px;color:#166534;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">' . $t['order_lbl'] . '</div>
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
          <div>
            <div style="font-size:11px;color:#166534;font-weight:700;text-transform:uppercase;letter-spacing:1px;">' . $t['order_num'] . '</div>
            <div style="font-size:20px;font-weight:900;color:#15803d;">#' . e($order->order_number) . '</div>
          </div>
          <div style="text-align:right;">
            <div style="font-size:11px;color:#166534;font-weight:700;text-transform:uppercase;letter-spacing:1px;">' . $t['date_lbl'] . '</div>
            <div style="font-size:13px;color:#374151;font-weight:600;">' . $orderDate . '</div>
            <div style="font-size:12px;color:#64748b;">' . $orderTime . '</div>
          </div>
        </div>
      </div>
    </td>
  </tr>

  <!-- Items table -->
  <tr>
    <td style="padding:20px 32px 0;">
      <div style="font-size:13px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">' . $t['items_lbl'] . '</div>
      <table width="100%" cellpadding="0" cellspacing="0" style="border:1.5px solid #e2e8f0;border-radius:12px;overflow:hidden;">
        <thead>
          <tr style="background:#f8fafc;">
            <th style="font-size:11px;color:#94a3b8;padding:10px 8px;text-align:left;font-weight:600;text-transform:uppercase;">' . $t['col_product'] . '</th>
            <th style="font-size:11px;color:#94a3b8;padding:10px 8px;text-align:center;font-weight:600;text-transform:uppercase;">' . $t['col_qty'] . '</th>
            <th style="font-size:11px;color:#94a3b8;padding:10px 8px;text-align:right;font-weight:600;text-transform:uppercase;">' . $t['col_price'] . '</th>
          </tr>
        </thead>
        <tbody>' . $itemsHtml . '</tbody>
        <tfoot style="background:#f8fafc;">
          ' . $shippingRow . '
          <tr>
            <td colspan="2" style="padding:12px 8px;font-size:15px;font-weight:800;color:#1e293b;border-top:2px solid #e2e8f0;">' . $t['total'] . '</td>
            <td style="padding:12px 8px;font-size:16px;font-weight:900;color:#1a9e4a;text-align:right;border-top:2px solid #e2e8f0;">৳' . number_format($order->total_amount, 0) . '</td>
          </tr>
        </tfoot>
      </table>
    </td>
  </tr>

  <!-- Delivery info -->
  <tr>
    <td style="padding:20px 32px 0;">
      <div style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:14px;padding:16px 20px;">
        <div style="font-size:13px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;">' . $t['delivery_lbl'] . '</div>
        <table width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td style="font-size:13px;color:#64748b;padding:4px 0;width:120px;">' . $t['name'] . '</td>
            <td style="font-size:13px;color:#1e293b;font-weight:600;padding:4px 0;">' . e($order->customer_name) . '</td>
          </tr>
          <tr>
            <td style="font-size:13px;color:#64748b;padding:4px 0;">' . $t['mobile'] . '</td>
            <td style="font-size:13px;color:#1e293b;font-weight:600;padding:4px 0;">📞 ' . e($order->customer_mobile ?? '') . '</td>
          </tr>
          <tr>
            <td style="font-size:13px;color:#64748b;padding:4px 0;">' . $t['address'] . '</td>
            <td style="font-size:13px;color:#1e293b;padding:4px 0;">' . e($order->customer_address ?? '') . '</td>
          </tr>
          <tr>
            <td style="font-size:13px;color:#64748b;padding:4px 0;">' . $t['area'] . '</td>
            <td style="font-size:13px;color:#1e293b;padding:4px 0;">' . e($order->shipping_location ?? '') . '</td>
          </tr>
          <tr>
            <td style="font-size:13px;color:#64748b;padding:4px 0;">' . $t['payment'] . '</td>
            <td style="padding:4px 0;">
              <span style="background:' . $paymentBadgeColor . ';color:' . $paymentTextColor . ';padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700;">' . $paymentLabel . '</span>
            </td>
          </tr>
        </table>
      </div>
    </td>
  </tr>

  <!-- Footer -->
  <tr>
    <td style="padding:24px 32px;text-align:center;border-top:1px solid #f1f5f9;">
      <p style="font-size:12px;color:#94a3b8;margin:0 0 4px;">' . $t['footer'] . '</p>
      <p style="font-size:11px;color:#cbd5e1;margin:0;">© ' . date('Y') . ' AmardoKar. All rights reserved.</p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body></html>';

        try {
            Mail::send([], [], function ($msg) use ($order, $email, $subject, $html) {
                $msg->to($email, $order->customer_name)
                    ->subject($subject)
                    ->html($html);
            });
        } catch (\Exception $e) {
            Log::error('Order status update email failed: ' . $e->getMessage());
        }
    }
}
