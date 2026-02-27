<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForceAuthController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SyncController;
use App\Http\Middleware\RefreshVendorData;
use App\Modules\Vendor\Controllers\VendorController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/orders/my', [OrderController::class, 'myOrders'])->middleware(['auth', 'verified'])->name('orders.my');
Route::resource('orders', OrderController::class)->middleware(['auth', 'verified']);

// Activity routes
Route::get('/activity', [ActivityController::class, 'show'])->middleware(['auth', 'verified'])->name('activity.index');
Route::get('/api/activity/recent', [ActivityController::class, 'recent'])->middleware(['auth', 'verified'])->name('activity.recent');

// Help routes
Route::get('/help', [HelpController::class, 'index'])->middleware(['auth', 'verified'])->name('help.index');

// Vendor routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/vendor/activate', [VendorController::class, 'showActivateForm'])->name('vendor.activate');
    Route::post('/vendor/activate', [VendorController::class, 'activate'])->name('vendor.activate.store');
    Route::get('/vendor/dashboard', [VendorController::class, 'dashboard'])->name('vendor.dashboard')->middleware('refresh.vendor');
    Route::get('/api/vendor/can-activate', [VendorController::class, 'canActivate'])->name('vendor.can-activate');
    Route::get('/vendor/edit', [VendorController::class, 'edit'])->name('vendor.edit');
    Route::patch('/vendor/{vendor}', [VendorController::class, 'update'])->name('vendor.update');
    Route::delete('/vendor/{vendor}', [VendorController::class, 'destroy'])->name('vendor.destroy');
});

Route::get('/debug/native', function () {
    return response()->json([
        'is_native' => \App\Helpers\ImageHelper::isNativeEnvironment(),
        'user_agent' => request()->userAgent(),
        'headers' => request()->headers->all(),
        'env_native' => env('NATIVEPHP_RUNNING'),
        'app_url' => config('app.url'),
        'request_url' => request()->getSchemeAndHttpHost(),
    ]);
});

// Public status endpoint (no auth required)
Route::get('/api/public/status', function () {
    return response()->json([
        'environment' => app()->environment(),
        'is_native' => \App\Helpers\ImageHelper::isNativeEnvironment(),
        'user_agent' => request()->userAgent(),
        'current_url' => request()->fullUrl(),
        'base_url' => \App\Helpers\ImageHelper::getBaseUrl(),
        'storage_url' => \App\Helpers\ImageHelper::getStorageBaseUrl(),
        'timestamp' => now()->toISOString(),
    ]);
});

// Public API route for images - bypasses CSRF and web middleware
Route::get('/api/images/{path}', function ($path) {
    try {
        return \App\Services\NativeImageService::serveImage($path);
    } catch (\Exception $e) {
        Log::error('API Image route error', [
            'path' => $path,
            'error' => $e->getMessage()
        ]);
        abort(404, 'Image not found');
    }
})->where('path', '.*')->name('api.image');

// Store public routes
Route::get('/store/{slug}', [StoreController::class, 'show'])->name('store.show');

// Custom storage route for NativePHP - serves files from storage/app/public
Route::get('/storage/{path}', function ($path) {
    // Debug: Write directly to a file
    file_put_contents(
        storage_path('debug_route.log'),
        "[" . date('Y-m-d H:i:s') . "] Route called with path: $path\n",
        FILE_APPEND
    );

    try {
        return \App\Services\NativeImageService::serveImage($path);
    } catch (\Exception $e) {
        // Fallback: write error to debug file
        file_put_contents(
            storage_path('debug_route.log'),
            "[" . date('Y-m-d H:i:s') . "] Exception: " . $e->getMessage() . "\n",
            FILE_APPEND
        );
        throw $e;
    }
})->where('path', '.*');

// Store management routes (vendor only)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/store/manage', [StoreController::class, 'manage'])->name('store.manage');
    Route::get('/store/products/create', [StoreController::class, 'createProduct'])->name('store.products.create');
    Route::post('/store/products', [StoreController::class, 'storeProduct'])->name('store.products.store');
    Route::get('/store/products/{product}/edit', [StoreController::class, 'editProduct'])->name('store.products.edit');
    Route::put('/store/products/{product}', [StoreController::class, 'updateProduct'])->name('store.products.update');
    Route::delete('/store/products/{product}', [StoreController::class, 'destroyProduct'])->name('store.products.destroy');
    Route::patch('/store/products/{product}/toggle-status', [StoreController::class, 'toggleProductStatus'])->name('store.products.toggle-status');

    // Sync routes (accessible via web middleware for CSRF)
    Route::middleware('web')->group(function () {
        Route::get('/api/sync', [SyncController::class, 'sync'])->name('sync.data');
        Route::post('/api/refresh', [SyncController::class, 'refresh'])->name('sync.refresh');
        Route::get('/api/status', [SyncController::class, 'status'])->name('sync.status');

        // Auth fix routes
        Route::get('/api/auth/status', [ForceAuthController::class, 'authStatus'])->name('auth.status');
        Route::post('/api/auth/force-logout', [ForceAuthController::class, 'forceLogout'])->name('auth.force-logout');
        Route::post('/api/auth/fix', [ForceAuthController::class, 'fixAuth'])->name('auth.fix');
    });
});

require __DIR__.'/auth.php';
