<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Order_detailController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\SellerController;

Route::apiResource('orders', OrderController::class);
Route::apiResource('order-details', Order_detailController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('platforms', PlatformController::class);
Route::apiResource('sellers', SellerController::class);