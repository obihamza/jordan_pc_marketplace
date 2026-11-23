<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CartItemController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderItemController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// All routes are prefixed with /api (see bootstrap/app.php).
Route::apiResources([
    'users' => UserController::class,
    'sellers' => SellerController::class,
    'addresses' => AddressController::class,
    'categories' => CategoryController::class,
    'products' => ProductController::class,
    'product-images' => ProductImageController::class,
    'carts' => CartController::class,
    'cart-items' => CartItemController::class,
    'coupons' => CouponController::class,
    'orders' => OrderController::class,
    'order-items' => OrderItemController::class,
    'payments' => PaymentController::class,
    'reviews' => ReviewController::class,
], ['parameters' => [
    'product-images' => 'productImage',
    'cart-items' => 'cartItem',
    'order-items' => 'orderItem',
]]);
