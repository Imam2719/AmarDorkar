<?php
// PATH: app/Jobs/SendOrderConfirmationEmail.php

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

class SendOrderConfirmationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries  = 3;
    public int $backoff = 60;

    public function __construct(
        public Order   $order,
        public ?string $tempPassword = null,
        public string  $locale = 'bn'          // ← dispatch করার সময় locale পাঠাও
    ) {}

    public function handle(): void
    {
        $order        = $this->order;
        $email        = $order->customer_email;
        $tempPassword = $this->tempPassword;
        $isBn         = $this->locale !== 'en';

        if (!$email) return;

        $order->loadMissing('items');

        // ── Dhaka timezone, 12-hour format ──────────────────────────
        $orderDate = Carbon::parse($order->created_at)
            ->timezone('Asia/Dhaka')
            ->format('d M Y');
        $orderTime = Carbon::parse($order->created_at)
            ->timezone('Asia/Dhaka')
            ->format('g:i A');   // e.g. 3:45 PM

        try {
            Mail::send([], [], function ($msg) use ($order, $email, $tempPassword, $isBn, $orderDate, $orderTime) {

                // ── Labels ──────────────────────────────────────────
                $t = $isBn ? [
                    'brand_sub'      => 'অর্ডার রিসিভড',
                    'badge'          => '📬 অর্ডার পাওয়া গেছে! কনফার্মেশনের জন্য অপেক্ষা করুন।',
                    'greeting'       => 'হ্যালো',
                    'suffix'         => ' ভাই/আপু,',
                    'greeting_sub'   => 'আপনার অর্ডারটি আমরা পেয়েছি এবং খুব শীঘ্রই প্রক্রিয়া করা হবে। নিচে আপনার অর্ডারের বিস্তারিত দেখুন।',
                    'order_num_lbl'  => 'Order Number',
                    'date_lbl'       => 'তারিখ',
                    'items_lbl'      => '📦 অর্ডারের পণ্য',
                    'col_product'    => 'পণ্য',
                    'col_qty'        => 'পরিমাণ',
                    'col_price'      => 'মূল্য',
                    'free_ship'      => '🎁 বিনামূল্যে শিপিং',
                    'ship_charge'    => 'শিপিং চার্জ',
                    'total'          => 'সর্বমোট',
                    'delivery_lbl'   => '📍 ডেলিভারি তথ্য',
                    'name'           => 'নাম',
                    'mobile'         => 'মোবাইল',
                    'address'        => 'ঠিকানা',
                    'area'           => 'এলাকা',
                    'payment'        => 'পেমেন্ট',
                    'pay_online'     => 'Online Payment',
                    'pay_cod'        => 'Cash on Delivery',
                    'acc_title'      => '🔑 আপনার অ্যাকাউন্ট তৈরি হয়েছে!',
                    'acc_desc'       => 'অর্ডার ট্র্যাক করতে এবং অ্যাকাউন্ট থেকে বিস্তারিত দেখতে নিচের তথ্য দিয়ে লগইন করুন:',
                    'acc_email'      => 'ইমেইল',
                    'acc_pass'       => 'পাসওয়ার্ড',
                    'acc_btn'        => '🔓 এখনই লগইন করুন',
                    'acc_warning'    => 'লগইন করার পর পাসওয়ার্ড পরিবর্তন করতে ভুলবেন না।',
                    'next_title'     => '⏭ এরপর কী হবে?',
                    'next_body'      => '✅ আমরা শীঘ্রই আপনার অর্ডার কনফার্ম করব<br>📦 পণ্য প্যাক করে ডেলিভারির জন্য প্রস্তুত করা হবে<br>🚚 ১–৩ কার্যদিবসের মধ্যে ডেলিভারি দেওয়া হবে<br>💵 পণ্য পাওয়ার সময় পেমেন্ট করুন',
                    'footer'         => 'AmardoKar-এ কেনাকাটা করার জন্য ধন্যবাদ! 💚',
                    'subject'        => '📬 অর্ডার পাওয়া গেছে – #' . $order->order_number . ' | AmardoKar',
                ] : [
                    'brand_sub'      => 'Order Received',
                    'badge'          => '📬 Order Received! Please wait for confirmation.',
                    'greeting'       => 'Hello',
                    'suffix'         => ',',
                    'greeting_sub'   => 'We have received your order and will process it shortly. Please see your order details below.',
                    'order_num_lbl'  => 'Order Number',
                    'date_lbl'       => 'Date',
                    'items_lbl'      => '📦 Order Items',
                    'col_product'    => 'Product',
                    'col_qty'        => 'Qty',
                    'col_price'      => 'Price',
                    'free_ship'      => '🎁 Free Shipping',
                    'ship_charge'    => 'Shipping Charge',
                    'total'          => 'Total',
                    'delivery_lbl'   => '📍 Delivery Information',
                    'name'           => 'Name',
                    'mobile'         => 'Mobile',
                    'address'        => 'Address',
                    'area'           => 'Area',
                    'payment'        => 'Payment',
                    'pay_online'     => 'Online Payment',
                    'pay_cod'        => 'Cash on Delivery',
                    'acc_title'      => '🔑 Your Account Has Been Created!',
                    'acc_desc'       => 'Use the credentials below to log in and track your order:',
                    'acc_email'      => 'Email',
                    'acc_pass'       => 'Password',
                    'acc_btn'        => '🔓 Login Now',
                    'acc_warning'    => 'Please change your password after logging in.',
                    'next_title'     => '⏭ What Happens Next?',
                    'next_body'      => '✅ We will confirm your order shortly<br>📦 Your items will be packed and prepared<br>🚚 Delivery within 1–3 business days<br>💵 Pay when you receive your order',
                    'footer'         => 'Thank you for shopping at AmardoKar! 💚',
                    'subject'        => '📬 Order Received – #' . $order->order_number . ' | AmardoKar',
                ];

                // ── Items rows ──────────────────────────────────────
                $itemsHtml = '';
                foreach ($order->items as $item) {
                    $size = $item->selected_size
                        ? '<span style="font-size:11px;background:#e0f2fe;color:#0369a1;padding:1px 7px;border-radius:12px;margin-left:6px;font-weight:600;">'
                          . e($item->selected_size) . '</span>'
                        : '';
                    $itemsHtml .= '
                    <tr>
                      <td style="padding:12px 8px;border-bottom:1px solid #f1f5f9;font-size:14px;color:#1e293b;">
                        ' . e($item->product_name) . $size . '
                      </td>
                      <td style="padding:12px 8px;border-bottom:1px solid #f1f5f9;font-size:14px;color:#475569;text-align:center;">
                        ' . $item->quantity . '
                      </td>
                      <td style="padding:12px 8px;border-bottom:1px solid #f1f5f9;font-size:14px;color:#1a9e4a;text-align:right;font-weight:600;">
                        ৳' . number_format($item->subtotal, 0) . '
                      </td>
                    </tr>';
                }

                $shippingRow = $order->shipping_charge > 0
                    ? '<tr><td colspan="2" style="padding:8px;font-size:13px;color:#64748b;">' . $t['ship_charge'] . '</td>
                       <td style="padding:8px;font-size:13px;color:#64748b;text-align:right;">৳' . number_format($order->shipping_charge, 0) . '</td></tr>'
                    : '<tr><td colspan="2" style="padding:8px;font-size:13px;color:#22c55e;">' . $t['free_ship'] . '</td>
                       <td style="padding:8px;font-size:13px;color:#22c55e;text-align:right;">৳০</td></tr>';

                $paymentBadgeColor = $order->payment_method === 'online' ? '#dbeafe' : '#dcfce7';
                $paymentTextColor  = $order->payment_method === 'online' ? '#1d4ed8' : '#166534';
                $paymentLabel      = $order->payment_method === 'online' ? $t['pay_online'] : $t['pay_cod'];

                // ── Password block ──────────────────────────────────
                $passwordBlock = '';
                if ($tempPassword) {
                    $loginUrl = url('/login');
                    $passwordBlock = '
  <tr>
    <td style="padding:20px 32px 0;">
      <div style="background:linear-gradient(135deg,#fffbeb,#fef3c7);border:2px solid #fbbf24;border-radius:14px;padding:18px 20px;">
        <div style="font-size:13px;font-weight:800;color:#92400e;text-transform:uppercase;letter-spacing:1px;margin-bottom:10px;">
          ' . $t['acc_title'] . '
        </div>
        <p style="font-size:13px;color:#78350f;margin:0 0 12px;">' . $t['acc_desc'] . '</p>
        <table width="100%" cellpadding="0" cellspacing="0" style="background:#fff8e1;border-radius:10px;padding:12px 16px;border:1px solid #fde68a;">
          <tr>
            <td style="font-size:13px;color:#78350f;padding:4px 0;width:90px;font-weight:600;">' . $t['acc_email'] . '</td>
            <td style="font-size:13px;color:#1e293b;padding:4px 0;font-weight:700;">' . e($email) . '</td>
          </tr>
          <tr>
            <td style="font-size:13px;color:#78350f;padding:4px 0;font-weight:600;">' . $t['acc_pass'] . '</td>
            <td style="font-size:14px;color:#1a9e4a;padding:4px 0;font-weight:800;letter-spacing:1px;">' . e($tempPassword) . '</td>
          </tr>
        </table>
        <div style="margin-top:14px;text-align:center;">
          <a href="' . $loginUrl . '" style="background:#f59e0b;color:#fff;text-decoration:none;padding:10px 24px;border-radius:8px;font-size:13px;font-weight:700;display:inline-block;">
            ' . $t['acc_btn'] . '
          </a>
        </div>
        <p style="font-size:11px;color:#a16207;margin:10px 0 0;text-align:center;">' . $t['acc_warning'] . '</p>
      </div>
    </td>
  </tr>';
                }

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
    <td style="background:linear-gradient(135deg,#1a9e4a 0%,#15803d 100%);padding:32px;text-align:center;">
      <div style="font-size:28px;font-weight:900;color:#fff;letter-spacing:-1px;">🛒 AmardoKar</div>
      <div style="color:rgba(255,255,255,0.9);font-size:14px;margin-top:6px;">' . $t['brand_sub'] . '</div>
    </td>
  </tr>

  <!-- Badge -->
  <tr>
    <td style="padding:28px 32px 0;text-align:center;">
      <div style="display:inline-block;background:#f0fdf4;border:2px solid #bbf7d0;border-radius:50px;padding:10px 24px;">
        <span style="color:#16a34a;font-size:20px;">✅</span>
        <span style="color:#166534;font-size:15px;font-weight:700;margin-left:8px;">' . $t['badge'] . '</span>
      </div>
    </td>
  </tr>

  <!-- Greeting -->
  <tr>
    <td style="padding:20px 32px 0;">
      <p style="font-size:16px;color:#1e293b;margin:0 0 6px;">' . $t['greeting'] . ' <strong style="color:#1a9e4a;">' . e($order->customer_name) . '</strong>' . $t['suffix'] . '</p>
      <p style="font-size:13px;color:#64748b;margin:0;">' . $t['greeting_sub'] . '</p>
    </td>
  </tr>

  <!-- Order number -->
  <tr>
    <td style="padding:20px 32px 0;">
      <div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1.5px solid #86efac;border-radius:14px;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
        <div>
          <div style="font-size:11px;color:#166534;font-weight:700;text-transform:uppercase;letter-spacing:1px;">' . $t['order_num_lbl'] . '</div>
          <div style="font-size:20px;font-weight:900;color:#15803d;letter-spacing:1px;margin-top:2px;">#' . e($order->order_number) . '</div>
        </div>
        <div style="text-align:right;">
          <div style="font-size:11px;color:#166534;font-weight:700;text-transform:uppercase;letter-spacing:1px;">' . $t['date_lbl'] . '</div>
          <div style="font-size:13px;color:#374151;font-weight:600;margin-top:2px;">' . $orderDate . '</div>
          <div style="font-size:12px;color:#64748b;">' . $orderTime . '</div>
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

' . $passwordBlock . '

  <!-- What happens next -->
  <tr>
    <td style="padding:20px 32px 0;">
      <div style="background:linear-gradient(135deg,#1a9e4a,#15803d);border-radius:14px;padding:20px;">
        <div style="font-size:13px;font-weight:700;color:#fff;margin-bottom:12px;">' . $t['next_title'] . '</div>
        <div style="font-size:12px;color:rgba(255,255,255,0.9);line-height:1.8;">' . $t['next_body'] . '</div>
      </div>
    </td>
  </tr>

  <!-- Footer -->
  <tr>
    <td style="padding:24px 32px;text-align:center;border-top:1px solid #f1f5f9;margin-top:20px;">
      <p style="font-size:12px;color:#94a3b8;margin:0 0 4px;">' . $t['footer'] . '</p>
      <p style="font-size:11px;color:#cbd5e1;margin:0;">© ' . date('Y') . ' AmardoKar. All rights reserved.</p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body></html>';

                $msg->to($order->customer_email, $order->customer_name)
                    ->subject($t['subject'])
                    ->html($html);
            });
        } catch (\Exception $e) {
            Log::error('Order confirmation email failed: ' . $e->getMessage());
        }
    }
}
