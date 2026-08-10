<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CacheController;
use App\Http\Controllers\CateringController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuSeedController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// Clear all caches — visit /clear-cache?token=YOUR_TOKEN (set CACHE_CLEAR_TOKEN in .env)
Route::get('/clear-cache', [CacheController::class, 'clear'])->name('cache.clear');

// Seed Dock Pizza menu — visit /seed-menu?token=YOUR_TOKEN (set SEED_MENU_TOKEN in .env)
Route::get('/seed-menu', [MenuSeedController::class, 'seed'])->name('menu.seed');

// Homepage
Route::get('/', [HomeController::class, 'index'])->name('home');

// Location selection
Route::post('/select-store', [\App\Http\Controllers\LocationController::class, 'selectStore'])->name('location.select');
Route::post('/clear-store', [\App\Http\Controllers\LocationController::class, 'clearStore'])->name('location.clear');

// Menu
Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
Route::get('/menu/product/{product:slug}', [MenuController::class, 'show'])->name('menu.show');

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Checkout Flow
Route::get('/checkout', [\App\Http\Controllers\CheckoutController::class, 'showCheckout'])->name('checkout');
Route::post('/checkout', [\App\Http\Controllers\CheckoutController::class, 'placeOrder'])->name('checkout.place');

// Orders
Route::get('/order/{orderNumber}/confirmation', [OrderController::class, 'confirmation'])->name('order.confirmation');
Route::get('/order/{orderNumber}/track', [OrderController::class, 'track'])->name('order.track');
Route::middleware('auth')->group(function () {
    Route::get('/orders', [OrderController::class, 'history'])->name('orders.history');
});

// Catering
Route::get('/catering', [CateringController::class, 'index'])->name('catering.index');
Route::post('/catering', [CateringController::class, 'submit'])->name('catering.submit');
