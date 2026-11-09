<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// ---------- Public ----------
Route::post('/login', [AuthController::class, 'login']);

// ---------- Authenticated ----------
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // ---------- Admin only ----------
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/ping', fn () => response()->json(['message' => 'Hello Admin']));
        // Phase 3: shops, products, shop-owners ekhane ashbe
    });

    // ---------- Shop owner only ----------
    Route::prefix('owner')->middleware('role:shop_owner')->group(function () {
        Route::get('/ping', fn () => response()->json(['message' => 'Hello Shop Owner']));
        // Phase 4: catalog, listings ekhane ashbe
    });
});