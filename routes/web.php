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
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Landing page (everyone)
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Guests only: login + register
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});

/*
|--------------------------------------------------------------------------
| Logged-in users (admin / seller / buyer).
| Each controller decides what the user may see/do from auth()->user():
| isAdmin() / isSeller(); everyone who isn't an admin can act as a buyer.
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::resource('users', UserController::class);

    // Seller applications: admin approves / rejects; admin + owner can open the ID picture.
    Route::patch('sellers/{seller}/approve', [SellerController::class, 'approve'])->name('sellers.approve');
    Route::patch('sellers/{seller}/reject', [SellerController::class, 'reject'])->name('sellers.reject');
    Route::get('sellers/{seller}/national-id', [SellerController::class, 'nationalId'])->name('sellers.national-id');

    // Browsing (index/show) is public and registered below; everything else needs a login.
    // These must come BEFORE the public routes so /products/create isn't read as /products/{product}.
    Route::resource('sellers', SellerController::class)->except(['index', 'show']);
    Route::resource('categories', CategoryController::class)->except(['index', 'show']);
    Route::resource('products', ProductController::class)->except(['index', 'show']);

    Route::resource('addresses', AddressController::class)->except(['show']);
    Route::resource('product-images', ProductImageController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('carts', CartController::class)->only(['index', 'destroy']);
    Route::resource('cart-items', CartItemController::class)->only(['store', 'update', 'destroy']);
    Route::resource('coupons', CouponController::class);
    Route::resource('orders', OrderController::class)->except(['edit']);
    Route::resource('order-items', OrderItemController::class)->only(['index', 'update']);
    Route::resource('payments', PaymentController::class)->only(['index', 'update']);
    Route::resource('reviews', ReviewController::class)->except(['show']);
});

/*
|--------------------------------------------------------------------------
| Public browsing: guests can look at products, categories and sellers.
| (The controllers return the guest.* views when nobody is logged in.)
|--------------------------------------------------------------------------
*/
Route::resource('products', ProductController::class)->only(['index', 'show']);
Route::resource('categories', CategoryController::class)->only(['index', 'show']);
Route::resource('sellers', SellerController::class)->only(['index', 'show']);

/*
|--------------------------------------------------------------------------
| Chat widget endpoint (open to guests too; see config/chatbot.php)
|--------------------------------------------------------------------------
*/
Route::post('/chatbot/message', [ChatbotController::class, 'message'])
    ->middleware('throttle:20,1')
    ->name('chatbot.message');
