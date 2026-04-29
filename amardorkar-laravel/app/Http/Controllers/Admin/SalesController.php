<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $search   = $request->input('search', '');
        $dateFrom = $request->input('date_from', '');
        $dateTo   = $request->input('date_to', '');

        $applyFilters = function ($query) use ($search, $dateFrom, $dateTo) {
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('customer_name',  'like', "%{$search}%")
                      ->orWhere('customer_email','like', "%{$search}%")
                      ->orWhere('product_name',  'like', "%{$search}%")
                      ->orWhere('order_id',      (int)$search);
                });
            }
            if ($dateFrom) {
                $query->whereDate('selling_time', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('selling_time', '<=', $dateTo);
            }
            return $query;
        };

        $sales = $applyFilters(
            SalesHistory::with('product')->latest('selling_time')
        )->paginate(20)->withQueryString();

        $stats = $applyFilters(SalesHistory::query())
            ->selectRaw(
                'COUNT(*) as total_sales,
                 COALESCE(SUM(final_amount),0) as total_revenue,
                 COALESCE(SUM(quantity),0) as total_quantity,
                 COALESCE(AVG(final_amount),0) as avg_sale_amount,
                 COUNT(DISTINCT customer_email) as unique_customers,
                 COUNT(DISTINCT product_name) as products_sold'
            )->first();

        // DB-agnostic monthly chart data (last 6 months)
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            $monthExpr = "DATE_FORMAT(selling_time,'%Y-%m')";
        } elseif ($driver === 'sqlite') {
            $monthExpr = "strftime('%Y-%m', selling_time)";
        } else {
            // PostgreSQL / others
            $monthExpr = "TO_CHAR(selling_time, 'YYYY-MM')";
        }

        $monthlySales = SalesHistory::selectRaw(
            "{$monthExpr} as month,
             COUNT(*) as sales,
             COALESCE(SUM(final_amount),0) as revenue"
        )
        ->where('selling_time', '>=', now()->subMonths(6))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        return view('admin.sales.index', compact(
            'sales', 'stats', 'monthlySales', 'search', 'dateFrom', 'dateTo'
        ));
    }
}
