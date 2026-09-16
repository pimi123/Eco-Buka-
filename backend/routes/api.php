<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\ContactMessageController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\NestPayPaymentController;
use App\Http\Controllers\Api\NestPayResultController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);
Route::get('/categories/{slug}/products', [ProductController::class, 'byCategory']);
Route::get('/collections', [CollectionController::class, 'index']);
Route::get('/collections/{slug}', [CollectionController::class, 'show']);
Route::get('/collections/{slug}/products', [CollectionController::class, 'products']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/featured', [ProductController::class, 'featured']);
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products/category/{slug}', [ProductController::class, 'byCategory']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:orders');
Route::post('/orders/{order}/payments/nestpay', [NestPayPaymentController::class, 'store'])->middleware('throttle:orders');
Route::post('/payments/nestpay/success', [NestPayResultController::class, 'success'])->middleware('throttle:orders');
Route::post('/payments/nestpay/failure', [NestPayResultController::class, 'failure'])->middleware('throttle:orders');
Route::post('/contact-messages', [ContactMessageController::class, 'store'])->middleware('throttle:contact-messages');

Route::get('/home/hero-banners', [HomeController::class, 'heroBanners']);
Route::get('/home/promo-card-section/{sectionKey}', [HomeController::class, 'promoCardSection']);
Route::get('/home/promo-cards/{sectionKey}', [HomeController::class, 'promoCards']);
Route::get('/home/showcase/{sectionKey}', [HomeController::class, 'showcase']);
Route::get('/home/navigation-cards/{sectionKey}', [HomeController::class, 'navigationCards']);
Route::get('/home/feature-banners/{sectionKey}', [HomeController::class, 'featureBanners']);
Route::get('/homepage', [HomeController::class, 'homepage']);

Route::match(['get', 'post'], ' ', function (Request $request) {
    return response('NestPay SUCCESS endpoint reached.', 200)
        ->header('Content-Type', 'text/plain');
});

Route::match(['get', 'post'], '/nestpay-test/failure', function (Request $request) {
    return response('NestPay FAILURE endpoint reached.', 200)
        ->header('Content-Type', 'text/plain');
});