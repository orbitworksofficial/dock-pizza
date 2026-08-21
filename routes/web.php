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

// Blog (public)
Route::get('/blog', [\App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [\App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');

// Generated from routes + content, never hand-edited files
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [\App\Http\Controllers\SitemapController::class, 'robots'])->name('robots');

// ── Admin ────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    // Auth (guest)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [\App\Http\Controllers\Admin\AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('login.attempt');
    });
});

// Author-level: authors and admins. Controllers still check per-record
// ownership — this only decides who may reach the door.
Route::middleware(['auth', 'admin:author'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::post('/uploads/image', [\App\Http\Controllers\Admin\ImageUploadController::class, 'store'])->name('uploads.image');

    Route::post('/logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');

    // Posts — authors reach these, but PostController re-checks ownership
    // on every record before acting on it.
    Route::get('/posts', [\App\Http\Controllers\Admin\PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/create', [\App\Http\Controllers\Admin\PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [\App\Http\Controllers\Admin\PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}/edit', [\App\Http\Controllers\Admin\PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [\App\Http\Controllers\Admin\PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [\App\Http\Controllers\Admin\PostController::class, 'destroy'])->name('posts.destroy');

    // Taxonomy
    Route::get('/categories', [\App\Http\Controllers\Admin\TaxonomyController::class, 'categories'])->name('categories.index');
    Route::post('/categories', [\App\Http\Controllers\Admin\TaxonomyController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [\App\Http\Controllers\Admin\TaxonomyController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [\App\Http\Controllers\Admin\TaxonomyController::class, 'destroyCategory'])->name('categories.destroy');

    Route::get('/tags', [\App\Http\Controllers\Admin\TaxonomyController::class, 'tags'])->name('tags.index');
    Route::post('/tags', [\App\Http\Controllers\Admin\TaxonomyController::class, 'storeTag'])->name('tags.store');
    Route::put('/tags/{tag}', [\App\Http\Controllers\Admin\TaxonomyController::class, 'updateTag'])->name('tags.update');
    Route::delete('/tags/{tag}', [\App\Http\Controllers\Admin\TaxonomyController::class, 'destroyTag'])->name('tags.destroy');
});

// Admin-only: site-wide settings authors must not change.
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/seo', [\App\Http\Controllers\Admin\PageSeoController::class, 'index'])->name('seo.index');
    Route::get('/seo/create', [\App\Http\Controllers\Admin\PageSeoController::class, 'create'])->name('seo.create');
    Route::post('/seo', [\App\Http\Controllers\Admin\PageSeoController::class, 'store'])->name('seo.store');
    Route::get('/seo/{seo}/edit', [\App\Http\Controllers\Admin\PageSeoController::class, 'edit'])->name('seo.edit');
    Route::put('/seo/{seo}', [\App\Http\Controllers\Admin\PageSeoController::class, 'update'])->name('seo.update');
    Route::delete('/seo/{seo}', [\App\Http\Controllers\Admin\PageSeoController::class, 'destroy'])->name('seo.destroy');

    Route::get('/seo-technical', [\App\Http\Controllers\Admin\TechnicalSeoController::class, 'index'])->name('seo.technical');
});
