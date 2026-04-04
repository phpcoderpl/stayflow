<?php

use App\Http\Controllers\Public\GuestPortalController;
use App\Http\Controllers\Public\PublicBookingController;
use App\Http\Controllers\Public\PublicPropertyController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BlockedDateController;
use App\Http\Controllers\Web\BookingController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\GuestController;
use App\Http\Controllers\Web\PayUController;
use App\Http\Controllers\Web\PropertyController;
use App\Http\Controllers\Web\SeasonalPriceController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\ICalController;
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
Route::get('/', fn() => redirect('/properties'));

// Public property pages (no auth)
Route::get('/properties', [PublicPropertyController::class, 'index']);
Route::get('/properties/{slug}', [PublicPropertyController::class, 'show']);

// Booking flow (no auth)
Route::post('/bookings/check-availability', [PublicBookingController::class, 'checkAvailability']);
Route::post('/bookings', [PublicBookingController::class, 'store']);
Route::get('/booking/{confirmationCode}/pay', [PublicBookingController::class, 'pay']);
Route::get('/booking/{confirmationCode}/confirmation', [PublicBookingController::class, 'confirmation']);

// Guest portal (no auth)
Route::get('/guest/{token}', [GuestPortalController::class, 'show']);

// PayU (no auth, CSRF exempt for notify)
Route::post('/payu/checkout', [PayUController::class, 'checkout']);
Route::post('/payu/notify', [PayUController::class, 'notify']);

// iCal feeds (no auth)
Route::get('/ical/{token}.ics', [ICalController::class, 'export']);
Route::get('/booking/{confirmationCode}/calendar.ics', [ICalController::class, 'guestIcs']);

// Public API
Route::get('/api/properties/{propertyId}/availability', [AvailabilityController::class, 'index']);

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

    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/calendar', [BookingController::class, 'calendar']);
    Route::get('/bookings/create', [BookingController::class, 'create']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus']);

    Route::get('/guests', [GuestController::class, 'index']);
    Route::get('/guests/{guest}', [GuestController::class, 'show']);

    Route::post('/blocked-dates', [BlockedDateController::class, 'store']);
    Route::delete('/blocked-dates/{blockedDate}', [BlockedDateController::class, 'destroy']);

    Route::get('/settings', [SettingsController::class, 'index']);
    Route::put('/settings', [SettingsController::class, 'update']);
    Route::post('/settings/calendar-sync', [SettingsController::class, 'createCalendarSync']);
    Route::delete('/settings/calendar-sync/{calendarSync}', [SettingsController::class, 'deleteCalendarSync']);
    Route::put('/settings/email-templates/{emailTemplate}', [SettingsController::class, 'updateEmailTemplate']);
});
