<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PpnController;
use App\Http\Controllers\AreaController;

Route::get('/', function () {
    return view('welcome');
});

// ===== ADMIN ROUTES =====
Route::middleware(['auth','admin'])->group(function () {
    // Dashboard admin
    Route::get('/dashboard/admin', function(){
        return view('admin.dashboard');
    })->name('dashboard-admin');

    // Resource pelanggan untuk admin
    Route::resource('pelanggan', PelangganController::class);
    Route::resource('pengaturan/ppn', PpnController::class);
    Route::resource('pengaturan/area', AreaController::class);

    Route::get('/pelanggan/list', [PelangganController::class, 'list'])->name('pelanggan.list');
});

// ===== SALES ROUTES =====
Route::middleware(['auth','sales'])->group(function () {
    // Dashboard sales
    Route::get('/dashboard/sales', function(){
        return view('sales.dashboard');
    })->name('dashboard-sales');

    // Kalau sales juga butuh akses resource pelanggan, bisa diaktifkan
    // Route::resource('pelanggan', PelangganController::class);
});
