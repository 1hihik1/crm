<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Models\Service;
use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'services' => Service::orderBy('price')->get(),
    ]);
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Volt::route('/users', 'user-manager')->name('users.index');
    Volt::route('/rooms', 'room-manager')->name('rooms.index');
});

Route::middleware(['auth', 'role:admin|employee'])->group(function () {
    Volt::route('/parts', 'parts-manager')->name('parts.index');
    Volt::route('/services', 'service-manager')->name('services.index');
    Volt::route('/purchases', 'purchase-manager')->name('purchases.index');
});

Route::middleware(['auth', 'verified', 'role:client'])->group(function () {
    Volt::route('/wallet/topup', 'wallet-topup')->name('wallet.topup');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Volt::route('/cars', 'car-manager')->name('cars.index');
    Volt::route('/orders', 'order-list')->name('orders.index');
    Volt::route('/orders/{id}', 'order-detail')->name('orders.detail');
});

require __DIR__.'/auth.php';
