<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AffiliateLinkController;
use App\Http\Controllers\AnalyticsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/ref/{code}', [AffiliateLinkController::class, 'trackClick']);

// Public Product routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/verify_token', [AuthController::class, 'verifyToken']);

    // Products
    Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    Route::get('/my-products', [ProductController::class, 'myProducts']);

    // Orders
    Route::apiResource('orders', OrderController::class);
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);

    // Affiliate Links
    Route::get('/affiliate-links', [AffiliateLinkController::class, 'index']);
    Route::post('/affiliate-links', [AffiliateLinkController::class, 'store']);
    Route::get('/affiliate-links/{affiliateLink}/whatsapp', [AffiliateLinkController::class, 'getWhatsAppLink']);
    Route::get('/affiliate-stats', [AffiliateLinkController::class, 'stats']);

    // Analytics
    Route::prefix('analytics')->group(function () {
        Route::get('/overview', [AnalyticsController::class, 'overview']);
        Route::get('/merchants', [AnalyticsController::class, 'salesByMerchant']);
        Route::get('/affiliates', [AnalyticsController::class, 'salesByAffiliate']);
        Route::get('/products', [AnalyticsController::class, 'salesByProduct']);
        Route::get('/time-series', [AnalyticsController::class, 'timeBasedAnalytics']);
        Route::get('/merchant-report', [AnalyticsController::class, 'merchantAnalytics']);
    });
});