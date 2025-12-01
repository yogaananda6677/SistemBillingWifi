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
use App\Http\Controllers\AdminTagihanController;
use App\Http\Controllers\PembayaranController;



// PUBLIC ROOT
Route::get('/', function () {
    return view('welcome');
});

// ===== ADMIN ROUTES =====
Route::middleware(['auth','admin'])->group(function () {

    Route::get('/dashboard/admin', [DashboardController::class, 'index'])->name('dashboard-admin');

    Route::resource('pelanggan', PelangganController::class);
    Route::resource('sales/data-sales', SalesController::class);

    Route::get('/sales/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
    Route::put('/pengeluaran/update-status/{id}', 
        [PengajuanController::class, 'updateStatus']
    )->name('pengajuan.updateStatus');

    Route::resource('pengaturan/ppn', PpnController::class);
    Route::resource('pengaturan/area', AreaController::class);
    Route::resource('pengaturan/paket-layanan', PaketController::class);
    Route::resource('/tagihan', TagihanController::class);

    Route::get('/pelanggan/list', [PelangganController::class, 'list'])->name('pelanggan.list');
});


// DEFAULT PAGE → DASHBOARD SALES
Route::get('/', function () {
    return view('seles2.dashboard.index');
})->name('welcome');


// ========== SALES ROUTES ==========
Route::prefix('seles2')->name('seles2.')->group(function () {

    // DASHBOARD
    Route::get('/dashboard', function () {
        return view('seles2.dashboard.index');
    })->name('dashboard');


    // ================== PELANGGAN ==================
    Route::get('/pelanggan', fn() => view('seles2.pelanggan.index'))->name('pelanggan.index');
    Route::get('/pelanggan/{id}', fn($id) => view('seles2.pelanggan.show'))->name('pelanggan.show');
    Route::get('/pelanggan/belum-bayar', fn() => view('seles2.pelanggan.belum-bayar'))->name('pelanggan.belum-bayar');
    Route::get('/pelanggan/sudah-bayar', fn() => view('seles2.pelanggan.sudah-bayar'))->name('pelanggan.sudah-bayar');
    Route::get('/pelanggan/baru', fn() => view('seles2.pelanggan.pelanggan-baru'))->name('pelanggan.baru');
    Route::get('/pelanggan/berhenti', fn() => view('seles2.pelanggan.berhenti'))->name('pelanggan.berhenti');
    Route::get('/pelanggan/create', fn() => view('seles2.pelanggan.create'))->name('pelanggan.create');



    // ================== PEMBUKUAN ==================
    Route::prefix('pembukuan')->name('pembukuan.')->group(function () {

        Route::get('/', fn() => view('seles2.pembukuan.index'))->name('index');
        Route::get('/detail', fn() => view('seles2.pembukuan.detail'))->name('detail');

        // --------- PENGAJUAN NESTED ----------
        Route::prefix('pengajuan')->name('pengajuan.')->group(function () {

            Route::get('/', fn() => view('seles2.pembukuan.pengajuan.index'))->name('index');
            Route::get('/create', fn() => view('seles2.pembukuan.pengajuan.create'))->name('create');
            Route::get('/{id}', fn($id) => view('seles2.pembukuan.pengajuan.show'))->name('show');

        });

    });


    // ================= SETORAN =================
    Route::get('/setoran', fn() => view('seles2.setoran.index'))->name('setoran.index');
    Route::get('/setoran/riwayat', fn() => view('seles2.setoran.riwayat'))->name('setoran.riwayat');


    // ================= PROFILE =================
    Route::get('/profile', fn() => view('seles2.profile.index'))->name('profile');
    Route::get('/profile/edit', fn() => view('seles2.profile.edit'))->name('profile.edit');
    Route::get('/profile/password', fn() => view('seles2.profile.password'))->name('profile.password');

});
