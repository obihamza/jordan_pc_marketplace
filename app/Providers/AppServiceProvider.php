<?php

namespace App\Providers;

use App\Http\Controllers\NotificationController;
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

        // Admin layout: the bell (notifications) and the number of seller applications waiting.
        View::composer('layouts.admin', function ($view) {
            $user = auth()->user();

            $view->with('pendingSellers', Seller::where('verification_status', 'pending')->count());
            $view->with('bellFeed', $user ? NotificationController::payload($user) : ['unread' => 0, 'items' => []]);
        });
    }
}
