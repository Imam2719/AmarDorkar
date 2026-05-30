<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LanguageMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $lang = session('language', 'en');
        app()->setLocale($lang);
        return $next($request);
    }
}