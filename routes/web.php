<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminTagihanController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\PpnController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\TagihanPelangganSalesController;
use Illuminate\Support\Facades\Route;
use App\Models\Pelanggan;
use App\Http\Controllers\Sales\PelangganSalesController;
use App\Http\Controllers\Sales\DashboardSalesController;
use App\Http\Controllers\Sales\TagihanSalesController;
use App\Http\Controllers\Sales\PembayaranSalesController;
use App\Http\Controllers\Sales\SalesPengajuanController;
use App\Models\Pengeluaran;
use App\Http\Controllers\PembukuanController;

Route::get('/', function () {
    return view('welcome');
});


// ===============================
// ======== ADMIN ROUTES =========
// ===============================
Route::middleware(['auth', 'admin'])->group(function () {

    // Dashboard admin
    Route::get('/dashboard/admin', [DashboardController::class, 'index'])
        ->name('dashboard-admin');

    // Resource pelanggan untuk admin
    Route::resource('pelanggan', PelangganController::class);

    // Halaman status pelanggan (menu terpisah)
    Route::get('/pelanggan-status', [PelangganController::class, 'status'])
        ->name('pelanggan.status');

    // Aksi status
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

    // Halaman list tagihan belum lunas (pembayaran oleh admin)
    Route::get('/admin/tagihan', [AdminTagihanController::class, 'index'])
        ->name('admin.tagihan.index');

    // Bayar banyak tagihan sekaligus
    Route::post('/admin/tagihan/bayar-banyak', [AdminTagihanController::class, 'bayarBanyak'])
        ->name('admin.tagihan.bayar-banyak');

    // Pembayaran (admin)
    Route::get('/pembayaran/riwayat', [PembayaranController::class, 'riwayat'])
        ->name('pembayaran.riwayat');

    Route::delete('/pembayaran/item/{id}', [PembayaranController::class, 'hapusItem'])
        ->name('pembayaran.item.destroy');
    Route::delete('/pembayaran/item-bulk', [PembayaranController::class, 'hapusItemBulk'])
        ->name('pembayaran.item.bulkDestroy');

    // Master sales (data sales)
    Route::resource('sales/data-sales', SalesController::class);

    // Pengajuan (admin)
    Route::get('/admin/pengajuan', [PengajuanController::class, 'index'])
        ->name('admin.pengajuan.index');

    // Pembukuan (admin)
    Route::get('/pembukuan', [PembukuanController::class, 'index'])
        ->name('pembukuan.index');
    Route::get('/pembukuan/{sales}', [PembukuanController::class, 'show'])
        ->name('pembukuan.show');

    // Lihat bukti pengajuan (admin)
    Route::get('/pengajuan/bukti/{pengajuan:id_pengeluaran}', function (Pengeluaran $pengajuan) {

        if (!$pengajuan->bukti_file) {
            abort(404, 'Bukti tidak ditemukan.');
        }

        $path = storage_path('app/public/' . $pengajuan->bukti_file);

        if (!file_exists($path)) {
            abort(404, 'File bukti tidak ditemukan.');
        }

        return response()->file($path);

    })->name('admin.pengajuan.bukti');

    // Update status pengeluaran
    Route::put('/pengeluaran/update-status/{id}', [PengajuanController::class, 'updateStatus'])
        ->name('pengajuan.updateStatus');

    // Pengaturan
    Route::resource('pengaturan/ppn', PpnController::class);
    Route::resource('pengaturan/area', AreaController::class);
    Route::resource('pengaturan/paket-layanan', PaketController::class);
    Route::resource('/tagihan', TagihanController::class);
    Route::resource('/pengaturan/profil', ProfilController::class);
    Route::resource('/pengaturan/admin', AdminController::class);

});


