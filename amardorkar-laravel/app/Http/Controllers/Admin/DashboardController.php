<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Models\DiscountProduct;
use App\Models\SalesHistory;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Stats cards ────────────────────────────────────────────────
        $stats = [
            'total_products'  => Product::count(),
            'total_orders'    => Order::count(),
            'total_customers' => User::count(),
            'total_revenue'   => Order::whereNotIn('status', ['cancelled'])->sum('total_amount'),
            'pending_orders'  => Order::where('status', 'pending')->count(),
            'delivered_orders'=> Order::where('status', 'delivered')->count(),
            'today_orders'    => Order::whereDate('created_at', today())->count(),
            'active_discounts'=> DiscountProduct::where('is_active', true)
                                    ->where('start_date', '<=', now())
                                    ->where('end_date', '>=', now())->count(),
        ];

        // ── Recent orders (last 10) ────────────────────────────────────
        $recentOrders = Order::with('items')
            ->latest()
            ->limit(10)
            ->get();

        // ── Monthly sales chart data (last 6 months) ───────────────────
        $monthlySales = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $data  = Order::whereYear('created_at', $month->year)
                          ->whereMonth('created_at', $month->month)
                          ->whereNotIn('status', ['cancelled'])
                          ->selectRaw('COUNT(*) as orders, COALESCE(SUM(total_amount),0) as revenue')
                          ->first();

            $monthlySales[] = [
                'month'   => $month->format('M Y'),
                'orders'  => (int) $data->orders,
                'revenue' => (float) $data->revenue,
            ];
        }

        return view('admin.dashboard', compact('stats', 'recentOrders', 'monthlySales'));
    }
}
