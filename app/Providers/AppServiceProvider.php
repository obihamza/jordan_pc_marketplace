<?php

namespace App\Providers;

use App\Http\Controllers\NotificationController;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Seller;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The views use Bootstrap 5, so render pagination links with Bootstrap markup.
        Paginator::useBootstrapFive();

        // Dashboard layout (admin + seller): the bell (notifications) and the number of seller applications waiting.
        View::composer('layouts.dashboard', function ($view) {
            $user = auth()->user();

            $view->with('bellFeed', $user ? NotificationController::payload($user) : ['unread' => 0, 'items' => []]);

            // Data for the "New product" pop-up.
            if ($user && ($user->isAdmin() || $user->isSeller())) {
                $view->with('modalCategories', Category::orderBy('name')->get());
            }
            if ($user?->isAdmin()) {
                $view->with('modalSellers', Seller::approved()->get());
                $view->with('pendingSellers', Seller::where('verification_status', 'pending')->count());
            } elseif ($user?->isSeller()) {
                // Order lines the seller still has to prepare (side bar notice + badge).
                $view->with('pendingSales', OrderItem::where('seller_id', $user->id)->whereIn('item_status', ['pending', 'confirmed'])->count());
            }
        });
    }
}
