<?php

namespace App\Providers;

use App\Http\Controllers\NotificationController;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\Seller;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('partials.nav', function ($view) {
            $view->with('navCategories', Category::orderBy('name')->take(8)->get(['id', 'name']));
        });

        View::composer('layouts.dashboard', function ($view) {
            $user = auth()->user();

            $view->with('bellFeed', $user ? NotificationController::payload($user) : ['unread' => 0, 'items' => []]);

            if ($user && ($user->isAdmin() || $user->isSeller())) {
                $view->with('modalCategories', Category::orderBy('name')->get());
            }
            if ($user?->isAdmin()) {
                $view->with('modalSellers', Seller::approved()->get());
                $view->with('pendingSellers', Seller::where('verification_status', 'pending')->count());
                $view->with('pendingReviews', Review::where('status', 'pending')->count());
            } elseif ($user?->isSeller()) {
                $view->with('pendingSales', OrderItem::where('seller_id', $user->id)->whereIn('item_status', ['pending', 'confirmed'])->count());
            }
        });
    }
}
