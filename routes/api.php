<?php

use App\Http\Controllers\Api\AdminDashboardApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin/dashboard')->name('api.admin.dashboard.')->group(function () {
    Route::get('stats', [AdminDashboardApiController::class, 'stats'])->name('stats');
    Route::get('charts', [AdminDashboardApiController::class, 'charts'])->name('charts');
    Route::get('recent-orders', [AdminDashboardApiController::class, 'recentOrders'])->name('recent-orders');
    Route::get('top-products', [AdminDashboardApiController::class, 'topProducts'])->name('top-products');
    Route::get('low-stock', [AdminDashboardApiController::class, 'lowStock'])->name('low-stock');
});
