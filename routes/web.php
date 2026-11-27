<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\SellerDashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/seller', [SellerDashboardController::class, 'index'])->name('seller.dashboard');

    Route::get('/assistant', [ChatbotController::class, 'page'])->name('assistant');

    Route::get('/notifications/feed', [NotificationController::class, 'feed'])->name('notifications.feed');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifications/{id}', [NotificationController::class, 'open'])->name('notifications.open');

    Route::get('users/{user}/avatar', [UserController::class, 'avatar'])->name('users.avatar');
    Route::resource('users', UserController::class);

    Route::patch('sellers/{seller}/approve', [SellerController::class, 'approve'])->name('sellers.approve');
    Route::patch('sellers/{seller}/reject', [SellerController::class, 'reject'])->name('sellers.reject');
    Route::get('sellers/{seller}/national-id', [SellerController::class, 'nationalId'])->name('sellers.national-id');

    Route::resource('sellers', SellerController::class)->except(['index', 'show']);
    Route::resource('categories', CategoryController::class)->except(['index', 'show']);
    Route::resource('products', ProductController::class)->except(['index', 'show']);

    Route::resource('addresses', AddressController::class)->except(['show']);
    Route::resource('product-images', ProductImageController::class)->only(['index', 'create', 'store', 'update', 'destroy']);
    Route::resource('carts', CartController::class)->only(['index', 'destroy']);
    Route::resource('cart-items', CartItemController::class)->only(['store', 'update', 'destroy']);
    Route::resource('coupons', CouponController::class);
    Route::resource('orders', OrderController::class)->except(['edit']);
    Route::resource('order-items', OrderItemController::class)->only(['index', 'update']);
    Route::resource('payments', PaymentController::class)->only(['index', 'update']);
    Route::patch('reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
    Route::patch('reviews/{review}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');
    Route::resource('reviews', ReviewController::class)->except(['show']);
});

Route::get('/product-images/{productImage}/file', [ProductImageController::class, 'file'])->name('product-images.file');

Route::resource('products', ProductController::class)->only(['index', 'show']);
Route::resource('categories', CategoryController::class)->only(['index', 'show']);
Route::resource('sellers', SellerController::class)->only(['index', 'show']);

Route::post('/chatbot/message', [ChatbotController::class, 'message'])
    ->middleware('throttle:20,1')
    ->name('chatbot.message');
