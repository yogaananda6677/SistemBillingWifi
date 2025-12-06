<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminTagihanController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PaketController;
// use App\Http\Controllers\DashboardSalesController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\PpnController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\Sales\DashboardSalesController;
use App\Http\Controllers\Sales\PelangganSalesController;
use App\Http\Controllers\Sales\PembayaranSalesController;
use App\Http\Controllers\Sales\PembukuanSalesController;
use App\Http\Controllers\Sales\SalesPengajuanController;
use App\Http\Controllers\Sales\SetoranSalesController;
use App\Http\Controllers\Sales\TagihanSalesController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SetoranAdminController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\TagihanPelangganSalesController;
use App\Models\Pelanggan;
use App\Models\Pengeluaran;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ===== ADMIN ROUTES =====
Route::middleware(['auth', 'admin'])->group(function () {
    // Dashboard admin

    Route::get('/dashboard/admin', [DashboardController::class, 'index'])
        ->name('dashboard-admin');
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
    Route::get('/admin/pengajuan', [PengajuanController::class, 'index'])
        ->name('admin.pengajuan.index');

    // routes/web.php
    Route::get('/pembukuan', [\App\Http\Controllers\PembukuanController::class, 'index'])
        ->name('pembukuan.index');
    Route::get('/pembukuan/{id}', [\App\Http\Controllers\PembukuanController::class, 'show'])
        ->name('pembukuan.show');

    // List semua (sales, area)
    Route::get('/setoran-sales', [SetoranAdminController::class, 'index'])
        ->name('admin.setoran.index');

    // Riwayat per sales-area
    Route::get('/setoran-sales/{id_sales}/{id_area}/riwayat', [SetoranAdminController::class, 'riwayat'])
        ->name('admin.setoran.riwayat');

    // Simpan setoran
    Route::post('/setoran-sales/store', [SetoranAdminController::class, 'store'])
        ->name('admin.setoran.store');

    // Edit setoran
    Route::get('/setoran-sales/{id_setoran}/edit', [SetoranAdminController::class, 'edit'])
        ->name('admin.setoran.edit');

    // Update setoran
    Route::put('/setoran-sales/{id_setoran}', [SetoranAdminController::class, 'update'])
        ->name('admin.setoran.update');

    // Hapus setoran
    Route::delete('/setoran-sales/{id_setoran}', [SetoranAdminController::class, 'destroy'])
        ->name('admin.setoran.destroy');

    Route::get('/laporan', [LaporanController::class, 'index'])
        ->name('laporan.index');

    // Export Excel (multi-sheet)
    Route::get('/laporan/export/excel', [LaporanController::class, 'exportExcel'])
        ->name('laporan.export.excel');

Route::get('/laporan/export/rekap-harian-bulanan', [LaporanController::class, 'exportRekapHarianBulanan'])
    ->name('laporan.exportRekapHarianBulanan');


Route::get('/laporan/export/rekap-keuangan', [LaporanController::class, 'exportRekapKeuangan'])
    ->name('laporan.exportRekapKeuangan');


    // Export PDF (opsional nanti)
    Route::get('/laporan/export/pdf', [LaporanController::class, 'exportPdf'])
        ->name('laporan.export.pdf');

    Route::get('/pengajuan/bukti/{pengajuan:id_pengeluaran}', function (Pengeluaran $pengajuan) {

        if (! $pengajuan->bukti_file) {
            abort(404, 'Bukti tidak ditemukan.');
        }

        $path = storage_path('app/public/'.$pengajuan->bukti_file);

        if (! file_exists($path)) {
            abort(404, 'File bukti tidak ditemukan.');
        }

        return response()->file($path);

    })->name('admin.pengajuan.bukti');

    Route::put('/pengeluaran/update-status/{id}',
        [App\Http\Controllers\PengajuanController::class, 'updateStatus']
    )->name('pengajuan.updateStatus');
    Route::resource('pengaturan/ppn', PpnController::class);
    Route::resource('pengaturan/area', AreaController::class);
    Route::resource('pengaturan/paket-layanan', PaketController::class);
    Route::resource('/tagihan', TagihanController::class);
    Route::resource('/pengaturan/profil', ProfilController::class);
    Route::resource('/pengaturan/admin', AdminController::class);

    Route::get('/pelanggan/list', [PelangganController::class, 'list'])->name('pelanggan.list');

});

