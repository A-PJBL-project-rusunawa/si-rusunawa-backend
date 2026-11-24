<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\FacilityController;

// ==========================================
// AUTENTIKASI TANPA LOGIN
// ==========================================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

// INFORMASI KAMAR (PUBLIK)
Route::get('/rooms', [RoomController::class, 'index']);
Route::get('/rooms/{id}', [RoomController::class, 'show']);

// INFORMASI FASILITAS (PUBLIK)
Route::get('/rooms/{roomId}/facilities', [
    FacilityController::class,
    'index'
]);

// ==========================================
// ROUTE YANG MEMBUTUHKAN LOGIN
// ==========================================

Route::middleware('auth:sanctum')->group(function () {

    // Profil pengguna
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);


    // ======================================
    // BOOKING MAHASISWA
    // ======================================

    Route::post('/bookings', [BookingController::class, 'store']);

    Route::get('/my-bookings', [
        BookingController::class,
        'myBookings'
    ]);

// Detail pemesanan milik pengguna
Route::get('/bookings/{id}', [
    BookingController::class,
    'show'
]);

// Batalkan pemesanan milik pengguna
Route::delete('/bookings/{id}', [
    BookingController::class,
    'cancel'
]);

    // ======================================
    // ROUTE KHUSUS ADMIN
    // ======================================

    Route::middleware('admin')->prefix('admin')->group(function () {

        // Booking
        Route::get('/bookings', [
            BookingController::class,
            'index'
        ]);

// Detail pemesanan untuk admin
Route::get('/bookings/{id}', [
    BookingController::class,
    'adminShow'
]);
        Route::patch('/bookings/{id}/status', [
            BookingController::class,
            'updateStatus'
        ]);

        // Check-in dan check-out penghuni
        Route::post('/bookings/{id}/check-in', [
            BookingController::class,
            'checkIn'
        ]);

        Route::post('/bookings/{id}/check-out', [
            BookingController::class,
            'checkOut'
        ]);


        // Manajemen kamar
        Route::post('/rooms', [
            RoomController::class,
            'store'
        ]);

        Route::put('/rooms/{id}', [
            RoomController::class,
            'update'
        ]);

        Route::delete('/rooms/{id}', [
            RoomController::class,
            'destroy'
        ]);


        // Manajemen fasilitas
        Route::post('/rooms/{roomId}/facilities', [
            FacilityController::class,
            'store'
        ]);

        Route::patch('/facilities/{id}', [
            FacilityController::class,
            'update'
        ]);

        Route::delete('/facilities/{id}', [
            FacilityController::class,
            'destroy'
        ]);

    });

});
