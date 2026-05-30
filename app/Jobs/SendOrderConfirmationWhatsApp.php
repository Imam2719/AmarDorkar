<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmationWhatsApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries  = 3;
    public int $backoff = 60;

    public function __construct(public Order $order) {}

    public function handle(WhatsAppService $whatsapp): void
    {
        $order  = $this->order;
        $mobile = $order->customer_mobile;

        if (!$mobile) return;

        $order->loadMissing('items');

        // পণ্যের তালিকা তৈরি
        $itemsList = '';
        foreach ($order->items as $item) {
            $size = $item->selected_size ? " ({$item->selected_size})" : '';
            $itemsList .= "   • {$item->product_name}{$size} × {$item->quantity} = ৳" . number_format($item->subtotal, 0) . "\n";
        }

        $shipping = $order->shipping_charge > 0
            ? "🚚 শিপিং চার্জ: ৳" . number_format($order->shipping_charge, 0) . "\n"
            : "🎁 শিপিং: বিনামূল্যে\n";

        $paymentMethod = $order->payment_method === 'online' ? 'অনলাইন পেমেন্ট' : 'ক্যাশ অন ডেলিভারি';

        $message = "🛒 *অর্ডার পাওয়া গেছে!*\n\n"
            . "আস্সালামু আলাইকুম *{$order->customer_name}* ভাই/আপু,\n\n"
            . "আপনার অর্ডারটি আমরা পেয়েছি। শীঘ্রই কনফার্ম করা হবে।\n\n"
            . "📋 *অর্ডার নম্বর:* #{$order->order_number}\n"
            . "📅 তারিখ: " . $order->created_at->format('d M Y, h:i A') . "\n\n"
            . "📦 *পণ্যসমূহ:*\n{$itemsList}\n"
            . $shipping
            . "💰 *মোট: ৳" . number_format($order->total_amount, 0) . "*\n"
            . "💳 পেমেন্ট: {$paymentMethod}\n\n"
            . "📍 ডেলিভারি ঠিকানা:\n"
            . "{$order->customer_address}\n\n"
            . "⏱ ডেলিভারি সময়: ১–৩ কার্যদিবস\n\n"
            . "যেকোনো প্রশ্নে আমাদের সাথে যোগাযোগ করুন।\n"
            . "_AmardoKar_ 💚";

        $result = $whatsapp->sendText($mobile, $message);

        if (!$result['success']) {
            Log::warning('SendOrderConfirmationWhatsApp failed', [
                'order_id' => $order->id,
                'mobile'   => $mobile,
                'error'    => $result['error'] ?? 'unknown',
            ]);
            throw new \RuntimeException('WhatsApp send failed: ' . ($result['error'] ?? 'unknown'));
        }
    }
}