// ===== SALES ROUTES =====
Route::middleware(['auth', 'sales'])->group(function () {
    // Dashboard sales
    Route::get('/dashboard/sales', [DashboardSalesController::class, 'index'])->name('dashboard-sales');
    // Resource tagihan pelanggan untuk sales
    Route::resource('tagihan-pelanggan', TagihanPelangganSalesController::class);
    // LIST PENGAJUAN SALES

    // LIST PENGAJUAN
    Route::get('/dashboard/sales/pengajuan', [SalesPengajuanController::class, 'index'])
        ->name('sales.pengajuan.index');

    // FORM TAMBAH
    Route::get('/dashboard/sales/pengajuan/create', [SalesPengajuanController::class, 'create'])
        ->name('sales.pengajuan.create');

    // SIMPAN PENGAJUAN BARU
    Route::post('/dashboard/sales/pengajuan', [SalesPengajuanController::class, 'store'])
        ->name('sales.pengajuan.store');

    // FORM EDIT
    Route::get('/dashboard/sales/pengajuan/{pengeluaran}/edit', [SalesPengajuanController::class, 'edit'])
        ->name('sales.pengajuan.edit');

    // UPDATE PENGAJUAN
    Route::put('/dashboard/sales/pengajuan/{pengeluaran}', [SalesPengajuanController::class, 'update'])
        ->name('sales.pengajuan.update');

    // HAPUS PENGAJUAN
    Route::delete('/dashboard/sales/pengajuan/{pengeluaran}', [SalesPengajuanController::class, 'destroy'])
        ->name('sales.pengajuan.destroy');
    Route::get('/sales/pengajuan/bukti/{pengeluaran}', [SalesPengajuanController::class, 'showBukti'])
        ->name('sales.pengajuan.bukti');

    Route::prefix('seles2')->name('seles2.')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | PELANGGAN (SALES)
        |--------------------------------------------------------------------------
        */
        Route::prefix('pelanggan')->name('pelanggan.')->group(function () {

            // LIST PELANGGAN SALES (MOBILE)
            Route::get('/', [PelangganSalesController::class, 'index'])
                ->name('index');

            // HALAMAN STATUS PELANGGAN (baru / aktif / berhenti / isolir)
            Route::get('/status', [PelangganSalesController::class, 'status'])
                ->name('status');

            // HALAMAN STATUS PEMBAYARAN (sudah / belum bayar)
            Route::get('/status-bayar', [PelangganSalesController::class, 'statusBayar'])
                ->name('statusBayar');

            // FILTER: BELUM BAYAR (VERSI LAMA BERDASARKAN KOLOM status_bayar)
            Route::get('/belum-bayar', function () {
                $pelanggan = Pelanggan::where('status_bayar', 'belum')
                    ->latest()
                    ->paginate(10);

                return view('seles2.pelanggan.belum-bayar', compact('pelanggan'));
            })->name('belum-bayar');

            // FILTER: SUDAH BAYAR (VERSI LAMA BERDASARKAN KOLOM status_bayar)
            Route::get('/sudah-bayar', function () {
                $pelanggan = Pelanggan::where('status_bayar', 'sudah')
                    ->latest()
                    ->paginate(10);

                return view('seles2.pelanggan.sudah-bayar', compact('pelanggan'));
            })->name('sudah-bayar');

            // DETAIL PELANGGAN SALES (MOBILE)
            Route::get('/{id}', [PelangganSalesController::class, 'show'])
                ->name('show');
        });

        /*
        |--------------------------------------------------------------------------
        | TAGIHAN (PEMBAYARAN) SALES – HALAMAN PEMBAYARAN
        |--------------------------------------------------------------------------
        */
        Route::get('/tagihan', [TagihanSalesController::class, 'index'])
            ->name('tagihan.index');

        Route::post('/tagihan/bayar-banyak', [TagihanSalesController::class, 'bayarBanyak'])
            ->name('tagihan.bayar-banyak');

        /*
    |----------------------------------------------------------------------
    | RIWAYAT PEMBAYARAN (SALES)
    |----------------------------------------------------------------------
    */
        Route::prefix('pembayaran')->name('pembayaran.')->group(function () {
            Route::get('/riwayat', [PembayaranSalesController::class, 'riwayat'])
                ->name('riwayat');
        });

        /*
        |--------------------------------------------------------------------------
        | PEMBUKUAN
        |--------------------------------------------------------------------------
        */
        Route::prefix('pembukuan')->name('pembukuan.')->group(function () {

            // PAKAI CONTROLLER, BUKAN CLOSURE LAGI
            Route::get('/', [PembukuanSalesController::class, 'index'])
                ->name('index');

            // kalau tetap mau halaman detail & pengajuan, biarkan yang lain:
            Route::get('/detail', fn () => view('seles2.pembukuan.detail'))
                ->name('detail');

            Route::prefix('pengajuan')->name('pengajuan.')->group(function () {
                Route::get('/', fn () => view('seles2.pembukuan.pengajuan.index'))
                    ->name('index');
                Route::get('/create', fn () => view('seles2.pembukuan.pengajuan.create'))
                    ->name('create');
                Route::get('/{id}', fn ($id) => view('seles2.pembukuan.pengajuan.show'))
                    ->name('show');
            });

            // --------- PENGAJUAN NESTED ----------
            Route::prefix('pengajuan')->name('pengajuan.')->group(function () {

                Route::get('/', fn () => view('seles2.pembukuan.pengajuan.index'))
                    ->name('index');

                Route::get('/create', fn () => view('seles2.pembukuan.pengajuan.create'))
                    ->name('create');

                Route::get('/{id}', fn ($id) => view('seles2.pembukuan.pengajuan.show'))
                    ->name('show');
            });
        });

        /*
        |--------------------------------------------------------------------------
        | SETORAN
        |--------------------------------------------------------------------------
        */

        // ...

        // SETORAN
        Route::get('/setoran', [SetoranSalesController::class, 'index'])
            ->name('setoran.index');

        // Kalau mau, riwayat bisa diarahkan ke halaman yang sama
        Route::get('/setoran/riwayat', [SetoranSalesController::class, 'index'])
            ->name('setoran.riwayat');

        /*
        |--------------------------------------------------------------------------
        | PROFILE
        |--------------------------------------------------------------------------
        */
        Route::get('/profile', fn () => view('seles2.profil.index'))
            ->name('profile');

        Route::get('/profile/edit', fn () => view('seles2.profile.edit'))
            ->name('profile.edit');

        Route::get('/profile/password', fn () => view('seles2.profile.password'))
            ->name('profile.password');
    });

});

Route::get('/install-superadmin', [SuperAdminController::class, 'install']);
