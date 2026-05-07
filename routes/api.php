<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\SettingsController;

// Publik
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Butuh login
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/overview',            [DashboardController::class, 'overview']);
        Route::get('/sales-trend',         [DashboardController::class, 'salesTrend']);
        Route::get('/revenue-by-platform', [DashboardController::class, 'revenueByPlatform']);
        Route::get('/top-products',        [DashboardController::class, 'topProducts']);
        Route::post('/add-sale',           [DashboardController::class, 'addSale']);
    });

    // Platform
    Route::get('/platforms/comparison', [PlatformController::class, 'comparison']);
    Route::get('/platforms/metrics',    [PlatformController::class, 'metrics']);

    // Product
    Route::get('/products',         [ProductController::class, 'index']);
    Route::get('/products/heatmap', [ProductController::class, 'heatmap']);

    // Campaign
    Route::get('/campaigns',        [CampaignController::class, 'index']);
    Route::get('/campaigns/funnel', [CampaignController::class, 'funnel']);

    // Settings
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::put('/settings', [SettingsController::class, 'update']);
});