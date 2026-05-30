<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        // ── Registered users query ────────────────────────────────────
        $userQuery = User::select(
                'users.id',
                'users.name',
                'users.email',
                'users.mobile',
                'users.created_at',
                DB::raw('COUNT(DISTINCT o.id) as total_orders'),
                DB::raw('COALESCE(SUM(o.total_amount),0) as total_spent'),
                DB::raw('MAX(o.created_at) as last_order_date')
            )
            ->leftJoin('orders as o', function ($join) {
                $join->on('o.user_id', '=', 'users.id')
                     ->whereIn('o.status', ['delivered','processing','shipped']);
            })
            ->groupBy('users.id','users.name','users.email','users.mobile','users.created_at')
            ->orderByDesc(DB::raw('MAX(o.created_at)'));

        if ($search) {
            $userQuery->where(function ($q) use ($search) {
                $q->where('users.name',   'like', "%{$search}%")
                  ->orWhere('users.email',  'like', "%{$search}%")
                  ->orWhere('users.mobile', 'like', "%{$search}%");
            });
        }

        // ── AJAX infinite scroll request (skip/take) ──────────────────
        if ($request->ajax() || $request->wantsJson()) {
            $page    = (int) $request->input('page', 1);
            $perPage = 20;
            $total   = $userQuery->count();
            $users   = $userQuery->skip(($page - 1) * $perPage)->take($perPage)->get();

            return response()->json([
                'users' => $users->map(fn($u) => [
                    'id'              => $u->id,
                    'name'            => $u->name,
                    'email'           => $u->email,
                    'mobile'          => $u->mobile,
                    'total_orders'    => $u->total_orders,
                    'total_spent'     => number_format($u->total_spent),
                    'joined'          => $u->created_at->format('d M Y'),
                    'last_order_date' => $u->last_order_date
                        ? \Carbon\Carbon::parse($u->last_order_date)->format('d M Y')
                        : null,
                    'orders_url'      => route('admin.customers.orders', $u->id),
                    'initial'         => strtoupper(substr($u->name, 0, 1)),
                ]),
                'total'     => $total,
                'has_more'  => ($page * $perPage) < $total,
                'next_page' => $page + 1,
            ]);
        }

        // ── Normal page load: প্রথম 20টা server-side (infinite scroll বাকিটা করবে) ──
        $total   = $userQuery->count();
        $users   = $userQuery->take(20)->get();
        $hasMore = $total > 20;

        // ── All-time stats (cache 30 min) ─────────────────────────────
        $allStats = Cache::remember('admin_customer_all_stats', 1800, function () {
            return SalesHistory::selectRaw(
                'COUNT(DISTINCT customer_email) as total_customers,
                 COUNT(*) as total_sales,
                 SUM(final_amount) as total_revenue,
                 SUM(CASE WHEN is_discounted=1 THEN discount_amount ELSE 0 END) as total_discounts_given'
            )->first();
        });

        // ── Top 5 customers by spending (cache 30 min) ────────────────
        $topCustomers = Cache::remember('admin_customer_top5', 1800, function () {
            return SalesHistory::select(
                'customer_name', 'customer_email',
                DB::raw('SUM(final_amount) as total_spent'),
                DB::raw('COUNT(*) as purchase_count')
            )
            ->groupBy('customer_email','customer_name')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();
        });

        // ── Recent activity (cache 5 min) ─────────────────────────────
        $recentActivity = Cache::remember('admin_customer_recent', 300, function () {
            return SalesHistory::select(
                'customer_name','customer_email','product_name','final_amount','selling_time'
            )
            ->latest('selling_time')
            ->limit(10)
            ->get();
        });

        // ── Category-wise buying stats (cache 30 min) ─────────────────
        $locale = app()->getLocale();
        $categoryStats = Cache::remember("admin_category_stats_{$locale}", 1800, function () use ($locale) {
            return DB::table('sales_history as sh')
                ->join('products as p',  'p.id',  '=', 'sh.product_id')
                ->join('categories as c','c.id',  '=', 'p.category_id')
                ->select(
                    'c.id as category_id',
                    DB::raw('CASE WHEN "' . $locale . '" = "bn" THEN c.name_bn ELSE c.name_en END as category_name'),
                    'c.color',
                    'c.icon',
                    DB::raw('SUM(sh.quantity) as total_qty'),
                    DB::raw('SUM(sh.final_amount) as total_revenue'),
                    DB::raw('COUNT(DISTINCT sh.customer_email) as unique_buyers'),
                    DB::raw('COUNT(*) as total_transactions')
                )
                ->groupBy('c.id','c.name_bn','c.name_en','c.color','c.icon')
                ->orderByDesc('total_qty')
                ->get();
        });

        // ── Top product per category (cache 30 min) ───────────────────
        $topProductsByCategory = Cache::remember("admin_top_products_by_cat_{$locale}", 1800, function () use ($locale) {
            return DB::table('sales_history as sh')
                ->join('products as p',  'p.id', '=', 'sh.product_id')
                ->join('categories as c','c.id', '=', 'p.category_id')
                ->select(
                    'c.id as category_id',
                    'p.id as product_id',
                    'sh.product_name',
                    DB::raw('SUM(sh.quantity) as total_sold'),
                    DB::raw('SUM(sh.final_amount) as revenue')
                )
                ->groupBy('c.id','p.id','sh.product_name')
                ->orderByDesc('total_sold')
                ->get()
                ->groupBy('category_id')
                ->map(fn($g) => $g->first());
        });

        return view('admin.customers.index', compact(
            'users', 'allStats', 'topCustomers', 'recentActivity',
            'search', 'categoryStats', 'topProductsByCategory',
            'total', 'hasMore'
        ));
    }

    /**
     * Get a specific user's full order history (AJAX or full page)
     */
    public function userOrders(User $user, Request $request)
    {
        $orders = \App\Models\Order::where('user_id', $user->id)
            ->with('items')
            ->latest()
            ->get();

        $totalSpent = $orders->whereIn('status', ['delivered', 'processing', 'shipped'])->sum('total_amount');

        if ($request->wantsJson()) {
            return response()->json([
                'user'        => $user->only(['id','name','email','mobile']),
                'orders'      => $orders,
                'total_orders' => $orders->count(),
                'total_spent'  => $totalSpent,
            ]);
        }

        return view('admin.customers.order-history', compact('user', 'orders', 'totalSpent'));
    }
}