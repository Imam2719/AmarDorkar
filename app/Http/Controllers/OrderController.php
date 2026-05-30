<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class OrderController extends Controller
{
    private const PER_PAGE     = 10;
    private const RETURN_DAYS  = 7;

    /**
     * User's order history list — supports infinite scroll
     */
    public function index(Request $request)
    {
        $perPage    = self::PER_PAGE;
        $orderQuery = Order::where('user_id', auth()->id())
            ->with(['items'])
            ->latest();

        // ── AJAX Infinite Scroll ──────────────────────────────────────
        if ($request->ajax() || $request->wantsJson()) {
            $page   = (int) $request->input('page', 1);
            $total  = $orderQuery->count();
            $items  = $orderQuery->skip(($page - 1) * $perPage)->take($perPage)->get();
            $isBn   = app()->getLocale() === 'bn';

            $statusColors = [
                'pending'    => ['bg' => '#fef3c7', 'text' => '#92400e', 'border' => '#fde68a'],
                'confirmed'  => ['bg' => '#dbeafe', 'text' => '#1e40af', 'border' => '#bfdbfe'],
                'processing' => ['bg' => '#ede9fe', 'text' => '#5b21b6', 'border' => '#ddd6fe'],
                'shipped'    => ['bg' => '#e0f2fe', 'text' => '#0369a1', 'border' => '#bae6fd'],
                'delivered'  => ['bg' => '#dcfce7', 'text' => '#166534', 'border' => '#bbf7d0'],
                'cancelled'  => ['bg' => '#fee2e2', 'text' => '#991b1b', 'border' => '#fecaca'],
            ];

            return response()->json([
                'orders'   => $items->map(function ($order) use ($statusColors, $isBn) {
                    $sc = $statusColors[$order->status] ?? ['bg' => '#f3f4f6', 'text' => '#374151', 'border' => '#e5e7eb'];

                    // ── FIX: delivered_at থেকে return window হিসাব ──────
                    $retCanReturn = false;
                    $retDeadlineStr = null;
                    $retExisting  = null;
                    $retStatus    = null;

                    if ($order->status === 'delivered') {
                        // delivered_at আছে কিনা চেক করো
                        $retCanReturn   = $order->canRequestReturn(self::RETURN_DAYS);
                        $retDeadline    = $order->returnDeadline(self::RETURN_DAYS);
                        $retDeadlineStr = $retDeadline ? $retDeadline->format('d M') : null;

                        $retExisting = ReturnRequest::where('order_id', $order->id)
                            ->where('user_id', auth()->id())
                            ->whereNotIn('status', ['rejected'])
                            ->first();

                        if ($retExisting) {
                            $retStatus = [
                                'url'   => route('returns.show', $retExisting),
                                'badge' => $retExisting->status_badge,
                                'label' => $isBn ? $retExisting->status_label_bn : $retExisting->status_label,
                            ];
                        }
                    }

                    $topItems = $order->items->take(2)->map(fn($i) => [
                        'name' => $i->product_name,
                        'qty'  => $i->quantity,
                    ]);

                    return [
                        'id'               => $order->id,
                        'order_number'     => $order->order_number,
                        'created_at'       => $order->created_at->format('d M Y'),
                        'status'           => $order->status,
                        'status_label'     => ucfirst($order->status),
                        'status_bg'        => $sc['bg'],
                        'status_text'      => $sc['text'],
                        'status_border'    => $sc['border'],
                        'total_amount'     => number_format($order->total_amount, 0),
                        'payment_status'   => $order->payment_status,
                        'payment_label'    => $order->payment_status === 'paid'
                            ? ($isBn ? '✓ পেমেন্ট হয়েছে' : '✓ Paid')
                            : ($isBn ? 'পেমেন্ট বাকি' : 'Pending'),
                        'items'            => $topItems,
                        'extra_items'      => max(0, $order->items->count() - 2),
                        'detail_url'       => route('orders.show', $order),
                        'cancel_url'       => route('orders.cancel', $order),
                        'is_pending'       => $order->status === 'pending',
                        'is_delivered'     => $order->status === 'delivered',
                        'ret_existing'     => $retStatus,
                        'ret_can_return'   => $retCanReturn,
                        'ret_deadline'     => $retDeadlineStr,         // null হলে frontend hide করবে
                        'ret_create_url'   => route('returns.create', ['order_id' => $order->id]),
                        'more_items_label' => $isBn ? 'আরও পণ্য' : 'more items',
                        'is_bn'            => $isBn,
                    ];
                }),
                'has_more'  => ($page * $perPage) < $total,
                'next_page' => $page + 1,
                'total'     => $total,
            ]);
        }

        // ── Normal page load ──────────────────────────────────────────
        $total   = $orderQuery->count();
        $orders  = $orderQuery->take($perPage)->get();
        $hasMore = $total > $perPage;

        return view('orders.index', compact('orders', 'hasMore', 'total'));
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
            ['key' => 'pending',    'label' => 'Order Placed',    'icon' => 'fas fa-shopping-cart'],
            ['key' => 'confirmed',  'label' => 'Order Confirmed', 'icon' => 'fas fa-check-circle'],
            ['key' => 'processing', 'label' => 'Being Processed', 'icon' => 'fas fa-cogs'],
            ['key' => 'shipped',    'label' => 'Shipped',         'icon' => 'fas fa-truck'],
            ['key' => 'delivered',  'label' => 'Delivered',       'icon' => 'fas fa-home'],
        ];

        $statusOrder = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
        $currentIdx  = array_search($order->status, $statusOrder);

        return view('orders.show', compact('order', 'trackingSteps', 'statusOrder', 'currentIdx'));
    }

    /**
     * Download PDF memo
     */
    public function downloadMemo(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items.product.images');

        $pdf = Pdf::loadView('orders.memo', compact('order'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'dpi'                  => 150,
            ]);

        return $pdf->download('AmardoKar-Memo-' . $order->order_number . '.pdf');
    }

    /**
     * Cancel pending order
     */
    public function cancel(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return back()->with('error', 'শুধুমাত্র মুলতবি অবস্থার অর্ডার বাতিল করা যাবে।');
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    Product::where('id', $item->product_id)
                        ->increment('stock_quantity', $item->quantity);
                }
                if ($item->flash_sale_product_id) {
                    \App\Models\FlashSaleProduct::where('id', $item->flash_sale_product_id)
                        ->decrement('sold_quantity', $item->quantity);
                }
            }
            $order->update(['status' => 'cancelled']);
        });

        try {
            $this->notifyAdminOfCancellation($order);
        } catch (\Exception $e) {
            Log::error('Admin cancellation notify failed: ' . $e->getMessage());
        }

        return back()->with('success', 'আপনার অর্ডার #' . $order->order_number . ' সফলভাবে বাতিল করা হয়েছে।');
    }

    private function notifyAdminOfCancellation(Order $order): void
    {
        $admins = \App\Models\Admin::all();
        if ($admins->isEmpty()) return;

        $order->loadMissing('items');

        foreach ($admins as $admin) {
            try {
                Mail::send([], [], function ($msg) use ($order, $admin) {
                    $itemLines = $order->items->map(function ($item) {
                        return e($item->product_name) . ' × ' . $item->quantity
                             . ' = ৳' . number_format($item->subtotal, 0);
                    })->implode('<br>');

                    $html = '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;background:#f1f5f9;">'
                          . '<div style="max-width:520px;margin:30px auto;background:#fff;border-radius:12px;padding:28px;">'
                          . '<h2 style="color:#e53935;">❌ অর্ডার বাতিল হয়েছে!</h2>'
                          . '<table style="width:100%;font-size:14px;">'
                          . '<tr><td style="color:#64748b;width:140px;">Order #</td><td style="font-weight:700;color:#e53935;">' . e($order->order_number) . '</td></tr>'
                          . '<tr><td style="color:#64748b;">Customer</td><td>' . e($order->customer_name) . '</td></tr>'
                          . '<tr><td style="color:#64748b;">Total</td><td>৳' . number_format($order->total_amount, 0) . '</td></tr>'
                          . '</table><hr><p>' . $itemLines . '</p>'
                          . '</div></body></html>';

                    $msg->to($admin->email, $admin->name)
                        ->subject('অর্ডার বাতিল: ' . $order->order_number . ' | AmardoKar')
                        ->html($html);
                });
            } catch (\Exception $e) {
                Log::error('Cancel notification failed for ' . $admin->email . ': ' . $e->getMessage());
            }
        }
    }
}
