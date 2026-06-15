<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CategoryController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('products')->name('products.')->group(function () {
    Route::get('/',        [ProductController::class, 'index'])->name('index');
    Route::get('/{slug}',  [ProductController::class, 'show'])->name('show');
});

Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

// Cart — không cần login
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/',             [CartController::class, 'index'])->name('index');
    Route::post('/add',         [CartController::class, 'add'])->name('add');
    Route::patch('/{productId}',[CartController::class, 'update'])->name('update');
    Route::delete('/{productId}',[CartController::class, 'remove'])->name('remove');
});

// Auth routes
require __DIR__ . '/auth.php';

// Routes cần đăng nhập
Route::middleware(['auth'])->group(function () {

    // Account
    Route::get('/account',   [AccountController::class, 'index'])->name('account');
    Route::patch('/account', [AccountController::class, 'update'])->name('account.update');

    // Checkout
    Route::get('/checkout',  [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/',                        [OrderController::class, 'index'])->name('index');
        Route::get('/{orderNumber}',           [OrderController::class, 'show'])->name('show');
        Route::post('/{orderNumber}/cancel',   [OrderController::class, 'cancel'])->name('cancel');
    });
});

// Admin routes
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    // Dashboard
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
         ->name('dashboard');

    // Products
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/',              [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('index');
        Route::get('/create',        [\App\Http\Controllers\Admin\ProductController::class, 'create'])->name('create');
        Route::post('/',             [\App\Http\Controllers\Admin\ProductController::class, 'store'])->name('store');
        Route::get('/{product}/edit',[\App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}',     [\App\Http\Controllers\Admin\ProductController::class, 'update'])->name('update');
        Route::patch('/{product}/toggle-active', [\App\Http\Controllers\Admin\ProductController::class, 'toggleActive'])->name('toggle-active');
        Route::delete('/{product}',  [\App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('destroy');
        Route::post('/{product}/restore', [\App\Http\Controllers\Admin\ProductController::class, 'restore'])->name('restore');
        Route::post('/generate-slug',[\App\Http\Controllers\Admin\ProductController::class, 'generateSlug'])->name('generate-slug');
    });

    // Categories
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/',                          [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('index');
        Route::post('/',                         [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('store');
        Route::get('/{category}/edit',           [\App\Http\Controllers\Admin\CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}',                [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}',             [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('destroy');
        Route::patch('/{category}/toggle-active',[\App\Http\Controllers\Admin\CategoryController::class, 'toggleActive'])->name('toggle-active');
    });

    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/',                               [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('index');
        Route::get('/{order}',                        [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('show');
        Route::patch('/{order}/status',               [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('update-status');
    });
});