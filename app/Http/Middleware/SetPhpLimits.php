<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Runtime PHP limits for image upload routes.
 * This is needed because php artisan serve ignores .htaccess.
 * For Apache/Nginx production, .htaccess / nginx.conf handles this.
 */
class SetPhpLimits
{
    public function handle(Request $request, Closure $next)
    {
        // Only apply on upload routes to avoid overhead on every request
        $uploadRoutes = [
            'admin/banners',
            'admin/products',
            'reviews',
        ];

        $path = $request->path();
        $isUpload = $request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH');

        if ($isUpload) {
            foreach ($uploadRoutes as $route) {
                if (str_contains($path, $route)) {
                    @ini_set('upload_max_filesize', '50M');
                    @ini_set('post_max_size', '55M');
                    @ini_set('memory_limit', '256M');
                    @ini_set('max_execution_time', '120');
                    break;
                }
            }
        }

        return $next($request);
    }
}
