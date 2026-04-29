<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    /**
     * User's order history list
     */
    public function index(Request $request)
    {
        $orders = Order::where('user_id', auth()->id())
            ->with(['items.product.images'])
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Single order detail + tracking
     */
    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items.product.images');

        $trackingSteps = [
            ['key' => 'pending',    'label' => 'Order Placed',   'icon' => 'fas fa-shopping-cart'],
            ['key' => 'confirmed',  'label' => 'Order Confirmed','icon' => 'fas fa-check-circle'],
            ['key' => 'processing', 'label' => 'Being Processed','icon' => 'fas fa-cogs'],
            ['key' => 'shipped',    'label' => 'Shipped',        'icon' => 'fas fa-truck'],
            ['key' => 'delivered',  'label' => 'Delivered',      'icon' => 'fas fa-home'],
        ];

        $statusOrder = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
        $currentIdx  = array_search($order->status, $statusOrder);

        return view('orders.show', compact('order', 'trackingSteps', 'statusOrder', 'currentIdx'));
    }

    /**
     * User নিজে pending order cancel করতে পারবে।
     * Cancel হলে stock restore হবে এবং admin-কে notify করা হবে।
     */
    public function cancel(Order $order)
    {
        // Security: only the owner
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // শুধু pending orders cancel করা যাবে
        if ($order->status !== 'pending') {
            return back()->with('error', 'শুধুমাত্র মুলতবি অবস্থার অর্ডার বাতিল করা যাবে।');
        }

        DB::transaction(function () use ($order) {
            // Stock restore করো
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    Product::where('id', $item->product_id)
                        ->increment('stock_quantity', $item->quantity);
                }

                // Flash sale quota restore
                if ($item->flash_sale_product_id) {
                    \App\Models\FlashSaleProduct::where('id', $item->flash_sale_product_id)
                        ->decrement('sold_quantity', $item->quantity);
                }
            }

            $order->update(['status' => 'cancelled']);
        });

        // Admin-কে email notify করো (background)
        try {
            $this->notifyAdminOfCancellation($order);
        } catch (\Exception $e) {
            Log::error('Admin cancellation notify failed: ' . $e->getMessage());
        }

        return back()->with('success', 'আপনার অর্ডার #' . $order->order_number . ' সফলভাবে বাতিল করা হয়েছে।');
    }

    /**
     * User order cancel করলে সব admin-কে email পাঠাও।
     */
    private function notifyAdminOfCancellation(Order $order): void
    {
        $admins = \App\Models\Admin::all();
        if ($admins->isEmpty()) return;

        $order->loadMissing('items');
        $user = auth()->user();

        foreach ($admins as $admin) {
            try {
                Mail::send([], [], function ($msg) use ($order, $admin, $user) {
                    $itemLines = $order->items->map(function ($item) {
                        return e($item->product_name) . ' × ' . $item->quantity
                             . ' = ৳' . number_format($item->subtotal, 0);
                    })->implode('<br>');

                    $html = '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;background:#f1f5f9;">'
                          . '<div style="max-width:520px;margin:30px auto;background:#fff;border-radius:12px;padding:28px;box-shadow:0 2px 12px rgba(0,0,0,.08);">'
                          . '<h2 style="color:#e53935;margin:0 0 6px;">❌ অর্ডার বাতিল হয়েছে!</h2>'
                          . '<p style="color:#64748b;font-size:13px;margin:0 0 20px;">AmardoKar — গ্রাহক নিজে বাতিল করেছেন</p>'
                          . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                          . '<tr><td style="padding:6px 0;color:#64748b;width:140px;">Order #</td><td style="font-weight:700;color:#e53935;">' . e($order->order_number) . '</td></tr>'
                          . '<tr><td style="padding:6px 0;color:#64748b;">Customer</td><td>' . e($order->customer_name) . '</td></tr>'
                          . '<tr><td style="padding:6px 0;color:#64748b;">Mobile</td><td>' . e($order->customer_mobile) . '</td></tr>'
                          . '<tr><td style="padding:6px 0;color:#64748b;">Address</td><td>' . e($order->customer_address) . '</td></tr>'
                          . '<tr><td style="padding:6px 0;color:#64748b;">Cancelled By</td><td style="color:#e53935;font-weight:600;">User (নিজে)</td></tr>'
                          . '<tr><td style="padding:6px 0;color:#64748b;">Order Total</td><td style="font-weight:700;">৳' . number_format($order->total_amount, 0) . '</td></tr>'
                          . '</table>'
                          . '<hr style="margin:18px 0;border:none;border-top:1px solid #e2e8f0;">'
                          . '<p style="font-size:13px;color:#475569;margin:0 0 6px;"><strong>Items (stock restored):</strong></p>'
                          . '<p style="font-size:13px;color:#475569;margin:0;">' . $itemLines . '</p>'
                          . '</div></body></html>';

                    $msg->to($admin->email, $admin->name)
                        ->subject('অর্ডার বাতিল: ' . $order->order_number . ' | AmardoKar')
                        ->html($html);
                });
            } catch (\Exception $e) {
                Log::error('Admin cancel notification failed for ' . $admin->email . ': ' . $e->getMessage());
            }
        }
    }
}