// ===============================
// ========= SALES ROUTES ========
// ===============================
// Semua route sales sekarang otomatis berprefix:
// - URL:   /sales/...
// - Name:  sales....
Route::middleware(['auth', 'sales'])
    ->prefix('sales')
    ->name('sales.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD SALES
        |--------------------------------------------------------------------------
        */
        Route::get('/dashboard', [DashboardSalesController::class, 'index'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | TAGIHAN PELANGGAN (SALES)
        |--------------------------------------------------------------------------
        */
        Route::resource('tagihan-pelanggan', TagihanPelangganSalesController::class);

        /*
        |--------------------------------------------------------------------------
        | PENGAJUAN SALES (FORM & LIST)
        |--------------------------------------------------------------------------
        */
        Route::get('/pengajuan', [SalesPengajuanController::class, 'index'])
            ->name('pengajuan.index');

        Route::get('/pengajuan/create', [SalesPengajuanController::class, 'create'])
            ->name('pengajuan.create');

        Route::post('/pengajuan', [SalesPengajuanController::class, 'store'])
            ->name('pengajuan.store');

        Route::get('/pengajuan/{pengeluaran}/edit', [SalesPengajuanController::class, 'edit'])
            ->name('pengajuan.edit');

        Route::put('/pengajuan/{pengeluaran}', [SalesPengajuanController::class, 'update'])
            ->name('pengajuan.update');

        Route::delete('/pengajuan/{pengeluaran}', [SalesPengajuanController::class, 'destroy'])
            ->name('pengajuan.destroy');

        Route::get('/pengajuan/bukti/{pengeluaran}', [SalesPengajuanController::class, 'showBukti'])
            ->name('pengajuan.bukti');


        /*
        |--------------------------------------------------------------------------
        | SALES2 (MOBILE VIEW) - PELANGGAN & STATUS
        |--------------------------------------------------------------------------
        | URL:  /sales/seles2/...
        | Name: sales.seles2....
        */
        Route::prefix('seles2')->name('seles2.')->group(function () {

            // Halaman statis blade
            Route::get('/pelanggan', fn () => view('seles2.pelanggan.index'))
                ->name('pelanggan.index');
            Route::get('/pelanggan/{id}', fn ($id) => view('seles2.pelanggan.show'))
                ->name('pelanggan.show');
            Route::get('/pelanggan/belum-bayar', fn () => view('seles2.pelanggan.belum-bayar'))
                ->name('pelanggan.belum-bayar');
            Route::get('/pelanggan/sudah-bayar', fn () => view('seles2.pelanggan.sudah-bayar'))
                ->name('pelanggan.sudah-bayar');
            Route::get('/pelanggan/baru', fn () => view('seles2.pelanggan.pelanggan-baru'))
                ->name('pelanggan.baru');
            Route::get('/pelanggan/berhenti', fn () => view('seles2.pelanggan.berhenti'))
                ->name('pelanggan.berhenti');
            Route::get('/pelanggan/create', fn () => view('seles2.pelanggan.create'))
                ->name('pelanggan.create');

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
            Route::get('/detail/{id}', [PelangganSalesController::class, 'show'])
                ->name('show');
        });


        /*
        |--------------------------------------------------------------------------
        | TAGIHAN (PEMBAYARAN) SALES – HALAMAN PEMBAYARAN
        |--------------------------------------------------------------------------
        | URL:  /sales/tagihan
        | Name: sales.tagihan.index
        */
        Route::get('/tagihan', [TagihanSalesController::class, 'index'])
            ->name('tagihan.index');

        Route::post('/tagihan/bayar-banyak', [TagihanSalesController::class, 'bayarBanyak'])
            ->name('tagihan.bayar-banyak');


        /*
        |--------------------------------------------------------------------------
        | RIWAYAT PEMBAYARAN (SALES)
        |--------------------------------------------------------------------------
        | URL:  /sales/pembayaran/riwayat
        | Name: sales.pembayaran.riwayat
        */
        Route::prefix('pembayaran')->name('pembayaran.')->group(function () {
            Route::get('/riwayat', [PembayaranSalesController::class, 'riwayat'])
                ->name('riwayat');
        });


        /*
        |--------------------------------------------------------------------------
        | PEMBUKUAN (SALES)
        |--------------------------------------------------------------------------
        | URL:  /sales/pembukuan/...
        | Name: sales.pembukuan....
        */
        Route::prefix('pembukuan')->name('pembukuan.')->group(function () {

            Route::get('/', fn () => view('seles2.pembukuan.index'))
                ->name('index');

            Route::get('/detail', fn () => view('seles2.pembukuan.detail'))
                ->name('detail');

            // --------- PENGAJUAN NESTED (SALES PEMBUKUAN) ----------
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
        | SETORAN (SALES)
        |--------------------------------------------------------------------------
        | URL:  /sales/setoran, /sales/setoran/riwayat
        | Name: sales.setoran.index, sales.setoran.riwayat
        */
        Route::get('/setoran', fn () => view('seles2.setoran.index'))
            ->name('setoran.index');

        Route::get('/setoran/riwayat', fn () => view('seles2.setoran.riwayat'))
            ->name('setoran.riwayat');


        /*
        |--------------------------------------------------------------------------
        | PROFILE (SALES)
        |--------------------------------------------------------------------------
        | URL:  /sales/profile, /sales/profile/edit, /sales/profile/password
        | Name: sales.profile, sales.profile.edit, sales.profile.password
        */
        Route::get('/profile', fn () => view('seles2.profile.index'))
            ->name('profile');

        Route::get('/profile/edit', fn () => view('seles2.profile.edit'))
            ->name('profile.edit');

        Route::get('/profile/password', fn () => view('seles2.profile.password'))
            ->name('profile.password');

    });
