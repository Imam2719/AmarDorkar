<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;

class ReturnRequestController extends Controller
{
    public function index(Request $request)
    {
        $search       = $request->input('search', '');
        $statusFilter = $request->input('status', '');

        $query = ReturnRequest::with(['order', 'user', 'images'])->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")
                                                    ->orWhere('email', 'like', "%{$search}%"))
                  ->orWhereHas('order', fn($o) => $o->where('order_number', 'like', "%{$search}%"));
            });
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $stats = [
            'total'            => ReturnRequest::count(),
            'pending'          => ReturnRequest::where('status', 'pending')->count(),
            'under_review'     => ReturnRequest::where('status', 'under_review')->count(),
            'approved'         => ReturnRequest::where('status', 'approved')->count(),
            'rejected'         => ReturnRequest::where('status', 'rejected')->count(),
            'refund_initiated' => ReturnRequest::where('status', 'refund_initiated')->count(),
            'refunded'         => ReturnRequest::where('status', 'refunded')->count(),
        ];

        // AJAX infinite scroll request
        if ($request->ajax() || $request->wantsJson()) {
            $page    = (int) $request->input('page', 1);
            $perPage = 20;
            $total   = $query->count();
            $returns = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

            return response()->json([
                'returns'  => $returns->map(fn($ret) => [
                    'id'            => $ret->id,
                    'return_number' => $ret->return_number,
                    'customer_name' => $ret->user->name  ?? '—',
                    'customer_email'=> $ret->user->email ?? '',
                    'order_number'  => $ret->order->order_number ?? '—',
                    'order_url'     => route('admin.orders.show', $ret->order_id),
                    'reason_en'     => $ret->reason_label,
                    'reason_bn'     => $ret->reason_label_bn,
                    'status'        => $ret->status,
                    'status_badge'  => $ret->status_badge,
                    'status_en'     => $ret->status_label,
                    'status_bn'     => $ret->status_label_bn,
                    'date'          => $ret->created_at->format('d M Y'),
                    'view_url'      => route('admin.returns.show', $ret),
                    'delete_url'    => route('admin.returns.destroy', $ret),
                ]),
                'total'     => $total,
                'has_more'  => ($page * $perPage) < $total,
                'next_page' => $page + 1,
            ]);
        }

        // Normal page load — first 20
        $total   = $query->count();
        $returns = $query->take(20)->get();
        $hasMore = $total > 20;

        return view('admin.returns.index', compact(
            'returns', 'stats', 'search', 'statusFilter', 'total', 'hasMore'
        ));
    }

    public function show(ReturnRequest $returnRequest)
    {
        $returnRequest->load(['order.items.product.images', 'user', 'images']);
        return view('admin.returns.show', compact('returnRequest'));
    }

    public function update(Request $request, ReturnRequest $returnRequest)
    {
        $request->validate([
            'status'        => 'required|in:pending,under_review,approved,rejected,refund_initiated,refunded',
            'admin_notes'   => 'nullable|string|max:2000',
            'refund_amount' => 'nullable|numeric|min:0',
            'refund_method' => 'nullable|string|max:100',
        ]);

        $data = [
            'status'        => $request->status,
            'admin_notes'   => $request->admin_notes,
            'refund_amount' => $request->refund_amount,
            'refund_method' => $request->refund_method,
        ];

        if (in_array($request->status, ['approved', 'rejected', 'refunded']) && !$returnRequest->resolved_at) {
            $data['resolved_at'] = now();
        }

        $returnRequest->update($data);

        return back()->with('success',
            app()->getLocale() === 'bn'
                ? 'রিটার্ন রিকোয়েস্ট আপডেট হয়েছে।'
                : 'Return request updated successfully.'
        );
    }

    public function destroy(ReturnRequest $returnRequest)
    {
        $returnRequest->delete();

        return redirect()->route('admin.returns.index')->with('success',
            app()->getLocale() === 'bn'
                ? 'রিটার্ন রিকোয়েস্ট মুছে ফেলা হয়েছে।'
                : 'Return request deleted.'
        );
    }
}