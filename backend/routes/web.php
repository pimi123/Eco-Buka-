<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HomepageContentController;
use App\Http\Controllers\Admin\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.post');
Route::get('/login', fn () => redirect()->route('admin.login'))->name('login');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/homepage-content-display', [HomepageContentController::class, 'index'])->name('homepage-content.index');
    Route::post('/homepage-content-display/order', [HomepageContentController::class, 'updateOrder'])->name('homepage-content.order');
    Route::get('/homepage-content-display/{section}/promo-cards', [HomepageContentController::class, 'editPromoCards'])->name('homepage-content.promo-cards.edit');
    Route::put('/homepage-content-display/{section}/promo-cards', [HomepageContentController::class, 'updatePromoCards'])->name('homepage-content.promo-cards.update');
    Route::get('/homepage-content-display/{section}/featured-products', [HomepageContentController::class, 'editFeaturedProducts'])->name('homepage-content.featured-products.edit');
    Route::put('/homepage-content-display/{section}/featured-products', [HomepageContentController::class, 'updateFeaturedProducts'])->name('homepage-content.featured-products.update');
    Route::get('/homepage-content-display/{section}/mixed-showcase', [HomepageContentController::class, 'editMixedShowcase'])->name('homepage-content.mixed-showcase.edit');
    Route::put('/homepage-content-display/{section}/mixed-showcase', [HomepageContentController::class, 'updateMixedShowcase'])->name('homepage-content.mixed-showcase.update');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::get('/product-picker', [ContentController::class, 'productPicker'])->name('product-picker');
    Route::get('/{resource}', [ContentController::class, 'index'])->name('content.index');
    Route::get('/{resource}/create', [ContentController::class, 'create'])->name('content.create');
    Route::post('/{resource}', [ContentController::class, 'store'])->name('content.store');
    Route::get('/{resource}/{id}/edit', [ContentController::class, 'edit'])->name('content.edit');
    Route::put('/{resource}/{id}', [ContentController::class, 'update'])->name('content.update');
    Route::delete('/{resource}/{id}', [ContentController::class, 'destroy'])->name('content.destroy');
});
