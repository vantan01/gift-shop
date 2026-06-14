<?php

namespace App\Providers;

use App\Services\CartService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Services\OrderService;
use App\Services\DashboardService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Đăng ký CartService là singleton
        // Cùng một request luôn dùng chung 1 instance
        $this->app->singleton(CartService::class, function () {
            return new CartService();
        });


        $this->app->singleton(OrderService::class, function ($app) {
            return new OrderService($app->make(CartService::class));
        });


        $this->app->singleton(DashboardService::class, fn() => new DashboardService());
    }

    public function boot(): void
    {
        Paginator::useTailwind();
    }
}
