<?php

use App\Http\Controllers\ProfileController;
use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Volt::route('/parts', 'part-manager')->name('parts.index');
    Volt::route('/cars', 'car-manager')->name('cars.index');
    Volt::route('/orders', 'order-manager')->name('orders.index');
});

require __DIR__.'/auth.php';
