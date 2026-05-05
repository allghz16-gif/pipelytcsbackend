<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Order_detailController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;

// Auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// CRUD routes
Route::apiResource('orders', OrderController::class);
Route::apiResource('order-details', Order_detailController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('platforms', PlatformController::class);
Route::apiResource('sellers', SellerController::class);

// Dashboard routes
Route::prefix('dashboard')->group(function () {
    Route::get('/summary',             [DashboardController::class, 'summary']);
    Route::get('/sales-trend',         [DashboardController::class, 'salesTrend']);
    Route::get('/revenue-by-platform', [DashboardController::class, 'revenueByPlatform']);
    Route::get('/peak-hours',          [DashboardController::class, 'peakHours']);
    Route::get('/best-products',       [DashboardController::class, 'bestProducts']);
    Route::get('/low-stock',           [DashboardController::class, 'lowStock']);
    Route::get('/platform-comparison', [DashboardController::class, 'platformComparison']);
});

// Test route (hapus nanti)
Route::get('/test', function() {
    return App\Models\Order::first();
});