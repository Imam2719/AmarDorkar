<?php
namespace App\Jobs;

use App\Models\Admin;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAdminOrderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries  = 3;
    public int $backoff = 60;

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        $admins = Admin::all();
        if ($admins->isEmpty()) return;

        $order = $this->order;
        $order->loadMissing('items');

        // FIX: Send ONE email with BCC to all admins instead of N separate SMTP calls.
        // Each new admin added would previously cause N more SMTP calls per order.
        try {
            Mail::send([], [], function ($msg) use ($order, $admins) {

                $itemsHtml = '';
                foreach ($order->items as $item) {
                    $itemsHtml .= '
                    <tr>
                      <td style="padding:10px 8px;border-bottom:1px solid #f1f5f9;font-size:13px;color:#1e293b;">'
                        . e($item->product_name) . '</td>
                      <td style="padding:10px 8px;border-bottom:1px solid #f1f5f9;font-size:13px;text-align:center;">'
                        . $item->quantity . '</td>
                      <td style="padding:10px 8px;border-bottom:1px solid #f1f5f9;font-size:13px;color:#1a9e4a;font-weight:700;text-align:right;">৳'
                        . number_format($item->subtotal, 0) . '</td>
                    </tr>';
                }

                $html = '<!DOCTYPE html><html><body style="margin:0;padding:0;background:#0f172a;font-family:\'Segoe UI\',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#0f172a;padding:32px 16px;">
<tr><td align="center">
<table width="560" cellpadding="0" cellspacing="0" style="background:#1e293b;border-radius:20px;overflow:hidden;max-width:560px;width:100%;">
  <tr><td style="background:linear-gradient(135deg,#f59e0b,#d97706);padding:28px 32px;">
    <table width="100%"><tr>
      <td><div style="font-size:22px;font-weight:900;color:#fff;">🔔 AmardoKar Admin</div>
          <div style="color:rgba(255,255,255,.85);font-size:13px;margin-top:4px;">নতুন অর্ডার নোটিফিকেশন</div></td>
      <td style="text-align:right;"><div style="background:rgba(255,255,255,.2);border-radius:12px;padding:8px 16px;">
        <div style="font-size:11px;color:rgba(255,255,255,.8);text-transform:uppercase;">Total</div>
        <div style="font-size:22px;font-weight:900;color:#fff;">৳' . number_format($order->total_amount, 0) . '</div>
      </div></td>
    </tr></table>
  </td></tr>
  <tr><td style="padding:24px 32px 0;">
    <p style="font-size:15px;color:#f1f5f9;margin:0 0 4px;">AmardoKar-এ একটি নতুন অর্ডার এসেছে।</p>
    <p style="font-size:13px;color:#94a3b8;margin:0;">Order #' . e($order->order_number) . ' · ' . $order->created_at->format('d M Y, h:i A') . '</p>
  </td></tr>
  <tr><td style="padding:20px 32px 0;">
    <table width="100%"><tr>
      <td width="48%" style="background:#0f172a;border-radius:12px;padding:16px;vertical-align:top;">
        <div style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">📋 অর্ডার</div>
        <div style="font-size:18px;font-weight:900;color:#fbbf24;margin-bottom:6px;">#' . e($order->order_number) . '</div>
        <span style="background:#fef3c7;color:#92400e;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;">' . strtoupper($order->payment_method ?? 'COD') . '</span>
      </td>
      <td width="4%"></td>
      <td width="48%" style="background:#0f172a;border-radius:12px;padding:16px;vertical-align:top;">
        <div style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">👤 গ্রাহক</div>
        <div style="font-size:14px;font-weight:700;color:#f1f5f9;margin-bottom:4px;">' . e($order->customer_name) . '</div>
        <div style="font-size:12px;color:#94a3b8;">📞 ' . e($order->customer_mobile ?? '') . '</div>
        <div style="font-size:12px;color:#94a3b8;">📍 ' . e($order->customer_address ?? '') . '</div>
      </td>
    </tr></table>
  </td></tr>
  <tr><td style="padding:20px 32px 0;">
    <div style="font-size:12px;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">📦 পণ্যসমূহ</div>
    <table width="100%" style="background:#0f172a;border-radius:12px;overflow:hidden;">
      <thead><tr style="background:#1e293b;">
        <th style="font-size:11px;color:#64748b;padding:10px 8px;text-align:left;border-bottom:1px solid #334155;">পণ্য</th>
        <th style="font-size:11px;color:#64748b;padding:10px 8px;text-align:center;border-bottom:1px solid #334155;">পরিমাণ</th>
        <th style="font-size:11px;color:#64748b;padding:10px 8px;text-align:right;border-bottom:1px solid #334155;">মূল্য</th>
      </tr></thead>
      <tbody>' . $itemsHtml . '</tbody>
      <tfoot><tr>
        <td colspan="2" style="padding:12px 8px;font-weight:800;color:#f1f5f9;border-top:2px solid #334155;">মোট</td>
        <td style="padding:12px 8px;font-size:16px;font-weight:900;color:#4ade80;text-align:right;border-top:2px solid #334155;">৳' . number_format($order->total_amount, 0) . '</td>
      </tr></tfoot>
    </table>
  </td></tr>
  <tr><td style="padding:24px 32px;text-align:center;">
    <a href="' . url('/admin/orders') . '" style="display:inline-block;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;text-decoration:none;padding:14px 36px;border-radius:12px;font-size:14px;font-weight:700;">🔍 অর্ডার ম্যানেজমেন্টে যান</a>
  </td></tr>
  <tr><td style="padding:16px 32px;text-align:center;border-top:1px solid #334155;">
    <p style="font-size:11px;color:#475569;margin:0;">AmardoKar Admin Notification · © ' . date('Y') . '</p>
  </td></tr>
</table></td></tr></table>
</body></html>';

                // First admin is primary To, rest are BCC
                $firstAdmin = $admins->first();
                $msg->to($firstAdmin->email, $firstAdmin->name);

                foreach ($admins->skip(1) as $admin) {
                    $msg->bcc($admin->email, $admin->name);
                }

                $msg->subject('🛒 নতুন অর্ডার: #' . $order->order_number . ' – ৳' . number_format($order->total_amount, 0) . ' | AmardoKar')
                    ->html($html);
            });
        } catch (\Exception $e) {
            Log::error('Admin order notification failed: ' . $e->getMessage());
        }

        $order->update([
            'admin_notified'          => true,
            'admin_notification_time' => now(),
        ]);
    }
}
