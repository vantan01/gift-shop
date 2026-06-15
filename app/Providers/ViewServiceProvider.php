<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\CartService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer(['layouts.header', 'layouts.app', 'pages.home'], function ($view) {
            $view->with('navCategories', Category::active()->orderBy('sort_order')->get());
        });

        View::composer('layouts.header', function ($view) {
            $view->with('cartCount', app(CartService::class)->getCount());
        });
    }
}
