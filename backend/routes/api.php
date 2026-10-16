<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Modules\Category\Controller\Category;

Route::get('/health', function () {
    try {
        DB::select('SELECT 1');
        Redis::ping();
        return response()->json(['success' => true, 'status' => 'ok', 'services' => ['postgres' => 'ok', 'redis' => 'ok']]);
    } catch (Throwable $e) {
        report($e);
        return response()->json(['success' => false, 'status' => 'degraded'], 503);
    }
});

Route::get('/categories', [Category::class, 'index']);
Route::get('/categories/{slug}', [Category::class, 'show'])->where('slug', '[A-Za-z0-9-]+');

// Register feature route files here when their controllers exist:
// require __DIR__.'/auth/login.php';
// Route::prefix('shop')->group(function () {
//     require __DIR__.'/frontend/products.php';
// });
// Route::middleware(['auth:api'])->prefix('backend')->group(function () {
//     require __DIR__.'/backend/products.php';
// });
