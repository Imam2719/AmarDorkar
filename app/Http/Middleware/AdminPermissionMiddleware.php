<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminPermissionMiddleware
{
    protected array $routePermissionMap = [
        'admin.dashboard'   => 'dashboard',
        'admin.orders'      => 'orders',
        'admin.products'    => 'products',
        'admin.categories'  => 'categories',
        'admin.discounts'   => 'discounts',
        'admin.flash-sales' => 'flash_sales',
        'admin.coupons'     => 'coupons',
        'admin.banners'     => 'banners',
        'admin.sales'       => 'sales',
        'admin.customers'   => 'customers',
        'admin.reviews'     => 'reviews',
        'admin.returns'     => 'returns',
        'admin.admins'      => null, // super_admin only
    ];

    public function handle(Request $request, Closure $next)
    {
        $admin = auth('admin')->user();

        if (!$admin) {
            return redirect('/')->with('open_login_modal', true);
        }

        // Super admin — সব কিছুতে access
        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName() ?? '';

        $requiredPermission = null;
        $routeFound = false;

        foreach ($this->routePermissionMap as $prefix => $permKey) {
            if (str_starts_with($routeName, $prefix)) {
                $requiredPermission = $permKey;
                $routeFound = true;
                break;
            }
        }

        // null permission মানে super_admin only route
        if ($routeFound && $requiredPermission === null) {
            abort(403, app()->getLocale() === 'bn'
                ? 'এই পেজে আপনার অ্যাক্সেস নেই।'
                : 'You do not have permission to access this page.');
        }

        // Route map এ নেই (theme toggle, logout ইত্যাদি) — allow
        if (!$routeFound) {
            return $next($request);
        }

        if (!$admin->hasPermission($requiredPermission)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => app()->getLocale() === 'bn'
                        ? 'এই কাজটি করার অনুমতি আপনার নেই।'
                        : 'You do not have permission to perform this action.',
                ], 403);
            }
            abort(403, app()->getLocale() === 'bn'
                ? 'এই পেজে আপনার অ্যাক্সেস নেই।'
                : 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}