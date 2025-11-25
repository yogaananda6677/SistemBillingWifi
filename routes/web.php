<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PpnController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TagihanController;


Route::get('/', function () {
    return view('welcome');
});

// ===== ADMIN ROUTES =====
Route::middleware(['auth','admin'])->group(function () {
    // Dashboard admin
    // Route::get('/dashboard/admin', function(){
    //     return view('admin.dashboard');
    // })->name('dashboard-admin');

    Route::get('/dashboard/admin', [DashboardController::class, 'index'])->name('dashboard-admin');

    // Resource pelanggan untuk admin
    Route::resource('pelanggan', PelangganController::class);
// Halaman status pelanggan (menu terpisah)
    Route::get('/pelanggan-status', [PelangganController::class, 'status'])
        ->name('pelanggan.status');

    // Aksi status (sudah cocok dengan method di controller kamu)
    Route::patch('/pelanggan/{pelanggan}/aktivasi', [PelangganController::class, 'aktivasi'])
        ->name('pelanggan.aktivasi');
    Route::patch('/pelanggan/{pelanggan}/isolir', [PelangganController::class, 'isolir'])
        ->name('pelanggan.isolir');
    Route::patch('/pelanggan/{pelanggan}/buka-isolir', [PelangganController::class, 'bukaIsolir'])
        ->name('pelanggan.buka_isolir');
    Route::patch('/pelanggan/{pelanggan}/berhenti', [PelangganController::class, 'berhenti'])
        ->name('pelanggan.berhenti');
    Route::get('/get-sales-by-area/{id_area}', [SalesController::class, 'getSalesByArea']);




    Route::resource('sales/data-sales', SalesController::class);
    Route::get('/sales/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
    Route::put('/pengeluaran/update-status/{id}', 
        [App\Http\Controllers\PengajuanController::class, 'updateStatus']
    )->name('pengajuan.updateStatus');


    
    

    Route::resource('pengaturan/ppn', PpnController::class);
    Route::resource('pengaturan/area', AreaController::class);
    Route::resource('pengaturan/paket-layanan', PaketController::class);
    Route::resource('/tagihan', TagihanController::class);


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
