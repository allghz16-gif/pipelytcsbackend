<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SalesInsightController;

// Publik
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Butuh login
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard-data',              [DashboardController::class, 'dashboardData']);
    Route::get('/dashboard/overview',          [DashboardController::class, 'overview']);
    Route::post('/dashboard/add-sale',         [DashboardController::class, 'addSale']);

    // Sales Insight
    Route::get('/sales-insights',              [SalesInsightController::class, 'index']);

    // Platform
    Route::get('/platform-comparison',         [PlatformController::class, 'platformComparison']);
    Route::get('/platforms/comparison',        [PlatformController::class, 'comparison']);
    Route::get('/platforms/metrics',           [PlatformController::class, 'metrics']);

    // Product
    Route::get('/product-performance',         [ProductController::class, 'productPerformance']);
    Route::get('/products',                    [ProductController::class, 'index']);
    Route::get('/products/heatmap',            [ProductController::class, 'heatmap']);

    // Campaign
    Route::get('/campaign-performance',        [CampaignController::class, 'campaignPerformance']);
    Route::get('/campaigns',                   [CampaignController::class, 'index']);
    Route::get('/campaigns/funnel',            [CampaignController::class, 'funnel']);

    // Settings
    Route::get('/settings',  [SettingsController::class, 'index']);
    Route::put('/settings',  [SettingsController::class, 'update']);
});