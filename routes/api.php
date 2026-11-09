<?php

use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\ShopController as AdminShopController;
use App\Http\Controllers\Api\Admin\ShopOwnerController as AdminShopOwnerController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Owner\CatalogController as OwnerCatalogController;
use App\Http\Controllers\Api\Owner\ListingController as OwnerListingController;
use App\Http\Controllers\Api\Public\ProductController as PublicProductController;
use App\Http\Controllers\Api\Public\ShopController as PublicShopController;
use Illuminate\Support\Facades\Route;

// ---------- Public (customer, no login) ----------
Route::post('/login', [AuthController::class, 'login']);

Route::get('/shops', [PublicShopController::class, 'index']);
Route::get('/shops/{slug}/products', [PublicShopController::class, 'products']);
Route::get('/products', [PublicProductController::class, 'index']);

// ---------- Authenticated ----------
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // ---------- Admin only ----------
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/ping', fn () => response()->json(['message' => 'Hello Admin']));

        Route::apiResource('shops', AdminShopController::class);
        Route::apiResource('products', AdminProductController::class);
        Route::apiResource('shop-owners', AdminShopOwnerController::class);
    });

    // ---------- Shop owner only ----------
    Route::prefix('owner')->middleware('role:shop_owner')->group(function () {
        Route::get('/ping', fn () => response()->json(['message' => 'Hello Shop Owner']));

        Route::get('/catalog', [OwnerCatalogController::class, 'index']);
        Route::post('/catalog', [OwnerCatalogController::class, 'store']);

        Route::apiResource('listings', OwnerListingController::class);
    });
});