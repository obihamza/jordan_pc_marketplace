<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Every controller decides what the user may see/do from auth()->user()
// (isAdmin() / isSeller(); everyone who isn't an admin can act as a buyer),
// so the routes only need the "auth" middleware.
// NOTE: the "auth" middleware redirects guests to a route named "login" -
// add your login routes when you build authentication.
Route::middleware('auth')->group(function () {

    Route::resource('users', UserController::class);
    Route::resource('sellers', SellerController::class);
    Route::resource('addresses', AddressController::class)->except(['show']);
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('product-images', ProductImageController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('carts', CartController::class)->only(['index', 'destroy']);
    Route::resource('cart-items', CartItemController::class)->only(['store', 'update', 'destroy']);
    Route::resource('coupons', CouponController::class);
    Route::resource('orders', OrderController::class)->except(['edit']);
    Route::resource('order-items', OrderItemController::class)->only(['index', 'update']);
    Route::resource('payments', PaymentController::class)->only(['index', 'update']);
    Route::resource('reviews', ReviewController::class)->except(['show']);
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
