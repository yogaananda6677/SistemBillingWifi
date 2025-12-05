<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminTagihanController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardSalesController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\PpnController;
<<<<<<< HEAD
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\TagihanPelangganSalesController;
=======
use App\Http\Controllers\Sales\DashboardSalesController;
use App\Http\Controllers\Sales\PelangganSalesController;
use App\Http\Controllers\Sales\PembayaranSalesController;
use App\Http\Controllers\Sales\TagihanSalesController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\TagihanPelangganSalesController;
use App\Models\Pelanggan;
>>>>>>> fitur2
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ===== ADMIN ROUTES =====
Route::middleware(['auth', 'admin'])->group(function () {
    // Dashboard admin
<<<<<<< HEAD
    Route::get('/dashboard/admin', [DashboardController::class, 'index'])->name('dashboard-admin');
=======

    Route::get('/dashboard/admin', [DashboardController::class, 'index'])
        ->name('dashboard-admin');
>>>>>>> fitur2

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
<<<<<<< HEAD
    Route::resource('/pengaturan/profil', ProfilController::class);
    Route::resource('/pengaturan/admin', AdminController::class);
=======

    Route::get('/pelanggan/list', [PelangganController::class, 'list'])->name('pelanggan.list');
>>>>>>> fitur2

});

// ===== SALES ROUTES =====
Route::middleware(['auth', 'sales'])->group(function () {
    // Dashboard sales
    Route::get('/dashboard/sales', [DashboardSalesController::class, 'index'])->name('dashboard-sales');
    // Resource tagihan pelanggan untuk sales
    Route::resource('tagihan-pelanggan', TagihanPelangganSalesController::class);

    Route::prefix('seles2')->name('seles2.')->group(function () {

<<<<<<< HEAD
// ========== SALES ROUTES ==========
Route::prefix('seles2')->name('seles2.')->group(function () {

    // ================== PELANGGAN ==================
    Route::get('/pelanggan', fn () => view('seles2.pelanggan.index'))->name('pelanggan.index');
    Route::get('/pelanggan/{id}', fn ($id) => view('seles2.pelanggan.show'))->name('pelanggan.show');
    Route::get('/pelanggan/belum-bayar', fn () => view('seles2.pelanggan.belum-bayar'))->name('pelanggan.belum-bayar');
    Route::get('/pelanggan/sudah-bayar', fn () => view('seles2.pelanggan.sudah-bayar'))->name('pelanggan.sudah-bayar');
    Route::get('/pelanggan/baru', fn () => view('seles2.pelanggan.pelanggan-baru'))->name('pelanggan.baru');
    Route::get('/pelanggan/berhenti', fn () => view('seles2.pelanggan.berhenti'))->name('pelanggan.berhenti');
    Route::get('/pelanggan/create', fn () => view('seles2.pelanggan.create'))->name('pelanggan.create');

    // ================== PEMBUKUAN ==================
    Route::prefix('pembukuan')->name('pembukuan.')->group(function () {

        Route::get('/', fn () => view('seles2.pembukuan.index'))->name('index');
        Route::get('/detail', fn () => view('seles2.pembukuan.detail'))->name('detail');

        // --------- PENGAJUAN NESTED ----------
        Route::prefix('pengajuan')->name('pengajuan.')->group(function () {

            Route::get('/', fn () => view('seles2.pembukuan.pengajuan.index'))->name('index');
            Route::get('/create', fn () => view('seles2.pembukuan.pengajuan.create'))->name('create');
            Route::get('/{id}', fn ($id) => view('seles2.pembukuan.pengajuan.show'))->name('show');

        });

    });

    // ================= SETORAN =================
    Route::get('/setoran', fn () => view('seles2.setoran.index'))->name('setoran.index');
    Route::get('/setoran/riwayat', fn () => view('seles2.setoran.riwayat'))->name('setoran.riwayat');

    // ================= PROFILE =================
    Route::get('/profile', fn () => view('seles2.profile.index'))->name('profile');
    Route::get('/profile/edit', fn () => view('seles2.profile.edit'))->name('profile.edit');
    Route::get('/profile/password', fn () => view('seles2.profile.password'))->name('profile.password');

=======
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

            Route::get('/', fn () => view('seles2.pembukuan.index'))
                ->name('index');

            Route::get('/detail', fn () => view('seles2.pembukuan.detail'))
                ->name('detail');

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
        Route::get('/setoran', fn () => view('seles2.setoran.index'))
            ->name('setoran.index');

        Route::get('/setoran/riwayat', fn () => view('seles2.setoran.riwayat'))
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

>>>>>>> fitur2
});
