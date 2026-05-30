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

class SendOrderStatusWhatsApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries  = 3;
    public int $backoff = 60;

    private static array $statusMessages = [
        'confirmed'  => [
            'emoji' => '✅',
            'title' => 'অর্ডার কনফার্ম হয়েছে!',
            'body'  => 'আপনার অর্ডারটি কনফার্ম করা হয়েছে এবং প্যাকেজিং শুরু হবে।',
        ],
        'processing' => [
            'emoji' => '🔄',
            'title' => 'অর্ডার প্রক্রিয়াধীন!',
            'body'  => 'আপনার পণ্য প্যাক করা হচ্ছে, শীঘ্রই পাঠানো হবে।',
        ],
        'shipped'    => [
            'emoji' => '🚚',
            'title' => 'পণ্য পাঠানো হয়েছে!',
            'body'  => 'আপনার পণ্য ডেলিভারির জন্য রওনা দিয়েছে। ১–২ দিনের মধ্যে পৌঁছাবে।',
        ],
        'delivered'  => [
            'emoji' => '🎉',
            'title' => 'ডেলিভারি সম্পন্ন!',
            'body'  => 'আপনার পণ্য পৌঁছে গেছে। AmardoKar থেকে কেনাকাটার জন্য ধন্যবাদ! 💚',
        ],
        'cancelled'  => [
            'emoji' => '❌',
            'title' => 'অর্ডার বাতিল হয়েছে',
            'body'  => 'আপনার অর্ডারটি বাতিল করা হয়েছে। কোনো প্রশ্ন থাকলে আমাদের জানান।',
        ],
    ];

    public function __construct(
        public Order  $order,
        public string $oldStatus,
        public string $newStatus
    ) {}

    public function handle(WhatsAppService $whatsapp): void
    {
        $order  = $this->order;
        $mobile = $order->customer_mobile;

        if (!$mobile) return;

        // যেসব status-এ notify করবো না
        if ($this->newStatus === 'pending') return;
        if (!isset(self::$statusMessages[$this->newStatus])) return;

        $info = self::$statusMessages[$this->newStatus];

        $message = "{$info['emoji']} *{$info['title']}*\n\n"
            . "প্রিয় *{$order->customer_name}*,\n\n"
            . "{$info['body']}\n\n"
            . "📋 অর্ডার নম্বর: *#{$order->order_number}*\n"
            . "💰 মোট: ৳" . number_format($order->total_amount, 0) . "\n\n"
            . "_AmardoKar_ 💚";

        $result = $whatsapp->sendText($mobile, $message);

        if (!$result['success']) {
            Log::warning('SendOrderStatusWhatsApp failed', [
                'order_id'   => $order->id,
                'mobile'     => $mobile,
                'new_status' => $this->newStatus,
                'error'      => $result['error'] ?? 'unknown',
            ]);
            throw new \RuntimeException('WhatsApp send failed: ' . ($result['error'] ?? 'unknown'));
        }
    }
}
