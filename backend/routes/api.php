<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/featured', [ProductController::class, 'featured']);
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products/category/{slug}', [ProductController::class, 'byCategory']);
Route::get('/products/{slug}', [ProductController::class, 'show']);

Route::get('/home/hero-banners', [HomeController::class, 'heroBanners']);
Route::get('/home/promo-cards/{sectionKey}', [HomeController::class, 'promoCards']);
Route::get('/home/showcase/{sectionKey}', [HomeController::class, 'showcase']);
Route::get('/home/navigation-cards/{sectionKey}', [HomeController::class, 'navigationCards']);
Route::get('/home/feature-banners/{sectionKey}', [HomeController::class, 'featureBanners']);
Route::get('/homepage', [HomeController::class, 'homepage']);
