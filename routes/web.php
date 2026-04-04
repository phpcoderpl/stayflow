<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\PropertyController;
use App\Http\Controllers\Web\SeasonalPriceController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

// Redirect root
Route::get('/', fn() => redirect('/login'));

// Admin routes
Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/properties', [PropertyController::class, 'index']);
    Route::get('/properties/create', [PropertyController::class, 'create']);
    Route::post('/properties', [PropertyController::class, 'store']);
    Route::get('/properties/{property}/edit', [PropertyController::class, 'edit']);
    Route::put('/properties/{property}', [PropertyController::class, 'update']);
    Route::delete('/properties/{property}', [PropertyController::class, 'destroy']);

    Route::post('/properties/{property}/photos', [PropertyController::class, 'uploadPhotos']);
    Route::delete('/properties/{property}/photos/{photo}', [PropertyController::class, 'deletePhoto']);
    Route::post('/properties/{property}/photos/{photo}/cover', [PropertyController::class, 'setCoverPhoto']);
    Route::post('/properties/{property}/photos/reorder', [PropertyController::class, 'reorderPhotos']);

    Route::post('/properties/{property}/seasonal-prices', [SeasonalPriceController::class, 'store']);
    Route::delete('/properties/{property}/seasonal-prices/{seasonalPrice}', [SeasonalPriceController::class, 'destroy']);
});
