<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/bookings', [BookingController::class,'index'])->name('bookings.index');

    // AJAX JSON endpoints
    Route::get('/api/bookings', [BookingController::class,'list']);
    Route::get('/api/bookings/mine', [BookingController::class,'mine']);
    Route::post('/api/bookings', [BookingController::class,'store']);
    Route::put('/api/bookings/{booking}', [BookingController::class,'update']);
    Route::delete('/api/bookings/{booking}', [BookingController::class,'destroy']);
});

require __DIR__.'/auth.php';
