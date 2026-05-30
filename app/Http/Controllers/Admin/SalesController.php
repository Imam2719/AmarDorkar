<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SalesController extends Controller
{
    // ══════════════════════════════════════════════════
    //  SHARED: build the filtered base query
    // ══════════════════════════════════════════════════
    private function buildBaseQuery(Request $request)
    {
        $search       = $request->input('search', '');
        $dateFrom     = $request->input('date_from', '');
        $dateTo       = $request->input('date_to', '');
        $statusFilter = $request->input('status', '');

        $q = DB::table('orders')
            ->join('order_items', 'orders.id',             '=', 'order_items.order_id')
            ->join('products',    'order_items.product_id', '=', 'products.id')
            ->join('categories',  'products.category_id',   '=', 'categories.id')
            ->select([
                'orders.id as order_id',
                'orders.order_number',
                'orders.customer_name',
                'orders.customer_email',
                'orders.customer_mobile',
                'orders.customer_address',
                'orders.shipping_location',
                'orders.status as order_status',
                'orders.payment_method',
                'orders.payment_status',
                'orders.shipping_charge',
                'orders.total_amount',
                'orders.created_at as order_date',
                'order_items.id as item_id',
                'order_items.product_name',
                'order_items.price as unit_price',
                'order_items.quantity',
                'order_items.subtotal',
                'order_items.is_discounted',
                'order_items.original_price',
                'order_items.discount_amount',
                'order_items.product_type',
                'order_items.product_size',
                'products.id as product_id',
                'products.name_bn as product_name_bn',
                'products.name_en as product_name_en',
                'categories.name_bn as category_name_bn',
                'categories.name_en as category_name_en',
            ]);

        if ($search) {
            $q->where(function ($sq) use ($search) {
                $sq->where('orders.customer_name',    'like', "%{$search}%")
                   ->orWhere('orders.customer_email', 'like', "%{$search}%")
                   ->orWhere('orders.order_number',   'like', "%{$search}%")
                   ->orWhere('order_items.product_name', 'like', "%{$search}%")
                   ->orWhere('products.name_bn',      'like', "%{$search}%")
                   ->orWhere('products.name_en',      'like', "%{$search}%");
            });
        }
        if ($dateFrom) $q->whereDate('orders.created_at', '>=', $dateFrom);
        if ($dateTo)   $q->whereDate('orders.created_at', '<=', $dateTo);

        if ($statusFilter === 'sold') {
            $q->where('orders.status', 'delivered');
        } elseif ($statusFilter === 'cancelled') {
            $q->where('orders.status', 'cancelled');
        } else {
            $q->whereIn('orders.status', ['delivered', 'cancelled']);
        }

        return $q;
    }

    // Fresh stats query
    private function statsQuery(string $status, Request $request)
    {
        $search   = $request->input('search', '');
        $dateFrom = $request->input('date_from', '');
        $dateTo   = $request->input('date_to', '');

        $q = DB::table('orders')
            ->join('order_items', 'orders.id',             '=', 'order_items.order_id')
            ->join('products',    'order_items.product_id', '=', 'products.id')
            ->join('categories',  'products.category_id',   '=', 'categories.id')
            ->where('orders.status', $status)
            ->select([]);

        if ($search) {
            $q->where(function ($sq) use ($search) {
                $sq->where('orders.customer_name',    'like', "%{$search}%")
                   ->orWhere('orders.customer_email', 'like', "%{$search}%")
                   ->orWhere('orders.order_number',   'like', "%{$search}%")
                   ->orWhere('order_items.product_name', 'like', "%{$search}%")
                   ->orWhere('products.name_bn',      'like', "%{$search}%")
                   ->orWhere('products.name_en',      'like', "%{$search}%");
            });
        }
        if ($dateFrom) $q->whereDate('orders.created_at', '>=', $dateFrom);
        if ($dateTo)   $q->whereDate('orders.created_at', '<=', $dateTo);

        return $q;
    }

    // ══════════════════════════════════════════════════
    //  INDEX  (supports Infinite Scroll via AJAX)
    // ══════════════════════════════════════════════════
    public function index(Request $request)
    {
        $search       = $request->input('search', '');
        $dateFrom     = $request->input('date_from', '');
        $dateTo       = $request->input('date_to', '');
        $statusFilter = $request->input('status', '');
        $perPage      = 20;

        // ── AJAX / Infinite Scroll request ───────────────────────────────────
        if ($request->ajax() || $request->wantsJson()) {
            $page  = (int) $request->input('page', 1);
            $items = $this->buildBaseQuery($request)
                ->orderByDesc('orders.created_at')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            $total   = $this->buildBaseQuery($request)->count();
            $hasMore = ($page * $perPage) < $total;
            $bn      = app()->getLocale() === 'bn';

            $rows = $items->map(function ($item) use ($bn) {
                $isSold      = $item->order_status === 'delivered';
                $isCancelled = $item->order_status === 'cancelled';

                $statusHtml = $isSold
                    ? '<span class="status-badge status-delivered"><i class="fas fa-check-circle"></i>' . ($bn ? 'বিক্রয়' : 'Sold') . '</span>'
                    : '<span class="status-badge status-cancelled"><i class="fas fa-times-circle"></i>' . ($bn ? 'বাতিল' : 'Cancelled') . '</span>';

                $payHtml = '<span class="status-badge payment-' . $item->payment_status . ' mt-1" style="font-size:10px;">' . $item->payment_status . '</span>';

                $priceHtml = '';
                if ($item->is_discounted && $item->discount_amount > 0) {
                    $priceHtml .= '<small class="text-muted text-decoration-line-through d-block" style="font-size:11px;">৳' . number_format($item->original_price * $item->quantity) . '</small>';
                    $priceHtml .= '<span class="badge bg-warning text-dark" style="font-size:10px;">-৳' . number_format($item->discount_amount) . '</span><br>';
                }
                $priceHtml .= '<strong class="' . ($isSold ? 'text-success' : 'text-muted') . '" style="font-size:13px;">৳' . number_format($item->subtotal) . '</strong>';
                if ($item->shipping_charge > 0) {
                    $priceHtml .= '<br><small class="text-muted" style="font-size:11px;">+৳' . number_format($item->shipping_charge) . ' ' . ($bn ? 'শিপিং' : 'ship.') . '</small>';
                }

                $productHtml = '<span class="bengali-text fw-semibold" style="font-size:12.5px;">' . ($bn ? $item->product_name_bn : $item->product_name_en) . '</span>';
                if ($item->product_type || $item->product_size) {
                    $productHtml .= '<br><small class="text-muted" style="font-size:11px;">' . $item->product_type . ($item->product_size ? ' · ' . $item->product_size : '') . '</small>';
                }

                $addrHtml = '<small class="bengali-text" style="font-size:11px;line-height:1.4;">' . e($item->customer_address) . '</small>';
                if ($item->shipping_location) {
                    $addrHtml .= '<br><span class="badge bg-light text-dark mt-1" style="font-size:10px;"><i class="fas fa-map-marker-alt me-1" style="color:var(--danger)"></i>' . e($item->shipping_location) . '</span>';
                }

                $date = \Carbon\Carbon::parse($item->order_date);

                return [
                    'order_id'       => $item->order_id,
                    'order_number'   => $item->order_number,
                    'payment_method' => strtoupper($item->payment_method),
                    'product_html'   => $productHtml,
                    'category'       => $bn ? $item->category_name_bn : $item->category_name_en,
                    'customer_name'  => $item->customer_name,
                    'customer_email' => $item->customer_email,
                    'customer_mobile'=> $item->customer_mobile,
                    'address_html'   => $addrHtml,
                    'quantity'       => $item->quantity,
                    'price_html'     => $priceHtml,
                    'status_html'    => $statusHtml . '<br>' . $payHtml,
                    'date_fmt'       => $date->format('d M Y'),
                    'time_fmt'       => $date->format('h:i A'),
                    'is_cancelled'   => $isCancelled,
                    'order_route'    => route('admin.orders.show', $item->order_id),
                ];
            });

            return response()->json([
                'rows'    => $rows,
                'hasMore' => $hasMore,
                'total'   => $total,
                'page'    => $page,
            ]);
        }

        // ── Normal full-page load ─────────────────────────────────────────────
        $baseQuery = $this->buildBaseQuery($request);

        // First 20 rows for initial render
        $initialItems = (clone $baseQuery)
            ->orderByDesc('orders.created_at')
            ->take($perPage)
            ->get();

        $totalRecords = (clone $baseQuery)->count();

        // Stats
        $soldStats = $this->statsQuery('delivered', $request)->selectRaw(
            'COUNT(*) as total_items,
             COALESCE(SUM(order_items.quantity),0) as total_qty,
             COALESCE(SUM(order_items.subtotal),0) as total_revenue,
             COALESCE(SUM(order_items.discount_amount),0) as total_discount,
             COUNT(DISTINCT orders.id) as total_orders,
             COUNT(DISTINCT orders.customer_email) as unique_customers'
        )->first();

        $cancelledStats = $this->statsQuery('cancelled', $request)->selectRaw(
            'COUNT(*) as total_items,
             COALESCE(SUM(order_items.quantity),0) as total_qty,
             COUNT(DISTINCT orders.id) as total_orders'
        )->first();

        // Top products
        $topQ = DB::table('orders')
            ->join('order_items', 'orders.id',             '=', 'order_items.order_id')
            ->join('products',    'order_items.product_id', '=', 'products.id')
            ->where('orders.status', 'delivered');
        if ($dateFrom) $topQ->whereDate('orders.created_at', '>=', $dateFrom);
        if ($dateTo)   $topQ->whereDate('orders.created_at', '<=', $dateTo);
        $topProducts = $topQ->selectRaw('
                products.id, products.name_bn, products.name_en,
                SUM(order_items.quantity) as total_qty,
                SUM(order_items.subtotal) as total_revenue,
                COUNT(DISTINCT orders.id) as order_count')
            ->groupBy('products.id', 'products.name_bn', 'products.name_en')
            ->orderByDesc('total_qty')
            ->limit(8)->get();

        // Category stats
        $catQ = DB::table('orders')
            ->join('order_items', 'orders.id',             '=', 'order_items.order_id')
            ->join('products',    'order_items.product_id', '=', 'products.id')
            ->join('categories',  'products.category_id',   '=', 'categories.id')
            ->whereIn('orders.status', ['delivered', 'cancelled']);
        if ($dateFrom) $catQ->whereDate('orders.created_at', '>=', $dateFrom);
        if ($dateTo)   $catQ->whereDate('orders.created_at', '<=', $dateTo);
        $categoryStats = $catQ->selectRaw('
                categories.id, categories.name_bn, categories.name_en,
                categories.color, categories.icon,
                SUM(CASE WHEN orders.status = "delivered" THEN order_items.quantity ELSE 0 END) as sold_qty,
                SUM(CASE WHEN orders.status = "cancelled" THEN order_items.quantity ELSE 0 END) as cancelled_qty,
                SUM(CASE WHEN orders.status = "delivered" THEN order_items.subtotal ELSE 0 END) as revenue,
                COUNT(DISTINCT orders.id) as order_count')
            ->groupBy('categories.id','categories.name_bn','categories.name_en','categories.color','categories.icon')
            ->orderByDesc('sold_qty')->get();

        // Daily sales (Bug 1 fix unchanged)
        $dateExpr = DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m-%d', orders.created_at)"
            : "DATE(orders.created_at)";

        $orderStatsQ = DB::table('orders')->where('status', 'delivered');
        if ($dateFrom) $orderStatsQ->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo)   $orderStatsQ->whereDate('created_at', '<=', $dateTo);
        if (!$dateFrom && !$dateTo) $orderStatsQ->where('created_at', '>=', now()->subDays(30));

        $dateExprOrders = DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m-%d', created_at)"
            : "DATE(created_at)";

        $dailyOrderStats = $orderStatsQ
            ->selectRaw("{$dateExprOrders} as sale_date,
                COUNT(*) as order_count,
                AVG(total_amount) as avg_order_value")
            ->groupByRaw($dateExprOrders)
            ->get()
            ->keyBy('sale_date');

        $dailyQ = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'delivered');
        if ($dateFrom) $dailyQ->whereDate('orders.created_at', '>=', $dateFrom);
        if ($dateTo)   $dailyQ->whereDate('orders.created_at', '<=', $dateTo);
        if (!$dateFrom && !$dateTo) $dailyQ->where('orders.created_at', '>=', now()->subDays(30));

        $dailySales = $dailyQ
            ->selectRaw("{$dateExpr} as sale_date,
                SUM(order_items.quantity) as qty_sold,
                SUM(order_items.subtotal) as total_amount")
            ->groupByRaw($dateExpr)
            ->orderBy('sale_date')
            ->get()
            ->map(function ($row) use ($dailyOrderStats) {
                $stats = $dailyOrderStats->get($row->sale_date);
                $row->order_count     = $stats->order_count     ?? 0;
                $row->avg_order_value = $stats->avg_order_value ?? 0;
                return $row;
            });

        $bestDay = $dailySales->sortByDesc('total_amount')->first();

        return view('admin.sales.index', compact(
            'initialItems', 'totalRecords',
            'soldStats', 'cancelledStats',
            'topProducts', 'categoryStats', 'dailySales', 'bestDay',
            'search', 'dateFrom', 'dateTo', 'statusFilter',
            'perPage'
        ));
    }

    // ══════════════════════════════════════════════════
    //  SHARED CSV BUILDER
    // ══════════════════════════════════════════════════
    private function buildCsvContent($rows, bool $bn): string
    {
        $buffer = fopen('php://temp', 'r+');
        fputs($buffer, "\xEF\xBB\xBF");

        fputcsv($buffer, [
            $bn ? 'অর্ডার নম্বর'       : 'Order Number',
            $bn ? 'পণ্যের নাম'          : 'Product Name',
            $bn ? 'ক্যাটাগরি'           : 'Category',
            $bn ? 'ধরন'                  : 'Type',
            $bn ? 'সাইজ'                 : 'Size',
            $bn ? 'গ্রাহক'               : 'Customer',
            $bn ? 'ইমেইল'               : 'Email',
            $bn ? 'মোবাইল'              : 'Mobile',
            $bn ? 'ডেলিভারি ঠিকানা'    : 'Delivery Address',
            $bn ? 'অঞ্চল'               : 'Location',
            $bn ? 'পরিমাণ'              : 'Qty',
            $bn ? 'একক মূল্য'           : 'Unit Price',
            $bn ? 'সাবটোটাল'            : 'Subtotal',
            $bn ? 'ডিসকাউন্ট'           : 'Discount',
            $bn ? 'শিপিং'               : 'Shipping',
            $bn ? 'আইটেম টোটাল'         : 'Item Total',
            $bn ? 'পেমেন্ট পদ্ধতি'      : 'Payment Method',
            $bn ? 'পেমেন্ট স্ট্যাটাস'   : 'Payment Status',
            $bn ? 'অর্ডার স্ট্যাটাস'    : 'Order Status',
            $bn ? 'তারিখ ও সময়'         : 'Date & Time',
        ]);

        foreach ($rows as $r) {
            fputcsv($buffer, [
                $r->order_number,
                $bn ? $r->product_name_bn : $r->product_name_en,
                $bn ? $r->category_name_bn : $r->category_name_en,
                $r->product_type,
                $r->product_size,
                $r->customer_name,
                $r->customer_email,
                $r->customer_mobile,
                $r->customer_address,
                $r->shipping_location,
                $r->quantity,
                number_format($r->unit_price, 2),
                number_format($r->subtotal, 2),
                number_format($r->discount_amount, 2),
                number_format($r->shipping_charge, 2),
                number_format($r->subtotal + $r->shipping_charge, 2),
                strtoupper($r->payment_method),
                $r->payment_status,
                $r->order_status === 'delivered'
                    ? ($bn ? 'বিক্রয়' : 'Sold')
                    : ($bn ? 'বাতিল' : 'Cancelled'),
                \Carbon\Carbon::parse($r->order_date)->format('d M Y h:i A'),
            ]);
        }

        rewind($buffer);
        $content = stream_get_contents($buffer);
        fclose($buffer);
        return $content;
    }

    // ══════════════════════════════════════════════════
    //  EXPORT CSV
    // ══════════════════════════════════════════════════
    public function exportCsv(Request $request)
    {
        $rows     = $this->buildBaseQuery($request)->orderByDesc('orders.created_at')->get();
        $bn       = app()->getLocale() === 'bn';
        $filename = 'sales_history_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response($this->buildCsvContent($rows, $bn), 200, $headers);
    }

    // ══════════════════════════════════════════════════
    //  EXPORT PDF
    // ══════════════════════════════════════════════════
    public function exportPdf(Request $request)
    {
        $rows      = $this->buildBaseQuery($request)->orderByDesc('orders.created_at')->get();
        $dateFrom  = $request->input('date_from', '');
        $dateTo    = $request->input('date_to', '');
        $bn        = app()->getLocale() === 'bn';

        $totalRevenue   = $rows->where('order_status', 'delivered')->sum('subtotal');
        $totalQty       = $rows->where('order_status', 'delivered')->sum('quantity');
        $totalDiscount  = $rows->where('order_status', 'delivered')->sum('discount_amount');
        $totalCancelled  = $rows->where('order_status', 'cancelled')->pluck('order_id')->unique()->count();
        $totalSoldOrders = $rows->where('order_status', 'delivered')->pluck('order_id')->unique()->count();

        $html = view('admin.sales.pdf', compact(
            'rows', 'dateFrom', 'dateTo', 'bn',
            'totalRevenue', 'totalQty', 'totalDiscount',
            'totalCancelled', 'totalSoldOrders'
        ))->render();

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                ->setPaper('a4', 'landscape')
                ->download('sales_history_' . now()->format('Y-m-d') . '.pdf');
        }

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    // ══════════════════════════════════════════════════
    //  SEND EMAIL
    // ══════════════════════════════════════════════════
    public function sendEmail(Request $request)
    {
        $request->validate(['recipient_email' => 'required|email']);

        $rows      = $this->buildBaseQuery($request)->orderByDesc('orders.created_at')->get();
        $bn        = app()->getLocale() === 'bn';
        $dateFrom  = $request->input('date_from', '');
        $dateTo    = $request->input('date_to', '');
        $toEmail   = $request->input('recipient_email');

        $totalRevenue   = $rows->where('order_status', 'delivered')->sum('subtotal');
        $totalQty       = $rows->where('order_status', 'delivered')->sum('quantity');
        $totalOrders    = $rows->where('order_status', 'delivered')->pluck('order_id')->unique()->count();
        $totalCancelled = $rows->where('order_status', 'cancelled')->pluck('order_id')->unique()->count();

        $csvContent  = $this->buildCsvContent($rows, $bn);
        $csvFilename = 'sales_history_' . now()->format('Y-m-d') . '.csv';

        $subject = $bn ? 'বিক্রয় ইতিহাস রিপোর্ট' : 'Sales History Report';
        if ($dateFrom || $dateTo) {
            $subject .= ' (' . ($dateFrom ?: ($bn ? 'শুরু' : 'Start'))
                      . ' → ' . ($dateTo ?: ($bn ? 'এখন' : 'Now')) . ')';
        }

        try {
            Mail::send('admin.sales.email', compact(
                'rows', 'bn', 'dateFrom', 'dateTo',
                'totalRevenue', 'totalQty', 'totalOrders', 'totalCancelled'
            ), function ($msg) use ($toEmail, $subject, $csvContent, $csvFilename) {
                $msg->to($toEmail)
                    ->subject($subject)
                    ->attachData($csvContent, $csvFilename, ['mime' => 'text/csv']);
            });

            return back()->with('export_success',
                ($request->input('locale') === 'bn'
                    ? "রিপোর্ট {$toEmail} এ পাঠানো হয়েছে (CSV attached)"
                    : "Report sent to {$toEmail} with CSV attached"));
        } catch (\Exception $e) {
            return back()->with('export_error', 'Email failed: ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════
    //  WHATSAPP
    // ══════════════════════════════════════════════════
    public function whatsapp(Request $request)
    {
        $rows     = $this->buildBaseQuery($request)->get();
        $bn       = app()->getLocale() === 'bn';
        $dateFrom = $request->input('date_from', '');
        $dateTo   = $request->input('date_to', '');

        $totalOrders    = $rows->where('order_status', 'delivered')->pluck('order_id')->unique()->count();
        $totalRevenue   = $rows->where('order_status', 'delivered')->sum('subtotal');
        $totalQty       = $rows->where('order_status', 'delivered')->sum('quantity');
        $totalCancelled = $rows->where('order_status', 'cancelled')->pluck('order_id')->unique()->count();

        $period = ($dateFrom || $dateTo)
            ? (($dateFrom ?: ($bn ? 'শুরু' : 'Start')) . ' → ' . ($dateTo ?: ($bn ? 'এখন' : 'Now')))
            : ($bn ? 'সব সময়' : 'All Time');

        $text  = "🛒 *" . ($bn ? 'আমারদোকান বিক্রয় রিপোর্ট' : 'Amardorkar Sales Report') . "*\n";
        $text .= "📅 " . ($bn ? 'সময়কাল' : 'Period') . ": {$period}\n\n";
        $text .= "✅ " . ($bn ? 'বিক্রয় অর্ডার' : 'Sold Orders')   . ": " . number_format($totalOrders)    . "\n";
        $text .= "💰 " . ($bn ? 'মোট আয়'        : 'Total Revenue') . ": ৳" . number_format($totalRevenue, 2) . "\n";
        $text .= "📦 " . ($bn ? 'পণ্য বিক্রয়'   : 'Items Sold')   . ": " . number_format($totalQty)       . "\n";
        $text .= "❌ " . ($bn ? 'বাতিল অর্ডার'   : 'Cancelled')    . ": " . number_format($totalCancelled) . "\n";

        $csvUrl = route('admin.sales.export.csv', array_filter([
            'search'    => $request->input('search', ''),
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'status'    => $request->input('status', ''),
        ]));
        $text .= "\n📎 *" . ($bn ? 'CSV রিপোর্ট ডাউনলোড' : 'Download CSV Report') . ":*\n" . $csvUrl;
        $text .= "\n\n_" . ($bn ? 'আমারদোকান অ্যাডমিন প্যানেল থেকে' : 'From Amardorkar Admin Panel') . "_";

        return response()->json(['url' => 'https://wa.me/?text=' . rawurlencode($text)]);
    }
}