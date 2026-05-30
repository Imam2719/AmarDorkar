<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\ReturnRequestImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReturnRequestController extends Controller
{
    private const MAX_IMAGES      = 5;
    private const MAX_IMG_SIZE_MB = 5;
    private const RETURN_WINDOW   = 7;   // দিন
    private const PER_PAGE        = 10;

    // ── User's return request list — supports infinite scroll ────────
    public function index(Request $request)
    {
        $perPage     = self::PER_PAGE;
        $returnQuery = ReturnRequest::with(['order', 'images'])
            ->where('user_id', Auth::id())
            ->latest();

        // ── AJAX Infinite Scroll ──────────────────────────────────────
        if ($request->ajax() || $request->wantsJson()) {
            $page   = (int) $request->input('page', 1);
            $total  = $returnQuery->count();
            $items  = $returnQuery->skip(($page - 1) * $perPage)->take($perPage)->get();
            $isBn   = app()->getLocale() === 'bn';

            return response()->json([
                'returns'   => $items->map(fn($ret) => [
                    'id'            => $ret->id,
                    'return_number' => $ret->return_number,
                    'status_badge'  => $ret->status_badge,
                    'status_label'  => $isBn ? $ret->status_label_bn : $ret->status_label,
                    'created_at'    => $ret->created_at->format('d M Y'),
                    'order_number'  => $ret->order->order_number ?? '—',
                    'reason_label'  => $isBn ? $ret->reason_label_bn : $ret->reason_label,
                    'refund_amount' => $ret->refund_amount ? number_format($ret->refund_amount, 2) : null,
                    'detail_url'    => route('returns.show', $ret),
                ]),
                'has_more'  => ($page * $perPage) < $total,
                'next_page' => $page + 1,
                'total'     => $total,
            ]);
        }

        // ── Normal page load ──────────────────────────────────────────
        $total   = $returnQuery->count();
        $returns = $returnQuery->take($perPage)->get();
        $hasMore = $total > $perPage;

        return view('returns.index', compact('returns', 'hasMore', 'total'));
    }

    // ── Show single return request ───────────────────────────────────
    public function show(ReturnRequest $returnRequest)
    {
        if ($returnRequest->user_id !== Auth::id()) {
            abort(403);
        }
        $returnRequest->load(['order.items.product.images', 'images']);
        return view('returns.show', compact('returnRequest'));
    }

    // ── Show create form ─────────────────────────────────────────────
    public function create(Request $request)
    {
        $orderId = $request->query('order_id');
        $order   = null;

        if ($orderId) {
            $order = Order::where('id', $orderId)
                ->where('user_id', Auth::id())
                ->with('items.product.images')
                ->first();

            if (!$order) {
                return redirect()->route('orders.index')
                    ->with('error', __('messages.return_order_not_found'));
            }

            // ── Validation 1: শুধুমাত্র delivered order ──
            if ($order->status !== 'delivered') {
                return redirect()->route('orders.show', $order)
                    ->with('error', __('messages.return_only_delivered'));
            }

            // ── Validation 2: delivered_at থাকতে হবে ──
            if (!$order->delivered_at) {
                return redirect()->route('orders.show', $order)
                    ->with('error', __('messages.return_window_expired'));
            }

            // ── Validation 3: 7 দিনের window ──
            if (!$order->canRequestReturn(self::RETURN_WINDOW)) {
                return redirect()->route('orders.show', $order)
                    ->with('error', __('messages.return_window_expired'));
            }

            // ── Validation 4: আগে থেকে pending/active return আছে কিনা ──
            $existing = ReturnRequest::where('order_id', $order->id)
                ->where('user_id', Auth::id())
                ->whereNotIn('status', ['rejected'])
                ->first();

            if ($existing) {
                return redirect()->route('returns.show', $existing)
                    ->with('info', __('messages.return_already_exists'));
            }
        }

        return view('returns.create', compact('order'));
    }

    // ── Store new return request ─────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'order_id'    => 'required|exists:orders,id',
            'reason'      => 'required|in:defective_product,wrong_item,not_as_described,damaged_in_shipping,changed_mind,missing_parts,other',
            'description' => 'required|string|min:20|max:2000',
            'images'      => 'nullable|array|max:' . self::MAX_IMAGES,
            'images.*'    => 'image|mimes:jpg,jpeg,png,webp|max:' . (self::MAX_IMG_SIZE_MB * 1024),
        ]);

        $order = Order::where('id', $request->order_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // ── Validation 1: শুধুমাত্র delivered order ──
        if ($order->status !== 'delivered') {
            return back()->with('error', __('messages.return_only_delivered'));
        }

        // ── Validation 2: delivered_at থাকতে হবে ──
        if (!$order->delivered_at) {
            return back()->with('error', __('messages.return_window_expired'));
        }

        // ── Validation 3: 7 দিনের window — delivered_at থেকে হিসাব ──
        if (!$order->canRequestReturn(self::RETURN_WINDOW)) {
            $deadline = $order->returnDeadline(self::RETURN_WINDOW);
            return back()->with('error',
                __('messages.return_window_expired') .
                ($deadline ? ' (Deadline: ' . $deadline->format('d M Y') . ')' : '')
            );
        }

        // ── Validation 4: duplicate check — rejected ছাড়া আগের request ──
        $existing = ReturnRequest::where('order_id', $order->id)
            ->where('user_id', Auth::id())
            ->whereNotIn('status', ['rejected'])
            ->first();

        if ($existing) {
            return redirect()->route('returns.show', $existing)
                ->with('info', __('messages.return_already_exists'));
        }

        // ── Save request ──────────────────────────────────────────────
        $returnRequest = ReturnRequest::create([
            'return_number' => ReturnRequest::generateReturnNumber(),
            'order_id'      => $order->id,
            'user_id'       => Auth::id(),
            'reason'        => $request->reason,
            'description'   => $request->description,
            'status'        => 'pending',
        ]);

        // ── Save images ───────────────────────────────────────────────
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $idx => $file) {
                if ($idx >= self::MAX_IMAGES) break;
                $data = base64_encode(file_get_contents($file->getRealPath()));
                ReturnRequestImage::create([
                    'return_request_id' => $returnRequest->id,
                    'image_data'        => $data,
                    'mime_type'         => $file->getMimeType(),
                    'sort_order'        => $idx,
                ]);
            }
        }

        return redirect()->route('returns.show', $returnRequest)
            ->with('success', __('messages.return_submitted'));
    }
}
