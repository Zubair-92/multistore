<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StoreAuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\SearchController;

// 🔹 Auth APIs
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('/store/register', [StoreAuthController::class, 'register']);
Route::post('/store/login', [StoreAuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);

// 🔹 Cart APIs (Protected)
Route::middleware('auth:sanctum')->group(function() {
    Route::post('/store/logout', [StoreAuthController::class, 'logout']);
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'viewCart']);
    Route::post('/cart/item/update/{id}', [CartController::class, 'updateItem']);
    Route::delete('/cart/item/remove/{id}', [CartController::class, 'removeItem']);
    Route::delete('/cart/clear', [CartController::class, 'clearCart']);
});

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
// ?? Category APIs
Route::get('categories', [CategoryController::class, 'index']);
Route::get('store-categories', [CategoryController::class, 'storeCategories']);

// ?? Store APIs
Route::get('stores', [StoreController::class, 'index']);
Route::get('stores/{id}', [StoreController::class, 'show']);
Route::get('stores/category/{categoryId}', [StoreController::class, 'storesByCategory']);

// ?? Order APIs (Protected)
Route::middleware('auth:sanctum')->group(function() {
    Route::post('checkout', [OrderController::class, 'checkout']);
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{id}', [OrderController::class, 'show']);
    Route::get('pos-orders', [OrderController::class, 'posOrders']);
    Route::get('pos-report', [OrderController::class, 'dailyReport']);


});

// ?? Profile APIs (Protected)
Route::middleware('auth:sanctum')->group(function() {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('profile/update', [AuthController::class, 'updateProfile']);
});

// ?? Wishlist APIs (Protected)
Route::middleware('auth:sanctum')->group(function() {
    Route::get('wishlist', [WishlistController::class, 'index']);
    Route::post('wishlist/toggle', [WishlistController::class, 'toggle']);
});

// ?? Search API
Route::get('search', [SearchController::class, 'search']);


