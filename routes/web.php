<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\Admin;

Route::post('/language', [LanguageController::class, 'switch'])->name('language.switch');
Route::get('/theme',    [LanguageController::class, 'theme'])->name('theme.switch');
// AJAX theme toggle (no page reload - like admin)
Route::post('/theme/ajax', function(\Illuminate\Http\Request $req) {
    $theme = $req->input('theme', 'light');
    session(['theme' => in_array($theme, ['light','dark']) ? $theme : 'light']);
    return response()->json(['ok' => true, 'theme' => $theme]);
})->name('theme.ajax');

Route::get('/',               [HomeController::class,    'index'])->name('home');
Route::get('/new-arrivals',   [HomeController::class,    'newArrivals'])->name('new-arrivals');
Route::get('/offers',         [HomeController::class,    'offers'])->name('offers');
Route::get('/product/{id}',   [ProductController::class, 'show'])->name('product.show');
Route::get('/search',         [SearchController::class,  'index'])->name('search');

Route::prefix('cart')->name('cart.')->group(function () {
    Route::post('/add',    [CartController::class, 'add'])->name('add');
    Route::post('/update', [CartController::class, 'update'])->name('update');
    Route::post('/remove', [CartController::class, 'remove'])->name('remove');
    Route::get('/items',   [CartController::class, 'items'])->name('items');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
    Route::get('/my-orders',                 [\App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/my-orders/{order}',         [\App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
    Route::post('/my-orders/{order}/cancel', [\App\Http\Controllers\OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/checkout',              [CheckoutController::class,     'index'])->name('checkout');
    Route::post('/checkout',             [CheckoutController::class,     'store'])->name('checkout.store');
    Route::get('/order-success/{order}', [CheckoutController::class,     'success'])->name('order.success');
    Route::post('/reviews',              [ProductReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}',   [ProductReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::get('/profile',    [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── IMAGE ROUTES: সরাসরি Database থেকে serve ───────────────────────────────
Route::get('/img/product/{id}', [ImageController::class, 'product'])->name('image.product');
Route::get('/img/banner/{id}',  [ImageController::class, 'banner'])->name('image.banner');
Route::get('/img/review/{id}',  [ImageController::class, 'review'])->name('image.review');

// ── ADMIN ROUTES ──────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {

    // Old admin login page now redirects to home (login popup)
    Route::get('/login',  fn() => redirect('/')->with('open_login_modal', true))->name('login');
    Route::post('/login', [Admin\AuthController::class, 'login'])->name('login.post')->middleware('throttle:5,1');

    // Admin Password Reset (OTP-based)
    Route::get('/forgot-password',  [Admin\AdminPasswordResetController::class, 'showForgotForm'])->name('password.forgot');
    Route::post('/forgot-password', [Admin\AdminPasswordResetController::class, 'sendOtp'])->name('password.send-otp')->middleware('throttle:3,5');
    Route::get('/verify-otp',       [Admin\AdminPasswordResetController::class, 'showOtpForm'])->name('password.otp-form');
    Route::post('/verify-otp',      [Admin\AdminPasswordResetController::class, 'verifyOtp'])->name('password.verify-otp')->middleware('throttle:10,1');
    Route::get('/reset-password',   [Admin\AdminPasswordResetController::class, 'showResetForm'])->name('password.reset-form');
    Route::post('/reset-password',  [Admin\AdminPasswordResetController::class, 'resetPassword'])->name('password.reset');

    Route::middleware('admin')->group(function () {
        Route::post('/logout', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // Admin theme toggle
        Route::post('/theme', function(\Illuminate\Http\Request $req) {
            $theme = $req->input('theme', 'light');
            session(['admin_theme' => in_array($theme, ['light','dark']) ? $theme : 'light']);
            return response()->json(['ok' => true]);
        })->name('theme');

        Route::resource('categories', Admin\CategoryController::class)->only(['index','store','update','destroy']);
        Route::patch('/categories/{category}/toggle', [Admin\CategoryController::class, 'toggleStatus'])->name('categories.toggle');

        Route::resource('products', Admin\ProductController::class);

        Route::resource('orders', Admin\OrderController::class)->only(['index','show','update','destroy']);
        Route::post('/orders/{order}/sell',  [Admin\OrderController::class, 'sell'])->name('orders.sell');
        Route::post('/orders/bulk-update',   [Admin\OrderController::class, 'bulkUpdate'])->name('orders.bulk-update');

        Route::resource('discounts', Admin\DiscountController::class)->except(['edit','create']);
        Route::post('/discounts/{discount}/toggle', [Admin\DiscountController::class, 'toggle'])->name('discounts.toggle');

        Route::resource('flash-sales', Admin\FlashSaleController::class)->except(['edit','create']);
        Route::post('/flash-sales/{flashSale}/toggle', [Admin\FlashSaleController::class, 'toggle'])->name('flash-sales.toggle');
        Route::post('/flash-sales/{flashSale}/products', [Admin\FlashSaleController::class, 'addProduct'])->name('flash-sales.add-product');
        Route::delete('/flash-sales/{flashSale}/products/{flashSaleProduct}', [Admin\FlashSaleController::class, 'removeProduct'])->name('flash-sales.remove-product');

        // Banners
        Route::get('/banners', [Admin\BannerController::class, 'index'])->name('banners.index');
        Route::post('/banners', [Admin\BannerController::class, 'store'])->name('banners.store');
        Route::patch('/banners/{banner}', [Admin\BannerController::class, 'update'])->name('banners.update');
        Route::delete('/banners/{banner}', [Admin\BannerController::class, 'destroy'])->name('banners.destroy');
        Route::patch('/banners/{banner}/toggle', [Admin\BannerController::class, 'toggle'])->name('banners.toggle');

        Route::get('/sales',     [Admin\SalesController::class,    'index'])->name('sales.index');
        Route::get('/customers', [Admin\CustomerController::class, 'index'])->name('customers.index');

        // FIX 7: Review Management
        Route::get('/reviews',                      [Admin\ReviewController::class, 'index'])->name('reviews.index');
        Route::patch('/reviews/{review}/approve',   [Admin\ReviewController::class, 'approve'])->name('reviews.approve');
        Route::patch('/reviews/{review}/reject',    [Admin\ReviewController::class, 'reject'])->name('reviews.reject');
        Route::delete('/reviews/{review}',          [Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');
        Route::get('/customers/{user}/orders', [Admin\CustomerController::class, 'userOrders'])->name('customers.orders');

        // ── User Messaging ────────────────────────────────────────────
        Route::post('/users/message',  [Admin\UserMessageController::class, 'send'])->name('users.message');
        Route::get('/users/list',      [Admin\UserMessageController::class, 'userList'])->name('users.list');

        // ── Admin Management (super_admin only) ───────────────────────
        Route::get('/admins',               [Admin\AdminManagementController::class, 'index'])->name('admins.index');
        Route::post('/admins',              [Admin\AdminManagementController::class, 'store'])->name('admins.store');
        Route::patch('/admins/{admin}',     [Admin\AdminManagementController::class, 'update'])->name('admins.update');
        Route::delete('/admins/{admin}',    [Admin\AdminManagementController::class, 'destroy'])->name('admins.destroy');
    });
});

require __DIR__.'/auth.php';