<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
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
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::get('/{resource}', [ContentController::class, 'index'])->name('content.index');
    Route::get('/{resource}/create', [ContentController::class, 'create'])->name('content.create');
    Route::post('/{resource}', [ContentController::class, 'store'])->name('content.store');
    Route::get('/{resource}/{id}/edit', [ContentController::class, 'edit'])->name('content.edit');
    Route::put('/{resource}/{id}', [ContentController::class, 'update'])->name('content.update');
    Route::delete('/{resource}/{id}', [ContentController::class, 'destroy'])->name('content.destroy');
});
