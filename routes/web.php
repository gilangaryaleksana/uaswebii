<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RajaOngkirController;
use App\Models\Product;

// Home page
Route::get('/', function () {
    $products = \App\Models\Product::all();
    return view('welcome', compact('products'));
})->name('welcome');

Route::get('/men', function () {
    $products = \App\Models\Product::all();
    return view('men', compact('products'));
})->name('men');

Route::get('/women', function () {
    $products = \App\Models\Product::all();
    return view('women', compact('products'));
})->name('women');

Route::get('/kids', function () {
    $products = \App\Models\Product::all();
    return view('kids', compact('products'));
})->name('kids');

// Auth
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Forgot Password (request reset link)
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

// Reset Password (form password baru)
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');


// User routes (AUTH)
Route::middleware('auth')->group(function () {
    Route::get('/beranda', function () {
        $products = Product::all();
        return view('v_beranda.app', compact('products'));
    })->name('beranda')->middleware('auth');


    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])
        ->name('user.dashboard');

    // Halaman order user
    Route::get('/order', [OrderController::class, 'index'])->name('user.order');

    // Checkout dari cart
    Route::post('/order/store', [OrderController::class, 'store'])->name('order.store');

    // Batalkan order
    Route::delete('/order/{order}/cancel', [OrderController::class, 'cancel'])->name('order.cancel');

    // Detail order
    Route::get('/order/{order}', [OrderController::class, 'show'])->name('order.show');

    // Wishlist page
    Route::get('/wishlists', [WishlistsController::class, 'index'])
        ->name('user.wishlists');

    // Add to wishlist
    Route::post('/wishlists/store', [WishlistsController::class, 'store'])
        ->name('wishlists.store');

    // Remove from wishlist
    Route::delete('/wishlist/{id}', [WishlistsController::class, 'destroy'])
        ->name('wishlists.destroy');

    Route::put('/dashboard/update', [UserController::class, 'updateProfile'])
        ->name('user.updateProfile');



    // ============================
    // CART SYSTEM (BARU)
    // ============================

    // Tampilkan cart
    Route::get('/cart', [CartController::class, 'index'])
        ->name('user.cart');

    // Tambah ke cart
    Route::post('/cart/store', [CartController::class, 'store'])
        ->name('cart.store');


    // Hapus item dengan DELETE method
    Route::delete('/cart/{cart}', [CartController::class, 'destroy'])
        ->name('cart.destroy');

    // Update quantity (AJAX)
    Route::post('/cart/{cart}/update-quantity', [CartController::class, 'updateQuantity'])
        ->name('cart.update-quantity');

    // Update size (AJAX)
    Route::post('/cart/{cart}/update-size', [CartController::class, 'updateSize'])
        ->name('cart.update-size');

    // Halaman konfirmasi pembayaran
    Route::get('/payment/confirm/{order}', [PaymentController::class, 'confirm'])
    ->name('payment.confirm');

    Route::post('/payment/confirm/{order}', [PaymentController::class, 'submit'])
        ->name('payment.submit');

    Route::get('/payment/qrcode/{order}', [PaymentController::class, 'qrcode'])
        ->name('payment.qrcode');
});


Route::middleware('auth')->group(function () {
    Route::post('/order/cart/checkout', [OrderController::class, 'createFromCart'])->name('order.createFromCart');
});

Route::middleware(['auth'])->group(function () {
    // Halaman checkout

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');

    Route::post('/checkout/select', [CheckoutController::class, 'selectItems'])->name('checkout.select');

    // Proses checkout
    Route::post('/checkout/process', [CheckoutController::class, 'processCheckout'])->name('checkout.process');

    // Buy Now
    Route::post('/buy-now', [CheckoutController::class, 'buyNow'])->name('buy.now');

    Route::get('/buy-now/cancel', [CheckoutController::class, 'cancelBuyNow'])->name('buy.now.cancel');

    Route::get('/order/{id}/repay', [CheckoutController::class, 'repay'])->name('checkout.repay');

    Route::post('/checkout/qris', [CheckoutController::class, 'createQris'])->name('checkout.qris');

    Route::post('/checkout/bca', [CheckoutController::class, 'createBcaVa'])->name('checkout.bca');

    Route::post('/checkout/mandiri', [CheckoutController::class, 'createMandiriVa'])->name('checkout.mandiri');

    Route::post('/checkout/alfamart', [CheckoutController::class, 'createAlfamartVa'])->name('checkout.alfamart');

    Route::post('/checkout/indomaret', [CheckoutController::class, 'createIndomaretVa'])->name('checkout.indomaret');
});

Route::post('/midtrans/notification', [CheckoutController::class, 'handleNotification'])->name('midtrans.notification');

// Admin routes
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/dashboard/analytics', [DashboardController::class, 'index'])
        ->name('admin.dashboard.analytics');

    Route::get('/admin/dashboard/ecommerce', [DashboardController::class, 'ecommerce'])
        ->name('admin.dashboard.ecommerce');

    Route::get('/admin/dashboard/users', [DashboardController::class, 'users'])
        ->name('admin.dashboard.users');

    Route::get('/admin/dashboard/order', [DashboardController::class, 'pendingOrder'])
            ->name('admin.dashboard.pendingOrder');

    Route::get(
        'admin/dashboard/calendar',
        [DashboardController::class, 'calendar']
    )->name('admin.dashboard.calendar');

    Route::get(
        'admin/dashboard/calendar/events',
        [DashboardController::class, 'calendarEvents']
    )->name('admin.dashboard.calendar.events');


    Route::patch(
        '/admin/order/{order}/status',
        [DashboardController::class, 'updateOrderStatus']
    )->name('admin.orders.updateStatus');

    Route::put('/admin/orders/{order}/status', [OrderController::class, 'updateStatus'])
    ->name('admin.order.updateStatus');


    Route::post(
        '/admin/orders/{order}/send-payment-email',
        [OrderController::class, 'sendPaymentEmail']
    )->name('admin.orders.sendPaymentEmail');

    Route::get('/admin/dashboard/invoice', [DashboardController::class, 'invoice'])
        ->name('admin.dashboard.invoice');

    Route::get('/invoice/{order}/download', [InvoiceController::class, 'download'])
    ->name('invoice.download');

    Route::get('/invoice/{order}/preview', [InvoiceController::class, 'preview'])
    ->name('invoice.preview');
});


// Products
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/product/{id}/{slug}', [ProductController::class, 'show'])->name('product.show');

// RajaOngkir API routes
Route::get('/api/provinces', [RajaOngkirController::class, 'getProvinces']);
Route::get('/api/cities/{provinceId}', [RajaOngkirController::class, 'getCities']);
