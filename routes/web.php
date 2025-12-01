<?php

use App\Http\Controllers\AdminTagihanController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\PpnController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\TagihanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ===== ADMIN ROUTES =====
Route::middleware(['auth', 'admin'])->group(function () {
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
    Route::delete('/tagihan/hapus-pelanggan', [TagihanController::class, 'hapusTagihanPelanggan'])
        ->name('tagihan.hapus-pelanggan');

    // halaman list tagihan belum lunas (pembayaran oleh admin)
    Route::get('/admin/tagihan', [AdminTagihanController::class, 'index'])
        ->name('admin.tagihan.index');

    // bayar satu tagihan
    Route::post('/admin/tagihan/{tagihan}/bayar', [AdminTagihanController::class, 'bayarSatu'])
        ->name('admin.tagihan.bayar-satu');

    // bayar banyak tagihan sekaligus
    Route::post('/admin/tagihan/bayar-banyak', [AdminTagihanController::class, 'bayarBanyak'])
        ->name('admin.tagihan.bayar-banyak');

    Route::get('/pembayaran/riwayat', [PembayaranController::class, 'riwayat'])
        ->name('pembayaran.riwayat');

    Route::delete('/pembayaran/item/{id}', [PembayaranController::class, 'hapusItem'])
        ->name('pembayaran.item.destroy');
    Route::delete('/pembayaran/item-bulk', [PembayaranController::class, 'hapusItemBulk'])
        ->name('pembayaran.item.bulkDestroy');

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
Route::middleware(['auth', 'sales'])->group(function () {
    // Dashboard sales
    Route::get('/dashboard/sales', function () {
        return view('sales.dashboard');
    })->name('dashboard-sales');

    // Kalau sales juga butuh akses resource pelanggan, bisa diaktifkan
    // Route::resource('pelanggan', PelangganController::class);
});
